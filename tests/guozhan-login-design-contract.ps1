$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$template = Get-Content -Raw (Join-Path $root 'themes\standard_pages\template\identification.tpl')
$standardHeader = Get-Content -Raw (Join-Path $root 'themes\standard_pages\template\header.tpl')
$galleryHeader = Get-Content -Raw (Join-Path $root 'themes\guozhan-gallery\template\header.tpl')
$admin = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\admin.php')
$style = Get-Content -Raw (Join-Path $root 'themes\standard_pages\guozhan-login.css')
$failures = [System.Collections.Generic.List[string]]::new()

if ($template -notmatch "id='guozhan_login_css'[^\r\n]+guozhan-login\.css") {
    $failures.Add('The customized login stylesheet is not explicitly loaded by the login template.')
}
if ($template -notmatch 'color-scheme" content="light') {
    $failures.Add('The login page does not opt out of browser-forced dark rendering.')
}
if ($template -match '比赛' -or $template -notmatch '首页轮播') {
    $failures.Add('The login copy does not match the current website management sections.')
}
if ($standardHeader -notmatch 'themes/guozhan-gallery/assets/logo-mark\.png' -or
    $galleryHeader -notmatch 'themes/guozhan-gallery/assets/logo-mark\.png' -or
    $admin -notmatch 'themes/guozhan-gallery/assets/logo-mark\.png') {
    $failures.Add('Public, login, and administrator pages do not consistently use the Guozhan logo as the browser icon.')
}
if ($style -notmatch 'color-scheme:\s*light') {
    $failures.Add('The customized login surface is not fixed to the website light color system.')
}
if ($style -notmatch 'hero-gallery-01\.jpg') {
    $failures.Add('The desktop login page does not reuse the website gallery visual asset.')
}
if ($style -notmatch 'grid-template-columns:\s*minmax\(0,\s*1fr\)\s+430px') {
    $failures.Add('The desktop login layout does not reserve a stable compact form width.')
}
if ($style -notmatch '#login-form\.guozhan-login-panel[\s\S]*?max-width:\s*430px') {
    $failures.Add('The login panel is not constrained to the intended compact width.')
}
if ($style -notmatch '@media\s*\(max-width:\s*720px\)[\s\S]*?\.guozhan-login-shell[\s\S]*?grid-template-columns:\s*1fr') {
    $failures.Add('The login page has no dedicated single-column mobile layout.')
}
if ($style -notmatch '@media\s*\(max-width:\s*720px\)[\s\S]*?#login-form\.guozhan-login-panel[\s\S]*?padding:\s*26px 20px') {
    $failures.Add('The mobile login panel does not use compact touch-friendly spacing.')
}
if ($style -match 'font-size:\s*clamp\([^;]*vw') {
    $failures.Add('Login typography still scales unpredictably with viewport width.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: customized desktop and mobile Guozhan login design contract is present.'
