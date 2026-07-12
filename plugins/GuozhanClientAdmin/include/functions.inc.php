<?php

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

function gzca_default_config()
{
  return array(
    'brand_name' => '图物计划国展素材馆',
    'brand_en' => 'AI Graphics Learning Plan',
    'logo_path' => '',
    'wechat' => 'aiguozhanhuihua',
    'phone' => '',
    'contact_note' => '咨询高清图、同类作品或使用说明时，请发送作品编号。',
    'qr_path' => '',
    'restrict_administrators' => true,
    'frontend_sync' => true,
    );
}

function gzca_init()
{
  global $conf;

  load_language('plugin.lang', GZCA_PATH);

  if (empty($conf['gzca_config']))
  {
    $conf['gzca_config'] = gzca_default_config();
  }
  elseif (!is_array($conf['gzca_config']))
  {
    $conf['gzca_config'] = safe_unserialize($conf['gzca_config']);
  }

  $conf['gzca_config'] = array_merge(gzca_default_config(), $conf['gzca_config']);
}

function gzca_config()
{
  global $conf;

  if (!isset($conf['gzca_config']) || !is_array($conf['gzca_config']))
  {
    gzca_init();
  }

  return $conf['gzca_config'];
}

function gzca_admin_url($tab='dashboard', $params=array())
{
  $params = array_merge(array('page' => 'plugin-'.GZCA_ID, 'tab' => $tab), $params);
  return get_root_url().'admin.php?'.http_build_query($params, '', '&');
}

function gzca_database_table_exists($table_name)
{
  $pattern = str_replace(
    array('\\', '_', '%'),
    array('\\\\', '\\_', '\\%'),
    (string)$table_name
    );
  $result = pwg_query("SHOW TABLES LIKE '".pwg_db_real_escape_string($pattern)."';");
  return pwg_db_num_rows($result) > 0;
}

function gzca_database_health()
{
  global $conf, $prefixeTable;

  $connection_result = pwg_query('SELECT 1 AS gzca_database_connected;');
  $connection = pwg_db_num_rows($connection_result) > 0;
  $works_table = gzca_database_table_exists(GZCA_WORKS_TABLE);
  $categories_table = gzca_database_table_exists(GZCA_CATEGORIES_TABLE);
  $competition_media_table = gzca_database_table_exists(GZCA_COMPETITION_MEDIA_TABLE);
  $category_contract = false;
  $upload_contract = false;
  if ($categories_table)
  {
    $expected_keys = gzca_contract_category_keys();
    $escaped_keys = implode("','", array_map('pwg_db_real_escape_string', $expected_keys));
    list($contract_count) = pwg_db_fetch_row(pwg_query(
      'SELECT COUNT(*) FROM '.GZCA_CATEGORIES_TABLE.
      " WHERE catalog_enabled = 1 AND system_key IN ('".$escaped_keys."');"
      ));
    $category_contract = (int)$contract_count === count($expected_keys);
    list($upload_count) = pwg_db_fetch_row(pwg_query(
      'SELECT COUNT(*) FROM '.GZCA_CATEGORIES_TABLE.
      " WHERE catalog_enabled = 1 AND direct_upload = 1 AND system_key IN ('".$escaped_keys."');"
      ));
    $upload_contract = (int)$upload_count === count($expected_keys);
  }
  $web_services = !isset($conf['allow_web_services']) || !empty($conf['allow_web_services']);
  $frontend_sync = !empty(gzca_config()['frontend_sync']);
  $theme_active = !empty($conf['default_theme']) && 'guozhan-gallery' === $conf['default_theme'];

  return array(
    'connection' => $connection,
    'works_table' => $works_table,
    'categories_table' => $categories_table,
    'competition_media_table' => $competition_media_table,
    'category_contract' => $category_contract,
    'upload_contract' => $upload_contract,
    'web_services' => $web_services,
    'frontend_sync' => $frontend_sync,
    'theme_active' => $theme_active,
    'table_prefix' => $prefixeTable,
    'ready' => $connection && $works_table && $categories_table && $competition_media_table && $category_contract && $upload_contract && $web_services && $frontend_sync && $theme_active,
    );
}

function gzca_register_ws_methods($event)
{
  $service = &$event[0];
  $service->addMethod(
    'gzca.images.getList',
    'gzca_ws_images_get_list',
    array(
      'cat_id' => array('default' => null, 'type' => WS_TYPE_INT | WS_TYPE_POSITIVE),
      'recursive' => array('default' => true, 'type' => WS_TYPE_BOOL),
      'query' => array('default' => ''),
      'competition' => array('default' => ''),
      'sort' => array('default' => 'custom'),
      'per_page' => array('default' => 16, 'maxValue' => 20, 'type' => WS_TYPE_INT | WS_TYPE_POSITIVE),
      'page' => array('default' => 0, 'type' => WS_TYPE_INT | WS_TYPE_POSITIVE),
      ),
    'Returns public gallery works with customer-admin metadata and server pagination.',
    GZCA_PATH.'include/ws.inc.php'
    );
  $service->addMethod(
    'gzca.categories.getList',
    'gzca_ws_categories_get_list',
    array(),
    'Returns the editable public artwork catalogue, exhibition sections and visible children.',
    GZCA_PATH.'include/ws.inc.php'
    );
  $service->addMethod(
    'gzca.competition.getMedia',
    'gzca_ws_competition_get_media',
    array(),
    'Returns active competition media and public artwork counts.',
    GZCA_PATH.'include/ws.inc.php'
    );
  $service->addMethod(
    'gzca.images.getInfo',
    'gzca_ws_images_get_info',
    array('image_id' => array('type' => WS_TYPE_ID)),
    'Returns one public gallery work with code, category and display metadata.',
    GZCA_PATH.'include/ws.inc.php'
    );
  $service->addMethod(
    'gzca.contact.get',
    'gzca_ws_contact_get',
    array(),
    'Returns public brand and customer-service information.',
    GZCA_PATH.'include/ws.inc.php'
    );
}

function gzca_is_customer_admin()
{
  $config = gzca_config();
  return !empty($config['restrict_administrators']) && is_admin() && !is_webmaster();
}

function gzca_is_plugin_admin_request()
{
  $page = isset($_GET['page']) ? $_GET['page'] : '';
  $section = isset($_GET['section']) ? $_GET['section'] : '';

  return 'plugin-'.GZCA_ID === $page
    || ('plugin' === $page && GZCA_ID.'/admin.php' === $section);
}

function gzca_restrict_customer_admin()
{
  if (!is_admin())
  {
    return;
  }

  if (gzca_is_plugin_admin_request())
  {
    return;
  }

  redirect(gzca_admin_url('dashboard'));
}

function gzca_render_frontend_bridge()
{
  global $template;

  $config = gzca_config();
  if (empty($config['frontend_sync']))
  {
    return;
  }

  $contact = array(
    'brandName' => $config['brand_name'],
    'brandEn' => $config['brand_en'],
    'logoUrl' => gzca_logo_url($config),
    'wechat' => $config['wechat'],
    'phone' => $config['phone'],
    'note' => $config['contact_note'],
    'qrUrl' => gzca_contact_qr_url($config),
    );

  $template->assign(array(
    'GZCA_PATH' => GZCA_PATH,
    'GZCA_VERSION' => GZCA_VERSION,
    'GZCA_CONTACT_JSON' => json_encode(
      $contact,
      JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
      ),
    ));
  $template->set_filename('gzca_frontend_bridge', realpath(GZCA_PATH.'template/frontend.tpl'));
  $template->parse('gzca_frontend_bridge');
}

function gzca_contact_qr_url($config=null)
{
  if (null === $config)
  {
    $config = gzca_config();
  }

  if (empty($config['qr_path']))
  {
    return '';
  }

  return get_root_url().ltrim($config['qr_path'], '/');
}

function gzca_logo_url($config=null)
{
  if (null === $config)
  {
    $config = gzca_config();
  }

  if (empty($config['logo_path']))
  {
    return '';
  }

  return get_root_url().ltrim($config['logo_path'], '/');
}

function gzca_clean_text($value, $max_length=5000)
{
  $value = trim(strip_tags((string)$value));
  if (function_exists('mb_substr'))
  {
    return mb_substr($value, 0, $max_length, 'UTF-8');
  }
  return substr($value, 0, $max_length);
}

function gzca_normalize_code($value)
{
  return strtoupper(trim((string)$value));
}

function gzca_valid_code($value)
{
  return (bool)preg_match('/^[A-Z0-9]{2,12}(?:-[A-Z0-9]{1,12})*-\d{3,6}$/', gzca_normalize_code($value));
}

function gzca_valid_prefix($value)
{
  return (bool)preg_match('/^[A-Z0-9]{2,12}(?:-[A-Z0-9]{1,12})*$/', gzca_normalize_code($value));
}

function gzca_strip_markers($comment)
{
  $comment = preg_replace('/<!--\s*GZCA_(?:CODE|PREFIX|SAMPLE):.*?-->/is', '', (string)$comment);
  return trim($comment);
}

function gzca_embed_code($comment, $code)
{
  return '<!--GZCA_CODE:'.gzca_normalize_code($code).'-->'.gzca_strip_markers($comment);
}

function gzca_embed_sample_code($comment, $code, $sample_key)
{
  return '<!--GZCA_CODE:'.gzca_normalize_code($code).'--><!--GZCA_SAMPLE:'.htmlspecialchars($sample_key, ENT_QUOTES, 'UTF-8').'-->'.gzca_strip_markers($comment);
}

function gzca_embed_prefix($comment, $prefix)
{
  return '<!--GZCA_PREFIX:'.gzca_normalize_code($prefix).'-->'.gzca_strip_markers($comment);
}

function gzca_extract_code($comment, $fallback='')
{
  if (preg_match('/GZCA_CODE:([A-Z0-9-]+)/i', (string)$comment, $matches))
  {
    return strtoupper($matches[1]);
  }
  if (preg_match('/[A-Z]{2,}(?:-[A-Z0-9]+)+-\d{3,6}/i', (string)$comment, $matches))
  {
    return strtoupper($matches[0]);
  }
  return $fallback;
}

function gzca_category_exists($category_id)
{
  $category_id = (int)$category_id;
  if ($category_id < 1)
  {
    return false;
  }

  list($count) = pwg_db_fetch_row(pwg_query(
    'SELECT COUNT(*) FROM '.CATEGORIES_TABLE.' WHERE id = '.$category_id.';'
    ));
  return (int)$count > 0;
}

function gzca_get_categories($catalog_only=false, $public_only=false)
{
  if (!gzca_database_table_exists(GZCA_CATEGORIES_TABLE) || !gzca_database_table_exists(GZCA_WORKS_TABLE))
  {
    return array();
  }

  $query = '
SELECT
    c.id,
    c.id_uppercat,
    c.name,
    c.comment,
    c.uppercats,
    c.global_rank,
    c.`rank`,
    c.visible,
    c.status,
    c.representative_picture_id,
    (SELECT COUNT(*) FROM '.CATEGORIES_TABLE.' AS child_c WHERE child_c.id_uppercat = c.id) AS child_count,
    (SELECT COUNT(DISTINCT count_ic.image_id)
       FROM '.IMAGE_CATEGORY_TABLE.' AS count_ic
         INNER JOIN '.CATEGORIES_TABLE.' AS count_c ON count_c.id = count_ic.category_id
       WHERE FIND_IN_SET(c.id, count_c.uppercats) > 0) AS image_count,
    (SELECT COUNT(DISTINCT public_ic.image_id)
       FROM '.IMAGE_CATEGORY_TABLE.' AS public_ic
         INNER JOIN '.CATEGORIES_TABLE.' AS public_count_c ON public_count_c.id = public_ic.category_id
         INNER JOIN '.IMAGES_TABLE.' AS public_i ON public_i.id = public_ic.image_id AND public_i.level = 0
         LEFT JOIN '.GZCA_WORKS_TABLE.' AS public_gw ON public_gw.image_id = public_i.id
       WHERE FIND_IN_SET(c.id, public_count_c.uppercats) > 0
         AND public_count_c.visible = \'true\'
         AND public_count_c.status = \'public\'
         AND (public_gw.status IS NULL OR public_gw.status = \'online\')) AS public_image_count,
    COALESCE(gc.code_prefix, \'\') AS code_prefix,
    COALESCE(gc.sort_order, c.`rank`) AS custom_sort_order,
    COALESCE(gc.catalog_enabled, 0) AS catalog_enabled,
    COALESCE(gc.direct_upload, 0) AS direct_upload,
    COALESCE(gc.system_key, \'\') AS system_key,
    COALESCE(gc.category_kind, \'custom\') AS category_kind,
    COALESCE(gc.reserved, 0) AS reserved
  FROM '.CATEGORIES_TABLE.' AS c
    LEFT JOIN '.GZCA_CATEGORIES_TABLE.' AS gc ON gc.category_id = c.id
  WHERE 1=1
    '.($catalog_only ? 'AND COALESCE(gc.catalog_enabled, 0) = 1' : '').'
    '.($public_only ? "AND c.visible = \'true\' AND c.status = \'public\'
    AND NOT EXISTS (
      SELECT 1 FROM ".CATEGORIES_TABLE." AS hidden_c
      WHERE FIND_IN_SET(hidden_c.id, c.uppercats) > 0
        AND (hidden_c.visible <> \'true\' OR hidden_c.status <> \'public\')
    )" : '').'
  ORDER BY c.global_rank, c.`rank`, c.name
;';

  return query2array($query);
}

function gzca_category_options($categories)
{
  $categories_by_id = array();
  foreach ($categories as $category)
  {
    $categories_by_id[(int)$category['id']] = $category;
  }

  $options = array();
  foreach ($categories as $category)
  {
    if (empty($category['catalog_enabled']))
    {
      continue;
    }
    if (empty($category['direct_upload']))
    {
      continue;
    }

    $path_ids = array_values(array_filter(array_map('intval', explode(',', (string)$category['uppercats']))));
    if (empty($path_ids))
    {
      $path_ids[] = (int)$category['id'];
    }
    $path_names = array();
    foreach ($path_ids as $path_id)
    {
      if (isset($categories_by_id[$path_id]))
      {
        $path_names[] = $categories_by_id[$path_id]['name'];
      }
    }
    if (empty($path_names))
    {
      $path_names[] = $category['name'];
    }

    $root_id = (int)$path_ids[0];
    $root_category = isset($categories_by_id[$root_id]) ? $categories_by_id[$root_id] : $category;
    $is_top_level = empty($category['id_uppercat']);
    $options[] = array(
      'id' => (int)$category['id'],
      'name' => implode(' / ', $path_names),
      'raw_name' => $category['name'],
      'option_label' => $is_top_level ? $category['name'].'（直接上传）' : $category['name'],
      'prefix' => $category['code_prefix'],
      'visible' => 'true' === $category['visible'],
      'direct_upload' => !empty($category['direct_upload']),
      'group_id' => $root_id,
      'group_name' => $root_category['name'],
      'group_kind' => $root_category['category_kind'],
      'reserved' => !empty($category['reserved']),
      'is_top_level' => $is_top_level,
      );
  }
  return $options;
}

function gzca_category_option_groups($categories)
{
  $groups = array();
  $group_order = array();
  foreach (gzca_category_options($categories) as $option)
  {
    $group_id = (int)$option['group_id'];
    if (!isset($groups[$group_id]))
    {
      $groups[$group_id] = array(
        'id' => $group_id,
        'name' => $option['group_name'],
        'kind' => $option['group_kind'],
        'options' => array(),
        );
      $group_order[] = $group_id;
    }
    $groups[$group_id]['options'][] = $option;
  }

  $result = array();
  foreach ($group_order as $group_id)
  {
    $result[] = $groups[$group_id];
  }
  return $result;
}

function gzca_category_is_upload_target($category_id)
{
  $category_id = (int)$category_id;
  if ($category_id < 1)
  {
    return false;
  }

  list($count) = pwg_db_fetch_row(pwg_query('
SELECT COUNT(*)
  FROM '.CATEGORIES_TABLE.' AS c
    INNER JOIN '.GZCA_CATEGORIES_TABLE.' AS gc ON gc.category_id = c.id
  WHERE c.id = '.$category_id.'
    AND gc.catalog_enabled = 1
    AND gc.direct_upload = 1
;'));
  return (int)$count > 0;
}

function gzca_category_path($category_id)
{
  $category_id = (int)$category_id;
  $result = pwg_query('SELECT uppercats FROM '.CATEGORIES_TABLE.' WHERE id = '.$category_id.' LIMIT 1;');
  if (0 === pwg_db_num_rows($result))
  {
    return '';
  }

  $row = pwg_db_fetch_assoc($result);
  $ids = array_values(array_filter(array_map('intval', explode(',', (string)$row['uppercats']))));
  if (empty($ids))
  {
    return '';
  }

  $names = array();
  $name_rows = query2array(
    'SELECT id, name FROM '.CATEGORIES_TABLE.' WHERE id IN ('.implode(',', $ids).');',
    'id',
    'name'
    );
  foreach ($ids as $id)
  {
    if (isset($name_rows[$id]))
    {
      $names[] = $name_rows[$id];
    }
  }
  return implode(' / ', $names);
}

function gzca_category_prefix($category_id)
{
  $category_id = (int)$category_id;
  $query = '
SELECT code_prefix
  FROM '.GZCA_CATEGORIES_TABLE.'
  WHERE category_id = '.$category_id.'
;';
  $result = pwg_query($query);
  if (pwg_db_num_rows($result) > 0)
  {
    $row = pwg_db_fetch_assoc($result);
    if (gzca_valid_prefix($row['code_prefix']))
    {
      return strtoupper($row['code_prefix']);
    }
  }
  return 'GZ';
}

function gzca_category_prefix_exists($prefix, $exclude_category_id=0)
{
  $prefix = pwg_db_real_escape_string(gzca_normalize_code($prefix));
  list($count) = pwg_db_fetch_row(pwg_query(
    'SELECT COUNT(*) FROM '.GZCA_CATEGORIES_TABLE.
    " WHERE code_prefix = '".$prefix."'".
    ((int)$exclude_category_id > 0 ? ' AND category_id <> '.(int)$exclude_category_id : '').';'
    ));
  return (int)$count > 0;
}

function gzca_save_category_meta($category_id, $prefix, $sort_order=0, $catalog_enabled=1, $direct_upload=1, $system_key=null, $category_kind=null, $reserved=null)
{
  $category_id = (int)$category_id;
  $prefix = gzca_normalize_code($prefix);
  $exists = pwg_db_num_rows(pwg_query(
    'SELECT category_id FROM '.GZCA_CATEGORIES_TABLE.' WHERE category_id = '.$category_id.';'
    )) > 0;

  $data = array(
    'code_prefix' => $prefix,
    'sort_order' => (int)$sort_order,
    'catalog_enabled' => !empty($catalog_enabled) ? 1 : 0,
    'direct_upload' => !empty($direct_upload) ? 1 : 0,
    'updated_at' => date('Y-m-d H:i:s'),
    );

  if (null !== $system_key)
  {
    $data['system_key'] = '' === trim((string)$system_key) ? null : strtolower(trim((string)$system_key));
  }
  if (null !== $category_kind)
  {
    $data['category_kind'] = in_array($category_kind, array('catalog', 'exhibition', 'custom'), true)
      ? $category_kind
      : 'custom';
  }
  if (null !== $reserved)
  {
    $data['reserved'] = !empty($reserved) ? 1 : 0;
  }

  if ($exists)
  {
    single_update(GZCA_CATEGORIES_TABLE, $data, array('category_id' => $category_id));
  }
  else
  {
    $data['category_id'] = $category_id;
    single_insert(GZCA_CATEGORIES_TABLE, $data);
  }
}

function gzca_find_category_id($name, $parent_id=null)
{
  $name = pwg_db_real_escape_string($name);
  $query = '
SELECT id
  FROM '.CATEGORIES_TABLE.'
  WHERE name = \''.$name.'\'
    AND id_uppercat '.(empty($parent_id) ? 'IS NULL' : '= '.(int)$parent_id).'
  LIMIT 1
;';
  $result = pwg_query($query);
  if (pwg_db_num_rows($result) === 0)
  {
    return null;
  }
  $row = pwg_db_fetch_assoc($result);
  return (int)$row['id'];
}

function gzca_category_contract()
{
  return array(
    array('key' => 'ink', 'name' => '国画', 'prefix' => 'GH', 'description' => '山水画、人物画和花鸟画作品。', 'children' => array(
      array('key' => 'ink-landscape', 'name' => '山水画', 'prefix' => 'GH-SS'),
      array('key' => 'ink-figure', 'name' => '人物画', 'prefix' => 'GH-RW'),
      array('key' => 'ink-flower-bird', 'name' => '花鸟画', 'prefix' => 'GH-HN'),
      )),
    array('key' => 'oil', 'name' => '油画', 'prefix' => 'YH', 'description' => '人物画、风景画和静物画作品。', 'children' => array(
      array('key' => 'oil-figure', 'name' => '人物画', 'prefix' => 'YH-RW'),
      array('key' => 'oil-landscape', 'name' => '风景画', 'prefix' => 'YH-FJ'),
      array('key' => 'oil-still', 'name' => '静物画', 'prefix' => 'YH-JW'),
      )),
    array('key' => 'print', 'name' => '版画', 'prefix' => 'BH', 'description' => '黑白木刻、木刻水印和丝网版画作品。', 'children' => array(
      array('key' => 'print-black-woodcut', 'name' => '黑白木刻', 'prefix' => 'BH-HB'),
      array('key' => 'print-watermark', 'name' => '木刻水印', 'prefix' => 'BH-SY'),
      array('key' => 'print-screen', 'name' => '丝网版画', 'prefix' => 'BH-SW'),
      )),
    array('key' => 'sculpture', 'name' => '雕塑', 'prefix' => 'DS', 'description' => '圆雕和浮雕作品。', 'children' => array(
      array('key' => 'sculpture-round', 'name' => '圆雕', 'prefix' => 'DS-YD'),
      array('key' => 'sculpture-relief', 'name' => '浮雕', 'prefix' => 'DS-FD'),
      )),
    array('key' => 'lacquer', 'name' => '漆画', 'prefix' => 'QH', 'description' => '人物、风景和静物漆画作品。', 'children' => array(
      array('key' => 'lacquer-figure', 'name' => '人物', 'prefix' => 'QH-RW'),
      array('key' => 'lacquer-landscape', 'name' => '风景', 'prefix' => 'QH-FJ'),
      array('key' => 'lacquer-still', 'name' => '静物', 'prefix' => 'QH-JW'),
      )),
    array('key' => 'watercolor', 'name' => '水彩', 'prefix' => 'SC', 'description' => '人物、风景和静物水彩作品。', 'children' => array(
      array('key' => 'watercolor-figure', 'name' => '人物', 'prefix' => 'SC-RW'),
      array('key' => 'watercolor-landscape', 'name' => '风景', 'prefix' => 'SC-FJ'),
      array('key' => 'watercolor-still', 'name' => '静物', 'prefix' => 'SC-JW'),
      )),
    array('key' => 'folk-art', 'name' => '农民画', 'prefix' => 'NM-ZH', 'description' => '不分小板块，直接浏览农民画作品。', 'direct' => true, 'children' => array()),
    array('key' => 'comic', 'name' => '漫画', 'prefix' => 'MH-ZH', 'description' => '不分小板块，直接浏览漫画作品。', 'direct' => true, 'children' => array()),
    array('key' => 'illustration', 'name' => '插画', 'prefix' => 'CH', 'description' => '绘画风格插画和数字插画作品。', 'children' => array(
      array('key' => 'illustration-painted', 'name' => '绘画风格插画', 'prefix' => 'CH-HH'),
      array('key' => 'illustration-digital', 'name' => '数字插画', 'prefix' => 'CH-SZ'),
      )),
    array('key' => 'caa-exhibitions', 'name' => '中美协展览专项画稿', 'prefix' => 'ZX', 'kind' => 'exhibition', 'description' => '专项征稿与展览画稿入口，可持续新增和修改画展。', 'children' => array(
      array('key' => 'caa-plum-blossom', 'name' => '梅花之韵——2026·中国画花鸟作品展', 'prefix' => 'ZX-MH'),
      array('key' => 'caa-landscape-oil', 'name' => '山水滋美——2026风景油画展', 'prefix' => 'ZX-YS'),
      array('key' => 'caa-young-lacquer', 'name' => '第五届青年漆画展览征稿通知', 'prefix' => 'ZX-QH'),
      array('key' => 'caa-unknown-01', 'name' => '未知画展 01', 'prefix' => 'ZX-W1', 'reserved' => true),
      array('key' => 'caa-unknown-02', 'name' => '未知画展 02', 'prefix' => 'ZX-W2', 'reserved' => true),
      array('key' => 'caa-unknown-03', 'name' => '未知画展 03', 'prefix' => 'ZX-W3', 'reserved' => true),
      array('key' => 'caa-unknown-04', 'name' => '未知画展 04', 'prefix' => 'ZX-W4', 'reserved' => true),
      array('key' => 'caa-unknown-05', 'name' => '未知画展 05', 'prefix' => 'ZX-W5', 'reserved' => true),
      array('key' => 'caa-unknown-06', 'name' => '未知画展 06', 'prefix' => 'ZX-W6', 'reserved' => true),
      array('key' => 'caa-unknown-07', 'name' => '未知画展 07', 'prefix' => 'ZX-W7', 'reserved' => true),
      )),
    );
}

function gzca_sample_works_manifest()
{
  return array(
    array(
      'key' => 'ink-mountain',
      'file' => 'ink-mountain.jpg',
      'category_key' => 'ink-landscape',
      'title' => '国画山水样例',
      'description' => '本地生成样例作品，用于服务器首次部署后检查分类、编号和详情页效果。',
      'competition_slug' => 'ink',
      'featured' => 1,
      'sort_order' => 10,
      'download_count' => 86,
      ),
    array(
      'key' => 'ink-flower-bird',
      'file' => 'ink-flower-bird.jpg',
      'category_key' => 'ink-flower-bird',
      'title' => '国画花鸟样例',
      'description' => '本地生成样例作品，用于检查国画花鸟分类和水墨比赛筛选。',
      'competition_slug' => 'ink',
      'featured' => 1,
      'sort_order' => 20,
      'download_count' => 73,
      ),
    array(
      'key' => 'oil-landscape',
      'file' => 'oil-landscape.jpg',
      'category_key' => 'oil-landscape',
      'title' => '油画风景样例',
      'description' => '本地生成样例作品，用于检查油画分类、首页排序和作品详情。',
      'competition_slug' => 'oil',
      'featured' => 1,
      'sort_order' => 30,
      'download_count' => 92,
      ),
    array(
      'key' => 'oil-architecture',
      'file' => 'oil-architecture.jpg',
      'category_key' => 'oil-still',
      'title' => '油画静物样例',
      'description' => '本地生成样例作品，用于检查油画板块下的第二个样例作品。',
      'competition_slug' => 'oil',
      'featured' => 0,
      'sort_order' => 40,
      'download_count' => 61,
      ),
    array(
      'key' => 'print-woodcut',
      'file' => 'print-woodcut.jpg',
      'category_key' => 'print-black-woodcut',
      'title' => '版画木刻样例',
      'description' => '本地生成样例作品，用于检查版画分类和版画比赛筛选。',
      'competition_slug' => 'print',
      'featured' => 1,
      'sort_order' => 50,
      'download_count' => 80,
      ),
    array(
      'key' => 'watercolor-landscape',
      'file' => 'watercolor-landscape.jpg',
      'category_key' => 'watercolor-landscape',
      'title' => '水彩风景样例',
      'description' => '本地生成样例作品，用于检查水彩分类和水彩比赛筛选。',
      'competition_slug' => 'watercolor',
      'featured' => 1,
      'sort_order' => 60,
      'download_count' => 67,
      ),
    array(
      'key' => 'folk-festival',
      'file' => 'folk-festival.jpg',
      'category_key' => 'folk-art',
      'title' => '农民画节庆样例',
      'description' => '本地生成样例作品，用于检查农民画直达分类。',
      'competition_slug' => '',
      'featured' => 0,
      'sort_order' => 70,
      'download_count' => 54,
      ),
    array(
      'key' => 'mixed-material',
      'file' => 'mixed-material.jpg',
      'category_key' => 'illustration-painted',
      'title' => '绘画风格插画样例',
      'description' => '本地生成样例作品，用于检查插画板块和搜索结果页。',
      'competition_slug' => '',
      'featured' => 0,
      'sort_order' => 80,
      'download_count' => 48,
      ),
    );
}

function gzca_sample_work_source($sample)
{
  $path = PHPWG_ROOT_PATH.'themes/guozhan-gallery/assets/works/'.$sample['file'];
  return is_file($path) ? $path : '';
}

function gzca_category_id_by_system_key($system_key)
{
  $system_key = strtolower(trim((string)$system_key));
  if ('' === $system_key || !gzca_database_table_exists(GZCA_CATEGORIES_TABLE))
  {
    return 0;
  }
  $result = pwg_query(
    "SELECT category_id FROM ".GZCA_CATEGORIES_TABLE." WHERE system_key = '".pwg_db_real_escape_string($system_key)."' AND catalog_enabled = 1 LIMIT 1;"
    );
  if (pwg_db_num_rows($result) === 0)
  {
    return 0;
  }
  $row = pwg_db_fetch_assoc($result);
  return (int)$row['category_id'];
}

function gzca_competition_medium_id_by_slug($slug)
{
  $slug = strtolower(trim((string)$slug));
  if ('' === $slug || !gzca_database_table_exists(GZCA_COMPETITION_MEDIA_TABLE))
  {
    return 0;
  }
  $result = pwg_query(
    "SELECT id FROM ".GZCA_COMPETITION_MEDIA_TABLE." WHERE slug = '".pwg_db_real_escape_string($slug)."' AND status = 'active' LIMIT 1;"
    );
  if (pwg_db_num_rows($result) === 0)
  {
    return 0;
  }
  $row = pwg_db_fetch_assoc($result);
  return (int)$row['id'];
}

function gzca_sample_work_exists($sample_key)
{
  $sample_key = trim((string)$sample_key);
  if ('' === $sample_key)
  {
    return false;
  }
  $result = pwg_query(
    "SELECT id FROM ".IMAGES_TABLE." WHERE comment LIKE '%GZCA_SAMPLE:".pwg_db_real_escape_string($sample_key)."%' LIMIT 1;"
    );
  return pwg_db_num_rows($result) > 0;
}

function gzca_sample_works_status()
{
  $total = 0;
  $imported = 0;
  $missing_assets = 0;
  foreach (gzca_sample_works_manifest() as $sample)
  {
    $total++;
    if (gzca_sample_work_exists($sample['key']))
    {
      $imported++;
    }
    if ('' === gzca_sample_work_source($sample))
    {
      $missing_assets++;
    }
  }
  return array(
    'total' => $total,
    'imported' => $imported,
    'missing_assets' => $missing_assets,
    'remaining' => max(0, $total - $imported),
    );
}

function gzca_import_sample_works(&$errors=array())
{
  global $conf;

  gzca_seed_categories();
  include_once(PHPWG_ROOT_PATH.'admin/include/functions_upload.inc.php');

  $created = 0;
  $skipped = 0;
  $category_ids = array();
  foreach (gzca_sample_works_manifest() as $sample)
  {
    if (gzca_sample_work_exists($sample['key']))
    {
      $skipped++;
      continue;
    }

    $source = gzca_sample_work_source($sample);
    if ('' === $source)
    {
      $errors[] = '样例图片缺失：'.$sample['file'];
      continue;
    }

    $category_id = gzca_category_id_by_system_key($sample['category_key']);
    if ($category_id < 1 || !gzca_category_is_upload_target($category_id))
    {
      $errors[] = '样例作品“'.$sample['title'].'”找不到可上传分类：'.$sample['category_key'];
      continue;
    }

    $prefix = gzca_category_prefix($category_id);
    if (!gzca_valid_prefix($prefix))
    {
      $errors[] = '样例作品“'.$sample['title'].'”所在分类缺少有效编号前缀。';
      continue;
    }

    $tmp = tempnam(sys_get_temp_dir(), 'gzca_sample_');
    if (false === $tmp || !copy($source, $tmp))
    {
      if (is_string($tmp) && file_exists($tmp))
      {
        @unlink($tmp);
      }
      $errors[] = '样例作品“'.$sample['title'].'”复制到临时文件失败。';
      continue;
    }

    $image_id = add_uploaded_file($tmp, $sample['file'], array($category_id), 0);
    if (empty($image_id))
    {
      if (file_exists($tmp))
      {
        @unlink($tmp);
      }
      $errors[] = '样例作品“'.$sample['title'].'”写入图库失败。';
      continue;
    }

    $code = gzca_next_code($prefix);
    $competition_medium_id = gzca_competition_medium_id_by_slug($sample['competition_slug']);
    single_update(
      IMAGES_TABLE,
      array(
        'name' => $sample['title'],
        'comment' => gzca_embed_sample_code($sample['description'], $code, $sample['key']),
        'level' => 0,
        ),
      array('id' => (int)$image_id)
      );
    gzca_save_work_meta(
      $image_id,
      $code,
      'online',
      !empty($sample['featured']) ? 1 : 0,
      isset($sample['sort_order']) ? (int)$sample['sort_order'] : 0,
      isset($sample['download_count']) ? (int)$sample['download_count'] : 0,
      $competition_medium_id,
      isset($sample['sort_order']) ? (int)$sample['sort_order'] : 0
      );
    gzca_set_category_cover($category_id, $image_id);
    update_category($category_id);
    $category_ids[$category_id] = true;
    $created++;
  }

  if ($created > 0)
  {
    foreach (array_keys($category_ids) as $category_id)
    {
      update_category((int)$category_id);
    }
    invalidate_user_cache();
  }

  return array(
    'created' => $created,
    'skipped' => $skipped,
    'errors' => $errors,
    );
}

function gzca_contract_category_keys()
{
  $keys = array();
  foreach (gzca_category_contract() as $group)
  {
    $keys[] = $group['key'];
    foreach ($group['children'] as $child)
    {
      $keys[] = $child['key'];
    }
  }
  return $keys;
}

function gzca_find_category_by_system_key($system_key)
{
  $system_key = pwg_db_real_escape_string(strtolower(trim((string)$system_key)));
  $result = pwg_query(
    "SELECT category_id FROM ".GZCA_CATEGORIES_TABLE." WHERE system_key = '".$system_key."' LIMIT 1;"
    );
  if (0 === pwg_db_num_rows($result))
  {
    return null;
  }
  $row = pwg_db_fetch_assoc($result);
  return (int)$row['category_id'];
}

function gzca_find_category_by_prefix($prefix, $parent_id=null)
{
  $prefix = pwg_db_real_escape_string(gzca_normalize_code($prefix));
  $query = '
SELECT c.id
  FROM '.CATEGORIES_TABLE.' AS c
    INNER JOIN '.GZCA_CATEGORIES_TABLE.' AS gc ON gc.category_id = c.id
  WHERE gc.code_prefix = \''.$prefix.'\'
    AND c.id_uppercat '.(empty($parent_id) ? 'IS NULL' : '= '.(int)$parent_id).'
  LIMIT 1
;';
  $result = pwg_query($query);
  if (0 === pwg_db_num_rows($result))
  {
    return null;
  }
  $row = pwg_db_fetch_assoc($result);
  return (int)$row['id'];
}

function gzca_seed_category_node($item, $parent_id, $sort_order, $kind, &$created)
{
  $category_id = gzca_find_category_by_system_key($item['key']);
  $matched_by_key = null !== $category_id;
  if (null === $category_id)
  {
    $category_id = gzca_find_category_id($item['name'], $parent_id);
  }
  if (null === $category_id)
  {
    $category_id = gzca_find_category_by_prefix($item['prefix'], $parent_id);
  }

  $description = isset($item['description'])
    ? $item['description']
    : (!empty($item['reserved']) ? '预留画展，后续可修改名称、编号前缀并上传作品。' : $item['name'].'作品。');

  if (null === $category_id)
  {
    $result = create_virtual_category(
      $item['name'],
      $parent_id,
      array('visible' => true, 'comment' => $description, 'status' => 'public')
      );
    if (empty($result['id']))
    {
      return null;
    }
    $category_id = (int)$result['id'];
    $created++;
  }
  elseif (!$matched_by_key)
  {
    single_update(
      CATEGORIES_TABLE,
      array('name' => $item['name'], 'comment' => $description),
      array('id' => $category_id)
      );
  }

  if (!$matched_by_key)
  {
    gzca_save_category_meta(
      $category_id,
      $item['prefix'],
      $sort_order,
      1,
      true,
      $item['key'],
      $kind,
      !empty($item['reserved'])
      );
  }

  return $category_id;
}

function gzca_disable_legacy_category_contract()
{
  $legacy_prefixes = array(
    'GH-FJ', 'GH-JW',
    'BH-FJ', 'BH-RW', 'BH-JW',
    'DS-FJ', 'DS-RW', 'DS-JW',
    );
  $escaped = array_map(function ($prefix) {
    return "'".pwg_db_real_escape_string($prefix)."'";
  }, $legacy_prefixes);
  pwg_query(
    'UPDATE '.GZCA_CATEGORIES_TABLE.
    ' SET catalog_enabled = 0, updated_at = NOW()'.
    " WHERE (system_key IS NULL OR system_key = '') AND code_prefix IN (".implode(',', $escaped).');'
    );
}

function gzca_mark_contract_categories_uploadable()
{
  $keys = gzca_contract_category_keys();
  if (empty($keys))
  {
    return;
  }
  $escaped = array_map(function ($key) {
    return "'".pwg_db_real_escape_string($key)."'";
  }, $keys);
  pwg_query(
    'UPDATE '.GZCA_CATEGORIES_TABLE.
    ' SET catalog_enabled = 1, direct_upload = 1, updated_at = NOW()'.
    ' WHERE system_key IN ('.implode(',', $escaped).');'
    );
}

function gzca_category_is_contract_node($category_id)
{
  $category_id = (int)$category_id;
  if ($category_id < 1)
  {
    return false;
  }
  $keys = gzca_contract_category_keys();
  if (empty($keys))
  {
    return false;
  }
  $escaped = array_map(function ($key) {
    return "'".pwg_db_real_escape_string($key)."'";
  }, $keys);
  list($count) = pwg_db_fetch_row(pwg_query(
    'SELECT COUNT(*) FROM '.GZCA_CATEGORIES_TABLE.
    ' WHERE category_id = '.$category_id.' AND system_key IN ('.implode(',', $escaped).');'
    ));
  return (int)$count > 0;
}

function gzca_seed_categories()
{
  $created = 0;
  foreach (gzca_category_contract() as $group_index => $group)
  {
    $kind = isset($group['kind']) ? $group['kind'] : 'catalog';
    $parent_id = gzca_seed_category_node($group, null, $group_index + 1, $kind, $created);
    if (null === $parent_id)
    {
      continue;
    }

    foreach ($group['children'] as $child_index => $child)
    {
      $child['direct'] = true;
      gzca_seed_category_node($child, $parent_id, $child_index + 1, $kind, $created);
    }
  }

  gzca_disable_legacy_category_contract();
  gzca_mark_contract_categories_uploadable();

  update_global_rank();
  invalidate_user_cache();
  return $created;
}

function gzca_valid_competition_slug($value)
{
  return (bool)preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', strtolower(trim((string)$value)));
}

function gzca_get_competition_media($active_only=false)
{
  if (!gzca_database_table_exists(GZCA_COMPETITION_MEDIA_TABLE) || !gzca_database_table_exists(GZCA_WORKS_TABLE) || !gzca_database_table_exists(GZCA_CATEGORIES_TABLE))
  {
    return array();
  }

  $query = '
SELECT
    gm.*,
    COUNT(DISTINCT CASE
      WHEN i.id IS NOT NULL
        AND i.level = 0
        AND (gw.status IS NULL OR gw.status = \'online\')
        AND count_c.visible = \'true\'
        AND count_c.status = \'public\'
        AND count_gc.category_id IS NOT NULL
        AND NOT EXISTS (
          SELECT 1 FROM '.CATEGORIES_TABLE.' AS hidden_count_c
          WHERE FIND_IN_SET(hidden_count_c.id, count_c.uppercats) > 0
            AND (hidden_count_c.visible <> \'true\' OR hidden_count_c.status <> \'public\')
        )
      THEN i.id
      ELSE NULL
    END) AS work_count
  FROM '.GZCA_COMPETITION_MEDIA_TABLE.' AS gm
    LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.competition_medium_id = gm.id
    LEFT JOIN '.IMAGES_TABLE.' AS i ON i.id = gw.image_id
    LEFT JOIN '.IMAGE_CATEGORY_TABLE.' AS count_ic ON count_ic.image_id = i.id
    LEFT JOIN '.CATEGORIES_TABLE.' AS count_c ON count_c.id = count_ic.category_id
    LEFT JOIN '.GZCA_CATEGORIES_TABLE.' AS count_gc ON count_gc.catalog_enabled = 1 AND FIND_IN_SET(count_gc.category_id, count_c.uppercats) > 0
  '.($active_only ? "WHERE gm.status = 'active'" : '').'
  GROUP BY gm.id
  ORDER BY gm.sort_order, gm.id
;';
  return query2array($query);
}

function gzca_competition_medium_exists($medium_id, $active_only=false)
{
  $medium_id = (int)$medium_id;
  if ($medium_id < 1)
  {
    return false;
  }
  list($count) = pwg_db_fetch_row(pwg_query(
    'SELECT COUNT(*) FROM '.GZCA_COMPETITION_MEDIA_TABLE.' WHERE id = '.$medium_id.
    ($active_only ? " AND status = 'active'" : '').';'
    ));
  return (int)$count > 0;
}

function gzca_competition_slug_exists($slug, $exclude_id=0)
{
  $slug = pwg_db_real_escape_string(strtolower(trim((string)$slug)));
  list($count) = pwg_db_fetch_row(pwg_query(
    "SELECT COUNT(*) FROM ".GZCA_COMPETITION_MEDIA_TABLE." WHERE slug = '".$slug."'".
    ((int)$exclude_id > 0 ? ' AND id <> '.(int)$exclude_id : '').';'
    ));
  return (int)$count > 0;
}

function gzca_save_competition_medium($medium_id, $name, $slug, $description='', $sort_order=0, $status='active')
{
  $medium_id = (int)$medium_id;
  $data = array(
    'name' => gzca_clean_text($name, 80),
    'slug' => strtolower(trim((string)$slug)),
    'description' => gzca_clean_text($description, 500),
    'sort_order' => max(0, (int)$sort_order),
    'status' => 'inactive' === $status ? 'inactive' : 'active',
    'updated_at' => date('Y-m-d H:i:s'),
    );
  if ($medium_id > 0)
  {
    single_update(GZCA_COMPETITION_MEDIA_TABLE, $data, array('id' => $medium_id));
    return $medium_id;
  }
  single_insert(GZCA_COMPETITION_MEDIA_TABLE, $data);
  return pwg_db_insert_id();
}

function gzca_work_meta($image)
{
  $code = !empty($image['gzca_code'])
    ? $image['gzca_code']
    : gzca_extract_code(isset($image['comment']) ? $image['comment'] : '', 'ID-'.$image['id']);
  $status = !empty($image['gzca_status'])
    ? $image['gzca_status']
    : ((int)$image['level'] === 0 ? 'online' : 'offline');

  return array(
    'code' => $code,
    'status' => $status,
    'featured' => !empty($image['featured']),
    'sort_order' => isset($image['sort_order']) ? (int)$image['sort_order'] : 0,
    'download_count' => isset($image['download_count']) ? (int)$image['download_count'] : 0,
    'competition_medium_id' => isset($image['competition_medium_id']) ? (int)$image['competition_medium_id'] : 0,
    'competition_name' => isset($image['competition_name']) ? (string)$image['competition_name'] : '',
    'competition_slug' => isset($image['competition_slug']) ? (string)$image['competition_slug'] : '',
    'competition_sort_order' => isset($image['competition_sort_order']) ? (int)$image['competition_sort_order'] : 0,
    );
}

function gzca_code_exists($code, $exclude_image_id=0)
{
  $code = pwg_db_real_escape_string(gzca_normalize_code($code));
  $query = '
SELECT COUNT(*)
  FROM '.GZCA_WORKS_TABLE.'
  WHERE code = \''.$code.'\''.
  ((int)$exclude_image_id > 0 ? ' AND image_id <> '.(int)$exclude_image_id : '').'
;';
  list($count) = pwg_db_fetch_row(pwg_query($query));
  return (int)$count > 0;
}

function gzca_save_work_meta($image_id, $code, $status, $featured=0, $sort_order=0, $download_count=0, $competition_medium_id=0, $competition_sort_order=0)
{
  $image_id = (int)$image_id;
  $data = array(
    'code' => gzca_normalize_code($code),
    'status' => 'offline' === $status ? 'offline' : 'online',
    'featured' => !empty($featured) ? 1 : 0,
    'sort_order' => (int)$sort_order,
    'download_count' => max(0, (int)$download_count),
    'competition_medium_id' => (int)$competition_medium_id > 0 ? (int)$competition_medium_id : null,
    'competition_sort_order' => max(0, (int)$competition_sort_order),
    'updated_at' => date('Y-m-d H:i:s'),
    );

  $exists = pwg_db_num_rows(pwg_query(
    'SELECT image_id FROM '.GZCA_WORKS_TABLE.' WHERE image_id = '.$image_id.';'
    )) > 0;

  if ($exists)
  {
    single_update(GZCA_WORKS_TABLE, $data, array('image_id' => $image_id));
  }
  else
  {
    $data['image_id'] = $image_id;
    single_insert(GZCA_WORKS_TABLE, $data);
  }
}

function gzca_next_code($prefix)
{
  $prefix = gzca_normalize_code($prefix);
  if (!gzca_valid_prefix($prefix))
  {
    $prefix = 'GZ';
  }

  $escaped = pwg_db_real_escape_string($prefix.'-%');
  $rows = query2array(
    'SELECT code FROM '.GZCA_WORKS_TABLE.' WHERE code LIKE \''.$escaped.'\';'
    );
  $max = 0;
  foreach ($rows as $row)
  {
    if (preg_match('/^'.preg_quote($prefix, '/').'-(\d+)$/', $row['code'], $matches))
    {
      $max = max($max, (int)$matches[1]);
    }
  }

  return $prefix.'-'.str_pad((string)($max + 1), 3, '0', STR_PAD_LEFT);
}

function gzca_normalize_uploads($files)
{
  $normalized = array();
  if (empty($files) || !isset($files['name']))
  {
    return $normalized;
  }

  if (!is_array($files['name']))
  {
    $files = array(
      'name' => array($files['name']),
      'type' => array($files['type']),
      'tmp_name' => array($files['tmp_name']),
      'error' => array($files['error']),
      'size' => array($files['size']),
      );
  }

  foreach ($files['name'] as $index => $name)
  {
    $normalized[] = array(
      'name' => $name,
      'type' => isset($files['type'][$index]) ? $files['type'][$index] : '',
      'tmp_name' => isset($files['tmp_name'][$index]) ? $files['tmp_name'][$index] : '',
      'error' => isset($files['error'][$index]) ? (int)$files['error'][$index] : UPLOAD_ERR_NO_FILE,
      'size' => isset($files['size'][$index]) ? (int)$files['size'][$index] : 0,
      );
  }

  return $normalized;
}

function gzca_image_derivative_url($image, $type)
{
  try
  {
    $source = new SrcImage($image);
    return DerivativeImage::url($type, $source);
  }
  catch (Throwable $error)
  {
    return '';
  }
}

function gzca_image_thumb_url($image)
{
  return gzca_image_derivative_url($image, IMG_SQUARE);
}

function gzca_image_list_url($image)
{
  return gzca_image_derivative_url($image, IMG_XSMALL);
}

function gzca_prepare_work_row($row)
{
  $meta = gzca_work_meta($row);
  $row['code'] = $meta['code'];
  $row['status'] = $meta['status'];
  $row['featured'] = $meta['featured'];
  $row['sort_order'] = $meta['sort_order'];
  $row['download_count'] = $meta['download_count'];
  $row['competition_medium_id'] = $meta['competition_medium_id'];
  $row['competition_name'] = $meta['competition_name'];
  $row['competition_slug'] = $meta['competition_slug'];
  $row['competition_sort_order'] = $meta['competition_sort_order'];
  $row['in_competition'] = $meta['competition_medium_id'] > 0;
  $row['description'] = gzca_strip_markers(isset($row['comment']) ? $row['comment'] : '');
  $row['thumb_url'] = gzca_image_thumb_url($row);
  $row['display_name'] = !empty($row['name']) ? $row['name'] : pathinfo($row['file'], PATHINFO_FILENAME);
  $row['category_id'] = isset($row['category_id']) ? (int)$row['category_id'] : 0;
  $row['category_name'] = !empty($row['category_name']) ? $row['category_name'] : '未分类';
  return $row;
}

function gzca_set_category_cover($category_id, $image_id)
{
  $category_id = (int)$category_id;
  $image_id = (int)$image_id;

  $query = '
SELECT COUNT(*)
  FROM '.IMAGE_CATEGORY_TABLE.'
  WHERE category_id = '.$category_id.'
    AND image_id = '.$image_id.'
;';
  list($count) = pwg_db_fetch_row(pwg_query($query));
  if ((int)$count === 0)
  {
    return false;
  }

  single_update(
    CATEGORIES_TABLE,
    array('representative_picture_id' => $image_id),
    array('id' => $category_id)
    );

  if (defined('USER_CACHE_CATEGORIES_TABLE'))
  {
    pwg_query(
      'UPDATE '.USER_CACHE_CATEGORIES_TABLE.' SET user_representative_picture_id = NULL WHERE cat_id = '.$category_id.';'
      );
  }
  return true;
}

function gzca_find_image($image_id)
{
  $image_id = (int)$image_id;
  $query = '
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
  WHERE i.id = '.$image_id.'
;';
  $result = pwg_query($query);
  if (pwg_db_num_rows($result) === 0)
  {
    return null;
  }
  return gzca_prepare_work_row(pwg_db_fetch_assoc($result));
}

function gzca_save_contact_qr($file, &$error='')
{
  if (empty($file) || UPLOAD_ERR_NO_FILE === (int)$file['error'])
  {
    return null;
  }
  if (UPLOAD_ERR_OK !== (int)$file['error'])
  {
    $error = '二维码上传失败，请重新选择图片。';
    return false;
  }
  if ((int)$file['size'] > 2 * 1024 * 1024)
  {
    $error = '二维码图片不能超过 2MB。';
    return false;
  }

  $allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
  if (!function_exists('finfo_open'))
  {
    $error = '服务器缺少 PHP fileinfo 扩展，暂时无法校验二维码图片类型。';
    return false;
  }
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  if (!isset($allowed[$mime]))
  {
    $error = '二维码仅支持 JPG、PNG 或 WebP。';
    return false;
  }

  global $conf;
  $directory = PHPWG_ROOT_PATH.$conf['data_location'].'guozhan-client-admin';
  if (!is_dir($directory) && !mkdir($directory, 0755, true))
  {
    $error = '无法创建二维码保存目录，请检查 _data 目录权限。';
    return false;
  }

  $relative = $conf['data_location'].'guozhan-client-admin/contact-qr.'.$allowed[$mime];
  $destination = PHPWG_ROOT_PATH.$relative;
  foreach (glob($directory.'/contact-qr.*') as $old_file)
  {
    @unlink($old_file);
  }

  if (!move_uploaded_file($file['tmp_name'], $destination))
  {
    $error = '二维码保存失败，请检查目录写入权限。';
    return false;
  }
  @chmod($destination, 0644);

  return $relative;
}

function gzca_save_logo($file, &$error='')
{
  if (empty($file) || UPLOAD_ERR_NO_FILE === (int)$file['error'])
  {
    return null;
  }
  if (UPLOAD_ERR_OK !== (int)$file['error'])
  {
    $error = 'Logo 上传失败，请重新选择图片。';
    return false;
  }
  if ((int)$file['size'] > 4 * 1024 * 1024)
  {
    $error = 'Logo 图片不能超过 4MB。';
    return false;
  }

  $allowed = array('image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp');
  if (!function_exists('finfo_open'))
  {
    $error = '服务器缺少 PHP fileinfo 扩展，暂时无法校验 Logo 图片类型。';
    return false;
  }
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $file['tmp_name']);
  finfo_close($finfo);
  if (!isset($allowed[$mime]))
  {
    $error = 'Logo 仅支持 JPG、PNG 或 WebP。';
    return false;
  }

  global $conf;
  $directory = PHPWG_ROOT_PATH.$conf['data_location'].'guozhan-client-admin';
  if (!is_dir($directory) && !mkdir($directory, 0755, true))
  {
    $error = '无法创建品牌图片保存目录，请检查 _data 目录权限。';
    return false;
  }

  $relative = $conf['data_location'].'guozhan-client-admin/brand-logo.'.$allowed[$mime];
  $destination = PHPWG_ROOT_PATH.$relative;
  foreach (glob($directory.'/brand-logo.*') as $old_file)
  {
    @unlink($old_file);
  }

  if (!move_uploaded_file($file['tmp_name'], $destination))
  {
    $error = 'Logo 保存失败，请检查目录写入权限。';
    return false;
  }
  @chmod($destination, 0644);

  return $relative;
}
