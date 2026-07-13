<?php

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

function gzca_normalize_email($email)
{
  return strtolower(trim((string)$email));
}

function gzca_email_fingerprint($email)
{
  $email = gzca_normalize_email($email);
  return '' === $email ? '' : hash('sha256', $email);
}

function gzca_mask_email($email)
{
  $email = gzca_normalize_email($email);
  $separator = strrpos($email, '@');
  if (false === $separator)
  {
    return '';
  }

  $local = substr($email, 0, $separator);
  $domain = substr($email, $separator + 1);
  if ('' === $local || '' === $domain)
  {
    return '';
  }

  $visible = substr($local, 0, min(2, strlen($local)));
  return $visible.'***@'.$domain;
}

function gzca_admin_security_record($user_id)
{
  $user_id = (int)$user_id;
  if ($user_id <= 0 || !gzca_database_table_exists(GZCA_ADMIN_SECURITY_TABLE))
  {
    return null;
  }

  $result = pwg_query('
SELECT user_id, verified_email_hash, email_verified_at, password_changed_at, sessions_revoked_before, updated_at
  FROM '.GZCA_ADMIN_SECURITY_TABLE.'
  WHERE user_id = '.$user_id.'
  LIMIT 1
;');

  return pwg_db_num_rows($result) > 0 ? pwg_db_fetch_assoc($result) : null;
}

function gzca_admin_session_revoked_before($user_id)
{
  $record = gzca_admin_security_record((int)$user_id);
  if (!is_array($record) || empty($record['sessions_revoked_before']))
  {
    return 0;
  }

  $timestamp = strtotime((string)$record['sessions_revoked_before']);
  return false === $timestamp ? 0 : (int)$timestamp;
}

function gzca_admin_email_security_status($user_id=null)
{
  global $user;

  $user_id = null === $user_id ? (int)$user['id'] : (int)$user_id;
  $account = $user_id > 0 ? getuserdata($user_id, false) : array();
  $email = isset($account['email']) ? gzca_normalize_email($account['email']) : '';
  $storage_ready = gzca_database_table_exists(GZCA_ADMIN_SECURITY_TABLE);
  $record = $storage_ready ? gzca_admin_security_record($user_id) : null;
  $fingerprint = gzca_email_fingerprint($email);
  $verified = '' !== $fingerprint
    && is_array($record)
    && !empty($record['verified_email_hash'])
    && !empty($record['email_verified_at'])
    && hash_equals((string)$record['verified_email_hash'], $fingerprint);

  return array(
    'user_id' => $user_id,
    'email' => $email,
    'masked_email' => $verified ? gzca_mask_email($email) : '',
    'verified' => $verified,
    'verified_at' => $verified ? (string)$record['email_verified_at'] : '',
    'storage_ready' => $storage_ready,
    'requires_binding' => $storage_ready && !$verified,
    );
}

function gzca_redirect_admin_profile_to_security()
{
  global $user;

  if (gzca_admin_account_is_allowed($user))
  {
    redirect(gzca_admin_url('security'));
  }
}

function gzca_block_admin_security_ws_bypass($allowed, $method_name, $params)
{
  global $user;

  if ($allowed instanceof PwgError || !gzca_admin_account_is_allowed($user))
  {
    return $allowed;
  }

  $blocked_methods = array(
    'pwg.users.setMyInfo',
    'pwg.users.setInfo',
    'pwg.users.generatePasswordLink',
    );
  if (in_array((string)$method_name, $blocked_methods, true))
  {
    return new PwgError(403, '管理员邮箱和密码只能在客户后台“账号安全”中完成验证后修改。');
  }

  return $allowed;
}

function gzca_security_mail_status()
{
  global $conf;

  $smtp_host = isset($conf['smtp_host']) ? trim((string)$conf['smtp_host']) : '';
  $smtp_user = isset($conf['smtp_user']) ? trim((string)$conf['smtp_user']) : '';
  $smtp_password = isset($conf['smtp_password']) ? (string)$conf['smtp_password'] : '';
  $sender_email = gzca_normalize_email(isset($conf['mail_sender_email']) ? $conf['mail_sender_email'] : '');
  $ready = '' !== $smtp_host
    && '' !== $smtp_user
    && '' !== $smtp_password
    && '' !== $sender_email
    && false !== filter_var($sender_email, FILTER_VALIDATE_EMAIL);

  return array(
    'ready' => $ready,
    'message' => $ready
      ? '系统发件服务已就绪，验证码会由独立服务邮箱发送。'
      : '系统发件邮箱尚未完成服务器私有配置，暂时不能发送验证码。请先配置独立 SMTP 发件账号。',
    );
}

function gzca_validate_admin_email($user_id, $email, &$error='')
{
  global $conf;

  $user_id = (int)$user_id;
  $email = gzca_normalize_email($email);
  if ('' === $email || false === filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email))
  {
    $error = '请输入有效的邮箱地址。';
    return false;
  }
  if (strlen($email) > 255)
  {
    $error = '邮箱地址过长，请更换后重试。';
    return false;
  }

  $escaped_email = pwg_db_real_escape_string($email);
  $query = '
SELECT COUNT(*)
  FROM '.USERS_TABLE.'
  WHERE UPPER('.$conf['user_fields']['email'].') = UPPER(\''.$escaped_email.'\')
    AND '.$conf['user_fields']['id'].' != '.$user_id.'
;';
  list($count) = pwg_db_fetch_row(pwg_query($query));
  if ((int)$count > 0)
  {
    $error = '该邮箱已经绑定其他账号。';
    return false;
  }

  return true;
}

function gzca_verify_current_password($user_id, $password)
{
  global $conf;

  if ('' === (string)$password)
  {
    return false;
  }

  $query = '
SELECT '.$conf['user_fields']['password'].' AS password
  FROM '.USERS_TABLE.'
  WHERE '.$conf['user_fields']['id'].' = '.(int)$user_id.'
  LIMIT 1
;';
  $result = pwg_query($query);
  if (0 === pwg_db_num_rows($result))
  {
    return false;
  }

  $row = pwg_db_fetch_assoc($result);
  return $conf['password_verify']((string)$password, (string)$row['password']);
}

function gzca_security_challenge($purpose)
{
  $purpose = (string)$purpose;
  if (!isset($_SESSION['gzca_security_challenges']) || !is_array($_SESSION['gzca_security_challenges']))
  {
    return null;
  }

  $challenge = isset($_SESSION['gzca_security_challenges'][$purpose])
    ? $_SESSION['gzca_security_challenges'][$purpose]
    : null;
  if (!is_array($challenge))
  {
    return null;
  }
  if (empty($challenge['expires_at']) || (int)$challenge['expires_at'] <= time())
  {
    unset($_SESSION['gzca_security_challenges'][$purpose]);
    return null;
  }

  return $challenge;
}

function gzca_security_challenge_status($purpose)
{
  $challenge = gzca_security_challenge($purpose);
  if (!is_array($challenge))
  {
    return array('active' => false, 'expires_at' => '', 'cooldown_seconds' => 0);
  }

  return array(
    'active' => true,
    'expires_at' => date('Y-m-d H:i', (int)$challenge['expires_at']),
    'cooldown_seconds' => max(0, (int)$challenge['sent_at'] + GZCA_SECURITY_RESEND_COOLDOWN - time()),
    );
}

function gzca_send_admin_security_code($purpose, $target_email, $user_id, &$error='')
{
  $allowed_purposes = array('bind_email', 'change_password');
  if (!in_array($purpose, $allowed_purposes, true))
  {
    $error = '验证码用途无效，请刷新页面后重试。';
    return false;
  }

  $mail_status = gzca_security_mail_status();
  if (!$mail_status['ready'])
  {
    $error = $mail_status['message'];
    return false;
  }

  $target_email = gzca_normalize_email($target_email);
  $existing = gzca_security_challenge($purpose);
  if (is_array($existing) && (int)$existing['sent_at'] + GZCA_SECURITY_RESEND_COOLDOWN > time())
  {
    $remaining = (int)$existing['sent_at'] + GZCA_SECURITY_RESEND_COOLDOWN - time();
    $error = '验证码发送过于频繁，请 '.$remaining.' 秒后再试。';
    return false;
  }

  include_once(PHPWG_ROOT_PATH.'include/functions_mail.inc.php');
  $user_code = generate_user_code();
  $purpose_label = 'bind_email' === $purpose ? '绑定恢复邮箱' : '修改管理员密码';
  $content = "你正在进行“{$purpose_label}”操作。\n\n验证码：{$user_code['code']}\n\n验证码 10 分钟内有效，请勿转发给任何人。若不是你本人操作，请忽略本邮件并尽快检查管理员账号安全。";
  $mail_sent = @pwg_mail(
    $target_email,
    array(
      'subject' => '[国展素材馆] 管理员安全验证码',
      'mail_title' => '国展素材馆',
      'mail_subtitle' => '管理员账号安全验证',
      'content' => $content,
      'content_format' => 'text/plain',
      )
    );
  if (!$mail_sent)
  {
    $error = '验证码发送失败，未创建验证任务。请检查系统发件服务后重试。';
    return false;
  }

  if (!isset($_SESSION['gzca_security_challenges']) || !is_array($_SESSION['gzca_security_challenges']))
  {
    $_SESSION['gzca_security_challenges'] = array();
  }
  $_SESSION['gzca_security_challenges'][$purpose] = array(
    'secret' => $user_code['secret'],
    'target_hash' => gzca_email_fingerprint($target_email),
    'user_id' => (int)$user_id,
    'purpose' => $purpose,
    'sent_at' => time(),
    'expires_at' => time() + GZCA_SECURITY_CODE_TTL,
    'attempts' => 0,
    );

  return true;
}

function gzca_verify_admin_security_code($purpose, $target_email, $code, $user_id, &$error='')
{
  $challenge = gzca_security_challenge($purpose);
  if (!is_array($challenge))
  {
    $error = '验证码不存在或已过期，请重新发送。';
    return false;
  }

  $target_hash = gzca_email_fingerprint($target_email);
  $valid_target = '' !== $target_hash
    && !empty($challenge['target_hash'])
    && hash_equals((string)$challenge['target_hash'], $target_hash);
  $valid_context = (int)$challenge['user_id'] === (int)$user_id
    && isset($challenge['purpose'])
    && (string)$challenge['purpose'] === (string)$purpose;
  $code = trim((string)$code);

  $_SESSION['gzca_security_challenges'][$purpose]['attempts'] = (int)$challenge['attempts'] + 1;
  $attempts = (int)$_SESSION['gzca_security_challenges'][$purpose]['attempts'];
  $valid_code = preg_match('/^\d{6}$/', $code)
    && $valid_target
    && $valid_context
    && verify_user_code((string)$challenge['secret'], $code);

  if (!$valid_code)
  {
    if ($attempts >= GZCA_SECURITY_MAX_ATTEMPTS)
    {
      unset($_SESSION['gzca_security_challenges'][$purpose]);
      $error = '验证码连续错误次数过多，本次验证已失效，请重新发送。';
    }
    else
    {
      $error = '验证码错误，还可尝试 '.(GZCA_SECURITY_MAX_ATTEMPTS - $attempts).' 次。';
    }
    return false;
  }

  unset($_SESSION['gzca_security_challenges'][$purpose]);
  return true;
}

function gzca_bind_verified_admin_email($user_id, $email, &$error='')
{
  global $conf, $user;

  $user_id = (int)$user_id;
  $email = gzca_normalize_email($email);
  if (!gzca_database_table_exists(GZCA_ADMIN_SECURITY_TABLE))
  {
    $error = '账号安全数据表尚未就绪，请先完成插件升级。';
    return false;
  }
  if (!gzca_validate_admin_email($user_id, $email, $error))
  {
    return false;
  }

  single_update(
    USERS_TABLE,
    array($conf['user_fields']['email'] => $email),
    array($conf['user_fields']['id'] => $user_id)
    );

  $data = array(
    'verified_email_hash' => gzca_email_fingerprint($email),
    'email_verified_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    );
  if (null === gzca_admin_security_record($user_id))
  {
    $data['user_id'] = $user_id;
    single_insert(GZCA_ADMIN_SECURITY_TABLE, $data);
  }
  else
  {
    single_update(GZCA_ADMIN_SECURITY_TABLE, $data, array('user_id' => $user_id));
  }

  deactivate_password_reset_key($user_id);
  if ((int)$user['id'] === $user_id)
  {
    $user['email'] = $email;
  }
  pwg_activity('user', $user_id, 'edit', array('fields' => 'verified_recovery_email'));
  return true;
}

function gzca_validate_new_admin_password($password, $username, $email, &$error='')
{
  $password = (string)$password;
  $length = strlen($password);
  if ($length < 12 || $length > 128)
  {
    $error = '新密码长度必须为 12-128 个字符。';
    return false;
  }
  if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password))
  {
    $error = '新密码至少要同时包含英文字母和数字。';
    return false;
  }

  $normalized_password = strtolower($password);
  $email_local = strstr(gzca_normalize_email($email), '@', true);
  if ('' !== (string)$username && false !== strpos($normalized_password, strtolower((string)$username)))
  {
    $error = '新密码不能包含管理员账号名。';
    return false;
  }
  if (false !== $email_local && strlen($email_local) >= 3 && false !== strpos($normalized_password, strtolower($email_local)))
  {
    $error = '新密码不能包含绑定邮箱的用户名部分。';
    return false;
  }

  return true;
}

function gzca_revoke_user_credentials($user_id)
{
  $user_id = (int)$user_id;
  deactivate_password_reset_key($user_id);
  deactivate_user_auth_keys($user_id);
  pwg_query('
UPDATE '.USER_AUTH_KEYS_TABLE.'
  SET expired_on = NOW(),
      revoked_on = CASE
        WHEN key_type = \'api_key\' AND revoked_on IS NULL THEN NOW()
        ELSE revoked_on
      END
  WHERE user_id = '.$user_id.'
;');
  delete_user_sessions($user_id);
}

function gzca_revoke_other_admin_sessions($user_id, &$error='', &$revoked_count=0)
{
  global $conf;

  $user_id = (int)$user_id;
  $revoked_count = 0;
  if ($user_id <= 0 || !gzca_database_table_exists(GZCA_ADMIN_SECURITY_TABLE))
  {
    $error = '账号安全数据表尚未就绪，未退出任何设备。';
    return false;
  }

  $session_id = session_id();
  if ('' === $session_id)
  {
    $error = '当前登录会话不可用，请重新登录后再试。';
    return false;
  }

  $current_session_id = get_remote_addr_session_hash().$session_id;
  $session_pattern = '%pwg_uid|i:'.$user_id.';%';
  $escaped_session_id = pwg_db_real_escape_string($current_session_id);
  $escaped_pattern = pwg_db_real_escape_string($session_pattern);

  $result = pwg_query('SELECT COUNT(*)
  FROM '.SESSIONS_TABLE.'
  WHERE data LIKE \''.$escaped_pattern.'\'
    AND id <> \''.$escaped_session_id.'\'
;');
  $row = pwg_db_fetch_row($result);
  $revoked_count = isset($row[0]) ? (int)$row[0] : 0;

  $revoked_at = time();
  $data = array(
    'sessions_revoked_before' => date('Y-m-d H:i:s', $revoked_at),
    'updated_at' => date('Y-m-d H:i:s', $revoked_at),
    );
  if (null === gzca_admin_security_record($user_id))
  {
    $data['user_id'] = $user_id;
    single_insert(GZCA_ADMIN_SECURITY_TABLE, $data);
  }
  else
  {
    single_update(GZCA_ADMIN_SECURITY_TABLE, $data, array('user_id' => $user_id));
  }

  pwg_query('DELETE
  FROM '.SESSIONS_TABLE.'
  WHERE data LIKE \''.$escaped_pattern.'\'
    AND id <> \''.$escaped_session_id.'\'
;');

  $_SESSION['gzca_admin_user_id'] = $user_id;
  $_SESSION['gzca_admin_issued_at'] = $revoked_at;
  if (!empty($conf['remember_me_name']))
  {
    setcookie(
      $conf['remember_me_name'],
      '',
      time() - 3600,
      cookie_path(),
      ini_get('session.cookie_domain'),
      ini_get('session.cookie_secure'),
      ini_get('session.cookie_httponly')
      );
  }

  pwg_activity('user', $user_id, 'edit', array('fields' => 'other_sessions_revoked'));
  return true;
}

function gzca_change_admin_password($user_id, $new_password)
{
  global $conf;

  $user_id = (int)$user_id;
  single_update(
    USERS_TABLE,
    array($conf['user_fields']['password'] => $conf['password_hash']((string)$new_password)),
    array($conf['user_fields']['id'] => $user_id)
    );

  if (gzca_database_table_exists(GZCA_ADMIN_SECURITY_TABLE))
  {
    $record = gzca_admin_security_record($user_id);
    $data = array(
      'password_changed_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s'),
      );
    if (null === $record)
    {
      $data['user_id'] = $user_id;
      single_insert(GZCA_ADMIN_SECURITY_TABLE, $data);
    }
    else
    {
      single_update(GZCA_ADMIN_SECURITY_TABLE, $data, array('user_id' => $user_id));
    }
  }

  pwg_activity('user', $user_id, 'edit', array('fields' => 'password_email_verified'));
  gzca_revoke_user_credentials($user_id);
}
