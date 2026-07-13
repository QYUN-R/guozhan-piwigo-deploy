<?php

declare(strict_types=1);

define('PHPWG_ROOT_PATH', __DIR__.'/fixtures/fake-root/');
define('GZCA_SECURITY_CODE_TTL', 600);
define('GZCA_SECURITY_RESEND_COOLDOWN', 60);
define('GZCA_SECURITY_MAX_ATTEMPTS', 5);
define('GZCA_PASSWORD_RESET_RESEND_COOLDOWN', 60);
define('GZCA_PASSWORD_RESET_HOURLY_LIMIT', 5);

$conf = array(
  'smtp_host' => 'smtp.example.test:587',
  'smtp_user' => 'mailer@example.test',
  'smtp_password' => 'test-only-password',
  'smtp_secure' => 'tls',
  'mail_sender_email' => 'mailer@example.test',
  );
$mail_should_succeed = true;
$sent_mail = array();
$_SESSION = array();

function generate_user_code()
{
  return array('secret' => 'test-secret', 'code' => '246810');
}

function verify_user_code($secret, $code)
{
  return hash_equals('test-secret', (string)$secret) && hash_equals('246810', (string)$code);
}

function pwg_mail($to, $args=array(), $tpl=array())
{
  global $mail_should_succeed, $sent_mail;
  $sent_mail[] = array('to' => $to, 'args' => $args);
  return $mail_should_succeed;
}

require dirname(__DIR__).'/plugins/GuozhanClientAdmin/include/security.inc.php';

function expect_true($condition, $message)
{
  if (!$condition)
  {
    fwrite(STDERR, 'FAIL: '.$message.PHP_EOL);
    exit(1);
  }
}

expect_true('customer@example.com' === gzca_normalize_email(' Customer@Example.COM '), 'Email normalization failed.');
expect_true('cu***@example.com' === gzca_mask_email('customer@example.com'), 'Email masking failed.');
expect_true(
  gzca_email_fingerprint('Customer@Example.com') === gzca_email_fingerprint('customer@example.com'),
  'Email fingerprinting is not case-normalized.'
  );

$now = 1800000000;
$rate_state = gzca_password_reset_rate_state(array(), $now);
expect_true($rate_state['allowed'], 'A first password-reset email was rate limited.');
$rate_state = gzca_password_reset_rate_state(array(
  'password_reset_sent_at' => date('Y-m-d H:i:s', $now - 30),
  'password_reset_window_started_at' => date('Y-m-d H:i:s', $now - 300),
  'password_reset_window_count' => 1,
  ), $now);
expect_true(!$rate_state['allowed'] && $rate_state['retry_after'] === 30, 'The reset-email resend cooldown was not enforced.');
$rate_state = gzca_password_reset_rate_state(array(
  'password_reset_sent_at' => date('Y-m-d H:i:s', $now - 120),
  'password_reset_window_started_at' => date('Y-m-d H:i:s', $now - 600),
  'password_reset_window_count' => GZCA_PASSWORD_RESET_HOURLY_LIMIT,
  ), $now);
expect_true(!$rate_state['allowed'] && $rate_state['retry_after'] === 3000, 'The hourly reset-email limit was not enforced.');

$grant = array('user_id' => 7, 'expires_at' => $now + 600);
expect_true(7 === gzca_password_reset_grant_user_id($grant, $now), 'A valid reset grant was rejected.');
$grant = array('user_id' => 7, 'expires_at' => $now - 1);
expect_true(false === gzca_password_reset_grant_user_id($grant, $now) && null === $grant, 'An expired reset grant was accepted or retained.');

$error = '';
expect_true(gzca_validate_new_admin_password('SecureDelivery2026', 'admin', 'customer@example.com', $error), 'A valid delivery password was rejected.');
$error = '';
expect_true(!gzca_validate_new_admin_password('short123', 'admin', 'customer@example.com', $error), 'A short password was accepted.');
$error = '';
expect_true(!gzca_validate_new_admin_password('adminSecure2026', 'admin', 'customer@example.com', $error), 'A password containing the administrator name was accepted.');

$mail_should_succeed = false;
$error = '';
expect_true(!gzca_send_admin_security_code('bind_email', 'customer@example.com', 7, $error), 'A failed mail delivery created a successful challenge.');
expect_true(empty($_SESSION['gzca_security_challenges']), 'Failed mail delivery left challenge state behind.');

$mail_should_succeed = true;
$error = '';
expect_true(gzca_send_admin_security_code('bind_email', 'customer@example.com', 7, $error), 'A valid bind-email challenge was not created.');
$serialized = serialize($_SESSION['gzca_security_challenges']['bind_email']);
expect_true(false === strpos($serialized, '246810'), 'The plaintext verification code was stored in the session.');
expect_true(false !== strpos($serialized, 'test-secret'), 'The verification secret was not stored in the session.');

$error = '';
expect_true(!gzca_send_admin_security_code('bind_email', 'customer@example.com', 7, $error), 'The resend cooldown was not enforced.');

for ($attempt = 1; $attempt <= GZCA_SECURITY_MAX_ATTEMPTS; $attempt++)
{
  $error = '';
  expect_true(!gzca_verify_admin_security_code('bind_email', 'customer@example.com', '000000', 7, $error), 'An invalid verification code was accepted.');
}
expect_true(null === gzca_security_challenge('bind_email'), 'The challenge survived the maximum invalid attempts.');

$error = '';
expect_true(gzca_send_admin_security_code('bind_email', 'customer@example.com', 7, $error), 'A replacement challenge was not created.');
$_SESSION['gzca_security_challenges']['bind_email']['expires_at'] = time() - 1;
$error = '';
expect_true(!gzca_verify_admin_security_code('bind_email', 'customer@example.com', '246810', 7, $error), 'An expired verification code was accepted.');

$error = '';
expect_true(gzca_send_admin_security_code('bind_email', 'customer@example.com', 7, $error), 'A final challenge was not created.');
$error = '';
expect_true(gzca_verify_admin_security_code('bind_email', 'customer@example.com', '246810', 7, $error), 'A valid verification code was rejected.');
expect_true(null === gzca_security_challenge('bind_email'), 'A successful challenge was not consumed.');

fwrite(STDOUT, 'PASS: account-security challenge and password-policy behavior is correct.'.PHP_EOL);
