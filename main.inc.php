<?php
/*
Plugin Name: Colored Tags (TypeT@gs)
Version: auto
Description: Allow to manage color of tags, as you want...
Plugin URI: auto
Author: Mistic
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

global $prefixeTable, $conf;

define('TYPETAGS_ID',      basename(dirname(__FILE__)));
define('TYPETAGS_PATH' ,   PHPWG_PLUGINS_PATH . TYPETAGS_ID . '/');
define('TYPETAGS_TABLE' ,  $prefixeTable . 'typetags');
define('TYPETAGS_ADMIN',   get_root_url().'admin.php?page=plugin-' . TYPETAGS_ID);
define('TYPETAGS_VERSION', 'auto');


$conf['TypeTags'] = unserialize($conf['TypeTags']);


include_once(TYPETAGS_PATH . 'include/events_public.inc.php');

// tags on picture page
/*if (script_basename() == 'picture')
{
  add_event_handler('loc_end_picture', 'typetags_picture');
}*/

// tags everywhere
if ($conf['TypeTags']['show_all'] and script_basename() != 'tags')
{
  add_event_handler('render_tag_name', 'typetags_render', 0, 2);
}

// tags on tags page
add_event_handler('loc_end_tags', 'typetags_tags');


if (defined('IN_ADMIN'))
{
  add_event_handler('get_admin_plugin_menu_links', 'typetags_admin_menu');

  add_event_handler('loc_begin_admin_page', 'typetags_admin');

  include_once(TYPETAGS_PATH . 'include/events_admin.inc.php');
}
