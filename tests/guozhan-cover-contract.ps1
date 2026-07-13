$ErrorActionPreference = 'Stop'
$root = Split-Path -Parent $PSScriptRoot
$admin = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\admin.php') -Raw
$functions = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\include\functions.inc.php') -Raw
$template = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\template\admin.tpl') -Raw
$adminScript = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.js') -Raw
$frontend = Get-Content -LiteralPath (Join-Path $root 'assets\guozhan.js') -Raw

$failures = [System.Collections.Generic.List[string]]::new()

if ($admin -notmatch '''unset_cover''\s*===\s*\$action' -or
    $admin -notmatch '分类封面已下架' -or
    $admin -notmatch 'gzca_category_path') {
    $failures.Add('The administrator cannot remove a category cover or identify the affected category.')
}

if ($functions -notmatch 'function\s+gzca_unset_category_cover\s*\(' -or
    $functions -notmatch '''representative_picture_id''\s*=>\s*null' -or
    $functions -notmatch 'category_cover_image_id' -or
    $functions -notmatch 'is_category_cover') {
    $failures.Add('Cover removal and current-cover state are not implemented in the backend model.')
}

if ($template -notmatch '当前封面' -or
    $template -notmatch 'value="unset_cover"' -or
    $template -notmatch '下架封面' -or
    $template -notmatch '设为此板块封面') {
    $failures.Add('The work table does not clearly distinguish setting and removing the current category cover.')
}

if ($adminScript -notmatch 'action\.value\s*===\s*"unset_cover"' -or
    $adminScript -notmatch '确认下架封面') {
    $failures.Add('Removing a category cover is not protected by the administrator confirmation panel.')
}

if ($frontend -notmatch 'coverUrl:\s*parent\.tn_url' -or
    $frontend -notmatch 'directory-cover' -or
    $frontend -notmatch 'quick-category-cover' -or
    $frontend -notmatch '--page-hero-image') {
    $failures.Add('The static frontend still ignores category cover URLs returned by the API.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: category covers have visible frontend placement, backend state, and removal controls.'
