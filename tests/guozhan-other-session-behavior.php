<?php

declare(strict_types=1);

define('PHPWG_ROOT_PATH', __DIR__.'/fixtures/fake-root/');
define('GZCA_ADMIN_SECURITY_TABLE', 'piwigo_gzca_admin_security');
define('SESSIONS_TABLE', 'piwigo_sessions');

$conf = array('remember_me_name' => 'pwg_remember');
$queries = array();
$updates = array();
$inserts = array();
$activities = array();
$fake_security_row = null;
$_SESSION = array(
  'pwg_uid' => 7,
  'gzca_admin_user_id' => 7,
  'gzca_admin_issued_at' => time() - 120,
  );
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
session_id('current-session');

class GzcaFakeResult
{
  public $rows;

  public function __construct($rows)
  {
    $this->rows = $rows;
  }
}

function gzca_database_table_exists($table)
{
  return GZCA_ADMIN_SECURITY_TABLE === $table;
}

function get_remote_addr_session_hash()
{
  return 'IPHASH';
}

function cookie_path()
{
  return '/';
}

function pwg_db_real_escape_string($value)
{
  return addslashes((string)$value);
}

function pwg_query($sql)
{
  global $queries, $fake_security_row;
  $queries[] = $sql;
  if (false !== strpos($sql, 'COUNT(*)'))
  {
    return new GzcaFakeResult(array(array(2)));
  }
  if (false !== strpos($sql, 'FROM '.GZCA_ADMIN_SECURITY_TABLE))
  {
    return new GzcaFakeResult(null === $fake_security_row ? array() : array($fake_security_row));
  }
  return new GzcaFakeResult(array());
}

function pwg_db_num_rows($result)
{
  return count($result->rows);
}

function pwg_db_fetch_assoc($result)
{
  return array_shift($result->rows);
}

function pwg_db_fetch_row($result)
{
  return array_shift($result->rows);
}

function single_update($table, $data, $where)
{
  global $updates;
  $updates[] = compact('table', 'data', 'where');
}

function single_insert($table, $data)
{
  global $inserts;
  $inserts[] = compact('table', 'data');
}

function pwg_activity($object, $object_id, $action, $details=array())
{
  global $activities;
  $activities[] = compact('object', 'object_id', 'action', 'details');
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

$error = '';
$revoked_count = 0;
expect_true(gzca_revoke_other_admin_sessions(7, $error, $revoked_count), 'Other sessions were not revoked.');
expect_true(2 === $revoked_count, 'The revoked session count was not returned.');
expect_true((int)$_SESSION['gzca_admin_user_id'] === 7, 'The current administrator session was not preserved.');
expect_true((int)$_SESSION['gzca_admin_issued_at'] >= time() - 2, 'The current administrator session was not renewed after password confirmation.');

$query_text = implode("\n", $queries);
expect_true(false !== strpos($query_text, 'DELETE'), 'No session deletion query was issued.');
expect_true(false !== strpos($query_text, "id <> 'IPHASHcurrent-session'"), 'The current session was not excluded from deletion.');
expect_true(false !== strpos($query_text, 'pwg_uid|i:7;'), 'The deletion query was not restricted to the current administrator.') ;
expect_true(count($inserts) === 1, 'A missing administrator security row was not created.');
expect_true(isset($inserts[0]['data']['sessions_revoked_before']), 'The session revocation cutoff was not stored.');

$fake_security_row = array(
  'user_id' => 7,
  'verified_email_hash' => '',
  'email_verified_at' => null,
  'password_changed_at' => null,
  'sessions_revoked_before' => date('Y-m-d H:i:s', time() - 30),
  'updated_at' => date('Y-m-d H:i:s'),
  );
expect_true(gzca_admin_session_revoked_before(7) >= time() - 32, 'The persisted session revocation cutoff was not read back.') ;

fwrite(STDOUT, 'PASS: other-device session revocation preserves only the current administrator session.'.PHP_EOL);
