$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$admin = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\admin.php') -Raw
$functions = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\include\functions.inc.php') -Raw
$script = Get-Content -LiteralPath (Join-Path $root 'plugins\GuozhanClientAdmin\assets\admin.js') -Raw
$failures = [System.Collections.Generic.List[string]]::new()

if ($script -notmatch 'function\s+findExactDuplicateFiles\s*\(' -or
    $script -notmatch 'subtle\.digest\("SHA-256"' -or
    $script -notmatch 'file\.arrayBuffer\(\)' -or
    $script -notmatch 'seen\.has\(hash\)' -or
    $script -notmatch '重复判断只比较文件内容哈希，不使用文件名或画面相似度') {
    $failures.Add('The browser does not preflight exact duplicate content with SHA-256.')
}

if ($functions -notmatch '''skipped_duplicates''\s*=>\s*0' -or
    $functions -notmatch 'gzca_find_duplicate_upload\(\$file\[''tmp_name''\],\s*\$source_sha256\)' -or
    $functions -notmatch 'GZCA_SOURCE_SHA256' -or
    $functions -notmatch 'LOCATE\(' -or
    $functions -notmatch '\$conf\[''upload_detect_duplicate''\]\s*=\s*false' -or
    $functions -notmatch 'hash_file\(''sha256'',\s*\$source\[''path''\]\)' -or
    $functions -notmatch 'hash_equals\(\$sha256sum,\s*\$candidate_sha256\)' -or
    $functions -notmatch '\$result\[''skipped_duplicates''\]\+\+' -or
    $functions -notmatch '内容哈希一致，已安全跳过' -or
    $functions -match '\$errors\[\]\s*=\s*''文件“''\.\$file_name\..*?已有作品') {
    $failures.Add('The server does not confirm existing-library duplicates with an exact SHA-256 content hash.')
}

if ($admin -notmatch '''skipped_duplicates''\s*=>\s*\$upload_result\[''skipped_duplicates''\]' -or
    $admin -notmatch '''notices''\s*=>\s*\$upload_result\[''notices''\]' -or
    $admin -notmatch '''watermark_failed''\s*=>\s*\$upload_result\[''watermark_failed''\]' -or
    $script -notmatch 'function\s+normalizeUploadBatchOutcome\s*\(' -or
    $script -notmatch 'stored\s*-\s*watermarkFailed' -or
    $script -notmatch '跳过完全重复') {
    $failures.Add('Upload progress cannot distinguish successful, skipped, and failed files.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: exact duplicate bytes are hash-checked and skipped without blocking similarly named or visually similar files.'
