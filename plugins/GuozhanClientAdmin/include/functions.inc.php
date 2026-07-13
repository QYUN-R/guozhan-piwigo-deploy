<?php

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

function gzca_default_config()
{
  return array(
    'brand_name' => '图物计划国展素材馆',
    'brand_en' => 'AI Graphics Learning Plan',
    'logo_path' => '',
    'wechat' => 'aiguozhanhuihua',
    'phone' => '',
    'contact_note' => '咨询高清图、同类作品或使用说明时，请发送作品编号。',
    'qr_path' => '',
    'contacts' => array(),
    'restrict_administrators' => true,
    'admin_login_whitelist_enabled' => true,
    'admin_login_whitelist' => array('admin'),
    'frontend_sync' => true,
    'hero_slides' => gzca_default_hero_slides(),
    );
}

function gzca_init()
{
  global $conf;

  load_language('plugin.lang', GZCA_PATH);

  if (empty($conf['gzca_config']))
  {
    $conf['gzca_config'] = gzca_default_config();
  }
  elseif (!is_array($conf['gzca_config']))
  {
    $conf['gzca_config'] = safe_unserialize($conf['gzca_config']);
    if (!is_array($conf['gzca_config']))
    {
      $conf['gzca_config'] = array();
    }
  }

  $conf['gzca_config'] = array_merge(gzca_default_config(), $conf['gzca_config']);
  $conf['gzca_config']['restrict_administrators'] = true;
  $conf['gzca_config']['admin_login_whitelist_enabled'] = true;
}

function gzca_config()
{
  global $conf;

  if (!isset($conf['gzca_config']) || !is_array($conf['gzca_config']))
  {
    gzca_init();
  }

  return $conf['gzca_config'];
}

function gzca_admin_url($tab='dashboard', $params=array())
{
  $params = array_merge(array('page' => 'plugin-'.GZCA_ID, 'tab' => $tab), $params);
  return get_root_url().'admin.php?'.http_build_query($params, '', '&');
}

function gzca_database_table_exists($table_name)
{
  $pattern = str_replace(
    array('\\', '_', '%'),
    array('\\\\', '\\_', '\\%'),
    (string)$table_name
    );
  $result = pwg_query("SHOW TABLES LIKE '".pwg_db_real_escape_string($pattern)."';");
  return pwg_db_num_rows($result) > 0;
}

function gzca_database_health()
{
  global $conf, $prefixeTable;

  $connection_result = pwg_query('SELECT 1 AS gzca_database_connected;');
  $connection = pwg_db_num_rows($connection_result) > 0;
  $works_table = gzca_database_table_exists(GZCA_WORKS_TABLE);
  $categories_table = gzca_database_table_exists(GZCA_CATEGORIES_TABLE);
  $category_contract = false;
  $upload_contract = false;
  if ($categories_table)
  {
    $expected_keys = gzca_contract_category_keys();
    $escaped_keys = implode("','", array_map('pwg_db_real_escape_string', $expected_keys));
    list($contract_count) = pwg_db_fetch_row(pwg_query(
      'SELECT COUNT(*) FROM '.GZCA_CATEGORIES_TABLE.
      " WHERE catalog_enabled = 1 AND system_key IN ('".$escaped_keys."');"
      ));
    $category_contract = (int)$contract_count === count($expected_keys);
    list($upload_count) = pwg_db_fetch_row(pwg_query(
      'SELECT COUNT(*) FROM '.GZCA_CATEGORIES_TABLE.
      " WHERE catalog_enabled = 1 AND direct_upload = 1 AND system_key IN ('".$escaped_keys."');"
      ));
    $upload_contract = (int)$upload_count === count($expected_keys);
  }
  $web_services = !isset($conf['allow_web_services']) || !empty($conf['allow_web_services']);
  $frontend_sync = !empty(gzca_config()['frontend_sync']);
  $theme_active = !empty($conf['default_theme']) && 'guozhan-gallery' === $conf['default_theme'];

  return array(
    'connection' => $connection,
    'works_table' => $works_table,
    'categories_table' => $categories_table,
    'category_contract' => $category_contract,
    'upload_contract' => $upload_contract,
    'web_services' => $web_services,
    'frontend_sync' => $frontend_sync,
    'theme_active' => $theme_active,
    'table_prefix' => $prefixeTable,
    'ready' => $connection && $works_table && $categories_table && $category_contract && $upload_contract && $web_services && $frontend_sync && $theme_active,
    );
}

function gzca_register_ws_methods($event)
{
  $service = &$event[0];
  $service->addMethod(
    'gzca.images.getList',
    'gzca_ws_images_get_list',
    array(
      'cat_id' => array('default' => null, 'type' => WS_TYPE_INT | WS_TYPE_POSITIVE),
      'recursive' => array('default' => true, 'type' => WS_TYPE_BOOL),
      'query' => array('default' => ''),
      'sort' => array('default' => 'custom'),
      'per_page' => array('default' => 16, 'maxValue' => 20, 'type' => WS_TYPE_INT | WS_TYPE_POSITIVE),
      'page' => array('default' => 0, 'type' => WS_TYPE_INT | WS_TYPE_POSITIVE),
      ),
    'Returns public gallery works with customer-admin metadata and server pagination.',
    GZCA_PATH.'include/ws.inc.php'
    );
  $service->addMethod(
    'gzca.categories.getList',
    'gzca_ws_categories_get_list',
    array(),
    'Returns the editable public artwork catalogue, exhibition sections and visible children.',
    GZCA_PATH.'include/ws.inc.php'
    );
  $service->addMethod(
    'gzca.images.getInfo',
    'gzca_ws_images_get_info',
    array('image_id' => array('type' => WS_TYPE_ID)),
    'Returns one public gallery work with code, category and display metadata.',
    GZCA_PATH.'include/ws.inc.php'
    );
  $service->addMethod(
    'gzca.contact.get',
    'gzca_ws_contact_get',
    array(),
    'Returns public brand and customer-service information.',
    GZCA_PATH.'include/ws.inc.php'
    );
  $service->addMethod(
    'gzca.home.get',
    'gzca_ws_home_get',
    array(),
    'Returns public homepage settings such as hero carousel slides.',
    GZCA_PATH.'include/ws.inc.php'
    );
}

function gzca_is_customer_admin()
{
  return is_admin();
}

function gzca_restrict_customer_admin()
{
  if (is_a_guest())
  {
    return;
  }

  gzca_validate_admin_session();

  $page = isset($_GET['page']) ? $_GET['page'] : '';
  $section = isset($_GET['section']) ? $_GET['section'] : '';
  $allowed = 'plugin-'.GZCA_ID === $page
    || ('plugin' === $page && GZCA_ID.'/admin.php' === $section);

  if (!$allowed)
  {
    redirect(gzca_admin_url('dashboard'));
  }
}


function gzca_admin_login_whitelist()
{
  $config = gzca_config();
  $whitelist = isset($config['admin_login_whitelist']) ? $config['admin_login_whitelist'] : array('admin');
  if (is_string($whitelist))
  {
    $whitelist = preg_split('/[\s,;]+/', $whitelist);
  }

  $items = array();
  foreach ((array)$whitelist as $username)
  {
    $username = strtolower(trim((string)$username));
    if ('' !== $username)
    {
      $items[] = $username;
    }
  }

  return array_unique($items);
}

function gzca_admin_account_is_allowed($account)
{
  $username = isset($account['username']) ? strtolower(trim((string)$account['username'])) : '';
  $status = isset($account['status']) ? (string)$account['status'] : '';

  return in_array($status, array('admin', 'webmaster'), true)
    && in_array($username, gzca_admin_login_whitelist(), true);
}

function gzca_remember_cookie_session($user_id)
{
  global $conf;

  if (empty($_COOKIE[$conf['remember_me_name']]))
  {
    return null;
  }

  $raw_cookie = stripslashes((string)$_COOKIE[$conf['remember_me_name']]);
  $cookie = explode('-', $raw_cookie);
  if (3 !== count($cookie) || !is_numeric($cookie[0]) || !is_numeric($cookie[1]))
  {
    return null;
  }

  $issued_at = (int)$cookie[1];
  if ((int)$cookie[0] !== (int)$user_id
      || $issued_at <= 0
      || $issued_at > time()
      || $issued_at + GZCA_ADMIN_SESSION_TTL < time())
  {
    return null;
  }

  return array(
    'value' => $raw_cookie,
    'issued_at' => $issued_at,
    );
}

function gzca_record_admin_session($user_id)
{
  global $conf;

  $user_id = (int)$user_id;
  $account = getuserdata($user_id, false);
  $account['id'] = $user_id;
  if (!gzca_admin_account_is_allowed($account))
  {
    unset($_SESSION['gzca_admin_user_id'], $_SESSION['gzca_admin_issued_at']);
    return;
  }

  $manual_issue = isset($GLOBALS['gzca_manual_login_issued_at'])
    ? (int)$GLOBALS['gzca_manual_login_issued_at']
    : 0;
  $remember_session = 0 === $manual_issue ? gzca_remember_cookie_session($user_id) : null;
  $issued_at = $manual_issue > 0
    ? $manual_issue
    : ($remember_session ? (int)$remember_session['issued_at'] : time());

  $_SESSION['gzca_admin_user_id'] = $user_id;
  $_SESSION['gzca_admin_issued_at'] = $issued_at;

  if ($remember_session)
  {
    // Core auto-login rotates the cookie timestamp. Restore the original value
    // so the seven-day administrator lifetime cannot silently roll forward.
    setcookie(
      $conf['remember_me_name'],
      $remember_session['value'],
      $issued_at + GZCA_ADMIN_SESSION_TTL,
      cookie_path(),
      ini_get('session.cookie_domain'),
      ini_get('session.cookie_secure'),
      ini_get('session.cookie_httponly')
      );
  }
}

function gzca_admin_login_url($reason)
{
  $redirect_to = cookie_path().'admin.php?page=plugin-'.GZCA_ID.'&tab=dashboard';
  return get_root_url().'identification.php?'.http_build_query(
    array(
      'redirect' => $redirect_to,
      'hide_redirect_error' => 1,
      'gzca_auth' => $reason,
      ),
    '',
    '&'
    );
}

function gzca_force_admin_reauthentication($reason)
{
  logout_user();
  redirect(gzca_admin_login_url($reason));
}

function gzca_validate_admin_session()
{
  global $user;

  if (!gzca_admin_account_is_allowed($user))
  {
    gzca_force_admin_reauthentication('unauthorized');
  }

  $session_user_id = isset($_SESSION['gzca_admin_user_id'])
    ? (int)$_SESSION['gzca_admin_user_id']
    : 0;
  $issued_at = isset($_SESSION['gzca_admin_issued_at'])
    ? (int)$_SESSION['gzca_admin_issued_at']
    : 0;

  if ($session_user_id <= 0 || $issued_at <= 0 || $session_user_id !== (int)$user['id'])
  {
    gzca_force_admin_reauthentication('reauth');
  }

  if ($issued_at > time() || $issued_at + GZCA_ADMIN_SESSION_TTL <= time())
  {
    gzca_force_admin_reauthentication('expired');
  }

  $revoked_before = function_exists('gzca_admin_session_revoked_before')
    ? gzca_admin_session_revoked_before((int)$user['id'])
    : 0;
  if ($revoked_before > 0 && $issued_at < $revoked_before)
  {
    gzca_force_admin_reauthentication('sessions_revoked');
  }

  return true;
}

function gzca_admin_identity()
{
  global $user;

  $issued_at = (int)$_SESSION['gzca_admin_issued_at'];
  $expires_at = $issued_at + GZCA_ADMIN_SESSION_TTL;

  return array(
    'id' => (int)$user['id'],
    'username' => (string)$user['username'],
    'role' => 'webmaster' === $user['status'] ? '站点管理员' : '管理员',
    'issued_at' => date('Y-m-d H:i', $issued_at),
    'expires_at' => date('Y-m-d H:i', $expires_at),
    );
}

function gzca_prepare_login_notice()
{
  global $page, $template;

  $reason = isset($_GET['gzca_auth']) ? (string)$_GET['gzca_auth'] : '';
  $messages = array(
    'reauth' => '后台安全策略已更新，请重新验证管理员身份。',
    'expired' => '管理员登录状态已超过 7 天，请重新输入邮箱和密码。',
    'unauthorized' => '当前邮箱没有后台权限，请使用已验证的管理员邮箱登录。',
    'password_changed' => '管理员密码已更新，旧登录状态和访问密钥已撤销，请使用新密码重新登录。',
    'sessions_revoked' => '此设备的管理员登录已被撤销，请重新输入邮箱和密码。',
    );

  if (isset($messages[$reason]))
  {
    $template->assign('GZCA_AUTH_NOTICE', $messages[$reason]);
  }
  if (!empty($page['errors']['login_form_error']))
  {
    $page['errors']['login_form_error'] = '邮箱或密码错误。';
  }
}

function gzca_capture_login_identifier($success, $username, $password, $remember_me)
{
  $GLOBALS['gzca_login_identifier'] = (string)$username;
  return $success;
}

function gzca_finalize_login_whitelist($state, $user_found, $remember_me)
{
  $identifier = isset($GLOBALS['gzca_login_identifier'])
    ? (string)$GLOBALS['gzca_login_identifier']
    : '';
  unset($GLOBALS['gzca_login_identifier']);

  $email_login_allowed = function_exists('gzca_admin_verified_email_login_allowed')
    && gzca_admin_verified_email_login_allowed($user_found, $identifier);
  if (!gzca_admin_account_is_allowed($user_found) || !$email_login_allowed)
  {
    $state['can_login'] = false;
    $state['reason'] = 'gzca_login_not_verified_email';
    return $state;
  }

  $GLOBALS['gzca_manual_login_issued_at'] = time();
  log_user((int)$user_found['id'], true);
  unset($GLOBALS['gzca_manual_login_issued_at']);
  $state['authenticated'] = true;

  return $state;
}
function gzca_render_frontend_bridge()
{
  global $template;

  $config = gzca_config();
  if (empty($config['frontend_sync']))
  {
    return;
  }

  $contacts = gzca_frontend_contacts($config);
  $primary_contact = !empty($contacts) ? $contacts[0] : array('wechat' => '', 'note' => '', 'qrUrl' => '');
  $contact = array(
    'brandName' => $config['brand_name'],
    'brandEn' => $config['brand_en'],
    'logoUrl' => gzca_logo_url($config),
    'wechat' => $primary_contact['wechat'],
    'phone' => $config['phone'],
    'note' => $primary_contact['note'],
    'qrUrl' => $primary_contact['qrUrl'],
    'contacts' => $contacts,
    );
  $hero_slides = gzca_frontend_hero_slides($config);

  $template->assign(array(
    'GZCA_PATH' => GZCA_PATH,
    'GZCA_VERSION' => GZCA_VERSION,
    'GZCA_CONTACT_JSON' => json_encode(
      $contact,
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
      ),
    'GZCA_HERO_JSON' => json_encode(
      $hero_slides,
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
      ),
    ));
  $template->set_filename('gzca_frontend_bridge', realpath(GZCA_PATH.'template/frontend.tpl'));
  $template->parse('gzca_frontend_bridge');
}

function gzca_public_asset_url($path)
{
  $path = ltrim((string)$path, '/');
  if ('' === $path)
  {
    return '';
  }

  $url = get_root_url().$path;
  $absolute_path = PHPWG_ROOT_PATH.$path;
  if (is_file($absolute_path))
  {
    $url .= (false === strpos($url, '?') ? '?' : '&').'v='.filemtime($absolute_path);
  }

  return $url;
}

function gzca_normalize_contacts($config=null)
{
  if (null === $config)
  {
    $config = gzca_config();
  }

  $legacy_wechat = isset($config['wechat']) ? (string)$config['wechat'] : '';
  $legacy_note = isset($config['contact_note']) ? (string)$config['contact_note'] : '';
  $legacy_qr = isset($config['qr_path']) ? (string)$config['qr_path'] : '';
  $stored = isset($config['contacts']) && is_array($config['contacts']) ? $config['contacts'] : array();
  $defaults = array(
    1 => array(
      'label' => '客服1',
      'wechat' => $legacy_wechat,
      'note' => $legacy_note,
      'qr_path' => $legacy_qr,
      'enabled' => true,
      ),
    2 => array(
      'label' => '客服2',
      'wechat' => $legacy_wechat,
      'note' => $legacy_note,
      'qr_path' => $legacy_qr,
      'enabled' => true,
      ),
    );
  $contacts = array();
  for ($slot = 1; $slot <= 2; $slot++)
  {
    $candidate = isset($stored[$slot - 1]) && is_array($stored[$slot - 1]) ? $stored[$slot - 1] : array();
    $contact = array_merge($defaults[$slot], $candidate);
    $contact['slot'] = $slot;
    $contact['index'] = $slot - 1;
    $contact['label'] = trim((string)$contact['label']);
    if ('' === $contact['label'])
    {
      $contact['label'] = '客服'.$slot;
    }
    $contact['wechat'] = trim((string)$contact['wechat']);
    $contact['note'] = trim((string)$contact['note']);
    $contact['qr_path'] = trim((string)$contact['qr_path']);
    $contact['enabled'] = !empty($contact['enabled']);
    if (1 === $slot && '' === $contact['wechat'] && '' !== $legacy_wechat)
    {
      $contact['wechat'] = $legacy_wechat;
    }
    if (1 === $slot && '' === $contact['qr_path'] && '' !== $legacy_qr)
    {
      $contact['qr_path'] = $legacy_qr;
    }
    $contact['qr_url'] = gzca_contact_qr_url_for($contact);
    $contacts[] = $contact;
  }
  return $contacts;
}

function gzca_contact_qr_url_for($contact)
{
  if (empty($contact['qr_path']))
  {
    return '';
  }
  return gzca_public_asset_url($contact['qr_path']);
}

function gzca_frontend_contacts($config=null)
{
  $items = array();
  foreach (gzca_normalize_contacts($config) as $contact)
  {
    if (empty($contact['enabled']) && '' === $contact['wechat'] && '' === $contact['qr_path'])
    {
      continue;
    }
    if (empty($contact['enabled']))
    {
      continue;
    }
    $items[] = array(
      'slot' => (int)$contact['slot'],
      'label' => $contact['label'],
      'wechat' => $contact['wechat'],
      'note' => $contact['note'],
      'qrUrl' => gzca_contact_qr_url_for($contact),
      );
  }
  if (empty($items))
  {
    foreach (gzca_normalize_contacts($config) as $contact)
    {
      if ('' !== $contact['wechat'] || '' !== $contact['qr_path'])
      {
        $items[] = array(
          'slot' => (int)$contact['slot'],
          'label' => $contact['label'],
          'wechat' => $contact['wechat'],
          'note' => $contact['note'],
          'qrUrl' => gzca_contact_qr_url_for($contact),
          );
        break;
      }
    }
  }
  return $items;
}

function gzca_contact_qr_url($config=null)
{
  if (null === $config)
  {
    $config = gzca_config();
  }

  $contacts = gzca_normalize_contacts($config);
  if (!empty($contacts[0]['qr_path']))
  {
    return gzca_public_asset_url($contacts[0]['qr_path']);
  }
  if (empty($config['qr_path']))
  {
    return '';
  }

  return gzca_public_asset_url($config['qr_path']);
}

function gzca_logo_url($config=null)
{
  if (null === $config)
  {
    $config = gzca_config();
  }

  if (empty($config['logo_path']))
  {
    return '';
  }

  return gzca_public_asset_url($config['logo_path']);
}

function gzca_default_hero_slides()
{
  return array(
    array('index' => 0, 'enabled' => true, 'title' => '首页轮播 01', 'image_path' => 'assets/hero-gallery-01.jpg', 'position' => 'center'),
    array('index' => 1, 'enabled' => true, 'title' => '首页轮播 02', 'image_path' => 'assets/hero-gallery-02.jpg', 'position' => 'center'),
    array('index' => 2, 'enabled' => true, 'title' => '首页轮播 03', 'image_path' => 'assets/hero-gallery-03.jpg', 'position' => 'center'),
    array('index' => 3, 'enabled' => false, 'title' => '预留轮播 04', 'image_path' => '', 'position' => 'center'),
    array('index' => 4, 'enabled' => false, 'title' => '预留轮播 05', 'image_path' => '', 'position' => 'center'),
    );
}

function gzca_normalize_hero_slides($config=null)
{
  if (null === $config)
  {
    $config = gzca_config();
  }

  $defaults = gzca_default_hero_slides();
  $stored = isset($config['hero_slides']) && is_array($config['hero_slides']) ? $config['hero_slides'] : array();
  $slides = array();
  foreach ($defaults as $index => $default)
  {
    $item = isset($stored[$index]) && is_array($stored[$index]) ? array_merge($default, $stored[$index]) : $default;
    $item['index'] = $index;
    $item['enabled'] = !empty($item['enabled']);
    $item['title'] = gzca_clean_text(isset($item['title']) ? $item['title'] : $default['title'], 80);
    $item['image_path'] = ltrim((string)(isset($item['image_path']) ? $item['image_path'] : ''), '/');
    $item['position'] = gzca_clean_text(isset($item['position']) ? $item['position'] : 'center', 40);
    if ('' === $item['position'])
    {
      $item['position'] = 'center';
    }
    $item['is_default'] = $item['image_path'] === $default['image_path'];
    $item['default_path'] = $default['image_path'];
    $item['default_url'] = '' !== $default['image_path'] ? gzca_public_asset_url($default['image_path']) : '';
    $item['url'] = '' !== $item['image_path'] ? gzca_public_asset_url($item['image_path']) : '';
    $slides[] = $item;
  }
  return $slides;
}

function gzca_frontend_hero_slides($config=null)
{
  $items = array();
  foreach (gzca_normalize_hero_slides($config) as $slide)
  {
    if (empty($slide['enabled']) || empty($slide['url']))
    {
      continue;
    }
    $items[] = array(
      'title' => $slide['title'],
      'url' => $slide['url'],
      'position' => $slide['position'],
      );
  }
  return $items;
}

function gzca_admin_hero_slides()
{
  return gzca_normalize_hero_slides();
}

function gzca_clean_text($value, $max_length=5000)
{
  $value = trim(strip_tags((string)$value));
  if (function_exists('mb_substr'))
  {
    return mb_substr($value, 0, $max_length, 'UTF-8');
  }
  return substr($value, 0, $max_length);
}

function gzca_sql_like_contains($value)
{
  $value = str_replace(
    array('\\', '%', '_'),
    array('\\\\', '\\%', '\\_'),
    (string)$value
    );
  return pwg_db_real_escape_string('%'.$value.'%');
}

function gzca_normalize_code($value)
{
  return strtoupper(trim((string)$value));
}

function gzca_valid_code($value)
{
  return (bool)preg_match('/^[A-Z0-9]{2,12}(?:-[A-Z0-9]{1,12})*-\d{3,6}$/', gzca_normalize_code($value));
}

function gzca_valid_prefix($value)
{
  return (bool)preg_match('/^[A-Z0-9]{2,12}(?:-[A-Z0-9]{1,12})*$/', gzca_normalize_code($value));
}

function gzca_strip_markers($comment)
{
  $comment = preg_replace('/<!--\s*GZCA_(?:CODE|PREFIX|SAMPLE):.*?-->/is', '', (string)$comment);
  return trim($comment);
}

function gzca_embed_code($comment, $code)
{
  return '<!--GZCA_CODE:'.gzca_normalize_code($code).'-->'.gzca_strip_markers($comment);
}

function gzca_embed_sample_code($comment, $code, $sample_key)
{
  $sample_key = preg_replace('/[^a-z0-9_-]/i', '', (string)$sample_key);
  return '<!--GZCA_CODE:'.gzca_normalize_code($code).'--><!--GZCA_SAMPLE:'.$sample_key.'-->'.gzca_strip_markers($comment);
}

function gzca_embed_prefix($comment, $prefix)
{
  return '<!--GZCA_PREFIX:'.gzca_normalize_code($prefix).'-->'.gzca_strip_markers($comment);
}

function gzca_extract_code($comment, $fallback='')
{
  if (preg_match('/GZCA_CODE:([A-Z0-9-]+)/i', (string)$comment, $matches))
  {
    return strtoupper($matches[1]);
  }
  if (preg_match('/[A-Z]{2,}(?:-[A-Z0-9]+)+-\d{3,6}/i', (string)$comment, $matches))
  {
    return strtoupper($matches[0]);
  }
  return $fallback;
}

function gzca_category_exists($category_id)
{
  $category_id = (int)$category_id;
  if ($category_id < 1)
  {
    return false;
  }

  list($count) = pwg_db_fetch_row(pwg_query(
    'SELECT COUNT(*) FROM '.CATEGORIES_TABLE.' WHERE id = '.$category_id.';'
    ));
  return (int)$count > 0;
}

function gzca_get_categories($catalog_only=false, $public_only=false)
{
  if (!gzca_database_table_exists(GZCA_CATEGORIES_TABLE) || !gzca_database_table_exists(GZCA_WORKS_TABLE))
  {
    return array();
  }

  $query = '
SELECT
    c.id,
    c.id_uppercat,
    c.name,
    c.comment,
    c.uppercats,
    c.global_rank,
    c.`rank`,
    c.visible,
    c.status,
    c.representative_picture_id,
    (SELECT COUNT(*) FROM '.CATEGORIES_TABLE.' AS child_c WHERE child_c.id_uppercat = c.id) AS child_count,
    (SELECT COUNT(DISTINCT count_ic.image_id)
       FROM '.IMAGE_CATEGORY_TABLE.' AS count_ic
         INNER JOIN '.CATEGORIES_TABLE.' AS count_c ON count_c.id = count_ic.category_id
       WHERE FIND_IN_SET(c.id, count_c.uppercats) > 0) AS image_count,
    (SELECT COUNT(DISTINCT public_ic.image_id)
       FROM '.IMAGE_CATEGORY_TABLE.' AS public_ic
         INNER JOIN '.CATEGORIES_TABLE.' AS public_count_c ON public_count_c.id = public_ic.category_id
         INNER JOIN '.IMAGES_TABLE.' AS public_i ON public_i.id = public_ic.image_id AND public_i.level = 0
         LEFT JOIN '.GZCA_WORKS_TABLE.' AS public_gw ON public_gw.image_id = public_i.id
       WHERE FIND_IN_SET(c.id, public_count_c.uppercats) > 0
         AND public_count_c.visible = \'true\'
         AND public_count_c.status = \'public\'
         AND (public_gw.status IS NULL OR public_gw.status = \'online\')) AS public_image_count,
    COALESCE(gc.code_prefix, \'\') AS code_prefix,
    COALESCE(gc.sort_order, c.`rank`) AS custom_sort_order,
    COALESCE(gc.catalog_enabled, 0) AS catalog_enabled,
    COALESCE(gc.direct_upload, 0) AS direct_upload,
    COALESCE(gc.system_key, \'\') AS system_key,
    COALESCE(gc.category_kind, \'custom\') AS category_kind,
    COALESCE(gc.reserved, 0) AS reserved
  FROM '.CATEGORIES_TABLE.' AS c
    LEFT JOIN '.GZCA_CATEGORIES_TABLE.' AS gc ON gc.category_id = c.id
  WHERE 1=1
    '.($catalog_only ? 'AND COALESCE(gc.catalog_enabled, 0) = 1' : '').'
    '.($public_only ? "AND c.visible = 'true' AND c.status = 'public'
    AND NOT EXISTS (
      SELECT 1 FROM ".CATEGORIES_TABLE." AS hidden_c
      WHERE FIND_IN_SET(hidden_c.id, c.uppercats) > 0
        AND (hidden_c.visible <> 'true' OR hidden_c.status <> 'public')
    )" : '').'
  ORDER BY c.global_rank, c.`rank`, c.name
;';

  return query2array($query);
}

function gzca_category_options($categories)
{
  $categories_by_id = array();
  foreach ($categories as $category)
  {
    $categories_by_id[(int)$category['id']] = $category;
  }

  $options = array();
  foreach ($categories as $category)
  {
    if (empty($category['catalog_enabled']))
    {
      continue;
    }
    if (empty($category['direct_upload']))
    {
      continue;
    }

    $path_ids = array_values(array_filter(array_map('intval', explode(',', (string)$category['uppercats']))));
    if (empty($path_ids))
    {
      $path_ids[] = (int)$category['id'];
    }
    $path_names = array();
    foreach ($path_ids as $path_id)
    {
      if (isset($categories_by_id[$path_id]))
      {
        $path_names[] = $categories_by_id[$path_id]['name'];
      }
    }
    if (empty($path_names))
    {
      $path_names[] = $category['name'];
    }

    $root_id = (int)$path_ids[0];
    $root_category = isset($categories_by_id[$root_id]) ? $categories_by_id[$root_id] : $category;
    $is_top_level = empty($category['id_uppercat']);
    $option_label = $is_top_level
      ? $category['name'].' / 直接上传 / 全部作品'
      : implode(' / ', $path_names);
    $options[] = array(
      'id' => (int)$category['id'],
      'name' => implode(' / ', $path_names),
      'raw_name' => $category['name'],
      'option_label' => $option_label,
      'prefix' => $category['code_prefix'],
      'visible' => 'true' === $category['visible'],
      'direct_upload' => !empty($category['direct_upload']),
      'group_id' => $root_id,
      'group_name' => $root_category['name'],
      'group_kind' => $root_category['category_kind'],
      'reserved' => !empty($category['reserved']),
      'is_top_level' => $is_top_level,
      'sort_order' => (int)$category['custom_sort_order'],
      );
  }
  return $options;
}

function gzca_compare_exhibition_category_options($left, $right)
{
  if (!empty($left['is_top_level']) !== !empty($right['is_top_level']))
  {
    return !empty($left['is_top_level']) ? -1 : 1;
  }

  if (!empty($left['reserved']) !== !empty($right['reserved']))
  {
    return !empty($left['reserved']) ? 1 : -1;
  }

  $left_name = isset($left['raw_name']) ? (string)$left['raw_name'] : (isset($left['name']) ? (string)$left['name'] : '');
  $right_name = isset($right['raw_name']) ? (string)$right['raw_name'] : (isset($right['name']) ? (string)$right['name'] : '');

  if (!empty($left['reserved']))
  {
    return strnatcasecmp($left_name, $right_name);
  }

  $left_order = isset($left['sort_order'])
    ? (int)$left['sort_order']
    : (isset($left['custom_sort_order']) ? (int)$left['custom_sort_order'] : PHP_INT_MAX);
  $right_order = isset($right['sort_order'])
    ? (int)$right['sort_order']
    : (isset($right['custom_sort_order']) ? (int)$right['custom_sort_order'] : PHP_INT_MAX);
  if ($left_order !== $right_order)
  {
    return $left_order < $right_order ? -1 : 1;
  }

  return strnatcasecmp($left_name, $right_name);
}

function gzca_category_option_groups($categories)
{
  $groups = array();
  $group_order = array();
  foreach (gzca_category_options($categories) as $option)
  {
    $group_id = (int)$option['group_id'];
    if (!isset($groups[$group_id]))
    {
      $groups[$group_id] = array(
        'id' => $group_id,
        'name' => $option['group_name'],
        'kind' => $option['group_kind'],
        'options' => array(),
        );
      $group_order[] = $group_id;
    }
    $groups[$group_id]['options'][] = $option;
  }

  foreach ($group_order as $group_id)
  {
    if ('exhibition' === $groups[$group_id]['kind'])
    {
      usort($groups[$group_id]['options'], 'gzca_compare_exhibition_category_options');
    }
  }

  $result = array();
  foreach ($group_order as $group_id)
  {
    $result[] = $groups[$group_id];
  }
  return $result;
}

function gzca_category_is_upload_target($category_id)
{
  $category_id = (int)$category_id;
  if ($category_id < 1)
  {
    return false;
  }

  list($count) = pwg_db_fetch_row(pwg_query('
SELECT COUNT(*)
  FROM '.CATEGORIES_TABLE.' AS c
    INNER JOIN '.GZCA_CATEGORIES_TABLE.' AS gc ON gc.category_id = c.id
  WHERE c.id = '.$category_id.'
    AND gc.catalog_enabled = 1
    AND gc.direct_upload = 1
;'));
  return (int)$count > 0;
}

function gzca_category_is_hidden($category)
{
  return isset($category['visible']) && 'true' !== (string)$category['visible'];
}

function gzca_category_is_soft_deleted($category)
{
  return gzca_category_is_hidden($category);
}

function gzca_prepare_category_row($category)
{
  $category['is_hidden'] = gzca_category_is_hidden($category);
  $category['is_deleted'] = $category['is_hidden'];
  $category['hide_label'] = $category['is_hidden'] ? '显示' : '隐藏';
  $category['hide_note'] = $category['is_hidden']
    ? '这个板块当前已隐藏。点击显示后，前台会重新展示，并恢复上传入口。'
    : '隐藏后前台不再展示这个板块，后台数据、图片、作品记录和分类记录都会保留。';
  $category['delete_note'] = '永久删除会移除这个板块及其子板块，并删除这些板块下的作品记录、原图和缩略图缓存。此操作会释放服务器空间，但不可恢复。';
  return $category;
}

function gzca_category_tree_ids($category_id)
{
  $category_id = (int)$category_id;
  if ($category_id < 1)
  {
    return array();
  }

  $rows = query2array(
    'SELECT id FROM '.CATEGORIES_TABLE.
    ' WHERE FIND_IN_SET('.$category_id.', uppercats) > 0'.
    ' ORDER BY LENGTH(uppercats), global_rank;'
    );
  $ids = array();
  foreach ($rows as $row)
  {
    $id = (int)$row['id'];
    if ($id > 0)
    {
      $ids[$id] = $id;
    }
  }
  if (empty($ids))
  {
    $ids[$category_id] = $category_id;
  }
  return array_values($ids);
}

function gzca_set_category_hidden($category_id, $hidden, &$error='')
{
  $category_id = (int)$category_id;
  if (!gzca_category_exists($category_id))
  {
    $error = '分类不存在或已经被删除。';
    return false;
  }

  $ids = gzca_category_tree_ids($category_id);
  if (empty($ids))
  {
    $error = '没有找到需要处理的板块。';
    return false;
  }

  $id_sql = implode(',', array_map('intval', $ids));
  $visible = $hidden ? 'false' : 'true';
  pwg_query("UPDATE ".CATEGORIES_TABLE." SET visible = '".$visible."' WHERE id IN (".$id_sql.");");
  pwg_query(
    'UPDATE '.GZCA_CATEGORIES_TABLE.
    ' SET catalog_enabled = 1, direct_upload = '.($hidden ? 0 : 1).', updated_at = NOW()'.
    ' WHERE category_id IN ('.$id_sql.');'
    );

  update_global_rank();
  invalidate_user_cache();
  return true;
}

function gzca_set_category_soft_deleted($category_id, $deleted, &$error='')
{
  return gzca_set_category_hidden($category_id, $deleted, $error);
}

function gzca_delete_category_tree($category_id, &$error='', &$summary=array())
{
  $category_id = (int)$category_id;
  $summary = array(
    'category_count' => 0,
    'linked_image_count' => 0,
    'image_count' => 0,
    'shared_image_count' => 0,
    );
  if (!gzca_category_exists($category_id))
  {
    $error = '分类不存在或已经被删除。';
    return false;
  }

  $ids = gzca_category_tree_ids($category_id);
  if (empty($ids))
  {
    $error = '没有找到需要删除的板块。';
    return false;
  }

  $id_sql = implode(',', array_map('intval', $ids));
  $summary['category_count'] = count($ids);

  $linked_image_ids = array_map('intval', query2array(
    'SELECT DISTINCT image_id FROM '.IMAGE_CATEGORY_TABLE.' WHERE category_id IN ('.$id_sql.');',
    null,
    'image_id'
    ));
  $summary['linked_image_count'] = count($linked_image_ids);

  $image_ids = array_map('intval', query2array(
    'SELECT DISTINCT ic.image_id
       FROM '.IMAGE_CATEGORY_TABLE.' AS ic
      WHERE ic.category_id IN ('.$id_sql.')
        AND NOT EXISTS (
          SELECT 1
            FROM '.IMAGE_CATEGORY_TABLE.' AS keep_ic
           WHERE keep_ic.image_id = ic.image_id
             AND keep_ic.category_id NOT IN ('.$id_sql.')
        );',
    null,
    'image_id'
    ));
  $summary['shared_image_count'] = max(0, $summary['linked_image_count'] - count($image_ids));

  if (!empty($image_ids))
  {
    $delete_error = '';
    $deleted_images = gzca_delete_works($image_ids, $delete_error);
    if (false === $deleted_images)
    {
      $error = $delete_error ?: '删除板块下作品失败，已停止删除板块，避免留下不完整数据。';
      return false;
    }
    $summary['image_count'] = (int)$deleted_images;
  }

  pwg_query('UPDATE '.IMAGES_TABLE.' SET storage_category_id = NULL WHERE storage_category_id IN ('.$id_sql.');');
  pwg_query('DELETE FROM '.IMAGE_CATEGORY_TABLE.' WHERE category_id IN ('.$id_sql.');');
  pwg_query('DELETE FROM '.USER_ACCESS_TABLE.' WHERE cat_id IN ('.$id_sql.');');
  pwg_query('DELETE FROM '.GROUP_ACCESS_TABLE.' WHERE cat_id IN ('.$id_sql.');');
  pwg_query('DELETE FROM '.OLD_PERMALINKS_TABLE.' WHERE cat_id IN ('.$id_sql.');');
  pwg_query('DELETE FROM '.USER_CACHE_CATEGORIES_TABLE.' WHERE cat_id IN ('.$id_sql.');');
  pwg_query('DELETE FROM '.GZCA_CATEGORIES_TABLE.' WHERE category_id IN ('.$id_sql.');');
  pwg_query('DELETE FROM '.CATEGORIES_TABLE.' WHERE id IN ('.$id_sql.');');

  update_global_rank();
  invalidate_user_cache();
  if (function_exists('trigger_notify'))
  {
    trigger_notify('delete_categories', $ids);
  }
  if (function_exists('pwg_activity'))
  {
    pwg_activity('album', $ids, 'delete', array('photo_deletion_mode' => 'delete_photos'));
  }
  return count($ids);
}

function gzca_category_path($category_id)
{
  $category_id = (int)$category_id;
  $result = pwg_query('SELECT uppercats FROM '.CATEGORIES_TABLE.' WHERE id = '.$category_id.' LIMIT 1;');
  if (0 === pwg_db_num_rows($result))
  {
    return '';
  }

  $row = pwg_db_fetch_assoc($result);
  $ids = array_values(array_filter(array_map('intval', explode(',', (string)$row['uppercats']))));
  if (empty($ids))
  {
    return '';
  }

  $names = array();
  $name_rows = query2array(
    'SELECT id, name FROM '.CATEGORIES_TABLE.' WHERE id IN ('.implode(',', $ids).');',
    'id',
    'name'
    );
  foreach ($ids as $id)
  {
    if (isset($name_rows[$id]))
    {
      $names[] = $name_rows[$id];
    }
  }
  return implode(' / ', $names);
}

function gzca_category_prefix($category_id)
{
  $category_id = (int)$category_id;
  $query = '
SELECT code_prefix
  FROM '.GZCA_CATEGORIES_TABLE.'
  WHERE category_id = '.$category_id.'
;';
  $result = pwg_query($query);
  if (pwg_db_num_rows($result) > 0)
  {
    $row = pwg_db_fetch_assoc($result);
    if (gzca_valid_prefix($row['code_prefix']))
    {
      return strtoupper($row['code_prefix']);
    }
  }
  return 'GZ';
}

function gzca_category_prefix_exists($prefix, $exclude_category_id=0)
{
  $prefix = pwg_db_real_escape_string(gzca_normalize_code($prefix));
  list($count) = pwg_db_fetch_row(pwg_query(
    'SELECT COUNT(*) FROM '.GZCA_CATEGORIES_TABLE.
    " WHERE code_prefix = '".$prefix."'".
    ((int)$exclude_category_id > 0 ? ' AND category_id <> '.(int)$exclude_category_id : '').';'
    ));
  return (int)$count > 0;
}

function gzca_save_category_meta($category_id, $prefix, $sort_order=0, $catalog_enabled=1, $direct_upload=1, $system_key=null, $category_kind=null, $reserved=null)
{
  $category_id = (int)$category_id;
  $prefix = gzca_normalize_code($prefix);
  $exists = pwg_db_num_rows(pwg_query(
    'SELECT category_id FROM '.GZCA_CATEGORIES_TABLE.' WHERE category_id = '.$category_id.';'
    )) > 0;

  $data = array(
    'code_prefix' => $prefix,
    'sort_order' => (int)$sort_order,
    'catalog_enabled' => !empty($catalog_enabled) ? 1 : 0,
    'direct_upload' => !empty($direct_upload) ? 1 : 0,
    'updated_at' => date('Y-m-d H:i:s'),
    );

  if (null !== $system_key)
  {
    $data['system_key'] = '' === trim((string)$system_key) ? null : strtolower(trim((string)$system_key));
  }
  if (null !== $category_kind)
  {
    $data['category_kind'] = in_array($category_kind, array('catalog', 'exhibition', 'custom'), true)
      ? $category_kind
      : 'custom';
  }
  if (null !== $reserved)
  {
    $data['reserved'] = !empty($reserved) ? 1 : 0;
  }

  if ($exists)
  {
    single_update(GZCA_CATEGORIES_TABLE, $data, array('category_id' => $category_id));
  }
  else
  {
    $data['category_id'] = $category_id;
    single_insert(GZCA_CATEGORIES_TABLE, $data);
  }
}

function gzca_find_category_id($name, $parent_id=null)
{
  $name = pwg_db_real_escape_string($name);
  $query = '
SELECT id
  FROM '.CATEGORIES_TABLE.'
  WHERE name = \''.$name.'\'
    AND id_uppercat '.(empty($parent_id) ? 'IS NULL' : '= '.(int)$parent_id).'
  LIMIT 1
;';
  $result = pwg_query($query);
  if (pwg_db_num_rows($result) === 0)
  {
    return null;
  }
  $row = pwg_db_fetch_assoc($result);
  return (int)$row['id'];
}

function gzca_category_contract()
{
  return array(
    array('key' => 'ink', 'name' => '国画', 'prefix' => 'GH', 'description' => '山水画、人物画和花鸟画作品。', 'children' => array(
      array('key' => 'ink-landscape', 'name' => '山水画', 'prefix' => 'GH-SS'),
      array('key' => 'ink-figure', 'name' => '人物画', 'prefix' => 'GH-RW'),
      array('key' => 'ink-flower-bird', 'name' => '花鸟画', 'prefix' => 'GH-HN'),
      )),
    array('key' => 'oil', 'name' => '油画', 'prefix' => 'YH', 'description' => '人物画、风景画和静物画作品。', 'children' => array(
      array('key' => 'oil-figure', 'name' => '人物画', 'prefix' => 'YH-RW'),
      array('key' => 'oil-landscape', 'name' => '风景画', 'prefix' => 'YH-FJ'),
      array('key' => 'oil-still', 'name' => '静物画', 'prefix' => 'YH-JW'),
      )),
    array('key' => 'print', 'name' => '版画', 'prefix' => 'BH', 'description' => '黑白木刻、木刻水印和丝网版画作品。', 'children' => array(
      array('key' => 'print-black-woodcut', 'name' => '黑白木刻', 'prefix' => 'BH-HB'),
      array('key' => 'print-watermark', 'name' => '木刻水印', 'prefix' => 'BH-SY'),
      array('key' => 'print-screen', 'name' => '丝网版画', 'prefix' => 'BH-SW'),
      )),
    array('key' => 'sculpture', 'name' => '雕塑', 'prefix' => 'DS', 'description' => '圆雕和浮雕作品。', 'children' => array(
      array('key' => 'sculpture-round', 'name' => '圆雕', 'prefix' => 'DS-YD'),
      array('key' => 'sculpture-relief', 'name' => '浮雕', 'prefix' => 'DS-FD'),
      )),
    array('key' => 'lacquer', 'name' => '漆画', 'prefix' => 'QH', 'description' => '人物、风景和静物漆画作品。', 'children' => array(
      array('key' => 'lacquer-figure', 'name' => '人物', 'prefix' => 'QH-RW'),
      array('key' => 'lacquer-landscape', 'name' => '风景', 'prefix' => 'QH-FJ'),
      array('key' => 'lacquer-still', 'name' => '静物', 'prefix' => 'QH-JW'),
      )),
    array('key' => 'watercolor', 'name' => '水彩', 'prefix' => 'SC', 'description' => '人物、风景和静物水彩作品。', 'children' => array(
      array('key' => 'watercolor-figure', 'name' => '人物', 'prefix' => 'SC-RW'),
      array('key' => 'watercolor-landscape', 'name' => '风景', 'prefix' => 'SC-FJ'),
      array('key' => 'watercolor-still', 'name' => '静物', 'prefix' => 'SC-JW'),
      )),
    array('key' => 'folk-art', 'name' => '农民画', 'prefix' => 'NM-ZH', 'description' => '不分小板块，直接浏览农民画作品。', 'direct' => true, 'children' => array()),
    array('key' => 'comic', 'name' => '漫画', 'prefix' => 'MH-ZH', 'description' => '不分小板块，直接浏览漫画作品。', 'direct' => true, 'children' => array()),
    array('key' => 'illustration', 'name' => '插画', 'prefix' => 'CH', 'description' => '绘画风格插画和数字插画作品。', 'children' => array(
      array('key' => 'illustration-painted', 'name' => '绘画风格插画', 'prefix' => 'CH-HH'),
      array('key' => 'illustration-digital', 'name' => '数字插画', 'prefix' => 'CH-SZ'),
      )),
    array('key' => 'caa-exhibitions', 'name' => '中美协展览专项画稿', 'prefix' => 'ZX', 'kind' => 'exhibition', 'description' => '专项征稿与展览画稿入口，可持续新增和修改画展。', 'children' => array(
      array('key' => 'caa-plum-blossom', 'name' => '梅花之韵——2026·中国画花鸟作品展', 'prefix' => 'ZX-MH'),
      array('key' => 'caa-landscape-oil', 'name' => '山水滋美——2026风景油画展', 'prefix' => 'ZX-YS'),
      array('key' => 'caa-young-lacquer', 'name' => '第五届青年漆画展览征稿通知', 'prefix' => 'ZX-QH'),
      array('key' => 'caa-unknown-01', 'name' => '未知画展 01', 'prefix' => 'ZX-W1', 'reserved' => true),
      array('key' => 'caa-unknown-02', 'name' => '未知画展 02', 'prefix' => 'ZX-W2', 'reserved' => true),
      array('key' => 'caa-unknown-03', 'name' => '未知画展 03', 'prefix' => 'ZX-W3', 'reserved' => true),
      array('key' => 'caa-unknown-04', 'name' => '未知画展 04', 'prefix' => 'ZX-W4', 'reserved' => true),
      array('key' => 'caa-unknown-05', 'name' => '未知画展 05', 'prefix' => 'ZX-W5', 'reserved' => true),
      array('key' => 'caa-unknown-06', 'name' => '未知画展 06', 'prefix' => 'ZX-W6', 'reserved' => true),
      array('key' => 'caa-unknown-07', 'name' => '未知画展 07', 'prefix' => 'ZX-W7', 'reserved' => true),
      )),
    );
}

function gzca_sample_works_manifest()
{
  return array(
    array(
      'key' => 'ink-mountain',
      'file' => 'ink-mountain.jpg',
      'category_key' => 'ink-landscape',
      'title' => '国画山水样例',
      'description' => '本地生成样例作品，用于服务器首次部署后检查分类、编号和详情页效果。',
      'featured' => 1,
      'sort_order' => 10,
      'download_count' => 86,
      ),
    array(
      'key' => 'ink-flower-bird',
      'file' => 'ink-flower-bird.jpg',
      'category_key' => 'ink-flower-bird',
      'title' => '国画花鸟样例',
      'description' => '本地生成样例作品，用于检查国画花鸟分类和详情展示。',
      'featured' => 1,
      'sort_order' => 20,
      'download_count' => 73,
      ),
    array(
      'key' => 'oil-landscape',
      'file' => 'oil-landscape.jpg',
      'category_key' => 'oil-landscape',
      'title' => '油画风景样例',
      'description' => '本地生成样例作品，用于检查油画分类、首页排序和作品详情。',
      'featured' => 1,
      'sort_order' => 30,
      'download_count' => 92,
      ),
    array(
      'key' => 'oil-architecture',
      'file' => 'oil-architecture.jpg',
      'category_key' => 'oil-still',
      'title' => '油画静物样例',
      'description' => '本地生成样例作品，用于检查油画板块下的第二个样例作品。',
      'featured' => 0,
      'sort_order' => 40,
      'download_count' => 61,
      ),
    array(
      'key' => 'print-woodcut',
      'file' => 'print-woodcut.jpg',
      'category_key' => 'print-black-woodcut',
      'title' => '版画木刻样例',
      'description' => '本地生成样例作品，用于检查版画分类和详情展示。',
      'featured' => 1,
      'sort_order' => 50,
      'download_count' => 80,
      ),
    array(
      'key' => 'watercolor-landscape',
      'file' => 'watercolor-landscape.jpg',
      'category_key' => 'watercolor-landscape',
      'title' => '水彩风景样例',
      'description' => '本地生成样例作品，用于检查水彩分类和详情展示。',
      'featured' => 1,
      'sort_order' => 60,
      'download_count' => 67,
      ),
    array(
      'key' => 'folk-festival',
      'file' => 'folk-festival.jpg',
      'category_key' => 'folk-art',
      'title' => '农民画节庆样例',
      'description' => '本地生成样例作品，用于检查农民画直达分类。',
      'featured' => 0,
      'sort_order' => 70,
      'download_count' => 54,
      ),
    array(
      'key' => 'mixed-material',
      'file' => 'mixed-material.jpg',
      'category_key' => 'illustration-painted',
      'title' => '绘画风格插画样例',
      'description' => '本地生成样例作品，用于检查插画板块和搜索结果页。',
      'featured' => 0,
      'sort_order' => 80,
      'download_count' => 48,
      ),
    );
}

function gzca_sample_work_source($sample)
{
  $path = PHPWG_ROOT_PATH.'themes/guozhan-gallery/assets/works/'.$sample['file'];
  return is_file($path) ? $path : '';
}

function gzca_category_id_by_system_key($system_key)
{
  $system_key = strtolower(trim((string)$system_key));
  if ('' === $system_key || !gzca_database_table_exists(GZCA_CATEGORIES_TABLE))
  {
    return 0;
  }
  $result = pwg_query(
    "SELECT category_id FROM ".GZCA_CATEGORIES_TABLE." WHERE system_key = '".pwg_db_real_escape_string($system_key)."' AND catalog_enabled = 1 LIMIT 1;"
    );
  if (pwg_db_num_rows($result) === 0)
  {
    return 0;
  }
  $row = pwg_db_fetch_assoc($result);
  return (int)$row['category_id'];
}



function gzca_sample_work_exists($sample_key)
{
  $sample_key = preg_replace('/[^a-z0-9_-]/i', '', trim((string)$sample_key));
  if ('' === $sample_key)
  {
    return false;
  }
  $sample_key = str_replace(
    array('\\', '%', '_'),
    array('\\\\', '\\%', '\\_'),
    $sample_key
    );
  $result = pwg_query(
    "SELECT id FROM ".IMAGES_TABLE." WHERE comment LIKE '%GZCA_SAMPLE:".pwg_db_real_escape_string($sample_key)."%' ESCAPE '\\\\' LIMIT 1;"
    );
  return pwg_db_num_rows($result) > 0;
}

function gzca_sample_works_status()
{
  $total = 0;
  $imported = 0;
  $missing_assets = 0;
  foreach (gzca_sample_works_manifest() as $sample)
  {
    $total++;
    if (gzca_sample_work_exists($sample['key']))
    {
      $imported++;
    }
    if ('' === gzca_sample_work_source($sample))
    {
      $missing_assets++;
    }
  }
  return array(
    'total' => $total,
    'imported' => $imported,
    'missing_assets' => $missing_assets,
    'remaining' => max(0, $total - $imported),
    );
}

function gzca_import_sample_works(&$errors=array())
{
  global $conf;

  gzca_seed_categories();
  include_once(PHPWG_ROOT_PATH.'admin/include/functions_upload.inc.php');

  $created = 0;
  $skipped = 0;
  $category_ids = array();
  foreach (gzca_sample_works_manifest() as $sample)
  {
    if (gzca_sample_work_exists($sample['key']))
    {
      $skipped++;
      continue;
    }

    $source = gzca_sample_work_source($sample);
    if ('' === $source)
    {
      $errors[] = '样例图片缺失：'.$sample['file'];
      continue;
    }

    $category_id = gzca_category_id_by_system_key($sample['category_key']);
    if ($category_id < 1 || !gzca_category_is_upload_target($category_id))
    {
      $errors[] = '样例作品“'.$sample['title'].'”找不到可上传分类：'.$sample['category_key'];
      continue;
    }

    $prefix = gzca_category_prefix($category_id);
    if (!gzca_valid_prefix($prefix))
    {
      $errors[] = '样例作品“'.$sample['title'].'”所在分类缺少有效编号前缀。';
      continue;
    }

    $tmp = tempnam(sys_get_temp_dir(), 'gzca_sample_');
    if (false === $tmp || !copy($source, $tmp))
    {
      if (is_string($tmp) && file_exists($tmp))
      {
        @unlink($tmp);
      }
      $errors[] = '样例作品“'.$sample['title'].'”复制到临时文件失败。';
      continue;
    }

    $image_id = add_uploaded_file($tmp, $sample['file'], array($category_id), 0);
    if (empty($image_id))
    {
      if (file_exists($tmp))
      {
        @unlink($tmp);
      }
      $errors[] = '样例作品“'.$sample['title'].'”写入图库失败。';
      continue;
    }

    $code = gzca_next_code($prefix);
    single_update(
      IMAGES_TABLE,
      array(
        'name' => $sample['title'],
        'comment' => gzca_embed_sample_code($sample['description'], $code, $sample['key']),
        'level' => 0,
        ),
      array('id' => (int)$image_id)
      );
    gzca_save_work_meta(
      $image_id,
      $code,
      'online',
      !empty($sample['featured']) ? 1 : 0,
      isset($sample['sort_order']) ? (int)$sample['sort_order'] : 0,
      isset($sample['download_count']) ? (int)$sample['download_count'] : 0
      );
    gzca_set_category_cover($category_id, $image_id);
    update_category($category_id);
    $category_ids[$category_id] = true;
    $created++;
  }

  if ($created > 0)
  {
    foreach (array_keys($category_ids) as $category_id)
    {
      update_category((int)$category_id);
    }
    invalidate_user_cache();
  }

  return array(
    'created' => $created,
    'skipped' => $skipped,
    'errors' => $errors,
    );
}

function gzca_contract_category_keys()
{
  $keys = array();
  foreach (gzca_category_contract() as $group)
  {
    $keys[] = $group['key'];
    foreach ($group['children'] as $child)
    {
      $keys[] = $child['key'];
    }
  }
  return $keys;
}

function gzca_find_category_by_system_key($system_key)
{
  $system_key = pwg_db_real_escape_string(strtolower(trim((string)$system_key)));
  $result = pwg_query(
    "SELECT category_id FROM ".GZCA_CATEGORIES_TABLE." WHERE system_key = '".$system_key."' LIMIT 1;"
    );
  if (0 === pwg_db_num_rows($result))
  {
    return null;
  }
  $row = pwg_db_fetch_assoc($result);
  return (int)$row['category_id'];
}

function gzca_find_category_by_prefix($prefix, $parent_id=null)
{
  $prefix = pwg_db_real_escape_string(gzca_normalize_code($prefix));
  $query = '
SELECT c.id
  FROM '.CATEGORIES_TABLE.' AS c
    INNER JOIN '.GZCA_CATEGORIES_TABLE.' AS gc ON gc.category_id = c.id
  WHERE gc.code_prefix = \''.$prefix.'\'
    AND c.id_uppercat '.(empty($parent_id) ? 'IS NULL' : '= '.(int)$parent_id).'
  LIMIT 1
;';
  $result = pwg_query($query);
  if (0 === pwg_db_num_rows($result))
  {
    return null;
  }
  $row = pwg_db_fetch_assoc($result);
  return (int)$row['id'];
}

function gzca_seed_category_node($item, $parent_id, $sort_order, $kind, &$created)
{
  $category_id = gzca_find_category_by_system_key($item['key']);
  $matched_by_key = null !== $category_id;
  if (null === $category_id)
  {
    $category_id = gzca_find_category_id($item['name'], $parent_id);
  }
  if (null === $category_id)
  {
    $category_id = gzca_find_category_by_prefix($item['prefix'], $parent_id);
  }

  $description = isset($item['description'])
    ? $item['description']
    : (!empty($item['reserved']) ? '预留画展，后续可修改名称、编号前缀并上传作品。' : $item['name'].'作品。');

  if (null === $category_id)
  {
    $result = create_virtual_category(
      $item['name'],
      $parent_id,
      array('visible' => true, 'comment' => $description, 'status' => 'public')
      );
    if (empty($result['id']))
    {
      return null;
    }
    $category_id = (int)$result['id'];
    $created++;
  }
  elseif (!$matched_by_key)
  {
    single_update(
      CATEGORIES_TABLE,
      array('name' => $item['name'], 'comment' => $description),
      array('id' => $category_id)
      );
  }

  if (!$matched_by_key)
  {
    gzca_save_category_meta(
      $category_id,
      $item['prefix'],
      $sort_order,
      1,
      true,
      $item['key'],
      $kind,
      !empty($item['reserved'])
      );
  }

  return $category_id;
}

function gzca_disable_legacy_category_contract()
{
  $legacy_prefixes = array(
    'GH-FJ', 'GH-JW',
    'BH-FJ', 'BH-RW', 'BH-JW',
    'DS-FJ', 'DS-RW', 'DS-JW',
    );
  $escaped = array_map(function ($prefix) {
    return "'".pwg_db_real_escape_string($prefix)."'";
  }, $legacy_prefixes);
  pwg_query(
    'UPDATE '.GZCA_CATEGORIES_TABLE.
    ' SET catalog_enabled = 0, updated_at = NOW()'.
    " WHERE (system_key IS NULL OR system_key = '') AND code_prefix IN (".implode(',', $escaped).');'
    );
}

function gzca_mark_contract_categories_uploadable()
{
  $keys = gzca_contract_category_keys();
  if (empty($keys))
  {
    return;
  }
  $escaped = array_map(function ($key) {
    return "'".pwg_db_real_escape_string($key)."'";
  }, $keys);
  pwg_query(
    'UPDATE '.GZCA_CATEGORIES_TABLE.
    ' SET catalog_enabled = 1, direct_upload = 1, updated_at = NOW()'.
    ' WHERE system_key IN ('.implode(',', $escaped).');'
    );
}

function gzca_category_is_contract_node($category_id)
{
  $category_id = (int)$category_id;
  if ($category_id < 1)
  {
    return false;
  }
  $keys = gzca_contract_category_keys();
  if (empty($keys))
  {
    return false;
  }
  $escaped = array_map(function ($key) {
    return "'".pwg_db_real_escape_string($key)."'";
  }, $keys);
  list($count) = pwg_db_fetch_row(pwg_query(
    'SELECT COUNT(*) FROM '.GZCA_CATEGORIES_TABLE.
    ' WHERE category_id = '.$category_id.' AND system_key IN ('.implode(',', $escaped).');'
    ));
  return (int)$count > 0;
}

function gzca_seed_categories()
{
  $created = 0;
  foreach (gzca_category_contract() as $group_index => $group)
  {
    $kind = isset($group['kind']) ? $group['kind'] : 'catalog';
    $parent_id = gzca_seed_category_node($group, null, $group_index + 1, $kind, $created);
    if (null === $parent_id)
    {
      continue;
    }

    foreach ($group['children'] as $child_index => $child)
    {
      $child['direct'] = true;
      gzca_seed_category_node($child, $parent_id, $child_index + 1, $kind, $created);
    }
  }

  gzca_disable_legacy_category_contract();
  gzca_mark_contract_categories_uploadable();

  update_global_rank();
  invalidate_user_cache();
  return $created;
}











function gzca_work_meta($image)
{
  $code = !empty($image['gzca_code'])
    ? $image['gzca_code']
    : gzca_extract_code(isset($image['comment']) ? $image['comment'] : '', 'ID-'.$image['id']);
  $status = !empty($image['gzca_status'])
    ? $image['gzca_status']
    : ((int)$image['level'] === 0 ? 'online' : 'offline');

  return array(
    'code' => $code,
    'status' => $status,
    'featured' => !empty($image['featured']),
    'sort_order' => isset($image['sort_order']) ? (int)$image['sort_order'] : 0,
    'download_count' => isset($image['download_count']) ? (int)$image['download_count'] : 0,
    );
}

function gzca_code_exists($code, $exclude_image_id=0)
{
  $code = pwg_db_real_escape_string(gzca_normalize_code($code));
  $query = '
SELECT COUNT(*)
  FROM '.GZCA_WORKS_TABLE.'
  WHERE code = \''.$code.'\''.
  ((int)$exclude_image_id > 0 ? ' AND image_id <> '.(int)$exclude_image_id : '').'
;';
  list($count) = pwg_db_fetch_row(pwg_query($query));
  return (int)$count > 0;
}

function gzca_save_work_meta($image_id, $code, $status, $featured=0, $sort_order=0, $download_count=0)
{
  $image_id = (int)$image_id;
  $data = array(
    'code' => gzca_normalize_code($code),
    'status' => 'offline' === $status ? 'offline' : 'online',
    'featured' => !empty($featured) ? 1 : 0,
    'sort_order' => (int)$sort_order,
    'download_count' => max(0, (int)$download_count),
    'updated_at' => date('Y-m-d H:i:s'),
    );

  $exists = pwg_db_num_rows(pwg_query(
    'SELECT image_id FROM '.GZCA_WORKS_TABLE.' WHERE image_id = '.$image_id.';'
    )) > 0;

  if ($exists)
  {
    single_update(GZCA_WORKS_TABLE, $data, array('image_id' => $image_id));
  }
  else
  {
    $data['image_id'] = $image_id;
    single_insert(GZCA_WORKS_TABLE, $data);
  }
}

function gzca_next_code_number($prefix)
{
  $prefix = gzca_normalize_code($prefix);
  if (!gzca_valid_prefix($prefix))
  {
    $prefix = 'GZ';
  }

  $escaped = pwg_db_real_escape_string($prefix.'-%');
  $rows = query2array(
    'SELECT code FROM '.GZCA_WORKS_TABLE.' WHERE code LIKE \''.$escaped.'\';'
    );
  $max = 0;
  foreach ($rows as $row)
  {
    if (preg_match('/^'.preg_quote($prefix, '/').'-(\d+)$/', $row['code'], $matches))
    {
      $max = max($max, (int)$matches[1]);
    }
  }

  return $max + 1;
}

function gzca_code_from_number($prefix, $number)
{
  $prefix = gzca_normalize_code($prefix);
  if (!gzca_valid_prefix($prefix))
  {
    $prefix = 'GZ';
  }

  return $prefix.'-'.str_pad((string)max(1, (int)$number), 3, '0', STR_PAD_LEFT);
}

function gzca_next_code($prefix)
{
  return gzca_code_from_number($prefix, gzca_next_code_number($prefix));
}

function gzca_upload_batch_limits()
{
  return array(
    'max_files' => 50,
    'max_bytes' => 72 * 1024 * 1024,
    );
}

function gzca_find_duplicate_upload($filepath)
{
  if (!is_file($filepath))
  {
    return null;
  }

  $md5sum = @md5_file($filepath);
  if (false === $md5sum || !preg_match('/^[a-f0-9]{32}$/', $md5sum))
  {
    return null;
  }

  $result = pwg_query('
SELECT i.id AS image_id, i.comment, gw.code
  FROM '.IMAGES_TABLE.' AS i
  LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
  WHERE i.md5sum = \''.pwg_db_real_escape_string($md5sum).'\'
  ORDER BY i.id ASC
  LIMIT 1
;');
  if (0 === pwg_db_num_rows($result))
  {
    return null;
  }

  $row = pwg_db_fetch_assoc($result);
  $image_id = (int)$row['image_id'];
  $code = gzca_normalize_code(isset($row['code']) ? $row['code'] : '');
  if ('' === $code)
  {
    $code = gzca_extract_code(isset($row['comment']) ? $row['comment'] : '', 'ID-'.$image_id);
  }

  $category_paths = array();
  $category_result = pwg_query('
SELECT DISTINCT category_id
  FROM '.IMAGE_CATEGORY_TABLE.'
  WHERE image_id = '.$image_id.'
  ORDER BY category_id ASC
;');
  while ($category = pwg_db_fetch_assoc($category_result))
  {
    $path = gzca_category_path((int)$category['category_id']);
    if ('' !== $path)
    {
      $category_paths[] = $path;
    }
  }

  return array(
    'image_id' => $image_id,
    'code' => $code,
    'category_paths' => array_values(array_unique($category_paths)),
    );
}

function gzca_upload_works_batch($album_id, $uploads, $publish_now, $set_cover, &$errors=array())
{
  global $conf;

  $errors = array();
  $album_id = (int)$album_id;
  $prefix = gzca_category_prefix($album_id);
  $uploads = is_array($uploads) ? $uploads : array();
  $batch_limits = gzca_upload_batch_limits();
  $result = array(
    'uploaded' => 0,
    'uploaded_ids' => array(),
    'uploaded_codes' => array(),
    'compressed_count' => 0,
    'compressed_saved_bytes' => 0,
    'category_path' => '',
    'message' => '',
    );

  if (!gzca_category_is_upload_target($album_id))
  {
    $errors[] = '请选择可以直接上传作品的前台板块、分类或画展。';
    return $result;
  }
  if (!gzca_valid_prefix($prefix))
  {
    $errors[] = '所选分类尚未设置有效编号前缀，请先到分类管理中完善。';
    return $result;
  }
  if (empty($uploads))
  {
    $errors[] = '请至少选择一张作品图片。';
    return $result;
  }
  if (count($uploads) > $batch_limits['max_files'])
  {
    $errors[] = '单批最多上传 '.$batch_limits['max_files'].' 张图片。当前批次 '.count($uploads).' 张，请使用自动分批上传。';
    return $result;
  }

  $upload_bytes = 0;
  foreach ($uploads as $upload)
  {
    $upload_bytes += isset($upload['size']) ? max(0, (int)$upload['size']) : 0;
  }
  if ($upload_bytes > $batch_limits['max_bytes'])
  {
    $errors[] = '单批图片总大小不能超过 72 MB。当前批次约 '.gzca_format_bytes($upload_bytes).'，请减少每批数量。';
    return $result;
  }

  if (!function_exists('add_uploaded_file'))
  {
    include_once(PHPWG_ROOT_PATH.'admin/include/functions_upload.inc.php');
  }

  $next_code_number = gzca_next_code_number($prefix);

  foreach ($uploads as $file)
  {
    $file_name = isset($file['name']) ? (string)$file['name'] : '';
    if (UPLOAD_ERR_OK !== (int)$file['error'])
    {
      $errors[] = '文件“'.htmlspecialchars($file_name, ENT_QUOTES, 'UTF-8').'”上传失败，错误代码 '.(int)$file['error'].'。';
      continue;
    }

    $extension = strtolower(get_extension($file_name));
    if (!in_array($extension, $conf['picture_ext'], true) || false === @getimagesize($file['tmp_name']))
    {
      $errors[] = '文件“'.htmlspecialchars($file_name, ENT_QUOTES, 'UTF-8').'”不是受支持的图片。';
      continue;
    }

    $compression_info = array();
    $prepared_upload = gzca_prepare_upload_image_file($file['tmp_name'], $file_name, $compression_info);
    $duplicate = gzca_find_duplicate_upload($prepared_upload['filepath']);
    if (null !== $duplicate)
    {
      if (!empty($prepared_upload['compressed']) && is_file($prepared_upload['filepath']))
      {
        @unlink($prepared_upload['filepath']);
      }

      $duplicate_code = htmlspecialchars($duplicate['code'], ENT_QUOTES, 'UTF-8');
      $duplicate_categories = empty($duplicate['category_paths'])
        ? '图库中的现有板块'
        : implode('、', $duplicate['category_paths']);
      $target_category = gzca_category_path($album_id);
      $errors[] = '文件“'.htmlspecialchars($file_name, ENT_QUOTES, 'UTF-8').'”与已有作品“'.$duplicate_code.'”相同，已有作品所属板块：“'.htmlspecialchars($duplicate_categories, ENT_QUOTES, 'UTF-8').'”。本次未上传，也未关联到“'.htmlspecialchars($target_category, ENT_QUOTES, 'UTF-8').'”。';
      continue;
    }

    if (!empty($prepared_upload['compressed']))
    {
      $result['compressed_count']++;
      $result['compressed_saved_bytes'] += max(0, (int)$prepared_upload['original_bytes'] - (int)$prepared_upload['final_bytes']);
    }

    $image_id = add_uploaded_file(
      $prepared_upload['filepath'],
      $prepared_upload['filename'],
      array($album_id),
      $publish_now ? 0 : 8
      );

    if (empty($image_id))
    {
      $errors[] = '文件“'.htmlspecialchars($file_name, ENT_QUOTES, 'UTF-8').'”未能写入图库。';
      continue;
    }

    $existing_meta = pwg_db_num_rows(pwg_query(
      'SELECT image_id FROM '.GZCA_WORKS_TABLE.' WHERE image_id = '.(int)$image_id.';'
      )) > 0;
    if (!$existing_meta)
    {
      $code = gzca_code_from_number($prefix, $next_code_number++);
      $title = gzca_clean_text(pathinfo($file_name, PATHINFO_FILENAME), 255);
      single_update(
        IMAGES_TABLE,
        array(
          'name' => $title,
          'comment' => gzca_embed_code('', $code),
          'level' => $publish_now ? 0 : 8,
          ),
        array('id' => (int)$image_id)
      );
      gzca_save_work_meta($image_id, $code, $publish_now ? 'online' : 'offline', 0, 0, 0);
      $result['uploaded_codes'][] = $code;
    }

    $result['uploaded_ids'][] = (int)$image_id;
  }

  if (!empty($result['uploaded_ids']))
  {
    $lounge_category_ids = gzca_flush_lounge_for_images($result['uploaded_ids']);
    $result['derivatives_generated'] = gzca_prewarm_image_derivatives($result['uploaded_ids']);
    if ($set_cover)
    {
      gzca_set_category_cover($album_id, $result['uploaded_ids'][0]);
    }
    foreach (array_unique(array_merge(array($album_id), $lounge_category_ids)) as $changed_category_id)
    {
      if ((int)$changed_category_id > 0)
      {
        update_category((int)$changed_category_id);
      }
    }
    invalidate_user_cache();
    $result['uploaded'] = count($result['uploaded_ids']);
    $result['category_path'] = gzca_category_path($album_id);
    $code_summary = empty($result['uploaded_codes'])
      ? ''
      : '，编号 '.reset($result['uploaded_codes']).(count($result['uploaded_codes']) > 1 ? ' 至 '.end($result['uploaded_codes']) : '');
    $result['message'] = '已上传 '.$result['uploaded'].' 张作品到“'.$result['category_path'].'”'.$code_summary.'。';
  }

  return $result;
}

function gzca_normalize_uploads($files)
{
  $normalized = array();
  if (empty($files) || !isset($files['name']))
  {
    return $normalized;
  }

  if (!is_array($files['name']))
  {
    $files = array(
      'name' => array($files['name']),
      'type' => array($files['type']),
      'tmp_name' => array($files['tmp_name']),
      'error' => array($files['error']),
      'size' => array($files['size']),
      );
  }

  foreach ($files['name'] as $index => $name)
  {
    $normalized[] = array(
      'name' => $name,
      'type' => isset($files['type'][$index]) ? $files['type'][$index] : '',
      'tmp_name' => isset($files['tmp_name'][$index]) ? $files['tmp_name'][$index] : '',
      'error' => isset($files['error'][$index]) ? (int)$files['error'][$index] : UPLOAD_ERR_NO_FILE,
      'size' => isset($files['size'][$index]) ? (int)$files['size'][$index] : 0,
      );
  }

  return $normalized;
}

function gzca_format_bytes($bytes)
{
  $bytes = max(0, (int)$bytes);
  if ($bytes >= 1024 * 1024)
  {
    return round($bytes / 1024 / 1024, 1).' MB';
  }
  return max(1, round($bytes / 1024)).' KB';
}

function gzca_upload_storage_limits()
{
  return array(
    'max_bytes' => 3 * 1024 * 1024,
    'target_bytes' => 2400 * 1024,
    'max_side' => 2400,
    'qualities' => array(88, 84, 80, 76, 72, 68),
    );
}

function gzca_delete_lounge_rows_for_images($image_ids)
{
  if (!defined('LOUNGE_TABLE'))
  {
    return;
  }
  $image_ids = array_values(array_unique(array_filter(array_map('intval', (array)$image_ids))));
  if (empty($image_ids))
  {
    return;
  }
  pwg_query('DELETE FROM '.LOUNGE_TABLE.' WHERE image_id IN ('.implode(',', $image_ids).');');
}

function gzca_flush_lounge_for_images($image_ids)
{
  if (!defined('LOUNGE_TABLE'))
  {
    return array();
  }
  $image_ids = array_values(array_unique(array_filter(array_map('intval', (array)$image_ids))));
  if (empty($image_ids))
  {
    return array();
  }
  if (!function_exists('associate_images_to_categories'))
  {
    include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
  }

  $rows = query2array('
SELECT image_id, category_id
  FROM '.LOUNGE_TABLE.'
  WHERE image_id IN ('.implode(',', $image_ids).')
  ORDER BY category_id ASC, image_id ASC
;');
  if (empty($rows))
  {
    return array();
  }

  $by_category = array();
  foreach ($rows as $row)
  {
    $category_id = (int)$row['category_id'];
    $image_id = (int)$row['image_id'];
    if ($category_id < 1 || $image_id < 1)
    {
      continue;
    }
    if (!isset($by_category[$category_id]))
    {
      $by_category[$category_id] = array();
    }
    $by_category[$category_id][] = $image_id;
  }

  foreach ($by_category as $category_id => $ids)
  {
    associate_images_to_categories(array_values(array_unique($ids)), array((int)$category_id));
  }
  gzca_delete_lounge_rows_for_images($image_ids);
  foreach (array_keys($by_category) as $category_id)
  {
    update_category((int)$category_id);
  }
  invalidate_user_cache();
  return array_map('intval', array_keys($by_category));
}

function gzca_prewarm_image_derivatives($image_ids, $types=null)
{
  $image_ids = array_values(array_unique(array_filter(array_map('intval', (array)$image_ids))));
  if (empty($image_ids) || !class_exists('SrcImage') || !class_exists('DerivativeImage'))
  {
    return 0;
  }

  if (null === $types)
  {
    $types = array(IMG_XSMALL, IMG_THUMB, IMG_SQUARE);
  }

  $rows = query2array('SELECT * FROM '.IMAGES_TABLE.' WHERE id IN ('.implode(',', $image_ids).');');
  $warmed = 0;
  foreach ($rows as $row)
  {
    foreach ($types as $type)
    {
      try
      {
        $source = new SrcImage($row);
        $derivative = DerivativeImage::get_one($type, $source);
        if (null === $derivative)
        {
          continue;
        }
        $path = $derivative->get_path();
        if (is_file($path))
        {
          $warmed++;
          continue;
        }

        $url = $derivative->get_url();
        $marker = 'i.php?';
        $pos = strpos($url, $marker);
        if (false === $pos)
        {
          continue;
        }
        $query = html_entity_decode(substr($url, $pos + strlen($marker)), ENT_QUOTES, 'UTF-8');
        $query = rawurldecode($query);
        if ('' === $query || '/' !== $query[0])
        {
          continue;
        }

        $php = (defined('PHP_BINARY') && is_executable(PHP_BINARY)) ? PHP_BINARY : '/usr/bin/php';
        if (!is_executable($php))
        {
          $php = 'php';
        }
        $host = isset($_SERVER['HTTP_HOST']) && '' !== $_SERVER['HTTP_HOST'] ? $_SERVER['HTTP_HOST'] : 'localhost';
        $cmd = 'cd '.escapeshellarg(PHPWG_ROOT_PATH).' && '.
          'QUERY_STRING='.escapeshellarg($query).' '.
          'REQUEST_URI='.escapeshellarg('/i.php?'.$query).' '.
          'SERVER_PROTOCOL='.escapeshellarg('HTTP/1.1').' '.
          'HTTP_HOST='.escapeshellarg($host).' '.
          'REMOTE_ADDR='.escapeshellarg('127.0.0.1').' '.
          escapeshellarg($php).' i.php > /dev/null 2>&1';
        @exec($cmd, $unused_output, $exit_code);
        if (0 === (int)$exit_code && is_file($path))
        {
          $warmed++;
        }
      }
      catch (Throwable $error)
      {
        continue;
      }
    }
  }
  return $warmed;
}


function gzca_try_compress_with_imagick($source_filepath, $target_filepath, $limits)
{
  if (!class_exists('Imagick'))
  {
    return false;
  }

  try
  {
    $image = new Imagick($source_filepath);
    if (method_exists($image, 'setIteratorIndex'))
    {
      $image->setIteratorIndex(0);
    }
    if (method_exists($image, 'autoOrient'))
    {
      $image->autoOrient();
    }
    $image->stripImage();

    $width = $image->getImageWidth();
    $height = $image->getImageHeight();
    $max_side = max($width, $height);
    if ($max_side > $limits['max_side'])
    {
      $scale = $limits['max_side'] / $max_side;
      $image->resizeImage(max(1, (int)round($width * $scale)), max(1, (int)round($height * $scale)), Imagick::FILTER_LANCZOS, 1);
    }

    $canvas = new Imagick();
    $canvas->newImage($image->getImageWidth(), $image->getImageHeight(), 'white', 'jpg');
    $canvas->compositeImage($image, Imagick::COMPOSITE_OVER, 0, 0);
    $canvas->setImageFormat('jpeg');
    $canvas->setImageCompression(Imagick::COMPRESSION_JPEG);

    foreach ($limits['qualities'] as $quality)
    {
      $canvas->setImageCompressionQuality($quality);
      $canvas->writeImage($target_filepath);
      if (@filesize($target_filepath) <= $limits['max_bytes'])
      {
        break;
      }
    }

    $image->clear();
    $image->destroy();
    $canvas->clear();
    $canvas->destroy();
    return is_file($target_filepath) && filesize($target_filepath) > 0;
  }
  catch (Throwable $error)
  {
    if (isset($image) && $image instanceof Imagick)
    {
      $image->clear();
      $image->destroy();
    }
    if (isset($canvas) && $canvas instanceof Imagick)
    {
      $canvas->clear();
      $canvas->destroy();
    }
    @unlink($target_filepath);
    return false;
  }
}

function gzca_try_compress_with_gd($source_filepath, $target_filepath, $image_type, $limits)
{
  if (!extension_loaded('gd'))
  {
    return false;
  }

  if (IMAGETYPE_JPEG === $image_type && function_exists('imagecreatefromjpeg'))
  {
    $source = @imagecreatefromjpeg($source_filepath);
  }
  elseif (IMAGETYPE_PNG === $image_type && function_exists('imagecreatefrompng'))
  {
    $source = @imagecreatefrompng($source_filepath);
  }
  elseif (IMAGETYPE_WEBP === $image_type && function_exists('imagecreatefromwebp'))
  {
    $source = @imagecreatefromwebp($source_filepath);
  }
  else
  {
    return false;
  }

  if (!$source)
  {
    return false;
  }

  $width = imagesx($source);
  $height = imagesy($source);
  $max_side = max($width, $height);
  $scale = $max_side > $limits['max_side'] ? $limits['max_side'] / $max_side : 1;
  $target_width = max(1, (int)round($width * $scale));
  $target_height = max(1, (int)round($height * $scale));
  $target = imagecreatetruecolor($target_width, $target_height);
  $white = imagecolorallocate($target, 255, 255, 255);
  imagefilledrectangle($target, 0, 0, $target_width, $target_height, $white);
  imagecopyresampled($target, $source, 0, 0, 0, 0, $target_width, $target_height, $width, $height);

  foreach ($limits['qualities'] as $quality)
  {
    imagejpeg($target, $target_filepath, $quality);
    if (@filesize($target_filepath) <= $limits['max_bytes'])
    {
      break;
    }
  }

  imagedestroy($source);
  imagedestroy($target);
  return is_file($target_filepath) && filesize($target_filepath) > 0;
}

function gzca_prepare_upload_image_file($source_filepath, $original_filename, &$compression_info=array())
{
  $compression_info = array(
    'compressed' => false,
    'original_bytes' => is_file($source_filepath) ? (int)@filesize($source_filepath) : 0,
    'final_bytes' => is_file($source_filepath) ? (int)@filesize($source_filepath) : 0,
    'filepath' => $source_filepath,
    'filename' => $original_filename,
    );

  if (!is_file($source_filepath))
  {
    return $compression_info;
  }

  $image_info = @getimagesize($source_filepath);
  if (empty($image_info) || !isset($image_info[0], $image_info[1], $image_info[2]))
  {
    return $compression_info;
  }

  $image_type = (int)$image_info[2];
  if (IMAGETYPE_GIF === $image_type)
  {
    return $compression_info;
  }

  $limits = gzca_upload_storage_limits();
  $original_bytes = (int)@filesize($source_filepath);
  $max_side = max((int)$image_info[0], (int)$image_info[1]);
  if ($original_bytes <= $limits['max_bytes'] && $max_side <= $limits['max_side'])
  {
    return $compression_info;
  }

  $tmp = tempnam(sys_get_temp_dir(), 'gzca-upload-');
  if (false === $tmp)
  {
    return $compression_info;
  }
  @unlink($tmp);
  $target_filepath = $tmp.'.jpg';

  $ok = gzca_try_compress_with_imagick($source_filepath, $target_filepath, $limits);
  if (!$ok)
  {
    $ok = gzca_try_compress_with_gd($source_filepath, $target_filepath, $image_type, $limits);
  }

  if (!$ok || !is_file($target_filepath))
  {
    @unlink($target_filepath);
    return $compression_info;
  }

  if ((int)@filesize($target_filepath) > $limits['max_bytes'])
  {
    foreach (array(2000, 1800, 1600) as $retry_side)
    {
      $retry_limits = $limits;
      $retry_limits['max_side'] = $retry_side;
      $retry_limits['qualities'] = array(76, 72, 68, 64);
      @unlink($target_filepath);
      $retry_ok = gzca_try_compress_with_imagick($source_filepath, $target_filepath, $retry_limits);
      if (!$retry_ok)
      {
        $retry_ok = gzca_try_compress_with_gd($source_filepath, $target_filepath, $image_type, $retry_limits);
      }
      if ($retry_ok && is_file($target_filepath) && (int)@filesize($target_filepath) <= $limits['max_bytes'])
      {
        break;
      }
    }
  }

  $final_bytes = (int)@filesize($target_filepath);
  if ($final_bytes <= 0 || ($final_bytes >= $original_bytes && $max_side <= $limits['max_side']) || $final_bytes > $limits['max_bytes'])
  {
    @unlink($target_filepath);
    return $compression_info;
  }

  $name = pathinfo((string)$original_filename, PATHINFO_FILENAME);
  $name = '' === $name ? 'artwork' : $name;
  $compression_info['compressed'] = true;
  $compression_info['final_bytes'] = $final_bytes;
  $compression_info['filepath'] = $target_filepath;
  $compression_info['filename'] = $name.'.jpg';
  return $compression_info;
}

function gzca_image_derivative_url($image, $type)
{
  try
  {
    $source = new SrcImage($image);
    return DerivativeImage::url($type, $source);
  }
  catch (Throwable $error)
  {
    return '';
  }
}

function gzca_image_thumb_url($image)
{
  return gzca_image_derivative_url($image, IMG_SQUARE);
}

function gzca_image_list_url($image)
{
  return gzca_image_derivative_url($image, IMG_XSMALL);
}

function gzca_category_from_work_code($code)
{
  $code = gzca_normalize_code($code);
  if ('' === $code || !gzca_database_table_exists(GZCA_CATEGORIES_TABLE))
  {
    return null;
  }

  $rows = query2array('
SELECT
    c.id,
    c.name,
    gc.code_prefix
  FROM '.CATEGORIES_TABLE.' AS c
    INNER JOIN '.GZCA_CATEGORIES_TABLE.' AS gc ON gc.category_id = c.id
  WHERE gc.catalog_enabled = 1
    AND gc.code_prefix <> \'\'
  ORDER BY LENGTH(gc.code_prefix) DESC, c.global_rank
;');
  foreach ($rows as $row)
  {
    $prefix = gzca_normalize_code($row['code_prefix']);
    if ('' !== $prefix && (0 === strpos($code, $prefix.'-') || $code === $prefix))
    {
      return array(
        'id' => (int)$row['id'],
        'name' => $row['name'],
        );
    }
  }

  return null;
}

function gzca_image_category_links($image_id)
{
  $image_id = (int)$image_id;
  if ($image_id < 1)
  {
    return array();
  }
  $rows = query2array('
SELECT
    ic.category_id,
    c.name AS category_name,
    gc.code_prefix,
    gc.system_key,
    gc.catalog_enabled,
    gc.category_kind
  FROM '.IMAGE_CATEGORY_TABLE.' AS ic
    INNER JOIN '.CATEGORIES_TABLE.' AS c ON c.id = ic.category_id
    LEFT JOIN '.GZCA_CATEGORIES_TABLE.' AS gc ON gc.category_id = c.id
  WHERE ic.image_id = '.$image_id.'
  ORDER BY LENGTH(c.uppercats) DESC, c.global_rank, c.id
;');
  $links = array();
  foreach ($rows as $row)
  {
    $category_id = (int)$row['category_id'];
    $path = gzca_category_path($category_id);
    $links[] = array(
      'id' => $category_id,
      'name' => $row['category_name'],
      'path' => '' !== $path ? $path : $row['category_name'],
      'prefix' => isset($row['code_prefix']) ? (string)$row['code_prefix'] : '',
      'key' => isset($row['system_key']) ? (string)$row['system_key'] : '',
      'catalog_enabled' => !empty($row['catalog_enabled']),
      'kind' => isset($row['category_kind']) ? (string)$row['category_kind'] : '',
      );
  }
  return $links;
}

function gzca_image_other_category_links($image_id, $target_category_id)
{
  $target_category_id = (int)$target_category_id;
  return array_values(array_filter(gzca_image_category_links($image_id), function ($link) use ($target_category_id) {
    return (int)$link['id'] !== $target_category_id;
  }));
}

function gzca_sync_images_to_single_category($image_ids, $target_category_id)
{
  $target_category_id = (int)$target_category_id;
  $image_ids = array_values(array_unique(array_filter(array_map('intval', (array)$image_ids))));
  $result = array('old_category_ids' => array(), 'removed_category_ids' => array());
  if ($target_category_id < 1 || empty($image_ids))
  {
    return $result;
  }

  if (!function_exists('update_category'))
  {
    include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
  }

  $id_sql = implode(',', $image_ids);
  $old_rows = query2array('SELECT DISTINCT category_id FROM '.IMAGE_CATEGORY_TABLE.' WHERE image_id IN ('.$id_sql.');');
  foreach ($old_rows as $old_row)
  {
    $category_id = (int)$old_row['category_id'];
    $result['old_category_ids'][] = $category_id;
    if ($category_id !== $target_category_id)
    {
      $result['removed_category_ids'][] = $category_id;
    }
  }

  pwg_query('DELETE FROM '.IMAGE_CATEGORY_TABLE.' WHERE image_id IN ('.$id_sql.') AND category_id <> '.$target_category_id.';');

  $existing_rows = query2array('SELECT image_id FROM '.IMAGE_CATEGORY_TABLE.' WHERE category_id = '.$target_category_id.' AND image_id IN ('.$id_sql.');');
  $existing = array();
  foreach ($existing_rows as $row)
  {
    $existing[(int)$row['image_id']] = true;
  }

  list($max_rank) = pwg_db_fetch_row(pwg_query('SELECT COALESCE(MAX(`rank`), 0) FROM '.IMAGE_CATEGORY_TABLE.' WHERE category_id = '.$target_category_id.';'));
  $rank = (int)$max_rank;
  $inserts = array();
  foreach ($image_ids as $image_id)
  {
    if (empty($existing[$image_id]))
    {
      $inserts[] = array(
        'image_id' => $image_id,
        'category_id' => $target_category_id,
        'rank' => ++$rank,
        );
    }
  }
  if (!empty($inserts))
  {
    mass_inserts(IMAGE_CATEGORY_TABLE, array('image_id', 'category_id', 'rank'), $inserts);
  }

  foreach (array_unique(array_merge($result['old_category_ids'], array($target_category_id))) as $category_id)
  {
    if ((int)$category_id > 0)
    {
      update_category((int)$category_id);
    }
  }
  invalidate_user_cache();
  return $result;
}

function gzca_delete_works($image_ids, &$error='')
{
  $image_ids = array_values(array_unique(array_filter(array_map('intval', (array)$image_ids))));
  if (empty($image_ids))
  {
    $error = '没有选择要删除的作品。';
    return false;
  }

  if (!function_exists('delete_elements'))
  {
    include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');
  }

  $id_sql = implode(',', $image_ids);
  $existing_ids = array_map('intval', query2array(
    'SELECT id FROM '.IMAGES_TABLE.' WHERE id IN ('.$id_sql.');',
    null,
    'id'
    ));
  if (empty($existing_ids))
  {
    gzca_delete_lounge_rows_for_images($image_ids);
    pwg_query('DELETE FROM '.GZCA_WORKS_TABLE.' WHERE image_id IN ('.$id_sql.');');
    $error = '作品不存在或已经被删除，已清理残留元数据。';
    return false;
  }

  gzca_delete_lounge_rows_for_images($existing_ids);
  $existing_sql = implode(',', $existing_ids);
  $old_category_ids = array_map('intval', query2array(
    'SELECT DISTINCT category_id FROM '.IMAGE_CATEGORY_TABLE.' WHERE image_id IN ('.$existing_sql.');',
    null,
    'category_id'
    ));

  delete_elements($existing_ids, true);

  $remaining_ids = array_map('intval', query2array(
    'SELECT id FROM '.IMAGES_TABLE.' WHERE id IN ('.$existing_sql.');',
    null,
    'id'
    ));
  $remaining_lookup = array_fill_keys($remaining_ids, true);
  $deleted_ids = array();
  foreach ($existing_ids as $image_id)
  {
    if (empty($remaining_lookup[$image_id]))
    {
      $deleted_ids[] = $image_id;
    }
  }

  if (empty($deleted_ids))
  {
    $error = '没有作品被删除，可能是图片文件权限或路径异常，请先检查服务器文件权限。';
    return false;
  }

  pwg_query('DELETE FROM '.GZCA_WORKS_TABLE.' WHERE image_id IN ('.implode(',', $deleted_ids).');');

  if (!empty($old_category_ids))
  {
    update_category(array_values(array_unique($old_category_ids)));
  }
  invalidate_user_cache();

  return count($deleted_ids);
}

function gzca_code_prefix_warning($code, $category_id)
{
  $code = gzca_normalize_code($code);
  $category_id = (int)$category_id;
  $prefix = gzca_category_prefix($category_id);
  if ('' === $code || '' === $prefix)
  {
    return '';
  }
  if (0 === strpos($code, $prefix.'-') || $code === $prefix)
  {
    return '';
  }
  return '当前作品编号前缀为“'.$code.'”，与所属分类前缀“'.$prefix.'”不一致。前台分类会以所属分类为准；如需统一编号，请手动改成 '.$prefix.'-001 这种格式。';
}

function gzca_prepare_work_row($row)
{
  $meta = gzca_work_meta($row);
  $row['code'] = $meta['code'];
  $row['status'] = $meta['status'];
  $row['is_public'] = isset($row['level']) && (int)$row['level'] === 0;
  $row['visibility'] = $row['is_public'] ? 'public' : 'private';
  $row['visibility_label'] = $row['is_public'] ? '公开' : '私密';
  $row['frontend_visible'] = 'online' === $row['status'] && $row['is_public'];
  $row['featured'] = $meta['featured'];
  $row['sort_order'] = $meta['sort_order'];
  $row['download_count'] = $meta['download_count'];
  $row['description'] = gzca_strip_markers(isset($row['comment']) ? $row['comment'] : '');
  $row['thumb_url'] = gzca_image_thumb_url($row);
  $row['display_name'] = !empty($row['name']) ? $row['name'] : pathinfo($row['file'], PATHINFO_FILENAME);
  $row['filesize_bytes'] = isset($row['filesize']) ? max(0, (int)$row['filesize']) * 1024 : 0;
  if (0 === $row['filesize_bytes'] && !empty($row['path']) && !url_is_remote($row['path']))
  {
    $original_path = get_element_path($row);
    if (is_file($original_path))
    {
      $row['filesize_bytes'] = (int)@filesize($original_path);
    }
  }
  $row['filesize_label'] = $row['filesize_bytes'] > 0
    ? gzca_format_bytes($row['filesize_bytes'])
    : '大小未知';
  $row['category_id'] = isset($row['category_id']) ? (int)$row['category_id'] : 0;
  $row['category_name'] = !empty($row['category_name']) ? $row['category_name'] : '未分类';
  if ($row['category_id'] < 1 || '未分类' === $row['category_name'])
  {
    $fallback_category = gzca_category_from_work_code($row['code']);
    if (is_array($fallback_category))
    {
      $row['category_id'] = (int)$fallback_category['id'];
      $row['category_name'] = $fallback_category['name'];
    }
  }
  $row['category_path'] = $row['category_id'] > 0 ? gzca_category_path($row['category_id']) : '';
  if ('' === $row['category_path'])
  {
    $row['category_path'] = $row['category_name'];
  }
  $category_parts = array_values(array_filter(array_map('trim', explode(' / ', $row['category_path']))));
  if (count($category_parts) > 1)
  {
    $row['category_board_name'] = reset($category_parts);
    $row['category_leaf_name'] = end($category_parts);
    $row['category_display_name'] = implode(' / ', $category_parts);
  }
  else
  {
    $row['category_board_name'] = $row['category_name'];
    $row['category_leaf_name'] = $row['category_id'] > 0 ? '直接上传 / 全部作品' : '未分类';
    $row['category_display_name'] = $row['category_board_name'].' / '.$row['category_leaf_name'];
  }
  $row['category_cover_image_id'] = isset($row['category_cover_image_id']) ? (int)$row['category_cover_image_id'] : 0;
  $row['is_category_cover'] = $row['category_id'] > 0
    && $row['category_cover_image_id'] > 0
    && (int)$row['id'] === $row['category_cover_image_id'];
  return $row;
}

function gzca_set_category_cover($category_id, $image_id)
{
  $category_id = (int)$category_id;
  $image_id = (int)$image_id;

  $query = '
SELECT COUNT(*)
  FROM '.IMAGE_CATEGORY_TABLE.'
  WHERE category_id = '.$category_id.'
    AND image_id = '.$image_id.'
;';
  list($count) = pwg_db_fetch_row(pwg_query($query));
  if ((int)$count === 0)
  {
    return false;
  }

  single_update(
    CATEGORIES_TABLE,
    array('representative_picture_id' => $image_id),
    array('id' => $category_id)
    );

  if (defined('USER_CACHE_CATEGORIES_TABLE'))
  {
    pwg_query(
      'UPDATE '.USER_CACHE_CATEGORIES_TABLE.' SET user_representative_picture_id = NULL WHERE cat_id = '.$category_id.';'
      );
  }
  return true;
}

function gzca_unset_category_cover($category_id, $image_id=0)
{
  $category_id = (int)$category_id;
  $image_id = (int)$image_id;
  if ($category_id < 1)
  {
    return false;
  }

  $result = pwg_query(
    'SELECT representative_picture_id FROM '.CATEGORIES_TABLE.' WHERE id = '.$category_id.' LIMIT 1;'
    );
  if (pwg_db_num_rows($result) === 0)
  {
    return false;
  }

  $category = pwg_db_fetch_assoc($result);
  $current_cover_id = isset($category['representative_picture_id']) ? (int)$category['representative_picture_id'] : 0;
  if ($current_cover_id < 1 || ($image_id > 0 && $current_cover_id !== $image_id))
  {
    return false;
  }

  single_update(
    CATEGORIES_TABLE,
    array('representative_picture_id' => null),
    array('id' => $category_id)
    );

  if (defined('USER_CACHE_CATEGORIES_TABLE'))
  {
    pwg_query(
      'UPDATE '.USER_CACHE_CATEGORIES_TABLE.' SET user_representative_picture_id = NULL WHERE cat_id = '.$category_id.';'
      );
  }
  return true;
}

function gzca_find_image($image_id)
{
  $image_id = (int)$image_id;
  $query = '
SELECT
    i.*,
    gw.code AS gzca_code,
    gw.status AS gzca_status,
    gw.featured,
    gw.sort_order,
    gw.download_count,
    ic.category_id,
    c.name AS category_name
  FROM '.IMAGES_TABLE.' AS i
    LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
    LEFT JOIN '.IMAGE_CATEGORY_TABLE.' AS ic ON ic.image_id = i.id
      AND ic.category_id = (
        SELECT ic2.category_id
          FROM '.IMAGE_CATEGORY_TABLE.' AS ic2
            INNER JOIN '.CATEGORIES_TABLE.' AS c2 ON c2.id = ic2.category_id
          WHERE ic2.image_id = i.id
          ORDER BY LENGTH(c2.uppercats) DESC, c2.global_rank
          LIMIT 1
      )
    LEFT JOIN '.CATEGORIES_TABLE.' AS c ON c.id = ic.category_id
  WHERE i.id = '.$image_id.'
;';
  $result = pwg_query($query);
  if (pwg_db_num_rows($result) === 0)
  {
    return null;
  }
  return gzca_prepare_work_row(pwg_db_fetch_assoc($result));
}

function gzca_stream_admin_work_download($image_id, &$error='')
{
  global $conf;

  $image_id = (int)$image_id;
  $work = $image_id > 0 ? gzca_find_image($image_id) : null;
  if (null === $work)
  {
    $error = '作品不存在或已被删除。';
    return false;
  }

  if (empty($work['path']) || url_is_remote($work['path']))
  {
    $error = '该作品没有可下载的本地原图。';
    return false;
  }

  $file_path = realpath(get_element_path($work));
  if (false === $file_path)
  {
    $error = '服务器上未找到该作品的原图文件。';
    return false;
  }

  $allowed_roots = array(realpath(PHPWG_ROOT_PATH));
  if (!empty($conf['upload_dir']))
  {
    $allowed_roots[] = realpath(PHPWG_ROOT_PATH.$conf['upload_dir']);
  }
  $allowed_roots[] = realpath(PHPWG_ROOT_PATH.'galleries');

  $path_allowed = false;
  foreach (array_unique(array_filter($allowed_roots)) as $allowed_root)
  {
    $allowed_prefix = rtrim($allowed_root, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
    if ($file_path === $allowed_root || 0 === strpos($file_path, $allowed_prefix))
    {
      $path_allowed = true;
      break;
    }
  }

  if (!$path_allowed || !is_file($file_path) || !is_readable($file_path))
  {
    $error = '原图路径无效或当前不可读取。';
    return false;
  }

  $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
  $extension = preg_replace('/[^a-z0-9]/i', '', $extension);
  $code = !empty($work['code']) ? strtoupper((string)$work['code']) : 'ARTWORK-'.$image_id;
  $filename = preg_replace('/[^A-Z0-9_-]/', '-', $code);
  if ('' !== $extension)
  {
    $filename .= '.'.$extension;
  }

  $mime = 'application/octet-stream';
  if (function_exists('finfo_open'))
  {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $detected_mime = false !== $finfo ? finfo_file($finfo, $file_path) : false;
    if (false !== $finfo)
    {
      finfo_close($finfo);
    }
    if (is_string($detected_mime) && preg_match('#^[a-z0-9.+-]+/[a-z0-9.+-]+$#i', $detected_mime))
    {
      $mime = $detected_mime;
    }
  }

  if (function_exists('ob_get_level'))
  {
    while (ob_get_level() > 0)
    {
      ob_end_clean();
    }
  }

  header('Content-Type: '.$mime);
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  header('Content-Length: '.filesize($file_path));
  header('Cache-Control: private, no-store, max-age=0');
  header('Pragma: no-cache');
  header('X-Content-Type-Options: nosniff');
  readfile($file_path);
  exit;
}

function gzca_save_contact_qr($file, &$error='', $slot=1)
{
  $slot = max(1, min(2, (int)$slot));
  if (empty($file) || UPLOAD_ERR_NO_FILE === (int)$file['error'])
  {
    return null;
  }
  if (UPLOAD_ERR_OK !== (int)$file['error'])
  {
    $error = '二维码上传失败，请重新选择图片。';
    return false;
  }
  if ((int)$file['size'] > 2 * 1024 * 1024)
  {
    $error = '二维码图片不能超过 2MB。';
    return false;
  }

  $allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
  if (!function_exists('finfo_open'))
  {
    $error = '服务器缺少 PHP fileinfo 扩展，暂时无法校验二维码图片类型。';
    return false;
  }
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  if (!isset($allowed[$mime]))
  {
    $error = '二维码仅支持 JPG、PNG 或 WebP。';
    return false;
  }

  global $conf;
  $directory = PHPWG_ROOT_PATH.$conf['data_location'].'guozhan-client-admin';
  if (!is_dir($directory) && !mkdir($directory, 0755, true))
  {
    $error = '无法创建二维码保存目录，请检查 _data 目录权限。';
    return false;
  }

  $prefix = 'contact-qr-'.$slot;
  $relative = $conf['data_location'].'guozhan-client-admin/'.$prefix.'.'.$allowed[$mime];
  $destination = PHPWG_ROOT_PATH.$relative;
  foreach (glob($directory.'/'.$prefix.'.*') as $old_file)
  {
    @unlink($old_file);
  }

  if (!move_uploaded_file($file['tmp_name'], $destination))
  {
    $error = '二维码保存失败，请检查目录写入权限。';
    return false;
  }
  @chmod($destination, 0644);

  return $relative;
}

function gzca_save_logo($file, &$error='')
{
  if (empty($file) || UPLOAD_ERR_NO_FILE === (int)$file['error'])
  {
    return null;
  }
  if (UPLOAD_ERR_OK !== (int)$file['error'])
  {
    $error = 'Logo 上传失败，请重新选择图片。';
    return false;
  }
  if ((int)$file['size'] > 4 * 1024 * 1024)
  {
    $error = 'Logo 图片不能超过 4MB。';
    return false;
  }

  $allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
  if (!function_exists('finfo_open'))
  {
    $error = '服务器缺少 PHP fileinfo 扩展，暂时无法校验 Logo 图片类型。';
    return false;
  }
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  if (!isset($allowed[$mime]))
  {
    $error = 'Logo 仅支持 JPG、PNG 或 WebP。';
    return false;
  }

  global $conf;
  $directory = PHPWG_ROOT_PATH.$conf['data_location'].'guozhan-client-admin';
  if (!is_dir($directory) && !mkdir($directory, 0755, true))
  {
    $error = '无法创建品牌图片保存目录，请检查 _data 目录权限。';
    return false;
  }

  $relative = $conf['data_location'].'guozhan-client-admin/brand-logo.'.$allowed[$mime];
  $destination = PHPWG_ROOT_PATH.$relative;
  foreach (glob($directory.'/brand-logo.*') as $old_file)
  {
    @unlink($old_file);
  }

  if (!move_uploaded_file($file['tmp_name'], $destination))
  {
    $error = 'Logo 保存失败，请检查目录写入权限。';
    return false;
  }
  @chmod($destination, 0644);

  return $relative;
}


function gzca_save_hero_slide_image($file, $slot, &$error='')
{
  $slot = max(1, min(5, (int)$slot));
  if (empty($file) || UPLOAD_ERR_NO_FILE === (int)$file['error'])
  {
    return null;
  }
  if (UPLOAD_ERR_OK !== (int)$file['error'])
  {
    $error = '轮播图上传失败，请重新选择图片。';
    return false;
  }
  if ((int)$file['size'] > 8 * 1024 * 1024)
  {
    $error = '轮播图不能超过 8MB，请先压缩后再上传。';
    return false;
  }
  if (!function_exists('finfo_open'))
  {
    $error = '服务器缺少 PHP fileinfo 扩展，暂时无法校验轮播图片类型。';
    return false;
  }

  $allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  if (!isset($allowed[$mime]) || false === @getimagesize($file['tmp_name']))
  {
    $error = '轮播图仅支持 JPG、PNG 或 WebP。';
    return false;
  }

  global $conf;
  $directory = PHPWG_ROOT_PATH.$conf['data_location'].'guozhan-client-admin';
  if (!is_dir($directory) && !mkdir($directory, 0755, true))
  {
    $error = '无法创建轮播图保存目录，请检查 _data 目录权限。';
    return false;
  }

  $compression_info = array();
  $prepared = gzca_prepare_upload_image_file($file['tmp_name'], $file['name'], $compression_info);
  $extension = strtolower(get_extension($prepared['filename']));
  if (!in_array($extension, array('jpg', 'jpeg', 'png', 'webp'), true))
  {
    $extension = 'jpg';
  }
  if ('jpeg' === $extension)
  {
    $extension = 'jpg';
  }

  foreach (glob($directory.'/home-hero-'.$slot.'.*') as $old_file)
  {
    @unlink($old_file);
  }
  $relative = $conf['data_location'].'guozhan-client-admin/home-hero-'.$slot.'.'.$extension;
  $destination = PHPWG_ROOT_PATH.$relative;

  if (is_uploaded_file($prepared['filepath']))
  {
    $ok = move_uploaded_file($prepared['filepath'], $destination);
  }
  else
  {
    $ok = @rename($prepared['filepath'], $destination);
  }
  if (!$ok)
  {
    $error = '轮播图保存失败，请检查目录写入权限。';
    return false;
  }
  @chmod($destination, 0644);
  return $relative;
}
