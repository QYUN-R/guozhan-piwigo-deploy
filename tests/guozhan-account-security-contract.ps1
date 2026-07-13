$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$main = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\main.inc.php')
$maintain = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\maintain.class.php')
$functions = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\include\functions.inc.php')
$securityPath = Join-Path $root 'plugins\GuozhanClientAdmin\include\security.inc.php'
if (Test-Path $securityPath) {
    $functions += "`n" + (Get-Content -Raw $securityPath)
}
$admin = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\admin.php')
$template = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\template\admin.tpl')
$style = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.css')
$adminHeader = Get-Content -Raw (Join-Path $root 'admin\themes\default\template\header.tpl')
$password = Get-Content -Raw (Join-Path $root 'password.php')
$passwordTemplate = Get-Content -Raw (Join-Path $root 'themes\standard_pages\template\password.tpl')
$failures = [System.Collections.Generic.List[string]]::new()

if ($main -notmatch "define\('GZCA_SECURITY_CODE_TTL',\s*600\)" -or
    $main -notmatch "define\('GZCA_SECURITY_RESEND_COOLDOWN',\s*60\)" -or
    $main -notmatch "define\('GZCA_SECURITY_MAX_ATTEMPTS',\s*5\)") {
    $failures.Add('Email security code lifetime, resend cooldown, and attempt limit are not fixed securely.')
}
if ($main -notmatch "define\('GZCA_PASSWORD_RESET_LINK_TTL',\s*900\)" -or
    $main -notmatch "define\('GZCA_PASSWORD_RESET_RESEND_COOLDOWN',\s*60\)" -or
    $main -notmatch "define\('GZCA_PASSWORD_RESET_HOURLY_LIMIT',\s*5\)") {
    $failures.Add('Administrator reset links do not have fixed expiry and delivery-rate limits.')
}
if ($main -notmatch 'GZCA_ADMIN_SECURITY_TABLE') {
    $failures.Add('The plugin does not define a dedicated administrator email-verification table.')
}
if ($main -notmatch "add_event_handler\('loc_begin_profile',\s*'gzca_redirect_admin_profile_to_security'") {
    $failures.Add('The native profile page can still bypass verified email and password-change controls.')
}
if ($main -notmatch "add_event_handler\('ws_invoke_allowed',\s*'gzca_block_admin_security_ws_bypass'") {
    $failures.Add('Sensitive native WebService methods are not protected by the verified account-security flow.')
}
if ($maintain -notmatch 'gzca_admin_security' -or
    $maintain -notmatch 'verified_email_hash' -or
    $maintain -notmatch 'email_verified_at') {
    $failures.Add('The plugin installer does not persist verified recovery-email state independently from the raw address.')
}
if ($maintain -notmatch 'sessions_revoked_before' -or
    $maintain -notmatch 'ensure_column\(\$admin_security_table,\s*''sessions_revoked_before''') {
    $failures.Add('The plugin installer does not persist the cutoff used to revoke remembered sessions on other devices.')
}
if ($maintain -notmatch 'password_reset_sent_at' -or
    $maintain -notmatch 'password_reset_window_started_at' -or
    $maintain -notmatch 'password_reset_window_count') {
    $failures.Add('Password-reset delivery rate limits are not persisted per administrator.')
}
if ($functions -notmatch 'function\s+gzca_admin_email_security_status\s*\(' -or
    $functions -notmatch 'hash_equals\s*\(' -or
    $functions -notmatch 'verified_email_hash') {
    $failures.Add('The bound address is not checked against a persistent verified-email fingerprint.')
}
if ($functions -notmatch 'function\s+gzca_mask_email\s*\(' -or
    $functions -notmatch 'function\s+gzca_security_mail_status\s*\(' -or
    $functions -notmatch 'in_array\(\$smtp_secure,\s*array\(''ssl'',\s*''tls''\),\s*true\)') {
    $failures.Add('The UI cannot safely report a masked recovery address and SMTP readiness.')
}
if ($functions -notmatch 'function\s+gzca_send_admin_security_code\s*\(' -or
    $functions -notmatch 'generate_user_code\s*\(' -or
    $functions -notmatch 'pwg_mail\s*\(' -or
    $functions -notmatch 'if\s*\(\s*!\s*\$mail_sent\s*\)') {
    $failures.Add('Security challenges are not sent through Piwigo mail with explicit delivery-failure handling.')
}
if ($functions -notmatch 'function\s+gzca_admin_password_recovery_allowed\s*\(' -or
    $functions -notmatch 'function\s+gzca_password_reset_delivery_allowed\s*\(' -or
    $functions -notmatch 'function\s+gzca_record_password_reset_delivery\s*\(' -or
    $functions -notmatch 'GZCA_PASSWORD_RESET_HOURLY_LIMIT') {
    $failures.Add('Administrator password recovery is not restricted to verified email with persistent rate limiting.')
}
$challengeAssignment = [regex]::Match(
    $functions,
    '\$_SESSION\[''gzca_security_challenges''\]\[\$purpose\]\s*=\s*array\((?<body>[\s\S]*?)\n\s*\);'
)
if (-not $challengeAssignment.Success -or
    $challengeAssignment.Groups['body'].Value -notmatch "'secret'\s*=>" -or
    $challengeAssignment.Groups['body'].Value -notmatch "'target_hash'\s*=>" -or
    $challengeAssignment.Groups['body'].Value -match "'code'\s*=>") {
    $failures.Add('Verification state must stay session-scoped and must never store the plaintext six-digit code.')
}
if ($functions -notmatch 'GZCA_SECURITY_CODE_TTL' -or
    $functions -notmatch 'GZCA_SECURITY_RESEND_COOLDOWN' -or
    $functions -notmatch 'GZCA_SECURITY_MAX_ATTEMPTS') {
    $failures.Add('Challenge expiry, resend throttling, or attempt lockout is not enforced.')
}
if ($functions -notmatch 'function\s+gzca_verify_current_password\s*\(' -or
    $functions -notmatch '\$conf\[''password_verify''\]') {
    $failures.Add('Binding an address and changing a password do not verify the current password through Piwigo.')
}
if ($functions -notmatch 'function\s+gzca_validate_new_admin_password\s*\(' -or
    $functions -notmatch 'strlen|pwg_strlen') {
    $failures.Add('The custom password-change flow has no explicit password policy.')
}
if ($functions -notmatch 'function\s+gzca_revoke_user_credentials\s*\(' -or
    $functions -notmatch 'delete_user_sessions\s*\(' -or
    $functions -notmatch 'deactivate_user_auth_keys\s*\(' -or
    $functions -notmatch 'USER_AUTH_KEYS_TABLE') {
    $failures.Add('Password changes do not revoke sessions, remember keys, reset keys, and API keys together.')
}
if ($functions -notmatch 'function\s+gzca_revoke_other_admin_sessions\s*\(' -or
    $functions -notmatch 'sessions_revoked_before' -or
    $functions -notmatch 'SESSIONS_TABLE' -or
    $functions -notmatch 'session_id\s*\(' -or
    $functions -notmatch 'id\s*<>') {
    $failures.Add('The administrator cannot revoke other device sessions while preserving the current browser session.')
}
if ($functions -notmatch 'gzca_admin_session_revoked_before\s*\(' -or
    $functions -notmatch "gzca_force_admin_reauthentication\('sessions_revoked'\)" -or
    $functions -notmatch "'sessions_revoked'\s*=>") {
    $failures.Add('Revoked remembered sessions are not rejected by the administrator session validator.')
}
if ($functions -notmatch 'function\s+gzca_redirect_admin_profile_to_security\s*\(' -or
    $functions -notmatch "redirect\s*\(\s*gzca_admin_url\('security'\)\s*\)") {
    $failures.Add('Whitelisted administrators are not redirected away from the unsafe native profile editor.')
}
if ($functions -notmatch 'function\s+gzca_block_admin_security_ws_bypass\s*\(' -or
    $functions -notmatch 'pwg\.users\.setMyInfo' -or
    $functions -notmatch 'pwg\.users\.setInfo' -or
    $functions -notmatch 'pwg\.users\.generatePasswordLink' -or
    $functions -notmatch 'new\s+PwgError\s*\(\s*403') {
    $failures.Add('Whitelisted administrators can still bypass email verification through native user WebServices.')
}
if ($admin -notmatch "'security'" -or
    $admin -notmatch "'send_bind_email_code'" -or
    $admin -notmatch "'verify_bind_email'" -or
    $admin -notmatch "'send_password_code'" -or
    $admin -notmatch "'change_admin_password'") {
    $failures.Add('The custom administrator does not expose all required account-security actions.')
}
if ($admin -notmatch '''revoke_other_sessions''\s*===\s*\$action' -or
    $admin -notmatch 'confirm_other_sessions' -or
    $admin -notmatch 'gzca_verify_current_password\s*\(' -or
    $admin -notmatch 'gzca_revoke_other_admin_sessions\s*\(') {
    $failures.Add('The remote-device logout action is missing password, confirmation, or server-side revocation checks.')
}
if ($admin -notmatch 'gzca_admin_email_security_status\s*\(' -or
    $admin -notmatch 'gzca_security_mail_status\s*\(' -or
    $admin -notmatch "GZCA_EMAIL_SECURITY") {
    $failures.Add('The account-security page is not given current verification and mail-delivery state.')
}
if ($admin -match 'requires_binding[\s\S]{0,220}redirect\s*\(\s*gzca_admin_url\(''security''') {
    $failures.Add('Recovery-email setup blocks the rest of the administrator workspace instead of remaining a non-blocking reminder.')
}
if ($template -notmatch '账号中心' -or
    $template -notmatch 'gzca-account-summary' -or
    $template -notmatch 'name="new_email"' -or
    $template -notmatch 'name="current_password"' -or
    $template -notmatch 'name="verification_code"' -or
    $template -notmatch 'autocomplete="one-time-code"' -or
    $template -notmatch 'name="new_password"' -or
    $template -notmatch 'name="new_password_confirm"') {
    $failures.Add('The responsive account center is missing the account summary, binding, or password-verification controls.')
}
if ($template -notmatch 'id="login-devices"' -or
    $template -notmatch 'value="revoke_other_sessions"' -or
    $template -notmatch 'name="confirm_other_sessions"' -or
    $template -notmatch '退出其他设备') {
    $failures.Add('The account-security page has no explicit password-confirmed control for logging out other devices.')
}
if ($template -notmatch 'gzca-email-overview' -or
    $template -notmatch 'gzca-email-binding' -or
    $template -notmatch 'gzca-security-steps' -or
    $template -notmatch 'gzca-security-step') {
    $failures.Add('The account-center workflow is not grouped into a clear email overview and numbered steps.')
}
if ($template -notmatch '\{if \$GZCA_EMAIL_SECURITY\.storage_ready\}[\s\S]{0,500}gzca-email-binding' -or
    $template -match '\{if \$GZCA_EMAIL_SECURITY\.storage_ready and \$GZCA_SECURITY_MAIL\.ready\}' -or
    $template -notmatch '\{if !\$GZCA_SECURITY_MAIL\.ready\}disabled aria-disabled="true"\{/if\}' -or
    $template -notmatch '绑定入口已保留') {
    $failures.Add('The recovery-email controls disappear when SMTP is unavailable instead of remaining visible and safely disabled.')
}
if ($style -notmatch '\.gzca-security-layout\s*\{[^}]*grid-template-columns:\s*minmax\(0,\s*1fr\)' -or
    $style -notmatch '\.gzca-security-layout\s*\{[^}]*max-width:' -or
    $style -notmatch '\.gzca-security-device-form' -or
    $style -notmatch '\.gzca-account-summary') {
    $failures.Add('The account-security page is not a constrained single-column workflow with a dedicated device form.')
}
if ($style -notmatch '@media\s*\(max-width:\s*720px\)[\s\S]*?\.gzca-security') {
    $failures.Add('The account-security page has no explicit mobile layout.')
}
if ($adminHeader -notmatch '\$lang_info\.code eq ''cn''\}zh-CN' -or
    $adminHeader -notmatch '\$lang_info\.code\|replace:''_'':''-''') {
    $failures.Add('The administrator document language is not normalized to a valid BCP 47 tag.')
}
if ($style -notmatch 'body#theAdminPage\s+#footer') {
    $failures.Add('The native fixed Piwigo footer can overlap the custom administrator on mobile.')
}
if ($password -notmatch 'gzca_admin_password_recovery_allowed\s*\(' -or
    $password -notmatch 'generate_password_link\s*\(' -or
    $password -notmatch 'pwg_generate_reset_password_mail\s*\(' -or
    $password -notmatch '\$page\[''action''\]\s*=\s*''sent''' -or
    $password -notmatch 'gzca_revoke_user_credentials\s*\(\s*\$user_id\s*\)') {
    $failures.Add('Forgot-password does not send a generic, verified-email-only one-time link or revoke credentials after reset.')
}
if ([regex]::Matches($password, 'gzca_admin_password_recovery_allowed\s*\(').Count -lt 3 -or
    $password -notmatch 'check_password_reset_key[\s\S]{0,2500}gzca_admin_password_recovery_allowed' -or
    $password -notmatch 'process_password_request[\s\S]{0,3000}gzca_admin_password_recovery_allowed') {
    $failures.Add('Legacy reset links or verified-code grants can bypass the current verified recovery-email state.')
}
if ($password -notmatch 'gzca_validate_new_admin_password\s*\(' -or
    $password -notmatch 'gzca_admin_account_is_allowed\s*\(') {
    $failures.Add('Email-verified administrator password resets can still choose a weak password.')
}
$resetKeyFunction = [regex]::Match($password, 'function\s+reset_password_key\s*\(\)[\s\S]*?\n\}')
if (-not $resetKeyFunction.Success -or
    $resetKeyFunction.Value -match 'deactivate_password_reset_key|deactivate_user_auth_keys') {
    $failures.Add('A rejected new password can consume the reset link before the password policy passes.')
}
if ($password -notmatch 'function\s+consume_password_reset_key\s*\(' -or
    $password -notmatch 'activation_key\s*=\s*NULL' -or
    $password -notmatch 'pwg_db_changes\s*\(\s*\)\s*===\s*1' -or
    $password -notmatch 'consume_password_reset_key\s*\([\s\S]{0,500}single_update\s*\(\s*USERS_TABLE') {
    $failures.Add('Password-reset links are not atomically consumed before the password update.')
}
if ($password -notmatch "'expires_at'\s*=>\s*time\(\)\s*\+\s*GZCA_SECURITY_CODE_TTL" -or
    $password -notmatch 'function\s+reset_password_code[\s\S]{0,600}unset\(\$_SESSION\[''valid_reset_password_code''\]\)' -or
    $functions -notmatch 'function\s+gzca_password_reset_grant_user_id[\s\S]{0,500}expires_at[\s\S]{0,500}\$grant\s*=\s*null') {
    $failures.Add('A verified legacy email code can leave an unbounded password-reset grant in the session.')
}
if ($passwordTemplate -notmatch '\$action eq ''sent''' -or
    $passwordTemplate -notmatch '链接仅可使用一次' -or
    $passwordTemplate -notmatch '发送重置链接' -or
    $passwordTemplate -notmatch '<meta name="referrer" content="no-referrer">' -or
    $password -notmatch "Cache-Control: no-store" -or
    $password -notmatch "Referrer-Policy: no-referrer" -or
    $password -notmatch "X-Robots-Tag: noindex") {
    $failures.Add('The password page does not present the secure one-time-link recovery flow.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: customer-owned verified recovery email and email-verified password changes are enforced.'
