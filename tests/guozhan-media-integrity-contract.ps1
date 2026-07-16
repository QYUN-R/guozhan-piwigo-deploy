$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$functions = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\include\functions.inc.php') -Raw
$frontend = Get-Content -LiteralPath (Join-Path $root 'assets\guozhan.js') -Raw
$rootPlaceholder = Get-Content -LiteralPath (Join-Path $root 'assets\art-placeholder.svg') -Raw
$themePlaceholder = Get-Content -LiteralPath (Join-Path $root 'themes\guozhan-gallery\assets\art-placeholder.svg') -Raw
$themePiwigo = Get-Content -LiteralPath (Join-Path $root 'themes\guozhan-gallery\assets\guozhan-piwigo.js') -Raw
$themeBackend = Get-Content -LiteralPath (Join-Path $root 'themes\guozhan-gallery\assets\guozhan-backend.js') -Raw
$themeFooter = Get-Content -LiteralPath (Join-Path $root 'themes\guozhan-gallery\template\footer.tpl') -Raw
$failures = [System.Collections.Generic.List[string]]::new()

if ($functions -notmatch 'function\s+gzca_uploaded_image_source_state\s*\(' -or
    $functions -notmatch 'md5_file\(\$source_path\)' -or
    $functions -notmatch 'gzca_rollback_incomplete_upload\(\$image_id\)' -or
    $functions -notmatch 'gzca_count_ready_frontend_derivatives' -or
    $functions -notmatch '水印展示图生成不完整，作品已自动下架') {
    $failures.Add('Uploads are not fully verified before an artwork can remain public.')
}

if (-not $functions.Contains(".'php'.PHP_MAJOR_VERSION.PHP_MINOR_VERSION") -or
    -not $functions.Contains('/usr/bin/php[0-9]*') -or
    -not $functions.Contains("preg_match('/^php(?:[0-9]+(?:\.[0-9]+)?)?$/', `$binary_name)")) {
    $failures.Add('Derivative generation does not reliably select a CLI PHP binary while excluding php-fpm.')
}

$apiMarker = $frontend.IndexOf('function normalizeApiWork')
if ($apiMarker -lt 0) {
    $failures.Add('The live API frontend section is missing.')
}
else {
    $apiFrontend = $frontend.Substring($apiMarker)
    if ($apiFrontend -match '\|\|\s*imageForCode\(' -or
        $apiFrontend -notmatch 'function\s+renderWorkCard[\s\S]*?processingImageUrl\(\)' -or
        $apiFrontend -notmatch 'data-api-image="true"' -or
        $apiFrontend -notmatch 'data-image-ready=') {
        $failures.Add('A missing live image can still be replaced with an unrelated sample artwork.')
    }
}

if ($frontend -notmatch 'document\.addEventListener\("error"[\s\S]*?data-processing-fallback' -or
    $frontend -notmatch 'art-placeholder\.svg\?v=20260716-media-integrity-1') {
    $failures.Add('Broken derivative URLs do not fall back to the neutral processing image.')
}

foreach ($placeholder in @($rootPlaceholder, $themePlaceholder)) {
    if ($placeholder -notmatch '图片处理中' -or
        $placeholder -notmatch '未使用其他作品替代' -or
        $placeholder -match 'linearGradient' -or
        $placeholder -match '>作品图像<') {
        $failures.Add('A placeholder still looks like a real artwork instead of an explicit processing state.')
        break
    }
}

$htmlFiles = @(
    'index.html',
    'screens\categories.html',
    'screens\category-list.html',
    'screens\contact.html',
    'screens\detail.html',
    'screens\search.html'
)
foreach ($relative in $htmlFiles) {
    $html = Get-Content -LiteralPath (Join-Path $root $relative) -Raw
    if ($html -notmatch 'guozhan\.js\?v=20260716-media-integrity-1') {
        $failures.Add("The browser cache version was not updated in $relative.")
    }
}

if ($themePiwigo -notmatch 'art-placeholder\.svg\?v=20260716-media-integrity-1' -or
    $themeBackend -notmatch 'art-placeholder\.svg\?v=20260716-media-integrity-1' -or
    $themeFooter -notmatch 'guozhan-piwigo\.js\?v=20260716-media-integrity-1' -or
    $themeFooter -notmatch 'guozhan-backend\.js\?v=20260716-media-integrity-1') {
    $failures.Add('The native Piwigo theme can retain a stale artwork-like placeholder.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: uploads are verified, derivative generation uses CLI PHP, and missing live images never borrow another artwork.'
