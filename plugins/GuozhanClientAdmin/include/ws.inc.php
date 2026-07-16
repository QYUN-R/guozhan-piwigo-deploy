<?php

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

include_once(PHPWG_ROOT_PATH.'include/ws_functions.inc.php');

function gzca_frontend_url($page, $params=array())
{
  $query = array_merge(array('gz_page' => $page), $params);
  return get_root_url().'?'.http_build_query($query, '', '&');
}

function gzca_ws_images_get_list($params, &$service)
{
  return gzca_ws_query_images($params);
}

function gzca_ws_images_get_info($params, &$service)
{
  $query_params = array(
    'cat_id' => null,
    'recursive' => true,
    'query' => '',
    'sort' => 'custom',
    'per_page' => 1,
    'page' => 0,
    );
  $result = gzca_ws_query_images($query_params, (int)$params['image_id'], true);
  if (empty($result['images']))
  {
    return new PwgError(404, 'Artwork not found or unavailable');
  }
  return $result['images'][0];
}

function gzca_ws_categories_get_list($params, &$service)
{
  $items = array();
  foreach (gzca_get_categories(true, true) as $category)
  {
    $thumbnail_url = '';
    if (!empty($category['representative_picture_id']))
    {
      $result = pwg_query('
SELECT i.*
  FROM '.IMAGES_TABLE.' AS i
    INNER JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
    INNER JOIN '.IMAGE_CATEGORY_TABLE.' AS cover_ic
      ON cover_ic.image_id = i.id
     AND cover_ic.category_id = '.(int)$category['id'].'
    INNER JOIN '.CATEGORIES_TABLE.' AS cover_c
      ON cover_c.id = cover_ic.category_id
  WHERE i.id = '.(int)$category['representative_picture_id'].'
    AND i.level = 0
    AND gw.status = \'online\'
    AND cover_c.visible = \'true\'
    AND cover_c.status = \'public\'
    AND NOT EXISTS (
      SELECT 1 FROM '.CATEGORIES_TABLE.' AS hidden_c
      WHERE FIND_IN_SET(hidden_c.id, cover_c.uppercats) > 0
        AND (hidden_c.visible <> \'true\' OR hidden_c.status <> \'public\')
    )
  LIMIT 1
;');
      if (pwg_db_num_rows($result) > 0)
      {
        $thumbnail_url = gzca_image_list_url(pwg_db_fetch_assoc($result));
      }
    }
    $items[] = array(
      'id' => (int)$category['id'],
      'id_uppercat' => empty($category['id_uppercat']) ? null : (int)$category['id_uppercat'],
      'name' => $category['name'],
      'comment' => trigger_change('render_category_description', $category['comment'], 'gzca_ws_categories_get_list'),
      'uppercats' => $category['uppercats'],
      'rank' => (int)$category['custom_sort_order'],
      'key' => $category['system_key'],
      'kind' => $category['category_kind'],
      'reserved' => !empty($category['reserved']),
      'code_prefix' => $category['code_prefix'],
      'direct_upload' => !empty($category['direct_upload']),
      'nb_images' => (int)$category['public_image_count'],
      'total_nb_images' => (int)$category['public_image_count'],
      'tn_url' => $thumbnail_url,
      'url' => gzca_frontend_url('category', array('cat_id' => (int)$category['id'])),
      );
  }
  return array('categories' => $items);
}

function gzca_ws_contact_get($params, &$service)
{
  $config = gzca_config();
  $contacts = gzca_frontend_contacts($config);
  $primary_contact = !empty($contacts) ? $contacts[0] : array('wechat' => '', 'note' => '', 'qrUrl' => '');
  return array(
    'brandName' => $config['brand_name'],
    'brandEn' => $config['brand_en'],
    'logoUrl' => gzca_logo_url($config),
    'wechat' => $primary_contact['wechat'],
    'phone' => $config['phone'],
    'note' => $primary_contact['note'],
    'qrUrl' => $primary_contact['qrUrl'],
    'contacts' => $contacts,
    'sendContent' => $config['contact_send_content'],
    'courseLearning' => $config['contact_course_learning'],
    'consultationTip' => $config['contact_consultation_tip'],
    );
}

function gzca_ws_home_get($params, &$service)
{
  return array(
    'heroSlides' => gzca_frontend_hero_slides(),
    );
}

function gzca_ws_query_images($params, $image_id=0, $include_detail=false)
{
  $per_page = max(1, min(20, (int)$params['per_page']));
  $page_number = max(0, (int)$params['page']);
  $cat_id = empty($params['cat_id']) ? 0 : (int)$params['cat_id'];
  $recursive = !isset($params['recursive']) || (bool)$params['recursive'];
  $search = gzca_clean_text(isset($params['query']) ? $params['query'] : '', 120);
  $sort = strtolower(trim((string)$params['sort']));

  $where = array(
    get_sql_condition_FandF(array('visible_images' => 'i.id'), null, true),
    get_sql_condition_FandF(array('forbidden_categories' => 'ic.category_id'), null, true),
    "c.visible = 'true'",
    "public_c.visible = 'true'",
    "public_c.status = 'public'",
    "NOT EXISTS (SELECT 1 FROM ".CATEGORIES_TABLE." AS hidden_c WHERE FIND_IN_SET(hidden_c.id, c.uppercats) > 0 AND (hidden_c.visible <> 'true' OR hidden_c.status <> 'public'))",
    "(gw.status IS NULL OR gw.status = 'online')",
    );

  if ($image_id > 0)
  {
    $where[] = 'i.id = '.(int)$image_id;
  }
  if ($cat_id > 0)
  {
    $where[] = $recursive
      ? 'FIND_IN_SET('.$cat_id.', c.uppercats) > 0'
      : 'c.id = '.$cat_id;
  }
  if ('' !== $search)
  {
    $like = gzca_sql_like_contains($search);
    $where[] = "(i.name LIKE '".$like."' ESCAPE '\\\' OR i.file LIKE '".$like."' ESCAPE '\\\' OR i.comment LIKE '".$like."' ESCAPE '\\\' OR gw.code LIKE '".$like."' ESCAPE '\\\' OR c.name LIKE '".$like."' ESCAPE '\\\' OR public_c.name LIKE '".$like."' ESCAPE '\\\')";
  }
  $order_by = 'CASE WHEN COALESCE(gw.sort_order, 0) > 0 THEN 0 ELSE 1 END, COALESCE(gw.sort_order, 0) ASC, i.date_available DESC, i.id DESC';
  if ('featured' === $sort)
  {
    $order_by = 'COALESCE(gw.featured, 0) DESC, CASE WHEN COALESCE(gw.sort_order, 0) > 0 THEN 0 ELSE 1 END, COALESCE(gw.sort_order, 0) ASC, i.date_available DESC, i.id DESC';
  }
  elseif ('downloads' === $sort)
  {
    $order_by = 'COALESCE(gw.download_count, 0) DESC, i.hit DESC, i.id DESC';
  }
  elseif ('hot' === $sort)
  {
    $order_by = 'i.hit DESC, i.date_available DESC, i.id DESC';
  }
  elseif ('code' === $sort)
  {
    $order_by = "CASE WHEN COALESCE(gw.code, '') = '' THEN 1 ELSE 0 END, gw.code ASC, i.id ASC";
  }
  elseif ('newest' === $sort)
  {
    $order_by = 'i.date_available DESC, i.id DESC';
  }

  $from = '
  FROM '.IMAGES_TABLE.' AS i
    INNER JOIN '.IMAGE_CATEGORY_TABLE.' AS ic ON ic.image_id = i.id
    INNER JOIN '.CATEGORIES_TABLE.' AS c ON c.id = ic.category_id
    INNER JOIN '.GZCA_CATEGORIES_TABLE.' AS public_gc ON public_gc.catalog_enabled = 1 AND FIND_IN_SET(public_gc.category_id, c.uppercats) > 0
    INNER JOIN '.CATEGORIES_TABLE.' AS public_c ON public_c.id = public_gc.category_id
    LEFT JOIN '.GZCA_WORKS_TABLE.' AS gw ON gw.image_id = i.id
';
  $where_sql = implode("\n    AND ", $where);

  list($total_count) = pwg_db_fetch_row(pwg_query('
SELECT COUNT(DISTINCT i.id)'.$from.'
  WHERE '.$where_sql.'
;'));
  $total_count = (int)$total_count;

  $offset = $page_number * $per_page;
  $rows = query2array('
SELECT
    i.*,
    COALESCE(gw.code, \'\') AS gzca_code,
    COALESCE(gw.featured, 0) AS gzca_featured,
    COALESCE(gw.sort_order, 0) AS gzca_sort_order,
    COALESCE(gw.download_count, 0) AS gzca_download_count,
    SUBSTRING_INDEX(GROUP_CONCAT(public_c.id ORDER BY LENGTH(public_c.uppercats) DESC, public_c.global_rank SEPARATOR \'||\'), \'||\', 1) AS gzca_category_id,
    SUBSTRING_INDEX(GROUP_CONCAT(public_c.name ORDER BY LENGTH(public_c.uppercats) DESC, public_c.global_rank SEPARATOR \'||\'), \'||\', 1) AS gzca_category_name,
    SUBSTRING_INDEX(GROUP_CONCAT(public_gc.system_key ORDER BY LENGTH(public_c.uppercats) DESC, public_c.global_rank SEPARATOR \'||\'), \'||\', 1) AS gzca_category_key,
    SUBSTRING_INDEX(GROUP_CONCAT(public_gc.category_kind ORDER BY LENGTH(public_c.uppercats) DESC, public_c.global_rank SEPARATOR \'||\'), \'||\', 1) AS gzca_category_kind,
    SUBSTRING_INDEX(GROUP_CONCAT(public_gc.reserved ORDER BY LENGTH(public_c.uppercats) DESC, public_c.global_rank SEPARATOR \'||\'), \'||\', 1) AS gzca_category_reserved,
    SUBSTRING_INDEX(GROUP_CONCAT(public_gc.code_prefix ORDER BY LENGTH(public_c.uppercats) DESC, public_c.global_rank SEPARATOR \'||\'), \'||\', 1) AS gzca_category_prefix
  '.$from.'
  WHERE '.$where_sql.'
  GROUP BY i.id
  ORDER BY '.$order_by.'
  LIMIT '.$offset.', '.$per_page.'
;');

  $images = array();
  foreach ($rows as $row)
  {
    $code = !empty($row['gzca_code'])
      ? strtoupper($row['gzca_code'])
      : gzca_extract_code($row['comment'], 'ID-'.$row['id']);
    $category_id = (int)$row['gzca_category_id'];
    $category_name = (string)$row['gzca_category_name'];
    $image = array(
      'id' => (int)$row['id'],
      'width' => (int)$row['width'],
      'height' => (int)$row['height'],
      'hit' => (int)$row['hit'],
      'file' => $row['file'],
      'name' => strip_tags(trigger_change('render_element_name', $row['name'], __FUNCTION__) ?? ''),
      'comment' => strip_tags(trigger_change('render_element_description', gzca_strip_markers($row['comment']), __FUNCTION__) ?? ''),
      'date_creation' => $row['date_creation'],
      'date_available' => $row['date_available'],
      'gzca_code' => $code,
      'featured' => (bool)$row['gzca_featured'],
      'sort_order' => (int)$row['gzca_sort_order'],
      'download_count' => (int)$row['gzca_download_count'],
      'category' => array(
        'id' => $category_id,
        'name' => $category_name,
        'key' => (string)$row['gzca_category_key'],
        'kind' => (string)$row['gzca_category_kind'],
        'reserved' => !empty($row['gzca_category_reserved']),
        'code_prefix' => (string)$row['gzca_category_prefix'],
        'url' => $category_id > 0 ? gzca_frontend_url('category', array('cat_id' => $category_id)) : '',
        ),
      );
    $standard_urls = ws_std_get_urls($row);
    if ($include_detail)
    {
      $detail_derivatives = array();
      if (isset($standard_urls['derivatives']['xsmall']))
      {
        $safe_xsmall_url = gzca_image_derivative_url($row, IMG_XSMALL);
        if ('' !== $safe_xsmall_url)
        {
          $detail_derivatives['xsmall'] = $standard_urls['derivatives']['xsmall'];
          $detail_derivatives['xsmall']['url'] = $safe_xsmall_url;
        }
      }

      $display_url = gzca_image_derivative_url($row, IMG_XLARGE);
      if ('' === $display_url)
      {
        $display_url = gzca_image_derivative_url($row, IMG_XSMALL);
      }
      $safe_urls = array(
        'page_url' => gzca_frontend_url('detail', array('image_id' => (int)$row['id'], 'code' => $code)),
        'display_url' => $display_url,
        'element_url' => $display_url,
        'derivatives' => $detail_derivatives,
        );
    }
    else
    {
      $thumbnail_derivatives = array();
      $thumbnail_url = gzca_image_list_url($row);
      if ('' !== $thumbnail_url && isset($standard_urls['derivatives']['xsmall']))
      {
        $thumbnail_derivatives['xsmall'] = $standard_urls['derivatives']['xsmall'];
        $thumbnail_derivatives['xsmall']['url'] = $thumbnail_url;
      }

      $safe_urls = array(
        'page_url' => gzca_frontend_url('detail', array('image_id' => (int)$row['id'], 'code' => $code)),
        'thumbnail_url' => $thumbnail_url,
        'derivatives' => $thumbnail_derivatives,
        );
    }
    $images[] = array_merge($image, $safe_urls);
  }

  return array(
    'paging' => array(
      'page' => $page_number,
      'per_page' => $per_page,
      'count' => count($images),
      'total_count' => $total_count,
      ),
    'images' => $images,
    );
}
