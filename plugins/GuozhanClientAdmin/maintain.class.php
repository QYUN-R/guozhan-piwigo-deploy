<?php

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

class GuozhanClientAdmin_maintain extends PluginMaintain
{
  private function default_config()
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

  private function ensure_column($table, $column, $definition)
  {
    $result = pwg_query("SHOW COLUMNS FROM `".$table."` LIKE '".$column."';");
    if (0 === pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `'.$table.'` ADD `'.$column.'` '.$definition.';');
    }
  }

  private function ensure_index($table, $index, $definition)
  {
    $result = pwg_query("SHOW INDEX FROM `".$table."` WHERE Key_name = '".$index."';");
    if (0 === pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `'.$table.'` ADD '.$definition.';');
    }
  }

  private function seed_competition_media($table)
  {
    $items = array(
      array('水墨', 'ink', 1),
      array('油画', 'oil', 2),
      array('版画', 'print', 3),
      array('水彩', 'watercolor', 4),
      );

    foreach ($items as $item)
    {
      $slug = pwg_db_real_escape_string($item[1]);
      $result = pwg_query("SELECT id FROM `".$table."` WHERE slug = '".$slug."' LIMIT 1;");
      if (0 === pwg_db_num_rows($result))
      {
        single_insert($table, array(
          'name' => $item[0],
          'slug' => $item[1],
          'description' => $item[0].'类比赛作品。',
          'sort_order' => $item[2],
          'status' => 'active',
          'updated_at' => date('Y-m-d H:i:s'),
          ));
      }
      else
      {
        pwg_query(
          "UPDATE `".$table."` SET description = '".pwg_db_real_escape_string($item[0].'类比赛作品。')."'".
          " WHERE slug = '".$slug."' AND description = '';"
          );
      }
    }
  }

  public function install($plugin_version, &$errors=array())
  {
    global $conf, $prefixeTable;

    $works_table = $prefixeTable.'gzca_works';
    $categories_table = $prefixeTable.'gzca_categories';
    $competition_media_table = $prefixeTable.'gzca_competition_media';
    $admin_security_table = $prefixeTable.'gzca_admin_security';

    pwg_query('
CREATE TABLE IF NOT EXISTS `'.$works_table.'` (
  `image_id` MEDIUMINT UNSIGNED NOT NULL,
  `code` VARCHAR(64) NOT NULL DEFAULT \'\',
  `status` ENUM(\'online\',\'offline\') NOT NULL DEFAULT \'online\',
  `featured` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT NOT NULL DEFAULT 0,
  `download_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `competition_medium_id` SMALLINT UNSIGNED DEFAULT NULL,
  `competition_sort_order` INT NOT NULL DEFAULT 0,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`image_id`),
  UNIQUE KEY `gzca_code` (`code`),
  KEY `gzca_competition_medium` (`competition_medium_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4
;');

    $this->ensure_column($works_table, 'download_count', 'INT UNSIGNED NOT NULL DEFAULT 0 AFTER `sort_order`');
    $this->ensure_column($works_table, 'competition_medium_id', 'SMALLINT UNSIGNED DEFAULT NULL AFTER `download_count`');
    $this->ensure_column($works_table, 'competition_sort_order', 'INT NOT NULL DEFAULT 0 AFTER `competition_medium_id`');

    pwg_query('
CREATE TABLE IF NOT EXISTS `'.$categories_table.'` (
  `category_id` SMALLINT UNSIGNED NOT NULL,
  `code_prefix` VARCHAR(48) NOT NULL DEFAULT \'\',
  `sort_order` INT NOT NULL DEFAULT 0,
  `catalog_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `direct_upload` TINYINT(1) NOT NULL DEFAULT 0,
  `system_key` VARCHAR(80) DEFAULT NULL,
  `category_kind` VARCHAR(24) NOT NULL DEFAULT \'custom\',
  `reserved` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `gzca_category_system_key` (`system_key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4
;');

    $this->ensure_column($categories_table, 'catalog_enabled', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `sort_order`');
    $this->ensure_column($categories_table, 'direct_upload', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `catalog_enabled`');
    $this->ensure_column($categories_table, 'system_key', 'VARCHAR(80) DEFAULT NULL AFTER `direct_upload`');
    $this->ensure_column($categories_table, 'category_kind', "VARCHAR(24) NOT NULL DEFAULT 'custom' AFTER `system_key`");
    $this->ensure_column($categories_table, 'reserved', 'TINYINT(1) NOT NULL DEFAULT 0 AFTER `category_kind`');
    $this->ensure_index($categories_table, 'gzca_category_system_key', 'UNIQUE KEY `gzca_category_system_key` (`system_key`)');

    pwg_query('
CREATE TABLE IF NOT EXISTS `'.$competition_media_table.'` (
  `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `slug` VARCHAR(64) NOT NULL,
  `description` VARCHAR(500) NOT NULL DEFAULT \'\',
  `sort_order` INT NOT NULL DEFAULT 0,
  `status` ENUM(\'active\',\'inactive\') NOT NULL DEFAULT \'active\',
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gzca_competition_slug` (`slug`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4
;');

    $this->ensure_column($competition_media_table, 'description', "VARCHAR(500) NOT NULL DEFAULT '' AFTER `slug`");

    $this->seed_competition_media($competition_media_table);

    pwg_query('
CREATE TABLE IF NOT EXISTS `'.$admin_security_table.'` (
  `user_id` MEDIUMINT UNSIGNED NOT NULL,
  `verified_email_hash` CHAR(64) NOT NULL DEFAULT \'\',
  `email_verified_at` DATETIME DEFAULT NULL,
  `password_changed_at` DATETIME DEFAULT NULL,
  `sessions_revoked_before` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4
;');

    $this->ensure_column($admin_security_table, 'sessions_revoked_before', 'DATETIME DEFAULT NULL AFTER `password_changed_at`');

    if (empty($conf['gzca_config']))
    {
      conf_update_param('gzca_config', $this->default_config(), true, 'serialize');
    }
  }

  public function update($old_version, $new_version, &$errors=array())
  {
    global $conf;

    $this->install($new_version, $errors);

    if (!empty($conf['gzca_config']))
    {
      $config = is_array($conf['gzca_config'])
        ? $conf['gzca_config']
        : safe_unserialize($conf['gzca_config']);
      if (isset($config['brand_name']) && '国展绘画展示馆' === $config['brand_name'])
      {
        $config['brand_name'] = '图物计划国展素材馆';
      }
      if (isset($config['brand_en']) && 'Guozhan Painting Gallery' === $config['brand_en'])
      {
        $config['brand_en'] = 'AI Graphics Learning Plan';
      }
      conf_update_param('gzca_config', $config, true, 'serialize');
    }
  }

  public function uninstall()
  {
    global $prefixeTable;

    pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'gzca_works`;');
    pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'gzca_categories`;');
    pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'gzca_competition_media`;');
    pwg_query('DROP TABLE IF EXISTS `'.$prefixeTable.'gzca_admin_security`;');
    conf_delete_param('gzca_config');
  }
}
