$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$admin = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\admin.php')
$functions = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\include\functions.inc.php')
$template = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\template\admin.tpl')
$script = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.js')
$style = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.css')
$failures = [System.Collections.Generic.List[string]]::new()

if ($template -match 'data-watermark-toggle' -or
    $template -match 'watermark_disable_confirmed' -or
    $template -notmatch 'class="gzca-watermark-policy"' -or
    $template -notmatch 'data-watermark-enabled') {
    $failures.Add('The administrator still exposes a watermark-off control or does not report the locked policy.')
}

if ($admin -notmatch 'gzca_set_frontend_watermark\(true' -or
    $admin -match '\$watermark_enabled\s*=\s*isset\(\$_POST\[''watermark_enabled''\]\)') {
    $failures.Add('A stale watermark form can still request that the backend disables watermark protection.')
}

if ($functions -notmatch '\[''watermark_enabled''\]\s*=\s*true' -or
    $functions -match 'if\s*\(empty\(\$watermark_upload_confirmed\)\)' -or
    $functions -notmatch 'if\s*\(empty\(\$watermark_state\[''enabled''\]\)\)\s*\{[\s\S]*?return\s+\$result;') {
    $failures.Add('Uploads can still bypass watermark protection after a confirmation.')
}

if ($script -match 'watermarkUploadConfirmed' -or
    $script -match 'data-watermark-upload-confirmed' -or
    $script -notmatch 'function\s+initDismissibleToasts\s*\(' -or
    $script -notmatch 'data-toast-dismiss-ready' -or
    $script -notmatch 'form\.getAttribute\("data-watermark-enabled"\)\s*!==\s*"1"[\s\S]*?showNotice\(') {
    $failures.Add('The browser still offers unwatermarked uploads or does not make toast icons dismissible.')
}

if ($style -notmatch '#pwg_toaster\s+\.toast_icon\[data-toast-dismiss-ready=' -or
    $style -notmatch '\.gzca-watermark-policy') {
    $failures.Add('The locked watermark policy or dismissible toast affordance is not styled.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: watermark protection is locked on, unwatermarked uploads are blocked, and toast icons are dismissible.'
