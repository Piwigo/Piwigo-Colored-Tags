<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

load_language('plugin.lang', typetags_PATH);

function get_color_text($color)
{
  if (strlen($color) == 7)
  {
    $rgb[] = hexdec(substr($color, 1, 2))/255;
    $rgb[] = hexdec(substr($color, 3, 2))/255;
    $rgb[] = hexdec(substr($color, 5, 2))/255;
  }
  else if (strlen($color) == 4)
  {
    $rgb[] = hexdec(substr($color, 1, 1))/15;
    $rgb[] = hexdec(substr($color, 2, 1))/15;
    $rgb[] = hexdec(substr($color, 3, 1))/15;
  }
  $l = (min($rgb) + max($rgb)) / 2;
  return $l > 0.45 ? '#000' : '#fff';
}

// +-----------------------------------------------------------------------+
// |                                edit typetags                          |
// +-----------------------------------------------------------------------+

if ( isset($_POST['edittypetag']) and (empty($_POST['typetag_name']) or empty($_POST['typetag_color'])) )
{
  $edited_typetag = array(
    'id' => $_POST['edited_typetag'],
    'name' => $_POST['typetag_name'],
    'color' => $_POST['typetag_color'],
  );
  
  array_push($page['errors'],  l10n('typetag_error'));
}
else if (isset($_POST['edittypetag']))
{
  // we must not rename typetag with an already existing name
  $query = '
SELECT id
  FROM '.typetags_TABLE.'
  WHERE
    name = "'.$_POST['typetag_name'].'"
    AND id != '.$_POST['edited_typetag'].'
;';

  if ( pwg_db_num_rows(pwg_query($query)) )
  {
    $edited_typetag = array(
      'id' => $_POST['edited_typetag'],
      'name' => $_POST['typetag_name'],
      'color' => $_POST['typetag_color'],
    );
    
    array_push($page['errors'], l10n('typetag_already_exists'));
  }
  else
  {
    $query = '
UPDATE '.typetags_TABLE.'
  SET 
    name = "'.$_POST['typetag_name'].'",
    color = "'.$_POST['typetag_color'].'"
  WHERE id = '.$_POST['edited_typetag'].'
;';
    pwg_query($query);
    
    array_push($page['infos'], l10n('typetag_saved'));
  }
}

// +-----------------------------------------------------------------------+
// |                           delete typetags                             |
// +-----------------------------------------------------------------------+

if (isset($_GET['deletetypetag']))
{
  $query = '
SELECT id
  FROM '.typetags_TABLE.'
  WHERE id = '.$_GET['deletetypetag'].'
;';
  
  if ( pwg_db_num_rows(pwg_query($query)) )
  {
    $query = '
UPDATE '.TAGS_TABLE.'
  SET id_typetags = NULL
  WHERE id_typetags = '.$_GET['deletetypetag'].'
;';
    pwg_query($query);
    
    $query = '
DELETE FROM '.typetags_TABLE.'
  WHERE id = '.$_GET['deletetypetag'].'
;';
    pwg_query($query);
    
    array_push($page['infos'], l10n('typetag_suppr'));
  }
  else
  {
    array_push($page['errors'], l10n('typetag_unknown'));
  }
}

// +-----------------------------------------------------------------------+
// |                               add a typetag                           |
// +-----------------------------------------------------------------------+

if ( isset($_POST['addtypetag']) and (empty($_POST['typetag_name']) or empty($_POST['typetag_color'])) )
{
  $template->assign('typetag', array(
    'NAME' => $_POST['typetag_name'],
    'COLOR' => $_POST['typetag_color'],
  ));
  
  array_push($page['errors'], l10n('typetag_error'));
}
else if (isset($_POST['addtypetag']))
{
  $typetag_name = $_POST['typetag_name'];
  $typetag_color = $_POST['typetag_color'];

  // does the tag already exists?
  $query = '
SELECT id
  FROM '.typetags_TABLE.'
  WHERE name = "'.$_POST['typetag_name'].'"
';

  if ( pwg_db_num_rows(pwg_query($query)) )
  {
    $template->assign('typetag', array(
      'NAME' => $_POST['typetag_name'],
      'COLOR' => $_POST['typetag_color'],
    ));
    
    array_push($page['errors'], l10n('typetag_already_exists'));
  }
  else
  {
    $query = '
INSERT INTO '.typetags_TABLE.'(
    name,
    color
  )
  VALUES(
    "'.$_POST['typetag_name'].'",
    "'.$_POST['typetag_color'].'"
  )
;';
    pwg_query($query);

    array_push($page['infos'], l10n('typetag_saved'));
  }
}

// +-----------------------------------------------------------------------+
// |                           Associate Tag to Typetage                   |
// +-----------------------------------------------------------------------+

if (isset($_POST['delete_all_assoc'])) 
{
  pwg_query('UPDATE '.TAGS_TABLE.' SET id_typetags = NULL;');
  array_push($page['infos'], l10n('All associations have been removed'));
} 
else if (isset($_POST['associations'])) 
{
  // beautify the parameters array
  $string = preg_replace('#[;]$#', null, $_POST['associations']);
  $associations = array();
  $a = explode(';', $string);
  
  foreach ($a as $s) 
  {
    $v = explode(':', $s);
    $associations[ltrim($v[0],'t-')] = ltrim($v[1],'tt-');
  }

  // save associations
  $updates = array();
  foreach ($associations as $tag => $typetag) 
  {
    array_push($updates, array(
      'id' => $tag,
      'id_typetags' => $typetag,
      ));
  }
  
  mass_updates(
    TAGS_TABLE, 
    array('primary' => array('id'), 'update' => array('id_typetags')),
    $updates
    );
  
  array_push($page['infos'], l10n('typetags_associated'));
}

// +-----------------------------------------------------------------------+
// |                          Configuration                                |
// +-----------------------------------------------------------------------+
if (isset($_POST['save_config']))
{
  $conf['TypeTags'] = array(
    'show_all' => $_POST['show_all'] == 'true',
    );
    
  conf_update_param('TypeTags', serialize($conf['TypeTags']));
}

$template->assign('SHOW_ALL', $conf['TypeTags']['show_all']);

// +-----------------------------------------------------------------------+
// |                             template init                             |
// +-----------------------------------------------------------------------+

$template->set_filenames(array('plugin_admin_content' => dirname(__FILE__) . '/admin/typetags_admin.tpl'));

// get all tags
$query = '
SELECT 
    id as tagid,
    name as tagname, 
    id_typetags as typetagid
  FROM '.TAGS_TABLE.'
  ORDER BY name ASC
;';
$all_tags = pwg_query($query);

while ($row = pwg_db_fetch_assoc($all_tags))
{
  if (!$row['typetagid']) $row['typetagid'] = 'NULL';
  $row['tagname'] = strip_tags(trigger_event('render_tag_name', $row['tagname']));
  $template->append('typetags_association', $row);
}

// get all typetags
$query = 'SELECT * FROM '.typetags_TABLE.' ORDER BY name;';
$all_typetags = pwg_query($query);

while ($row = mysql_fetch_assoc($all_typetags))
{
  $row['color_text'] = get_color_text($row['color']);
  $row['u_edit'] = typetags_ADMIN . '&edittypetag=' . $row['id'];
  $row['u_delete'] = typetags_ADMIN . '&deletetypetag=' . $row['id'];
  
  $template->append('typetags_selection', $row);
}

// edit form
if (isset($_GET['edittypetag']))
{
  $edited_typetag['id'] = $_GET['edittypetag'];
}

if (isset($edited_typetag['id']))
{
  $template->assign('edited_typetag', $edited_typetag['id']);
  
$query = '
SELECT 
    id,
    name,
    color
  FROM '.typetags_TABLE.'
  WHERE id = '.$edited_typetag['id'].'
;';
  $row = pwg_db_fetch_assoc(pwg_query($query));

  $template->assign('typetag', array(
    'ID' => $row['id'],
    'OLD_NAME' => $row['name'],
    'OLD_COLOR' => $row['color'],
    'COLOR_TEXT' => get_color_text($row['color']),
    'NAME' => isset($edited_typetag['name']) ? $edited_typetag['name'] : $row['name'],
    'COLOR'=> isset($edited_typetag['color']) ? $edited_typetag['color'] : $row['color'],
  ));
  
  $template->assign('IN_EDIT', true);
}

// +-----------------------------------------------------------------------+
// |                           sending html code                           |
// +-----------------------------------------------------------------------+

$template->assign('typetags_ADMIN', typetags_ADMIN);
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>