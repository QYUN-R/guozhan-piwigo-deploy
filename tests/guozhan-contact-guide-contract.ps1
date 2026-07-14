$ErrorActionPreference = 'Stop'

$root = Split-Path -Parent $PSScriptRoot
$functions = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\include\functions.inc.php')
$admin = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\admin.php')
$template = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\template\admin.tpl')
$webservice = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\include\ws.inc.php')
$frontend = Get-Content -Raw (Join-Path $root 'assets\guozhan.js')
$piwigoFrontend = Get-Content -Raw (Join-Path $root 'assets\guozhan-piwigo.js')
$themePiwigoFrontend = Get-Content -Raw (Join-Path $root 'themes\guozhan-gallery\assets\guozhan-piwigo.js')
$bridgeScript = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\assets\frontend.js')
$main = Get-Content -Raw (Join-Path $root 'plugins\GuozhanClientAdmin\main.inc.php')
$failures = [System.Collections.Generic.List[string]]::new()

if ($functions -notmatch "'contact_send_content'\s*=>\s*'作品编号 / 页面截图 / 学习需求'" -or
    $functions -notmatch "'contact_course_learning'\s*=>\s*'绘画课程咨询、学习方向与作品参考'" -or
    $functions -notmatch "'contact_consultation_tip'\s*=>\s*'请发送作品编号和学习需求，客服将及时协助。'") {
    $failures.Add('The three contact-guide fields do not have the requested short defaults.')
}

if ($admin -notmatch "\['contact_send_content'\]\s*=\s*gzca_clean_text" -or
    $admin -notmatch "\['contact_course_learning'\]\s*=\s*gzca_clean_text" -or
    $admin -notmatch "\['contact_consultation_tip'\]\s*=\s*gzca_clean_text") {
    $failures.Add('The administrator save action does not sanitize and persist all three contact-guide fields.')
}

if ($template -notmatch 'name="contact_send_content"' -or
    $template -notmatch 'name="contact_course_learning"' -or
    $template -notmatch 'name="contact_consultation_tip"' -or
    $template -notmatch '<span>课程学习</span>') {
    $failures.Add('The customer-service settings page cannot edit all three guide rows.')
}

if ($webservice -notmatch '''contacts''\s*=>\s*\$contacts' -or
    $webservice -notmatch '''sendContent''\s*=>\s*\$config\[''contact_send_content''\]' -or
    $webservice -notmatch '''courseLearning''\s*=>\s*\$config\[''contact_course_learning''\]' -or
    $webservice -notmatch '''consultationTip''\s*=>\s*\$config\[''contact_consultation_tip''\]') {
    $failures.Add('The public contact API does not return both contacts and the editable guide copy.')
}

if ($functions -notmatch '''sendContent''\s*=>\s*\$config\[''contact_send_content''\]' -or
    $functions -notmatch '''courseLearning''\s*=>\s*\$config\[''contact_course_learning''\]' -or
    $functions -notmatch '''consultationTip''\s*=>\s*\$config\[''contact_consultation_tip''\]') {
    $failures.Add('The Piwigo frontend bridge does not expose the editable guide copy.')
}

if ($frontend -notmatch 'function\s+contactGuideMarkup\s*\(contact,\s*actionHref\)' -or
    $frontend -notmatch '\.sendContent' -or
    $frontend -notmatch '\.courseLearning' -or
    $frontend -notmatch '\.consultationTip' -or
    $frontend -notmatch '<span>课程学习</span>' -or
    $frontend -match '<span>咨询范围</span>') {
    $failures.Add('The public contact page does not render the editable course-learning guide.')
}

if ($piwigoFrontend -match '咨询范围' -or
    $themePiwigoFrontend -match '咨询范围' -or
    $piwigoFrontend -notmatch 'data-contact-course-learning' -or
    $themePiwigoFrontend -notmatch 'data-contact-course-learning') {
    $failures.Add('A Piwigo fallback page still exposes the retired consultation-scope row.')
}

if ($bridgeScript -notmatch 'contact\.sendContent' -or
    $bridgeScript -notmatch 'contact\.courseLearning' -or
    $bridgeScript -notmatch 'contact\.consultationTip') {
    $failures.Add('The Piwigo frontend bridge cannot apply edited guide copy to fallback pages.')
}

if ($main -notmatch 'Version:\s*0\.9\.5' -or $main -notmatch "GZCA_VERSION',\s*'0\.9\.5'") {
    $failures.Add('The plugin version was not advanced for the editable contact-guide release.')
}

if ($failures.Count -gt 0) {
    $failures | ForEach-Object { Write-Output "FAIL: $_" }
    exit 1
}

Write-Output 'PASS: contact guide copy is editable, course-focused, API-backed, and versioned.'
