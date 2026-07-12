<?php
$themeconf = array(
  'name' => 'standard_pages',
  'parent' => 'default',
  'load_parent_css' => false,
  'img_dir' => 'themes/standard_pages/images',
);

$selected_skin = conf_get_param('standard_pages_selected_skin', 'default');
if (!preg_match('/^[a-z0-9_-]+$/i', $selected_skin) || !is_file(dirname(__FILE__).'/skins/'.$selected_skin.'.css'))
{
  $selected_skin = 'default';
}

$selected_logo = conf_get_param('standard_pages_selected_logo', 'piwigo_logo');
if (!in_array($selected_logo, array('piwigo_logo', 'custom_logo', 'gallery_title', 'none'), true))
{
  $selected_logo = 'piwigo_logo';
}

//send stantard pages conf options to tpl
$this->assign(
  array(
    'STD_PGS_SELECTED_SKIN' => $selected_skin,
    'STD_PGS_SELECTED_LOGO' => $selected_logo,
    'GALLERY_TITLE' => isset($page['gallery_title']) ? $page['gallery_title'] : $conf['gallery_title'],
  )
);

//Send custom logo path if custom_logo is the selected option
if ('custom_logo' == $selected_logo)
{
  $this->assign(
    array(
      'STD_PGS_SELECTED_LOGO_PATH' => conf_get_param('standard_pages_selected_logo_path', ''),
    )
  );
}

?>
