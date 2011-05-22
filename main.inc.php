<?php
/*
Plugin Name: TypeT@gs
Version: auto
Description: Allow to manage color of tags, as you want...
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=166
Author: Sakkhho & P@t & Mistic
*/

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

global $prefixeTable ;

define('typetags_DIR' , basename(dirname(__FILE__)));
define('typetags_PATH' , PHPWG_PLUGINS_PATH . typetags_DIR . '/');
define('typetags_TABLE' , $prefixeTable . 'typetags');
define('typetags_ADMIN', get_root_url().'admin.php?page=plugin-' . typetags_DIR);

function typetags_admin_menu($menu)
{
  array_push($menu, array(
    'NAME' => 'TypeT@gs',
    'URL' => typetags_ADMIN,
  ));
  return $menu;
}

function typetags()
{
  include(typetags_PATH . 'typetags.php');
}

if (script_basename() == 'tags')
{
  add_event_handler('loc_begin_page_header', 'typetags', 60);
}

add_event_handler('get_admin_plugin_menu_links', 'typetags_admin_menu');

?>