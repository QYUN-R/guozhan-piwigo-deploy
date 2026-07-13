$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$template = Get-Content -Raw (Join-Path $root 'themes\standard_pages\template\password.tpl')
$stylePath = Join-Path $root 'themes\standard_pages\guozhan-password.css'
$style = if (Test-Path $stylePath) { Get-Content -Raw $stylePath } else { '' }
$failures = [System.Collections.Generic.List[string]]::new()

if ($template -notmatch "guozhan-password\.css" -or $template -notmatch 'guozhan-password-page') {
    $failures.Add('The password-reset page does not load its customer-branded layout.')
}
if ($template -notmatch 'guozhan-login-brand' -or
    $template -notmatch 'guozhan-gallery/assets/logo-mark\.png' -or
    $template -notmatch '图物计划') {
    $failures.Add('The password-reset page does not share the delivered website identity.')
}
if ($template -match 'piwigo_logo' -or $template -match 'toggle_mode') {
    $failures.Add('The password-reset page still exposes the generic Piwigo logo or theme switcher.')
}
foreach ($field in @('username_or_email', 'user_code', 'use_new_pwd', 'passwordConf', 'pwg_token')) {
    if ($template -notmatch ('name="' + [regex]::Escape($field) + '"')) {
        $failures.Add('The password-reset template lost required field: ' + $field)
    }
}
if ($template -notmatch 'autocomplete="one-time-code"' -or
    $template -notmatch 'autocomplete="new-password"') {
    $failures.Add('The password-reset form is missing secure browser autocomplete semantics.')
}
if ($template -notmatch "errors\['password_form_error'\]\|escape:'html'" -or
    $template -notmatch "errors\['password_page_error'\]") {
    $failures.Add('Password-reset errors are not rendered safely.')
}
if ($style -notmatch '(?s)#thePasswordPage\s+\.guozhan-password-page\s*\{.*?hero-gallery-01\.jpg.*?cover\s+no-repeat') {
    $failures.Add('The password-reset page does not use the gallery artwork as a full-page background.')
}
if ($template -match 'guozhan-password-visual' -or $template -match 'guozhan-password-points') {
    $failures.Add('The password-reset page still contains the oversized explanatory hero block.')
}
if ($style -notmatch '(?s)\.guozhan-password-shell\s*\{.*?justify-content:\s*center' -or
    $style -notmatch '(?s)#password-form\.guozhan-password-panel\s*\{.*?max-width:\s*430px') {
    $failures.Add('The desktop password-reset form is not presented as a compact focused panel.')
}
if ($style -notmatch '#password-form\.guozhan-password-panel\s*\{[^}]*border:\s*0' -or
    $style -notmatch '#password-form\.guozhan-password-panel\s*\{[^}]*border-left:\s*0' -or
    $style -notmatch '#password-form\.guozhan-password-panel\s*\{[^}]*box-shadow:\s*none') {
    $failures.Add('The password-reset form still has detached card or decorative rail styling.')
}
if ($style -notmatch '#thePasswordPage\s+\.guozhan-password-field\s*\{[^}]*width:\s*100%' -or
    $style -notmatch '#thePasswordPage\s+\.guozhan-password-submit[^\{]*\{[^}]*width:\s*100%') {
    $failures.Add('Native Piwigo form widths can still shrink the password-reset field or submit button.')
}
if ($style -notmatch '#thePasswordPage\s+\.guozhan-password-form\s*\{[^}]*grid-template-columns:\s*minmax\(0,\s*1fr\)') {
    $failures.Add('Native Piwigo form columns can still split the password-reset form into half-width controls.')
}
if ($style -notmatch '(?s)#thePasswordPage\s+\.guozhan-login-brand\s+strong\s*\{.*?color:\s*#fffaf0\s*!important') {
    $failures.Add('The password-reset brand title does not remain readable over the artwork background.')
}
if ($style -notmatch '@media\s*\(max-width:\s*720px\)[\s\S]*?\.guozhan-password-shell[\s\S]*?justify-content:\s*center') {
    $failures.Add('The password-reset page does not center the focused form on mobile.')
}
if ($style -match 'font-size:\s*clamp\([^;]*vw') {
    $failures.Add('Password-reset typography scales unpredictably with viewport width.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: customer-branded desktop and mobile password-reset page contract is present.'
