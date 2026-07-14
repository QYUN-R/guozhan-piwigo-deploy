$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$functions = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\include\functions.inc.php')
$template = Get-Content -Raw (Join-Path $root 'themes\standard_pages\template\identification.tpl')
$failures = [System.Collections.Generic.List[string]]::new()

if ($functions -notmatch 'function\s+gzca_admin_login_redirect_path\s*\(' -or
    $functions -notmatch "cookie_path\(\)\.'admin\.php\?page=plugin-'\.GZCA_ID\.'&tab=dashboard'") {
    $failures.Add('There is no canonical internal dashboard path for administrator login redirects.')
}

if ($functions -notmatch 'function\s+gzca_admin_login_url\s*\([\s\S]*?gzca_admin_login_redirect_path\(\)') {
    $failures.Add('Forced administrator reauthentication does not reuse the canonical dashboard redirect.')
}

if ($functions -notmatch 'function\s+gzca_prepare_login_notice\s*\([\s\S]*?empty\(\$_GET\[''redirect''\]\)[\s\S]*?empty\(\$_POST\[''redirect''\]\)[\s\S]*?assign\(''U_REDIRECT'',\s*gzca_admin_login_redirect_path\(\)\)') {
    $failures.Add('A direct administrator login still leaves the post-login redirect empty instead of opening the dashboard.')
}

if ($template -notmatch '<input\s+type="hidden"\s+name="redirect"\s+value="\{\$U_REDIRECT\|@urlencode\|escape:''html''\}">') {
    $failures.Add('The login form does not submit the validated Piwigo redirect field.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: direct administrator login defaults to the custom dashboard while preserving validated redirects.'
