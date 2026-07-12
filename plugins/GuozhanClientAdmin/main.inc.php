<?php
/*
Plugin Name: 国展客户专用后台
Version: 0.5.2
Description: 为图物计划国展素材馆提供作品上传、板块与画展、编号、比赛、上下架、封面、排序和品牌客服管理。
Author: AI Graphics Learning Plan
Has Settings: true
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

define('GZCA_ID', basename(dirname(__FILE__)));
define('GZCA_PATH', PHPWG_PLUGINS_PATH.GZCA_ID.'/');
define('GZCA_VERSION', '0.5.2');

global $prefixeTable;
define('GZCA_WORKS_TABLE', $prefixeTable.'gzca_works');
define('GZCA_CATEGORIES_TABLE', $prefixeTable.'gzca_categories');
define('GZCA_COMPETITION_MEDIA_TABLE', $prefixeTable.'gzca_competition_media');

include_once(GZCA_PATH.'include/functions.inc.php');

add_event_handler('init', 'gzca_init');
add_event_handler('loc_begin_admin', 'gzca_restrict_customer_admin', 5);
add_event_handler('ws_add_methods', 'gzca_register_ws_methods');

if (!defined('IN_ADMIN'))
{
  add_event_handler('loc_after_page_header', 'gzca_render_frontend_bridge', 90);
}
