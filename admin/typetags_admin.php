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
  elseif (strlen($color) == 4)
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

if (isset($_POST['submit']) and !is_adviser())
{
  $typetag = mysql_escape_string($_POST['edited_typetag']);
  $typetag_name = mysql_escape_string($_POST['typetag_name']);
  $typetag_color = mysql_escape_string($_POST['typetag_color']);

  $existing_names = array();
  
  $query = '
SELECT id, name, color
  FROM '.typetags_TABLE.'
;';
  $result = pwg_query($query);
  while ($row = mysql_fetch_array($result))
  {
    array_push($existing_names, $row['name']);
    if ($typetag == $row['id'])
    {
      $current_name = $row['name'];
      $current_color = $row['color'];
    }
  }
  
  // we must not rename typetag with an already existing name
  if ($typetag_name != $current_name and in_array($typetag_name, $existing_names))
  {
    array_push($page['errors'], l10n('typetags_already exists'));

    $edited_typetag = array(
      'id' => $typetag,
      'name' => $typetag_name,
      'color' => $typetag_color);
  }
  else
  {
    array_push($page['infos'], l10n('typetags_saved'));

    mass_updates(typetags_TABLE,
      array(
        'primary' => array('id'),
        'update' => array('name', 'color')),
      array(
        array(
          'id' => $typetag,
          'name' => $typetag_name,
          'color' => $typetag_color)));
  }
}

// +-----------------------------------------------------------------------+
// |                               delete typetags                             |
// +-----------------------------------------------------------------------+

if (isset($_POST['deletetypetag']) and isset($_POST['typetags']) and !is_adviser())
{
  $query = '
SELECT name
  FROM '.typetags_TABLE.'
  WHERE id IN ('.implode(',', $_POST['typetags']).')
;';
  $typetag_names = array_from_query($query, 'name');
  
  $query = ' UPDATE '. TAGS_TABLE .'
      SET id_typetags =  NULL
      WHERE id_typetags IN ('.implode(',', $_POST['typetags']).')
         ;';
 pwg_query($query);   

  $query = '
  DELETE
  FROM '.typetags_TABLE.'
  WHERE id IN ('.implode(',', $_POST['typetags']).')
;';
  pwg_query($query);
  
  array_push($page['infos'],
    l10n_dec('typetag_suppr', 'typetags_suppr', count($typetag_names))
    .' : '.implode(', ', $typetag_names)
  );
}

// +-----------------------------------------------------------------------+
// |                               add a typetag                           |
// +-----------------------------------------------------------------------+

if (isset($_POST['addtypetag']) and (empty($_POST['add_typetag']) or empty ($_POST['hexval'])))
{
  array_push($page['errors'], l10n('typetags_error'));
  
  $template->assign('typetag', array(
    'NAME' => isset($_POST['add_typetag']) ? $_POST['add_typetag'] : '',
    'COLOR' => isset($_POST['hexval']) ? $_POST['hexval'] : ''));
}
elseif (isset($_POST['addtypetag']) and !empty($_POST['add_typetag']) and !empty ($_POST['hexval']) and !is_adviser())
{
  $typetag_name = $_POST['add_typetag'];
  $typetag_color = $_POST['hexval'];

  // does the tag already exists?
  $query = '
SELECT id
  FROM '.typetags_TABLE.'
  WHERE name = \''.$typetag_name.'\'
;';
  $existing_tags = array_from_query($query, 'id');

  if (count($existing_tags) == 0)
  {
    mass_inserts(
      typetags_TABLE,
      array('name', 'color'),
      array(
        array(
          'name' => $typetag_name,
          'color' => $typetag_color,
          )
        )
      );
    
    array_push($page['infos'], l10n('typetags_saved'));
  }
  else
  {
    array_push($page['errors'],l10n('typetags_already exists'));

    $template->assign('typetag', array(
      'NAME' => $typetag_name,
      'COLOR' => $typetag_color));
  }
}
elseif (isset($_POST['add']) and !is_adviser())
{ 
  array_push($page['errors'], l10n('typetags_error'));
}

// +-----------------------------------------------------------------------+
// |                           Associate Tag to Typetage                   |
// +-----------------------------------------------------------------------+

		
if (isset($_POST['associate']) AND !empty($_POST['assoc_tags']) AND ($_POST['typetaglist'] != 0))
{
  $typetag_id = $_POST['typetaglist'];

  $query = ' UPDATE '. TAGS_TABLE .'
      SET id_typetags = '. $typetag_id .'
       WHERE id IN ('.implode(',', $_POST['assoc_tags']).')
         ;';
 pwg_query($query);   
    
    array_push(
      $page['infos'],
      sprintf(
        l10n('typetags_associated'),
        stripslashes($typetag_id)
        )
      );
}
elseif (isset($_POST['associate']) and !is_adviser())
{ 
  array_push($page['errors'], l10n('error_associate'));
}

// +-----------------------------------------------------------------------+
// |                           Dissociate Tag to Typetage                  |
// +-----------------------------------------------------------------------+

if (isset($_POST['dissociate']) AND !empty($_POST['dissoc_tags']) and !is_adviser())
{
  $associated_tag_id = implode(',', $_POST['dissoc_tags']);

 $query = ' UPDATE '. TAGS_TABLE .'
          SET id_typetags = NULL
          WHERE id IN (' . $associated_tag_id . ')
          ;';
   pwg_query($query);

 
    array_push($page['infos'],
      sprintf(l10n('typetags_dissociated'),
        stripslashes($associated_tag_id )
        )
      );
}
elseif (isset($_POST['dissociate']) and !is_adviser())
{ 
  array_push($page['errors'], l10n('error_dissociate'));
}

// +-----------------------------------------------------------------------+
// |                             template init                             |
// +-----------------------------------------------------------------------+

$template->set_filenames(array('plugin_admin_content' => dirname(__FILE__) . '/typetags_admin.tpl'));

//Recupere les tags associés
$query = '
  SELECT t.id , t.name, tt.color
	  FROM ' . TAGS_TABLE . ' as t
	  INNER JOIN ' .typetags_TABLE. ' as tt
	  ON  t.id_typetags = tt.id
	  WHERE t.id_typetags is NOT NULL
	  ORDER by t.id';
$result = pwg_query($query);

$tags = array();

while ($row = mysql_fetch_assoc($result))
{
  array_push($tags, $row);		
}

usort($tags, 'name_compare');
$template->assign('typetags_dissociation', $tags);

// Récupère tous les tags
$query = '
	SELECT t.id , t.name, tt.color
	  FROM ' . TAGS_TABLE . ' as t 
		LEFT JOIN ' .typetags_TABLE.' as tt
		ON  t.id_typetags = tt.id';
$result = pwg_query($query);

$tags = array();

while ($row = mysql_fetch_assoc($result))
{
  array_push($tags, $row);		
}

usort($tags, 'name_compare');
$template->assign('typetags_association', $tags);

// +-----------------------------------------------------------------------+
// |                             form creation                             |
// +-----------------------------------------------------------------------+

$query = 'SELECT * FROM '.typetags_TABLE.';';
$result = pwg_query($query);
$typetags = array();
while ($row = mysql_fetch_assoc($result))
{
  $row['color_text'] = get_color_text($row['color']);
  array_push($typetags, $row);
}

usort($typetags, 'name_compare');

$template->assign('typetags_selection', $typetags);

if (isset($_POST['edittypetag']) and isset($_POST['typetags']))
{
  if (count($_POST['typetags']) > 1)
  {
    array_push($page['errors'], l10n('You should select only one Typetag to edit.'));
  }
  else
  {
    $edited_typetag['id'] = $_POST['typetags'][0];
  }
}
if (isset($edited_typetag))
{
  $template->assign('edited_typetag', $edited_typetag['id']);

  $query = '
    SELECT id, name, color
    FROM '.typetags_TABLE.'
    WHERE id = '.$edited_typetag['id'].'
    ;';
  $result = pwg_query($query);
  while ($row = mysql_fetch_array($result))
  {
    $template->assign('typetag',
      array(
        'ID' => $row['id'],
        'OLD_NAME' => $row['name'],
        'OLD_COLOR' => $row['color'],
        'COLOR_TEXT' => get_color_text($row['color']),
        'NAME' => isset($edited_typetag['name']) ? $edited_typetag['name'] : $row['name'],
        'COLOR'=> isset($edited_typetag['color']) ? $edited_typetag['color'] : $row['color']));
  }
}

// +-----------------------------------------------------------------------+
// |                           sending html code                           |
// +-----------------------------------------------------------------------+

$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>