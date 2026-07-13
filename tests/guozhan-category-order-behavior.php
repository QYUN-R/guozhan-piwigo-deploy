<?php

declare(strict_types=1);

define('PHPWG_ROOT_PATH', __DIR__.'/fixtures/fake-root/');

require dirname(__DIR__).'/plugins/GuozhanClientAdmin/include/functions.inc.php';

$rows = array(
  array('name' => '未知画展 07', 'custom_sort_order' => 10, 'reserved' => 1),
  array('name' => '第五届青年漆画展览征稿通知', 'custom_sort_order' => 3, 'reserved' => 0),
  array('name' => '未知画展 02', 'custom_sort_order' => 5, 'reserved' => 1),
  array('name' => '梅花之韵——2026·中国画花鸟作品展', 'custom_sort_order' => 1, 'reserved' => 0),
  array('name' => '未知画展 01', 'custom_sort_order' => 4, 'reserved' => 1),
  array('name' => '山水滋美——2026风景油画展', 'custom_sort_order' => 2, 'reserved' => 0),
  );

usort($rows, 'gzca_compare_exhibition_category_options');
$actual = array_column($rows, 'name');
$expected = array(
  '梅花之韵——2026·中国画花鸟作品展',
  '山水滋美——2026风景油画展',
  '第五届青年漆画展览征稿通知',
  '未知画展 01',
  '未知画展 02',
  '未知画展 07',
  );

if ($actual !== $expected)
{
  fwrite(STDERR, 'FAIL: exhibition category order was '.json_encode($actual, JSON_UNESCAPED_UNICODE).PHP_EOL);
  exit(1);
}

fwrite(STDOUT, 'PASS: administrator exhibition categories use named-first and natural reserved ordering.'.PHP_EOL);
