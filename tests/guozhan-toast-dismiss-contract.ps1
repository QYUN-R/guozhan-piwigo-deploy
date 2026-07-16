$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$script = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.js') -Raw
$style = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.css') -Raw
$failures = [System.Collections.Generic.List[string]]::new()

if ($script -notmatch 'document\.addEventListener\("click",\s*handleDismiss,\s*true\)' -or
    $script -notmatch 'document\.addEventListener\("keydown"[\s\S]*?handleDismiss\(event\)[\s\S]*?,\s*true\)' -or
    $script -notmatch 'element\.closest\("\.toast_icon"\)' -or
    $script -notmatch 'toast\.closest\("#pwg_toaster"\)' -or
    $script -notmatch 'toast\.parentNode\.removeChild\(toast\)' -or
    $script -notmatch 'new MutationObserver[\s\S]*?observe\(root,\s*\{\s*childList:\s*true,\s*subtree:\s*true\s*\}\)') {
    $failures.Add('Toast dismissal is not delegated in capture phase for nested and dynamically inserted close icons.')
}

if ($style -notmatch '#pwg_toaster\s+\.toast_icon\[data-toast-dismiss-ready="1"\][\s\S]*?pointer-events:\s*auto\s*!important' -or
    $style -notmatch '#pwg_toaster\s+\.toast_icon\[data-toast-dismiss-ready="1"\][\s\S]*?touch-action:\s*manipulation') {
    $failures.Add('The toast close icon can still be made non-clickable by the inherited Piwigo styles.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: administrator notices can be dismissed by mouse, touch, keyboard, and nested icon clicks.'
