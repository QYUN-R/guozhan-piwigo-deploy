<?php

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

check_status(ACCESS_ADMINISTRATOR);
include_once(PHPWG_ROOT_PATH.'admin/include/functions.php');

global $conf, $page, $template, $user;

$allowed_tabs = array('dashboard', 'upload', 'works', 'categories', 'competition', 'contact');
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
    $page['infos'][] = '已导入 '.$result['created'].' 张本地样例作品，可直接在前台检查作品列表、详情和比赛筛选。';
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
  $prefix = gzca_category_prefix($album_id);
  $publish_now = isset($_POST['publish_now']);
  $set_cover = isset($_POST['set_cover']);
  $competition_medium_id = isset($_POST['competition_medium_id']) ? (int)$_POST['competition_medium_id'] : 0;
  $uploads = gzca_normalize_uploads(isset($_FILES['artworks']) ? $_FILES['artworks'] : array());

  if (!gzca_category_is_upload_target($album_id))
  {
    $page['errors'][] = '请选择可以直接上传作品的前台板块、分类或画展。';
  }
  elseif (!gzca_valid_prefix($prefix))
  {
    $page['errors'][] = '所选分类尚未设置有效编号前缀，请先到分类管理中完善。';
  }
  elseif ($competition_medium_id > 0 && !gzca_competition_medium_exists($competition_medium_id, true))
  {
    $page['errors'][] = '所选比赛类型不存在或已停用。';
  }
  elseif (empty($uploads))
  {
    $page['errors'][] = '请至少选择一张作品图片。';
  }
  elseif (count($uploads) > 20)
  {
    $page['errors'][] = '一次最多上传 20 张图片，请分批上传。当前选择了 '.count($uploads).' 张。';
  }
  else
  {
    include_once(PHPWG_ROOT_PATH.'admin/include/functions_upload.inc.php');

    $uploaded_ids = array();
    $uploaded_codes = array();
    foreach ($uploads as $file)
    {
      if (UPLOAD_ERR_OK !== $file['error'])
      {
        $page['errors'][] = '文件“'.htmlspecialchars($file['name']).'”上传失败，错误代码 '.$file['error'].'。';
        continue;
      }

      $extension = strtolower(get_extension($file['name']));
      if (!in_array($extension, $conf['picture_ext'], true) || false === @getimagesize($file['tmp_name']))
      {
        $page['errors'][] = '文件“'.htmlspecialchars($file['name']).'”不是受支持的图片。';
        continue;
      }

      $image_id = add_uploaded_file(
        $file['tmp_name'],
        $file['name'],
        array($album_id),
        $publish_now ? 0 : 8
        );

      if (empty($image_id))
      {
        $page['errors'][] = '文件“'.htmlspecialchars($file['name']).'”未能写入图库。';
        continue;
      }

      $existing_meta = pwg_db_num_rows(pwg_query(
        'SELECT image_id FROM '.GZCA_WORKS_TABLE.' WHERE image_id = '.(int)$image_id.';'
        )) > 0;
      if (!$existing_meta)
      {
        $code = gzca_next_code($prefix);
        $title = gzca_clean_text(pathinfo($file['name'], PATHINFO_FILENAME), 255);
        single_update(
          IMAGES_TABLE,
          array(
            'name' => $title,
            'comment' => gzca_embed_code('', $code),
            'level' => $publish_now ? 0 : 8,
            ),
          array('id' => (int)$image_id)
        );
        gzca_save_work_meta($image_id, $code, $publish_now ? 'online' : 'offline', 0, 0, 0, $competition_medium_id);
        $uploaded_codes[] = $code;
      }

      $uploaded_ids[] = (int)$image_id;
    }

    if (!empty($uploaded_ids))
    {
      if ($set_cover)
      {
        gzca_set_category_cover($album_id, $uploaded_ids[0]);
      }
      update_category($album_id);
      invalidate_user_cache();
      $category_path = htmlspecialchars(gzca_category_path($album_id), ENT_QUOTES, 'UTF-8');
      $code_summary = empty($uploaded_codes)
        ? ''
        : '，编号 '.reset($uploaded_codes).(count($uploaded_codes) > 1 ? ' 至 '.end($uploaded_codes) : '');
      $page['infos'][] = '已上传 '.count($uploaded_ids).' 张作品到“'.$category_path.'”'.$code_summary.'。';
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
  $featured = isset($_POST['featured']) ? 1 : 0;
  $sort_order = isset($_POST['sort_order']) ? (int)$_POST['sort_order'] : 0;
  $download_count = isset($_POST['download_count']) ? max(0, (int)$_POST['download_count']) : 0;
  $competition_medium_id = isset($_POST['competition_medium_id']) ? (int)$_POST['competition_medium_id'] : 0;
  $competition_sort_order = isset($_POST['competition_sort_order']) ? max(0, (int)$_POST['competition_sort_order']) : 0;

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
  elseif ($competition_medium_id > 0 && !gzca_competition_medium_exists($competition_medium_id))
  {
    $page['errors'][] = '所选比赛类型不存在。';
  }
  else
  {
    single_update(
      IMAGES_TABLE,
      array(
        'name' => $title,
        'comment' => gzca_embed_code($description, $code),
        'level' => 'online' === $status ? 0 : 8,
        ),
      array('id' => $image_id)
      );

    move_images_to_categories(array($image_id), array($album_id));
    gzca_save_work_meta($image_id, $code, $status, $featured, $sort_order, $download_count, $competition_medium_id, $competition_sort_order);

    if (isset($_POST['set_cover']))
    {
      gzca_set_category_cover($album_id, $image_id);
    }

    update_category($album_id);
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
    gzca_save_work_meta($image_id, $work['code'], $status, $work['featured'], $work['sort_order'], $work['download_count'], $work['competition_medium_id'], $work['competition_sort_order']);
    invalidate_user_cache();
    $page['infos'][] = '作品已'.('online' === $status ? '上架' : '下架').'。';
  }
}

if ('set_cover' === $action)
{
  $image_id = isset($_POST['image_id']) ? (int)$_POST['image_id'] : 0;
  $album_id = isset($_POST['album_id']) ? (int)$_POST['album_id'] : 0;
  if (gzca_set_category_cover($album_id, $image_id))
  {
    invalidate_user_cache();
    $page['infos'][] = '分类封面已更新。';
  }
  else
  {
    $page['errors'][] = '设置封面失败，该作品不属于所选分类。';
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

if ('save_competition_medium' === $action)
{
  $medium_id = isset($_POST['medium_id']) ? (int)$_POST['medium_id'] : 0;
  $name = gzca_clean_text(isset($_POST['name']) ? $_POST['name'] : '', 80);
  $slug = strtolower(trim(isset($_POST['slug']) ? (string)$_POST['slug'] : ''));
  $description = gzca_clean_text(isset($_POST['description']) ? $_POST['description'] : '', 500);
  $sort_order = isset($_POST['sort_order']) ? max(0, (int)$_POST['sort_order']) : 0;
  $status = isset($_POST['status']) && 'inactive' === $_POST['status'] ? 'inactive' : 'active';

  if ('' === $name)
  {
    $page['errors'][] = '比赛类型名称不能为空。';
  }
  elseif (!gzca_valid_competition_slug($slug))
  {
    $page['errors'][] = '比赛标识只能使用小写字母、数字和短横线，例如 ink、watercolor。';
  }
  elseif ($medium_id > 0 && !gzca_competition_medium_exists($medium_id))
  {
    $page['errors'][] = '比赛类型不存在或已被删除。';
  }
  elseif (gzca_competition_slug_exists($slug, $medium_id))
  {
    $page['errors'][] = '比赛标识“'.$slug.'”已存在。';
  }
  else
  {
    gzca_save_competition_medium($medium_id, $name, $slug, $description, $sort_order, $status);
    $page['infos'][] = '比赛类型“'.$name.'”已保存。';
  }
}

if ('save_contact' === $action)
{
  $config = gzca_config();
  $config['brand_name'] = gzca_clean_text(isset($_POST['brand_name']) ? $_POST['brand_name'] : '', 120);
  $config['brand_en'] = gzca_clean_text(isset($_POST['brand_en']) ? $_POST['brand_en'] : '', 160);
  $config['wechat'] = gzca_clean_text(isset($_POST['wechat']) ? $_POST['wechat'] : '', 120);
  $config['phone'] = gzca_clean_text(isset($_POST['phone']) ? $_POST['phone'] : '', 80);
  $config['contact_note'] = gzca_clean_text(isset($_POST['contact_note']) ? $_POST['contact_note'] : '', 1000);
  $config['frontend_sync'] = isset($_POST['frontend_sync']);
  if (is_webmaster())
  {
    $config['restrict_administrators'] = isset($_POST['restrict_administrators']);
  }

  $qr_error = '';
  $qr_path = gzca_save_contact_qr(isset($_FILES['contact_qr']) ? $_FILES['contact_qr'] : array(), $qr_error);
  $logo_error = '';
  $logo_path = gzca_save_logo(isset($_FILES['brand_logo']) ? $_FILES['brand_logo'] : array(), $logo_error);
  if (false === $qr_path || false === $logo_path)
  {
    if (false === $qr_path)
    {
      $page['errors'][] = $qr_error;
    }
    if (false === $logo_path)
    {
      $page['errors'][] = $logo_error;
    }
  }
  else
  {
    if (is_string($qr_path) && '' !== $qr_path)
    {
      $config['qr_path'] = $qr_path;
    }
    if (is_string($logo_path) && '' !== $logo_path)
    {
      $config['logo_path'] = $logo_path;
    }
    conf_update_param('gzca_config', $config, true, 'serialize');
    $conf['gzca_config'] = $config;
    $page['infos'][] = '客服信息已保存，并会同步到前台客服入口。';
  }
}

$categories = gzca_get_categories(true);
$category_options = gzca_category_options($categories);
$category_option_groups = gzca_category_option_groups($categories);
$upload_target_count = count($category_options);
$competition_media = gzca_get_competition_media(false);
$active_competition_media = array_values(array_filter($competition_media, function ($item) {
  return 'active' === $item['status'];
}));
$database_health = gzca_database_health();
$sample_works_status = gzca_sample_works_status();

$stats = array();
list($stats['works']) = pwg_db_fetch_row(pwg_query('SELECT COUNT(*) FROM '.IMAGES_TABLE.';'));
list($stats['online']) = pwg_db_fetch_row(pwg_query('SELECT COUNT(*) FROM '.IMAGES_TABLE.' WHERE level = 0;'));
list($stats['offline']) = pwg_db_fetch_row(pwg_query('SELECT COUNT(*) FROM '.IMAGES_TABLE.' WHERE level > 0;'));
$stats['categories'] = count(array_filter($categories, function ($category) {
  return empty($category['id_uppercat']);
}));
$stats['competition'] = 0;
if (!empty($database_health['works_table']))
{
  list($stats['competition']) = pwg_db_fetch_row(pwg_query('SELECT COUNT(*) FROM '.GZCA_WORKS_TABLE.' WHERE competition_medium_id IS NOT NULL;'));
}

$recent_rows = !empty($database_health['works_table']) ? query2array('
SELECT
    i.*,
    gw.code AS gzca_code,
    gw.status AS gzca_status,
    gw.featured,
    gw.sort_order,
    gw.download_count,
    gw.competition_medium_id,
    gw.competition_sort_order,
    gm.name AS competition_name,
    gm.slug AS competition_slug,
    ic.category_id,
    c.name AS category_name
  FROM '.IMAGES_TABLE.' AS i
    LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
    LEFT JOIN '.GZCA_COMPETITION_MEDIA_TABLE.' AS gm ON gm.id = gw.competition_medium_id
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
$filters = array('q' => '', 'status' => '', 'album_id' => 0, 'competition_medium_id' => 0);
if ('works' === $tab)
{
  $query_text = isset($_GET['q']) ? gzca_clean_text($_GET['q'], 120) : '';
  $status_filter = isset($_GET['status']) && in_array($_GET['status'], array('online', 'offline'), true)
    ? $_GET['status']
    : '';
  $album_filter = isset($_GET['album_id']) ? (int)$_GET['album_id'] : 0;
  $competition_filter = isset($_GET['competition_medium_id']) ? (int)$_GET['competition_medium_id'] : 0;
  $filters = array('q' => $query_text, 'status' => $status_filter, 'album_id' => $album_filter, 'competition_medium_id' => $competition_filter);
  $page_number = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
  $page_size = 20;
  $where = array('1=1');

  if ('' !== $query_text)
  {
    $like = pwg_db_real_escape_string('%'.$query_text.'%');
    $where[] = '(i.name LIKE \''.$like.'\' OR i.file LIKE \''.$like.'\' OR i.comment LIKE \''.$like.'\' OR gw.code LIKE \''.$like.'\' OR gm.name LIKE \''.$like.'\')';
  }
  if ('online' === $status_filter)
  {
    $where[] = 'i.level = 0';
  }
  elseif ('offline' === $status_filter)
  {
    $where[] = 'i.level > 0';
  }
  if ($album_filter > 0)
  {
    $where[] = 'EXISTS (SELECT 1 FROM '.IMAGE_CATEGORY_TABLE.' AS filter_ic WHERE filter_ic.image_id = i.id AND filter_ic.category_id = '.$album_filter.')';
  }
  if ($competition_filter > 0)
  {
    $where[] = 'gw.competition_medium_id = '.$competition_filter;
  }

  $where_sql = implode(' AND ', $where);
  list($total) = pwg_db_fetch_row(pwg_query('
SELECT COUNT(*)
  FROM '.IMAGES_TABLE.' AS i
    LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
    LEFT JOIN '.GZCA_COMPETITION_MEDIA_TABLE.' AS gm ON gm.id = gw.competition_medium_id
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
    gw.competition_medium_id,
    gw.competition_sort_order,
    gm.name AS competition_name,
    gm.slug AS competition_slug,
    ic.category_id,
    c.name AS category_name
  FROM '.IMAGES_TABLE.' AS i
    LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
    LEFT JOIN '.GZCA_COMPETITION_MEDIA_TABLE.' AS gm ON gm.id = gw.competition_medium_id
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

  $base_params = array('q' => $query_text, 'status' => $status_filter, 'album_id' => $album_filter, 'competition_medium_id' => $competition_filter);
  $pager = array(
    'page' => $page_number,
    'pages' => $pages,
    'total' => (int)$total,
    'previous_url' => $page_number > 1 ? gzca_admin_url('works', array_merge($base_params, array('p' => $page_number - 1))) : '',
    'next_url' => $page_number < $pages ? gzca_admin_url('works', array_merge($base_params, array('p' => $page_number + 1))) : '',
    );
}

$edit_work = null;
if ('works' === $tab && !empty($_GET['edit']))
{
  $edit_work = gzca_find_image((int)$_GET['edit']);
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

$edit_competition_medium = null;
if ('competition' === $tab && !empty($_GET['edit']))
{
  foreach ($competition_media as $medium)
  {
    if ((int)$medium['id'] === (int)$_GET['edit'])
    {
      $edit_competition_medium = $medium;
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
  'GZCA_COMPETITION_MEDIA' => $competition_media,
  'GZCA_ACTIVE_COMPETITION_MEDIA' => $active_competition_media,
  'GZCA_WORKS' => $works,
  'GZCA_PAGER' => $pager,
  'GZCA_FILTERS' => $filters,
  'GZCA_EDIT_WORK' => $edit_work,
  'GZCA_EDIT_CATEGORY' => $edit_category,
  'GZCA_EDIT_COMPETITION_MEDIUM' => $edit_competition_medium,
  'GZCA_CONFIG' => gzca_config(),
  'GZCA_QR_URL' => gzca_contact_qr_url(),
  'GZCA_LOGO_URL' => gzca_logo_url(),
  'GZCA_URLS' => array(
    'dashboard' => gzca_admin_url('dashboard'),
    'upload' => gzca_admin_url('upload'),
    'works' => gzca_admin_url('works'),
    'categories' => gzca_admin_url('categories'),
    'competition' => gzca_admin_url('competition'),
    'contact' => gzca_admin_url('contact'),
    'gallery' => get_gallery_home_url(),
    ),
  ));

$template->set_filenames(array(
  'gzca_admin_content' => realpath(GZCA_PATH.'template/admin.tpl'),
  ));
$template->assign_var_from_handle('ADMIN_CONTENT', 'gzca_admin_content');
