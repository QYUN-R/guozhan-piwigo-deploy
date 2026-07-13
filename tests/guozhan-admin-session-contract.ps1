$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$main = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\main.inc.php')
$functions = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\include\functions.inc.php')
$security = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\include\security.inc.php')
$admin = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\admin.php')
$template = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\template\admin.tpl')
$style = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.css')
$login = Get-Content -Raw (Join-Path $root 'themes\standard_pages\template\identification.tpl')
$failures = [System.Collections.Generic.List[string]]::new()

if ($main -notmatch "define\('GZCA_ADMIN_SESSION_TTL',\s*604800\)") {
    $failures.Add('The administrator session TTL is not fixed at exactly seven days.')
}
if ($main -notmatch "\`$conf\['remember_me_length'\]\s*=\s*GZCA_ADMIN_SESSION_TTL" -or
    $main -notmatch "\`$conf\['session_length'\]\s*=\s*GZCA_ADMIN_SESSION_TTL") {
    $failures.Add('Piwigo remember-me and server session lifetimes are not both capped at seven days.')
}
if ($main -notmatch "add_event_handler\('user_login',\s*'gzca_record_admin_session'\)") {
    $failures.Add('Successful logins do not stamp a Guozhan administrator session.')
}
if ($main -notmatch "add_event_handler\('loc_end_identification',\s*'gzca_prepare_login_notice'\)") {
    $failures.Add('The login page has no safe session-expiry notice hook.')
}
if ($main -notmatch "add_event_handler\('try_log_user',\s*'gzca_capture_login_identifier',\s*5\)") {
    $failures.Add('The submitted login identifier is not captured before Piwigo resolves username or email.')
}
if ($functions -notmatch 'function\s+gzca_record_admin_session\s*\(' -or
    $functions -notmatch "\`$_SESSION\['gzca_admin_user_id'\]" -or
    $functions -notmatch "\`$_SESSION\['gzca_admin_issued_at'\]") {
    $failures.Add('The administrator user ID and absolute authentication time are not stored in the session.')
}
if ($functions -notmatch 'function\s+gzca_validate_admin_session\s*\(' -or
    $functions -notmatch 'GZCA_ADMIN_SESSION_TTL' -or
    $functions -notmatch 'gzca_admin_login_whitelist') {
    $failures.Add('Each administrator request does not revalidate TTL, role, and whitelist membership.')
}
if ($functions -notmatch 'function\s+gzca_force_admin_reauthentication\s*\(' -or
    $functions -notmatch 'logout_user\s*\(\s*\)' -or
    $functions -notmatch "gzca_auth") {
    $failures.Add('Missing, expired, or invalid administrator sessions are not logged out and redirected safely.')
}
if ($functions -notmatch 'function\s+gzca_capture_login_identifier\s*\(' -or
    $functions -notmatch 'gzca_login_identifier' -or
    $functions -notmatch 'gzca_admin_verified_email_login_allowed\s*\(' -or
    $security -notmatch 'function\s+gzca_admin_verified_email_login_allowed\s*\(') {
    $failures.Add('Administrator authentication is not restricted to the verified bound email address.')
}
if ($functions -notmatch 'log_user\s*\(\s*\(int\)\$user_found\[''id''\]\s*,\s*true\s*\)' -or
    $functions -notmatch "\`$state\['authenticated'\]\s*=\s*true") {
    $failures.Add('Allowed administrator password logins do not force the seven-day remember session.')
}
if ($admin -notmatch "'GZCA_ADMIN_IDENTITY'\s*=>\s*gzca_admin_identity") {
    $failures.Add('The custom administrator template is not given the verified administrator identity.')
}
if ($admin -notmatch "'logout'\s*=>") {
    $failures.Add('The custom administrator template has no logout URL.')
}
if ($template -notmatch 'gzca-admin-session' -or
    $template -notmatch 'GZCA_ADMIN_IDENTITY\.username' -or
    $template -notmatch 'GZCA_ADMIN_IDENTITY\.expires_at' -or
    $template -notmatch 'GZCA_URLS\.logout' -or
    $template -notmatch '退出登录') {
    $failures.Add('The custom administrator header does not show identity, expiry, and an explicit logout action.')
}
if ($style -notmatch '@media\s*\(max-width:\s*560px\)[\s\S]*?\.gzca-admin-session' -or
    $style -notmatch '@media\s*\(max-width:\s*560px\)[\s\S]*?\.gzca-logout-button') {
    $failures.Add('Administrator identity and logout controls are not explicitly adapted for mobile.')
}
if ($login -notmatch 'name="remember_me"[^>]*checked' -or
    $login -notmatch '保持登录 7 天' -or
    $login -notmatch '仅允许已授权管理员登录') {
    $failures.Add('The login page does not clearly offer the seven-day administrator-only session.')
}
if ($login -notmatch '<label for="username">管理员邮箱</label>' -or
    $login -notmatch 'type="email"\s+name="username"' -or
    $login -notmatch 'autocomplete="email"') {
    $failures.Add('The administrator login form still asks for an internal username instead of the verified email.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: seven-day administrator authentication, identity, logout, and mobile UI contract is present.'
