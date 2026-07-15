<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{if $PAGE_TITLE=='Home'|@translate}{$GALLERY_TITLE|strip_tags:false}{else}{$PAGE_TITLE|strip_tags:false}{/if}</title>
  <link rel="icon" type="image/png" href="{$ROOT_URL}themes/guozhan-gallery/assets/logo-mark.png?v=20260714-favicon-1">
  <link rel="stylesheet" href="{$ROOT_URL}themes/{$themeconf.id}/assets/guozhan.css?v=20260715-page-jump-1">
  <script>
    window.GUOZHAN_PIWIGO = true;
    window.GUOZHAN_ROOT_URL = "{$ROOT_URL}";
    window.GUOZHAN_THEME_ROOT = "{$ROOT_URL}themes/{$themeconf.id}/";
    window.GUOZHAN_ASSET_BASE = "{$ROOT_URL}themes/{$themeconf.id}/assets/";
  </script>
</head>
<body id="{$BODY_ID|escape:'html'}" data-piwigo-body-id="{$BODY_ID|escape:'html'}" data-piwigo-title="{$PAGE_TITLE|escape:'html'}">
  <div id="guozhan-app"></div>
  <div id="pwg-native" style="display:none!important" aria-hidden="true">


