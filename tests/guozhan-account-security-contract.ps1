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
$password = Get-Content -Raw (Join-Path $root 'password.php')
$failures = [System.Collections.Generic.List[string]]::new()

if ($main -notmatch "define\('GZCA_SECURITY_CODE_TTL',\s*600\)" -or
    $main -notmatch "define\('GZCA_SECURITY_RESEND_COOLDOWN',\s*60\)" -or
    $main -notmatch "define\('GZCA_SECURITY_MAX_ATTEMPTS',\s*5\)") {
    $failures.Add('Email security code lifetime, resend cooldown, and attempt limit are not fixed securely.')
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
if ($functions -notmatch 'function\s+gzca_admin_email_security_status\s*\(' -or
    $functions -notmatch 'hash_equals\s*\(' -or
    $functions -notmatch 'verified_email_hash') {
    $failures.Add('The bound address is not checked against a persistent verified-email fingerprint.')
}
if ($functions -notmatch 'function\s+gzca_mask_email\s*\(' -or
    $functions -notmatch 'function\s+gzca_security_mail_status\s*\(') {
    $failures.Add('The UI cannot safely report a masked recovery address and SMTP readiness.')
}
if ($functions -notmatch 'function\s+gzca_send_admin_security_code\s*\(' -or
    $functions -notmatch 'generate_user_code\s*\(' -or
    $functions -notmatch 'pwg_mail\s*\(' -or
    $functions -notmatch 'if\s*\(\s*!\s*\$mail_sent\s*\)') {
    $failures.Add('Security challenges are not sent through Piwigo mail with explicit delivery-failure handling.')
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
if ($admin -notmatch "redirect\s*\(\s*gzca_admin_url\('security'" -or
    $admin -notmatch 'requires_binding') {
    $failures.Add('An unverified customer administrator is not guided to first-login email binding.')
}
if ($admin -notmatch 'requires_binding[\s\S]{0,180}mail_status\[''ready''\][\s\S]{0,180}''security''\s*!==\s*\$tab') {
    $failures.Add('First-login binding can lock the customer out before the private SMTP sender is ready.')
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
if ($style -notmatch 'body#theAdminPage\s+#footer') {
    $failures.Add('The native fixed Piwigo footer can overlap the custom administrator on mobile.')
}
if ($password -notmatch 'if\s*\(\s*!\s*\$mail_send\s*\)' -or
    $password -notmatch 'gzca_revoke_user_credentials\s*\(\s*\$user_id\s*\)') {
    $failures.Add('The built-in forgot-password path still accepts failed mail delivery or leaves credentials active after reset.')
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

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: customer-owned verified recovery email and email-verified password changes are enforced.'
