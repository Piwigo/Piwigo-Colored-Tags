<?php
/*
Plugin Name: TypeT@gs
Version: auto
Description: Allow to manage color of tags, as you want...
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=166
Author: Sakkhho & P@t & Mistic
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $prefixeTable, $conf;

define('typetags_PATH' , PHPWG_PLUGINS_PATH . basename(dirname(__FILE__)) . '/');
define('typetags_TABLE' , $prefixeTable . 'typetags');
define('typetags_ADMIN', get_root_url().'admin.php?page=plugin-' . basename(dirname(__FILE__)));


include(typetags_PATH . 'typetags.php');
$conf['TypeTags'] = unserialize($conf['TypeTags']);

// tags on picture page
/*if (script_basename() == 'picture')
{
  add_event_handler('loc_end_picture', 'typetags_picture');
}*/

// tags everywhere
if ( $conf['TypeTags']['show_all'] and script_basename() != 'tags' )
{
  add_event_handler('render_tag_name', 'typetags_render', 0);
}
// tags on tags page
else if (script_basename() == 'tags')
{
  add_event_handler('loc_begin_page_header', 'typetags_tags');
}


add_event_handler('get_admin_plugin_menu_links', 'typetags_admin_menu');

function typetags_admin_menu($menu)
{
  array_push($menu, array(
    'NAME' => 'TypeT@gs',
    'URL' => typetags_ADMIN,
  ));
  return $menu;
}

?>