$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$template = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\template\admin.tpl')
$style = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.css')
$script = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.js')
$failures = [System.Collections.Generic.List[string]]::new()

if ($template -notmatch 'data-mobile-nav-toggle' -or
    $template -notmatch 'aria-controls="gzca-mobile-nav"' -or
    $template -notmatch '<nav\s+id="gzca-mobile-nav"[^>]*data-mobile-nav') {
    $failures.Add('The administrator shell has no accessible mobile navigation toggle and controlled menu.')
}

if ($template -notmatch 'gzca-category-layout\s+\{if\s+\$GZCA_EDIT_CATEGORY\}is-editing\{/if\}') {
    $failures.Add('Category management does not expose editing state for mobile content ordering.')
}

if ($script -notmatch 'function\s+initMobileNavigation\s*\(' -or
    $script -notmatch 'is-mobile-nav-open' -or
    $script -notmatch 'aria-expanded' -or
    $script -notmatch 'event\.key\s*===\s*"Escape"' -or
    $script -notmatch 'initMobileNavigation\(\)') {
    $failures.Add('The mobile administrator menu cannot be opened, closed, or dismissed with Escape.')
}

if ($style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-mobile-nav-toggle\s*\{[\s\S]*?display:\s*(inline-)?flex' -or
    $style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-nav\s*\{[\s\S]*?grid-template-columns:\s*repeat\(2,\s*minmax\(0,\s*1fr\)\)' -or
    $style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-sidebar\.is-mobile-nav-open\s+\.gzca-nav') {
    $failures.Add('The mobile navigation is not a compact two-column disclosure menu.')
}

if ($style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-shell\s+input[\s\S]*?font-size:\s*16px' -or
    $style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-shell\s+select[\s\S]*?font-size:\s*16px' -or
    $style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-shell\s+textarea[\s\S]*?font-size:\s*16px') {
    $failures.Add('Mobile form controls are not fixed at 16px, so mobile browsers may zoom while editing.')
}

if ($style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-button[\s\S]*?min-height:\s*44px' -or
    $style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-work-table\s+\.gzca-row-actions\s+(a|button)[\s\S]*?min-height:\s*44px') {
    $failures.Add('Primary and work-management actions do not meet the 44px mobile touch target.')
}

if ($style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-dropzone\s*\{[\s\S]*?min-height:\s*240px') {
    $failures.Add('The mobile upload drop zone still consumes too much of the first screen.')
}

if ($style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-category-list-panel\s*\{[\s\S]*?order:\s*1' -or
    $style -notmatch '@media\s*\(max-width:\s*820px\)[\s\S]*?\.gzca-category-layout\.is-editing\s+\.gzca-category-form-panel\s*\{[\s\S]*?order:\s*1') {
    $failures.Add('Category lists are not prioritized on mobile while preserving edit-first behavior.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: administrator mobile navigation, touch targets, forms, upload, and category flow contract is present.'
