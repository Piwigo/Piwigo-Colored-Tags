<?php
/*
Plugin Name: Colored Tags
Version: auto
Description: Allow to manage color of tags, as you want...
Plugin URI: auto
Author: Mistic
Author URI: http://www.strangeplanet.fr
*/

defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

if (basename(dirname(__FILE__)) != 'typetags')
{
  add_event_handler('init', 'typetags_error');
  function typetags_error()
  {
    global $page;
    $page['errors'][] = 'Colored Tags folder name is incorrect, uninstall the plugin and rename it to "typetags"';
  }
  return;
}

global $prefixeTable, $conf;

define('TYPETAGS_PATH' ,  PHPWG_PLUGINS_PATH . 'typetags/');
define('TYPETAGS_TABLE' , $prefixeTable . 'typetags');
define('TYPETAGS_ADMIN',  get_root_url().'admin.php?page=plugin-typetags');

include_once(TYPETAGS_PATH . 'include/events_public.inc.php');
include_once(TYPETAGS_PATH . 'include/functions.inc.php');

$conf['TypeTags'] = safe_unserialize($conf['TypeTags']);


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

// escape keywords meta
add_event_handler('loc_begin_page_header', 'typetags_escape');


if (defined('IN_ADMIN'))
{
  add_event_handler('get_admin_plugin_menu_links', 'typetags_admin_menu');

  add_event_handler('loc_begin_admin_page', 'typetags_admin');

  include_once(TYPETAGS_PATH . 'include/events_admin.inc.php');
}

// add api/service methods
add_event_handler('ws_add_methods', 'typetags_add_methods');

function typetags_add_methods($arr) 
{
  $service = &$arr[0];

  $service->addMethod(
    'typetags.tags.setType',
    'ws_typetags_tags_setType',
    array(
      'tag_id' => array('type' => WS_TYPE_ID, 'flags'=>WS_PARAM_FORCE_ARRAY),
      'typetag_id' => array('info' => 'Zero (0) for remove color')
      ),
    'Set/remove color for a list of tags',
    null,
    array('admin_only'=>true)
  );

  $service->addMethod(
    'typetags.type.add',
    'ws_typetags_type_add',
    array(
      'typetag_name' => array(),
      'typetag_color' => array('info' => 'In format RRVVBB (Example : FF0000 for red)')
      ),
    'Create a tag color'
    );
}


/**
 * API method
 * Set a color for tags
 * @param mixed[] $params
 *    @option int[] tag_id
 *    @option int typetag_id
 */
function ws_typetags_tags_setType($params, &$service) 
{
$query = '
UPDATE ' . TAGS_TABLE . '
  SET id_typetags = ' . ($params['typetag_id']!=0 ? $params['typetag_id'] : 'NULL') . '
  WHERE id IN ('.implode(',', $params['tag_id']).')
;';
  pwg_query($query);
}

/**
 * API method
 * Create a new type of tag
 * @param mixed[] $params
 *    @option string typetag_name
 *    @option string typetag_color
 */
function ws_typetags_type_add($params, &$service) 
{
  $name = $params['typetag_name'];
  $color = '#' . $params['typetag_color'];

  // does the tag already exists?
  $query = '
SELECT id
  FROM ' . TYPETAGS_TABLE . '
  WHERE name = "' . pwg_db_real_escape_string($name) . '"
';

  if (pwg_db_num_rows(pwg_query($query)))
  {
    return new PwgError(WS_ERR_INVALID_PARAM, l10n('This name is already used'));
  }
  else if ( ($color = check_color($color)) === false )
  {
    return new PwgError(WS_ERR_INVALID_PARAM, l10n('Invalid color'));
  }
  else
  {
    single_insert(
      TYPETAGS_TABLE, 
      array(
        "name" => pwg_db_real_escape_string($name),
        "color" => $color,
      )
    );

    $id = pwg_db_insert_id(IMAGES_TABLE);

    if (pwg_query($query)) 
    {
      return array(
        'id' => $id,
        'color' => $color,
        'color_text' => get_color_text($color),
        'name' => $name,
      );
    } 
    else 
    {
      return false;
    };
  }
}