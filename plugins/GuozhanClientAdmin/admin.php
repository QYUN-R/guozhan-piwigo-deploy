<?php

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

check_status(ACCESS_ADMINISTRATOR);
include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');

global $conf, $page, $template, $user;

$allowed_tabs = array('dashboard', 'home', 'upload', 'works', 'categories', 'contact', 'security');
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
if (!in_array($tab, $allowed_tabs, true))
{
  $tab = 'dashboard';
}

$action = isset($_POST['gzca_action']) ? $_POST['gzca_action'] : '';
if (!empty($action))
{
  check_pwg_token();
}

$email_security = gzca_admin_email_security_status((int)$user['id']);
$mail_status = gzca_security_mail_status();
$security_form_email = isset($_POST['new_email']) ? gzca_normalize_email($_POST['new_email']) : '';

if ('revoke_other_sessions' === $action)
{
  $current_password = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
  $confirmed = isset($_POST['confirm_other_sessions']) && '1' === (string)$_POST['confirm_other_sessions'];
  $session_error = '';
  $revoked_count = 0;
  if (!gzca_verify_current_password((int)$user['id'], $current_password))
  {
    $page['errors'][] = '当前密码不正确，未退出任何设备。';
  }
  elseif (!$confirmed)
  {
    $page['errors'][] = '请先确认退出其他设备，再执行此操作。';
  }
  elseif (gzca_revoke_other_admin_sessions((int)$user['id'], $session_error, $revoked_count))
  {
    $page['infos'][] = $revoked_count > 0
      ? '已退出其他设备的 '.$revoked_count.' 个登录会话，当前设备保持登录。'
      : '当前没有检测到其他登录会话，当前设备保持登录。';
  }
  else
  {
    $page['errors'][] = $session_error;
  }
}

if ('send_bind_email_code' === $action)
{
  $current_password = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
  $email_error = '';
  if (!gzca_verify_current_password((int)$user['id'], $current_password))
  {
    $page['errors'][] = '当前密码不正确，未发送验证码。';
  }
  elseif (!gzca_validate_admin_email((int)$user['id'], $security_form_email, $email_error))
  {
    $page['errors'][] = $email_error;
  }
  elseif (gzca_send_admin_security_code('bind_email', $security_form_email, (int)$user['id'], $email_error))
  {
    $page['infos'][] = '验证码已发送到 '.gzca_mask_email($security_form_email).'，10 分钟内有效。';
  }
  else
  {
    $page['errors'][] = $email_error;
  }
}

if ('verify_bind_email' === $action)
{
  $current_password = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
  $verification_code = isset($_POST['verification_code']) ? (string)$_POST['verification_code'] : '';
  $email_error = '';
  if (!gzca_verify_current_password((int)$user['id'], $current_password))
  {
    $page['errors'][] = '当前密码不正确，邮箱没有变更。';
  }
  elseif (!gzca_validate_admin_email((int)$user['id'], $security_form_email, $email_error))
  {
    $page['errors'][] = $email_error;
  }
  elseif (!gzca_verify_admin_security_code('bind_email', $security_form_email, $verification_code, (int)$user['id'], $email_error))
  {
    $page['errors'][] = $email_error;
  }
  elseif (gzca_bind_verified_admin_email((int)$user['id'], $security_form_email, $email_error))
  {
    $page['infos'][] = '恢复邮箱已验证并绑定为 '.gzca_mask_email($security_form_email).'。以后找回密码会使用这个邮箱。';
    $email_security = gzca_admin_email_security_status((int)$user['id']);
  }
  else
  {
    $page['errors'][] = $email_error;
  }
}

if ('send_password_code' === $action)
{
  $current_password = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
  $password_error = '';
  if (empty($email_security['verified']))
  {
    $page['errors'][] = '请先验证并绑定恢复邮箱，再修改管理员密码。';
  }
  elseif (!gzca_verify_current_password((int)$user['id'], $current_password))
  {
    $page['errors'][] = '当前密码不正确，未发送验证码。';
  }
  elseif (gzca_send_admin_security_code('change_password', $email_security['email'], (int)$user['id'], $password_error))
  {
    $page['infos'][] = '改密验证码已发送到 '.$email_security['masked_email'].'，10 分钟内有效。';
  }
  else
  {
    $page['errors'][] = $password_error;
  }
}

if ('change_admin_password' === $action)
{
  $current_password = isset($_POST['current_password']) ? (string)$_POST['current_password'] : '';
  $verification_code = isset($_POST['verification_code']) ? (string)$_POST['verification_code'] : '';
  $new_password = isset($_POST['new_password']) ? (string)$_POST['new_password'] : '';
  $new_password_confirm = isset($_POST['new_password_confirm']) ? (string)$_POST['new_password_confirm'] : '';
  $password_error = '';
  if (empty($email_security['verified']))
  {
    $page['errors'][] = '请先验证并绑定恢复邮箱，再修改管理员密码。';
  }
  elseif (!gzca_verify_current_password((int)$user['id'], $current_password))
  {
    $page['errors'][] = '当前密码不正确，密码没有变更。';
  }
  elseif ($new_password !== $new_password_confirm)
  {
    $page['errors'][] = '两次输入的新密码不一致。';
  }
  elseif ($new_password === $current_password)
  {
    $page['errors'][] = '新密码不能与当前密码相同。';
  }
  elseif (!gzca_validate_new_admin_password($new_password, $user['username'], $email_security['email'], $password_error))
  {
    $page['errors'][] = $password_error;
  }
  elseif (!gzca_verify_admin_security_code('change_password', $email_security['email'], $verification_code, (int)$user['id'], $password_error))
  {
    $page['errors'][] = $password_error;
  }
  else
  {
    gzca_change_admin_password((int)$user['id'], $new_password);
    logout_user();
    redirect(gzca_admin_login_url('password_changed'));
  }
}

if ('download_work' === $action)
{
  $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
  $download_error = '';
  if (!gzca_stream_admin_work_download($image_id, $download_error))
  {
    $page['errors'][] = $download_error ?: '原图下载失败，请确认作品仍然存在。';
  }
}

if (!function_exists('gzca_selected_image_ids_from_post'))
{
  function gzca_selected_image_ids_from_post($key='selected_images')
  {
    $raw = isset($_POST[$key]) ? $_POST[$key] : array();
    if (!is_array($raw))
    {
      $raw = array($raw);
    }
    $ids = array();
    foreach ($raw as $id)
    {
      $id = (int)$id;
      if ($id > 0)
      {
        $ids[$id] = $id;
      }
    }
    return array_values($ids);
  }
}

if ('seed_categories' === $action)
{
  $created = gzca_seed_categories();
  $page['infos'][] = $created > 0
    ? '已创建 '.$created.' 个前台板块，并同步为 39 个可上传位置。'
    : '前台 10 个大板块和 29 个子板块已经存在，39 个可上传位置已同步，客户修改的名称与排序已保留。';
}

if ('import_sample_works' === $action)
{
  $sample_errors = array();
  $result = gzca_import_sample_works($sample_errors);
  if ($result['created'] > 0)
  {
    $page['infos'][] = '已导入 '.$result['created'].' 张本地样例作品，可直接在前台检查作品列表和详情。';
  }
  if ($result['skipped'] > 0)
  {
    $page['infos'][] = '已有 '.$result['skipped'].' 张样例作品，已跳过重复导入。';
  }
  foreach ($sample_errors as $sample_error)
  {
    $page['errors'][] = $sample_error;
  }
}

if ('upload_works' === $action)
{
  $album_id = isset($_POST['album_id']) ? (int)$_POST['album_id'] : 0;
  $publish_now = isset($_POST['publish_now']) && '1' === (string)$_POST['publish_now'];
  $set_cover = isset($_POST['set_cover']);
  $uploads = gzca_normalize_uploads(isset($_FILES['artworks']) ? $_FILES['artworks'] : array());
  $async_upload = isset($_POST['gzca_async']) && '1' === (string)$_POST['gzca_async'];

  $upload_errors = array();
  $upload_result = gzca_upload_works_batch($album_id, $uploads, $publish_now, $set_cover, $upload_errors);

  if ($async_upload)
  {
    if (function_exists('ob_get_level'))
    {
      while (ob_get_level() > 0)
      {
        ob_end_clean();
      }
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
      'ok' => empty($upload_errors) && $upload_result['uploaded'] > 0,
      'uploaded' => $upload_result['uploaded'],
      'codes' => $upload_result['uploaded_codes'],
      'message' => $upload_result['message'],
      'compressed_count' => $upload_result['compressed_count'],
      'compressed_saved' => gzca_format_bytes($upload_result['compressed_saved_bytes']),
      'errors' => $upload_errors,
      ), JSON_UNESCAPED_UNICODE);
    exit;
  }

  foreach ($upload_errors as $upload_error)
  {
    $page['errors'][] = $upload_error;
  }

  if ($upload_result['uploaded'] > 0)
  {
    $page['infos'][] = htmlspecialchars($upload_result['message'], ENT_QUOTES, 'UTF-8');
    if ($upload_result['compressed_count'] > 0)
    {
      $page['infos'][] = '已自动压缩 '.$upload_result['compressed_count'].' 张新上传图片，目标控制在约 1-3MB，节省约 '.gzca_format_bytes($upload_result['compressed_saved_bytes']).' 存储空间。';
    }
  }
}

if ('save_work' === $action)
{
  $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
  $album_id = isset($_POST['album_id']) ? (int)$_POST['album_id'] : 0;
  $title = gzca_clean_text(isset($_POST['title']) ? $_POST['title'] : '', 255);
  $code = gzca_normalize_code(isset($_POST['code']) ? $_POST['code'] : '');
  $description = gzca_clean_text(isset($_POST['description']) ? $_POST['description'] : '', 5000);
  $status = isset($_POST['status']) && 'offline' === $_POST['status'] ? 'offline' : 'online';
  $visibility = isset($_POST['visibility']) && 'private' === $_POST['visibility'] ? 'private' : 'public';
  $featured = isset($_POST['featured']) ? 1 : 0;
  $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
  $download_count = isset($_POST['download_count']) ? max(0, (int)$_POST['download_count']) : 0;

  if (null === gzca_find_image($image_id))
  {
    $page['errors'][] = '作品不存在或已被删除。';
  }
  elseif (!gzca_category_is_upload_target($album_id))
  {
    $page['errors'][] = '请选择可以直接放置作品的前台板块、分类或画展。';
  }
  elseif ('' === $title)
  {
    $page['errors'][] = '作品名称不能为空。';
  }
  elseif (!gzca_valid_code($code))
  {
    $page['errors'][] = '作品编号格式不正确，例如 YH-FJ-001。';
  }
  elseif (gzca_code_exists($code, $image_id))
  {
    $page['errors'][] = '作品编号“'.$code.'”已经存在，请换一个编号。';
  }
  else
  {
    single_update(
      IMAGES_TABLE,
      array(
        'name' => $title,
        'comment' => gzca_embed_code($description, $code),
        'level' => 'public' === $visibility ? 0 : 8,
        ),
      array('id' => $image_id)
      );

    $sync_result = gzca_sync_images_to_single_category(array($image_id), $album_id);
    gzca_save_work_meta($image_id, $code, $status, $featured, $sort_order, $download_count);

    if (isset($_POST['set_cover']))
    {
      gzca_set_category_cover($album_id, $image_id);
    }

    foreach (array_unique(array_merge(isset($sync_result['old_category_ids']) ? $sync_result['old_category_ids'] : array(), array($album_id))) as $changed_category_id)
    {
      if ((int)$changed_category_id > 0)
      {
        update_category((int)$changed_category_id);
      }
    }
    invalidate_user_cache();
    pwg_activity('photo', $image_id, 'edit', array('fields' => 'gzca_client_admin'));
    $page['infos'][] = '作品“'.$title.'”已保存。';
  }
}

if ('toggle_work' === $action)
{
  $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
  $work = gzca_find_image($image_id);
  if (null === $work)
  {
    $page['errors'][] = '作品不存在或已被删除。';
  }
  else
  {
    $status = 'online' === $work['status'] ? 'offline' : 'online';
    single_update(IMAGES_TABLE, array('level' => 'online' === $status ? 0 : 8), array('id' => $image_id));
    gzca_save_work_meta($image_id, $work['code'], $status, $work['featured'], $work['sort_order'], $work['download_count']);
    invalidate_user_cache();
    $page['infos'][] = '作品已'.('online' === $status ? '上架' : '下架').'。';
  }
}

if ('delete_work' === $action)
{
  $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
  $delete_confirm = isset($_POST['delete_confirm']) ? trim($_POST['delete_confirm']) : '';
  $delete_error = '';

  if ('永久删除作品' !== $delete_confirm)
  {
    $page['errors'][] = '永久删除作品需要确认，本次没有执行删除。';
  }
  else
  {
    $deleted_count = gzca_delete_works(array($image_id), $delete_error);
    if (false !== $deleted_count && $deleted_count > 0)
    {
      $page['infos'][] = '作品已永久删除：原图、缩略图/缓存图、数据库记录已清理。';
    }
    else
    {
      $page['errors'][] = $delete_error ?: '永久删除作品失败，未找到可删除的作品。';
    }
  }
}

if ('bulk_works' === $action)
{
  $selected_ids = gzca_selected_image_ids_from_post();
  $bulk_action = isset($_POST['bulk_action']) ? $_POST['bulk_action'] : '';
  $bulk_album_id = isset($_POST['bulk_album_id']) ? (int)$_POST['bulk_album_id'] : 0;
  $bulk_delete_confirm = isset($_POST['bulk_delete_confirm']) ? trim($_POST['bulk_delete_confirm']) : '';

  if (empty($selected_ids))
  {
    $page['errors'][] = '请先勾选要批量处理的作品。';
  }
  elseif (count($selected_ids) > 500)
  {
    $page['errors'][] = '一次最多批量处理 500 张作品，请缩小筛选范围后分批操作。';
  }
  elseif (!in_array($bulk_action, array('online', 'offline', 'move', 'delete'), true))
  {
    $page['errors'][] = '请选择要执行的批量操作。';
  }
  elseif ('move' === $bulk_action && !gzca_category_is_upload_target($bulk_album_id))
  {
    $page['errors'][] = '请选择可以放置作品的目标板块。';
  }
  elseif ('delete' === $bulk_action && '永久删除作品' !== $bulk_delete_confirm)
  {
    $page['errors'][] = '批量永久删除作品需要确认，本次没有执行删除。';
  }
  else
  {
    $changed = 0;
    if ('online' === $bulk_action || 'offline' === $bulk_action)
    {
      foreach ($selected_ids as $image_id)
      {
        $work = gzca_find_image($image_id);
        if (null === $work)
        {
          continue;
        }
        single_update(IMAGES_TABLE, array('level' => 'online' === $bulk_action ? 0 : 8), array('id' => $image_id));
        gzca_save_work_meta($image_id, $work['code'], $bulk_action, $work['featured'], $work['sort_order'], $work['download_count']);
        $changed++;
      }
      if ($changed > 0)
      {
        invalidate_user_cache();
      }
      $page['infos'][] = 'online' === $bulk_action
        ? '已批量上架 '.$changed.' 张作品。'
        : '已批量下架 '.$changed.' 张作品。';
    }
    elseif ('move' === $bulk_action)
    {
      $sync_result = gzca_sync_images_to_single_category($selected_ids, $bulk_album_id);
      $changed = count($selected_ids);
      $page['infos'][] = '已批量移动 '.$changed.' 张作品到“'.htmlspecialchars(gzca_category_path($bulk_album_id), ENT_QUOTES, 'UTF-8').'”。作品编号保持不变。';
    }
    elseif ('delete' === $bulk_action)
    {
      $delete_error = '';
      $deleted_count = gzca_delete_works($selected_ids, $delete_error);
      if (false !== $deleted_count)
      {
        $page['infos'][] = '已批量永久删除 '.$deleted_count.' 张作品：原图、缩略图/缓存图、数据库记录已清理。';
      }
      else
      {
        $page['errors'][] = $delete_error ?: '批量永久删除作品失败。';
      }
    }
  }
}

if ('set_cover' === $action)
{
  $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
  $album_id = isset($_POST['album_id']) ? (int)$_POST['album_id'] : 0;
  if (gzca_set_category_cover($album_id, $image_id))
  {
    invalidate_user_cache();
    $cover_category = htmlspecialchars(gzca_category_path($album_id), ENT_QUOTES, 'UTF-8');
    $page['infos'][] = '已设为“'.$cover_category.'”板块封面。该板块页面顶部会立即使用此图；父级板块封面还会显示在前台分类入口卡片。作品下架或设为私密时，前台会暂时隐藏封面。';
  }
  else
  {
    $page['errors'][] = '设置封面失败，该作品不属于所选分类。';
  }
}

if ('unset_cover' === $action)
{
  $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
  $album_id = isset($_POST['album_id']) ? (int)$_POST['album_id'] : 0;
  if (gzca_unset_category_cover($album_id, $image_id))
  {
    invalidate_user_cache();
    $cover_category = htmlspecialchars(gzca_category_path($album_id), ENT_QUOTES, 'UTF-8');
    $page['infos'][] = '“'.$cover_category.'”分类封面已下架，作品本身没有删除或下架。';
  }
  else
  {
    $page['errors'][] = '下架封面失败：该作品已经不是这个板块的当前封面，请刷新页面后重试。';
  }
}

if (in_array($action, array('hide_category', 'show_category'), true))
{
  $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
  $hidden = 'hide_category' === $action;
  $category_error = '';
  if (gzca_set_category_hidden($category_id, $hidden, $category_error))
  {
    $page['infos'][] = $hidden
      ? '板块已隐藏：前台不再显示，图片、作品记录和分类记录已保留。'
      : '板块已显示：前台会重新展示，并允许上传。';
  }
  else
  {
    $page['errors'][] = $category_error ?: ($hidden ? '隐藏板块失败。' : '显示板块失败。');
  }
}

if ('delete_category' === $action)
{
  $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
  $delete_confirm = isset($_POST['delete_confirm']) ? trim($_POST['delete_confirm']) : '';
  $category_error = '';
  $delete_summary = array();
  if ('永久删除' !== $delete_confirm)
  {
    $page['errors'][] = '永久删除需要输入确认文字“永久删除”，本次没有执行删除。';
  }
  else
  {
    $deleted_count = gzca_delete_category_tree($category_id, $category_error, $delete_summary);
    if (false !== $deleted_count)
    {
      $deleted_images = isset($delete_summary['image_count']) ? (int)$delete_summary['image_count'] : 0;
      $shared_images = isset($delete_summary['shared_image_count']) ? (int)$delete_summary['shared_image_count'] : 0;
      $message = '板块已永久删除：共移除 '.$deleted_count.' 个分类节点，并删除 '.$deleted_images.' 张只属于该板块的作品图片文件、缩略图/缓存图和作品记录。';
      if ($shared_images > 0)
      {
        $message .= ' 另有 '.$shared_images.' 张作品仍属于其他板块，本次只解除当前板块关联，已保留图片文件。';
      }
      $page['infos'][] = $message;
    }
    else
    {
      $page['errors'][] = $category_error ?: '永久删除板块失败。';
    }
  }
}

if ('create_category' === $action)
{
  $name = gzca_clean_text(isset($_POST['name']) ? $_POST['name'] : '', 255);
  $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
  $prefix = gzca_normalize_code(isset($_POST['code_prefix']) ? $_POST['code_prefix'] : '');
  $description = gzca_clean_text(isset($_POST['description']) ? $_POST['description'] : '', 5000);
  $visible = isset($_POST['visible']);
  $direct_upload = isset($_POST['direct_upload']);
  $category_kind = isset($_POST['category_kind']) && 'exhibition' === $_POST['category_kind'] ? 'exhibition' : 'catalog';
  $reserved = isset($_POST['reserved']);

  if ('' === $name)
  {
    $page['errors'][] = '分类名称不能为空。';
  }
  elseif (!gzca_valid_prefix($prefix))
  {
    $page['errors'][] = '分类编号前缀格式不正确，例如 YH-FJ。';
  }
  elseif (gzca_category_prefix_exists($prefix))
  {
    $page['errors'][] = '编号前缀“'.$prefix.'”已经被其他板块使用。';
  }
  elseif (!empty($parent_id) && !gzca_category_exists($parent_id))
  {
    $page['errors'][] = '上级分类不存在。';
  }
  else
  {
    $result = create_virtual_category(
      $name,
      $parent_id,
      array('visible' => $visible, 'comment' => $description, 'status' => 'public')
      );
    if (!empty($result['id']))
    {
      gzca_save_category_meta(
        $result['id'],
        $prefix,
        0,
        1,
        $direct_upload || !empty($parent_id),
        'custom-'.(int)$result['id'],
        $category_kind,
        $reserved
        );
      invalidate_user_cache();
      $page['infos'][] = '分类“'.$name.'”已创建。';
    }
    else
    {
      $page['errors'][] = isset($result['error']) ? $result['error'] : '分类创建失败。';
    }
  }
}

if ('save_category' === $action)
{
  $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
  $name = gzca_clean_text(isset($_POST['name']) ? $_POST['name'] : '', 255);
  $prefix = gzca_normalize_code(isset($_POST['code_prefix']) ? $_POST['code_prefix'] : '');
  $description = gzca_clean_text(isset($_POST['description']) ? $_POST['description'] : '', 5000);
  $rank = isset($_POST['sort_order']) ? max(0, (int)$_POST['sort_order']) : 0;
  $visible = isset($_POST['visible']);
  $direct_upload = isset($_POST['direct_upload']);
  $category_kind = isset($_POST['category_kind']) && 'exhibition' === $_POST['category_kind'] ? 'exhibition' : 'catalog';
  $reserved = isset($_POST['reserved']);

  if (!gzca_category_exists($category_id))
  {
    $page['errors'][] = '分类不存在或已被删除。';
  }
  elseif ('' === $name)
  {
    $page['errors'][] = '分类名称不能为空。';
  }
  elseif (!gzca_valid_prefix($prefix))
  {
    $page['errors'][] = '分类编号前缀格式不正确，例如 YH-FJ。';
  }
  elseif (gzca_category_prefix_exists($prefix, $category_id))
  {
    $page['errors'][] = '编号前缀“'.$prefix.'”已经被其他板块使用。';
  }
  else
  {
    single_update(
      CATEGORIES_TABLE,
      array(
        'name' => $name,
        'comment' => $description,
        'rank' => $rank,
        'visible' => $visible ? 'true' : 'false',
        ),
      array('id' => $category_id)
      );
    gzca_save_category_meta($category_id, $prefix, $rank, 1, $direct_upload || gzca_category_is_contract_node($category_id), null, $category_kind, $reserved);
    update_global_rank();
    invalidate_user_cache();
    pwg_activity('album', $category_id, 'edit', array('fields' => 'gzca_client_admin'));
    $page['infos'][] = '分类“'.$name.'”已保存。';
  }
}

if ('save_home_hero' === $action)
{
  $config = gzca_config();
  $slides = gzca_normalize_hero_slides($config);
  foreach ($slides as $index => $slide)
  {
    $slot = $index + 1;
    $slide['enabled'] = isset($_POST['hero_enabled'][$index]);
    $slide['title'] = gzca_clean_text(isset($_POST['hero_title'][$index]) ? $_POST['hero_title'][$index] : $slide['title'], 80);
    $position = isset($_POST['hero_position'][$index]) ? $_POST['hero_position'][$index] : $slide['position'];
    $allowed_positions = array('center', 'center top', 'center bottom', 'left center', 'right center');
    $slide['position'] = in_array($position, $allowed_positions, true) ? $position : 'center';

    if (isset($_POST['hero_clear'][$index]))
    {
      $slide['image_path'] = !empty($slide['default_path']) ? $slide['default_path'] : '';
      if (empty($slide['default_path']))
      {
        $slide['enabled'] = false;
      }
    }

    $field = 'hero_image_'.$index;
    $upload_error = '';
    $uploaded_path = gzca_save_hero_slide_image(isset($_FILES[$field]) ? $_FILES[$field] : array(), $slot, $upload_error);
    if (false === $uploaded_path)
    {
      $page['errors'][] = '轮播位 '.(int)$slot.'：'.$upload_error;
    }
    elseif (is_string($uploaded_path) && '' !== $uploaded_path)
    {
      $slide['image_path'] = $uploaded_path;
      $slide['enabled'] = true;
    }

    $slides[$index] = array(
      'index' => $index,
      'enabled' => !empty($slide['enabled']),
      'title' => $slide['title'],
      'image_path' => $slide['image_path'],
      'position' => $slide['position'],
      );
  }

  if (empty($page['errors']))
  {
    $config['hero_slides'] = $slides;
    conf_update_param('gzca_config', $config, true, 'serialize');
    $conf['gzca_config'] = $config;
    $page['infos'][] = '首页轮播已保存，前台会自动同步新的轮播壁纸。';
  }
}

if ('save_contact' === $action)
{
  $config = gzca_config();
  $defaults = gzca_default_config();
  $config['brand_name'] = gzca_clean_text(isset($_POST['brand_name']) ? $_POST['brand_name'] : '', 120);
  $config['brand_en'] = gzca_clean_text(isset($_POST['brand_en']) ? $_POST['brand_en'] : '', 160);
  $config['phone'] = gzca_clean_text(isset($_POST['phone']) ? $_POST['phone'] : '', 80);
  $config['contact_send_content'] = gzca_clean_text(isset($_POST['contact_send_content']) ? $_POST['contact_send_content'] : '', 200);
  $config['contact_course_learning'] = gzca_clean_text(isset($_POST['contact_course_learning']) ? $_POST['contact_course_learning'] : '', 200);
  $config['contact_consultation_tip'] = gzca_clean_text(isset($_POST['contact_consultation_tip']) ? $_POST['contact_consultation_tip'] : '', 240);
  foreach (array('contact_send_content', 'contact_course_learning', 'contact_consultation_tip') as $copy_key)
  {
    if ('' === $config[$copy_key])
    {
      $config[$copy_key] = $defaults[$copy_key];
    }
  }
  $config['frontend_sync'] = isset($_POST['frontend_sync']);
  $config['restrict_administrators'] = true;

  $contacts = gzca_normalize_contacts($config);
  $posted_contacts = isset($_POST['contacts']) && is_array($_POST['contacts']) ? $_POST['contacts'] : array();
  $has_error = false;
  for ($slot = 1; $slot <= 2; $slot++)
  {
    $index = $slot - 1;
    $posted = isset($posted_contacts[$index]) && is_array($posted_contacts[$index]) ? $posted_contacts[$index] : array();
    $contacts[$index]['label'] = gzca_clean_text(isset($posted['label']) ? $posted['label'] : '客服'.$slot, 60);
    $contacts[$index]['wechat'] = gzca_clean_text(isset($posted['wechat']) ? $posted['wechat'] : '', 120);
    $contacts[$index]['note'] = gzca_clean_text(isset($posted['note']) ? $posted['note'] : '', 1000);
    $contacts[$index]['enabled'] = isset($posted['enabled']);
    if (1 === $slot)
    {
      $contacts[$index]['enabled'] = true;
    }
    if ('' === $contacts[$index]['label'])
    {
      $contacts[$index]['label'] = '客服'.$slot;
    }
    $file_key = 'contact_qr_'.$slot;
    $qr_error = '';
    $qr_path = gzca_save_contact_qr(isset($_FILES[$file_key]) ? $_FILES[$file_key] : array(), $qr_error, $slot);
    if (false === $qr_path)
    {
      $page['errors'][] = $qr_error;
      $has_error = true;
    }
    elseif (is_string($qr_path) && '' !== $qr_path)
    {
      $contacts[$index]['qr_path'] = $qr_path;
    }
  }

  $logo_error = '';
  $logo_path = gzca_save_logo(isset($_FILES['brand_logo']) ? $_FILES['brand_logo'] : array(), $logo_error);
  if (false === $logo_path)
  {
    $page['errors'][] = $logo_error;
    $has_error = true;
  }

  if (!$has_error)
  {
    if (is_string($logo_path) && '' !== $logo_path)
    {
      $config['logo_path'] = $logo_path;
    }
    $config['contacts'] = $contacts;
    $config['wechat'] = $contacts[0]['wechat'];
    $config['contact_note'] = $contacts[0]['note'];
    $config['qr_path'] = $contacts[0]['qr_path'];
    conf_update_param('gzca_config', $config, true, 'serialize');
    $conf['gzca_config'] = $config;
    $page['infos'][] = '客服信息与沟通文案已保存，前台客服入口会自动同步。';
  }
}

$categories = array_map('gzca_prepare_category_row', gzca_get_categories(true));
$category_options = gzca_category_options($categories);
$category_option_groups = gzca_category_option_groups($categories);
$upload_target_count = count($category_options);
$database_health = gzca_database_health();
$sample_works_status = gzca_sample_works_status();
$email_security = gzca_admin_email_security_status((int)$user['id']);
$email_security_view = $email_security;
unset($email_security_view['email']);
$email_security_view['bind_challenge'] = gzca_security_challenge_status('bind_email');
$email_security_view['password_challenge'] = gzca_security_challenge_status('change_password');

$stats = array();
list($stats['works']) = pwg_db_fetch_row(pwg_query('SELECT COUNT(*) FROM '.IMAGES_TABLE.';'));
list($stats['online']) = pwg_db_fetch_row(pwg_query('SELECT COUNT(*) FROM '.IMAGES_TABLE.' WHERE level = 0;'));
list($stats['offline']) = pwg_db_fetch_row(pwg_query('SELECT COUNT(*) FROM '.IMAGES_TABLE.' WHERE level > 0;'));
$stats['categories'] = count(array_filter($categories, function ($category) {
  return empty($category['id_uppercat']);
}));
$recent_rows = !empty($database_health['works_table']) ? query2array('
SELECT
    i.*,
    gw.code AS gzca_code,
    gw.status AS gzca_status,
    gw.featured,
    gw.sort_order,
    gw.download_count,
    ic.category_id,
    c.name AS category_name,
    c.representative_picture_id AS category_cover_image_id
  FROM '.IMAGES_TABLE.' AS i
    LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
    LEFT JOIN '.IMAGE_CATEGORY_TABLE.' AS ic ON ic.image_id = i.id
      AND ic.category_id = (
        SELECT ic2.category_id
          FROM '.IMAGE_CATEGORY_TABLE.' AS ic2
            INNER JOIN '.CATEGORIES_TABLE.' AS c2 ON c2.id = ic2.category_id
          WHERE ic2.image_id = i.id
          ORDER BY LENGTH(c2.uppercats) DESC, c2.global_rank
          LIMIT 1
      )
    LEFT JOIN '.CATEGORIES_TABLE.' AS c ON c.id = ic.category_id
  ORDER BY i.id DESC
  LIMIT 6
;') : array();
$recent_works = array_map('gzca_prepare_work_row', $recent_rows);

$works = array();
$pager = array('page' => 1, 'pages' => 1, 'total' => 0, 'previous_url' => '', 'next_url' => '');
$filters = array('q' => '', 'status' => '', 'visibility' => '', 'album_id' => 0);
if ('works' === $tab)
{
  $query_text = isset($_GET['q']) ? gzca_clean_text($_GET['q'], 120) : '';
  $status_filter = isset($_GET['status']) && in_array($_GET['status'], array('online', 'offline'), true)
    ? $_GET['status']
    : '';
  $visibility_filter = isset($_GET['visibility']) && in_array($_GET['visibility'], array('public', 'private'), true)
    ? $_GET['visibility']
    : '';
  $album_filter = isset($_GET['album_id']) ? (int)$_GET['album_id'] : 0;
  $per_page_options = array(50, 100, 200);
  $page_size = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
  if (!in_array($page_size, $per_page_options, true))
  {
    $page_size = 50;
  }
  $filters = array('q' => $query_text, 'status' => $status_filter, 'visibility' => $visibility_filter, 'album_id' => $album_filter, 'per_page' => $page_size);
  $page_number = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
  $where = array('1=1');

  if ('' !== $query_text)
  {
    $like = gzca_sql_like_contains($query_text);
    $where[] = '(i.name LIKE \''.$like.'\' ESCAPE \'\\\\\' OR i.file LIKE \''.$like.'\' ESCAPE \'\\\\\' OR i.comment LIKE \''.$like.'\' ESCAPE \'\\\\\' OR gw.code LIKE \''.$like.'\' ESCAPE \'\\\\\' OR EXISTS (SELECT 1 FROM '.IMAGE_CATEGORY_TABLE.' AS search_ic INNER JOIN '.CATEGORIES_TABLE.' AS search_c ON search_c.id = search_ic.category_id WHERE search_ic.image_id = i.id AND search_c.name LIKE \''.$like.'\' ESCAPE \'\\\\\'))';
  }
  if ('online' === $status_filter)
  {
    $where[] = "COALESCE(gw.status, IF(i.level = 0, 'online', 'offline')) = 'online'";
  }
  elseif ('offline' === $status_filter)
  {
    $where[] = "COALESCE(gw.status, IF(i.level = 0, 'online', 'offline')) = 'offline'";
  }
  if ('public' === $visibility_filter)
  {
    $where[] = 'i.level = 0';
  }
  elseif ('private' === $visibility_filter)
  {
    $where[] = 'i.level > 0';
  }
  if ($album_filter > 0)
  {
    $where[] = 'EXISTS (SELECT 1 FROM '.IMAGE_CATEGORY_TABLE.' AS filter_ic INNER JOIN '.CATEGORIES_TABLE.' AS filter_c ON filter_c.id = filter_ic.category_id WHERE filter_ic.image_id = i.id AND FIND_IN_SET('.$album_filter.', filter_c.uppercats) > 0)';
  }
  $where_sql = implode(' AND ', $where);
  list($total, $total_filesize_kb) = pwg_db_fetch_row(pwg_query('
SELECT COUNT(*), COALESCE(SUM(COALESCE(i.filesize, 0)), 0)
  FROM '.IMAGES_TABLE.' AS i
    LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
  WHERE '.$where_sql.'
;'));
  $pages = max(1, (int)ceil((int)$total / $page_size));
  $page_number = min($page_number, $pages);
  $offset = ($page_number - 1) * $page_size;

  $rows = query2array('
SELECT
    i.*,
    gw.code AS gzca_code,
    gw.status AS gzca_status,
    gw.featured,
    gw.sort_order,
    gw.download_count,
    ic.category_id,
    c.name AS category_name,
    c.representative_picture_id AS category_cover_image_id
  FROM '.IMAGES_TABLE.' AS i
    LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
    LEFT JOIN '.IMAGE_CATEGORY_TABLE.' AS ic ON ic.image_id = i.id
      AND ic.category_id = (
        SELECT ic2.category_id
          FROM '.IMAGE_CATEGORY_TABLE.' AS ic2
            INNER JOIN '.CATEGORIES_TABLE.' AS c2 ON c2.id = ic2.category_id
          WHERE ic2.image_id = i.id
          ORDER BY LENGTH(c2.uppercats) DESC, c2.global_rank
          LIMIT 1
      )
    LEFT JOIN '.CATEGORIES_TABLE.' AS c ON c.id = ic.category_id
  WHERE '.$where_sql.'
  ORDER BY COALESCE(gw.sort_order, 0) ASC, i.id DESC
  LIMIT '.$offset.', '.$page_size.'
;');
  $works = array_map('gzca_prepare_work_row', $rows);

  $base_params = array('q' => $query_text, 'status' => $status_filter, 'visibility' => $visibility_filter, 'album_id' => $album_filter, 'per_page' => $page_size);
  $page_links = array();
  $last_link_page = 0;
  for ($link_page = 1; $link_page <= $pages; $link_page++)
  {
    $near_current = abs($link_page - $page_number) <= 2;
    $near_edge = $link_page <= 2 || $link_page > $pages - 2;
    if (!$near_current && !$near_edge)
    {
      continue;
    }
    if ($last_link_page > 0 && $link_page > $last_link_page + 1)
    {
      $page_links[] = array('page' => 0, 'label' => '...', 'url' => '', 'current' => false);
    }
    $page_links[] = array(
      'page' => $link_page,
      'label' => (string)$link_page,
      'url' => gzca_admin_url('works', array_merge($base_params, array('p' => $link_page))),
      'current' => $link_page === $page_number,
      );
    $last_link_page = $link_page;
  }
  $pager = array(
    'page' => $page_number,
    'pages' => $pages,
    'total' => (int)$total,
    'total_size_label' => (int)$total_filesize_kb > 0 ? gzca_format_bytes((int)$total_filesize_kb * 1024) : '0 KB',
    'per_page' => $page_size,
    'per_page_options' => $per_page_options,
    'first_url' => $page_number > 1 ? gzca_admin_url('works', array_merge($base_params, array('p' => 1))) : '',
    'previous_url' => $page_number > 1 ? gzca_admin_url('works', array_merge($base_params, array('p' => $page_number - 1))) : '',
    'next_url' => $page_number < $pages ? gzca_admin_url('works', array_merge($base_params, array('p' => $page_number + 1))) : '',
    'last_url' => $page_number < $pages ? gzca_admin_url('works', array_merge($base_params, array('p' => $pages))) : '',
    'links' => $page_links,
    );
}

$edit_work = null;
if ('works' === $tab && !empty($_GET['edit']))
{
  $edit_work = gzca_find_image((int)$_GET['edit']);
  if (is_array($edit_work))
  {
    $edit_work['category_links'] = gzca_image_category_links((int)$edit_work['id']);
    $edit_work['other_category_links'] = gzca_image_other_category_links((int)$edit_work['id'], (int)$edit_work['category_id']);
    $edit_work['code_prefix_warning'] = gzca_code_prefix_warning($edit_work['code'], (int)$edit_work['category_id']);
  }
}

$edit_category = null;
if ('categories' === $tab && !empty($_GET['edit']))
{
  foreach ($categories as $category)
  {
    if ((int)$category['id'] === (int)$_GET['edit'])
    {
      $category['description'] = gzca_strip_markers($category['comment']);
      $edit_category = $category;
      break;
    }
  }
}

$parent_categories = array_values(array_filter($categories, function ($category) {
  return empty($category['id_uppercat']);
}));
$category_groups = array();
foreach ($parent_categories as $parent_category)
{
  $parent_category['children'] = array_values(array_filter($categories, function ($category) use ($parent_category) {
    return (int)$category['id_uppercat'] === (int)$parent_category['id'];
  }));
  if ('exhibition' === $parent_category['category_kind'])
  {
    usort($parent_category['children'], 'gzca_compare_exhibition_category_options');
  }
  $category_groups[] = $parent_category;
}

$page['page'] = 'plugin';
$page['tab'] = $tab;
$template->assign(array(
  'ADMIN_PAGE_TITLE' => '客户专用后台',
  'GZCA_PATH' => GZCA_PATH,
  'GZCA_VERSION' => GZCA_VERSION,
  'GZCA_TAB' => $tab,
  'GZCA_TOKEN' => get_pwg_token(),
  'GZCA_IS_WEBMASTER' => is_webmaster(),
  'GZCA_IS_CUSTOMER_ADMIN' => gzca_is_customer_admin(),
  'GZCA_ADMIN_IDENTITY' => gzca_admin_identity(),
  'GZCA_EMAIL_SECURITY' => $email_security_view,
  'GZCA_SECURITY_MAIL' => $mail_status,
  'GZCA_SECURITY_FORM' => array('new_email' => $security_form_email),
  'GZCA_STATS' => $stats,
  'GZCA_DATABASE_HEALTH' => $database_health,
  'GZCA_SAMPLE_WORKS_STATUS' => $sample_works_status,
  'GZCA_RECENT_WORKS' => $recent_works,
  'GZCA_CATEGORIES' => $categories,
  'GZCA_CATEGORY_OPTIONS' => $category_options,
  'GZCA_CATEGORY_OPTION_GROUPS' => $category_option_groups,
  'GZCA_UPLOAD_TARGET_COUNT' => $upload_target_count,
  'GZCA_PARENT_CATEGORIES' => $parent_categories,
  'GZCA_CATEGORY_GROUPS' => $category_groups,
  'GZCA_WORKS' => $works,
  'GZCA_PAGER' => $pager,
  'GZCA_FILTERS' => $filters,
  'GZCA_EDIT_WORK' => $edit_work,
  'GZCA_EDIT_CATEGORY' => $edit_category,
  'GZCA_CONFIG' => gzca_config(),
  'GZCA_HERO_SLIDES' => gzca_admin_hero_slides(),
  'GZCA_CONTACTS' => gzca_normalize_contacts(),
  'GZCA_QR_URL' => gzca_contact_qr_url(),
  'GZCA_LOGO_URL' => gzca_logo_url(),
  'GZCA_URLS' => array(
    'dashboard' => gzca_admin_url('dashboard'),
    'home' => gzca_admin_url('home'),
    'upload' => gzca_admin_url('upload'),
    'works' => gzca_admin_url('works'),
    'categories' => gzca_admin_url('categories'),
    'contact' => gzca_admin_url('contact'),
    'security' => gzca_admin_url('security'),
    'gallery' => get_gallery_home_url(),
    'logout' => get_root_url().'index.php?act=logout',
    ),
  ));

$template->set_filenames(array(
  'gzca_admin_content' => realpath(GZCA_PATH.'template/admin.tpl'),
  ));
$template->assign_var_from_handle('ADMIN_CONTENT', 'gzca_admin_content');
