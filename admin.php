<?php
defined('TYPETAGS_PATH') or die('Hacking attempt!');

load_language('plugin.lang', TYPETAGS_PATH);

include_once(TYPETAGS_PATH . 'include/functions.inc.php');


// +-----------------------------------------------------------------------+
// |                                edit typetags                          |
// +-----------------------------------------------------------------------+
if (isset($_POST['edittypetag']))
{
  if (empty($_POST['typetag_name']) or empty($_POST['typetag_color']))
  {
    $page['errors'][] = l10n('You must fill all fields (name and color)');
  }
  else
  {
    // we must not rename typetag with an already existing name
    $query = '
SELECT id
  FROM ' . TYPETAGS_TABLE . '
  WHERE
    name = "' . pwg_db_real_escape_string($_POST['typetag_name']) . '"
    AND id != ' . $_POST['edited_typetag'] . '
;';

    if (pwg_db_num_rows(pwg_query($query)))
    {
      $page['errors'][] = l10n('This name is already used');
    }
    else if ( ($color = check_color($_POST['typetag_color'])) === false )
    {
      $page['errors'][] = l10n('Invalid color');
    }
    else
    {
      $query = '
UPDATE '.TYPETAGS_TABLE.'
  SET
    name = "' . pwg_db_real_escape_string($_POST['typetag_name']) . '",
    color = "' . $color . '"
  WHERE id = ' . $_POST['edited_typetag'] . '
;';
      pwg_query($query);

      $page['infos'][] = l10n('Color saved');
    }
  }

  if (count($page['errors']))
  {
    $edited_typetag = array(
      'id' => $_POST['edited_typetag'],
      'name' => $_POST['typetag_name'],
      'color' => $_POST['typetag_color'],
      );
  }
}

// +-----------------------------------------------------------------------+
// |                           delete typetags                             |
// +-----------------------------------------------------------------------+
if (isset($_GET['deletetypetag']))
{
  $query = '
UPDATE ' . TAGS_TABLE . '
  SET id_typetags = NULL
  WHERE id_typetags = ' . intval($_GET['deletetypetag']) . '
;';
  pwg_query($query);

  $query = '
DELETE FROM ' . TYPETAGS_TABLE . '
  WHERE id = ' . intval($_GET['deletetypetag']) . '
;';
  pwg_query($query);

  if (pwg_db_changes())
  {
    $page['infos'][] = l10n('Color deleted');
  }
}

// +-----------------------------------------------------------------------+
// |                               add a typetag                           |
// +-----------------------------------------------------------------------+
if (isset($_POST['addtypetag']))
{
  if (empty($_POST['typetag_name']) or empty($_POST['typetag_color']))
  {
    $page['errors'][] = l10n('You must fill all fields (name and color)');
  }
  else
  {
    $typetag_name = $_POST['typetag_name'];
    $typetag_color = $_POST['typetag_color'];

    // does the tag already exists?
    $query = '
SELECT id
  FROM ' . TYPETAGS_TABLE . '
  WHERE name = "' . pwg_db_real_escape_string($_POST['typetag_name']) . '"
';

    if (pwg_db_num_rows(pwg_query($query)))
    {
      $page['errors'][] = l10n('This name is already used');
    }
    else if ( ($color = check_color($_POST['typetag_color'])) === false )
    {
      $page['errors'][] = l10n('Invalid color');
    }
    else
    {
      $query = '
INSERT INTO ' . TYPETAGS_TABLE . '(
    name,
    color
  )
  VALUES(
    "' . pwg_db_real_escape_string($_POST['typetag_name']) . '",
    "' . $color . '"
  )
;';
      pwg_query($query);

      $page['infos'][] = l10n('Color added');
    }
  }

  if (count($page['errors']))
  {
    $template->assign('typetag', array(
      'NAME' => $_POST['typetag_name'],
      'COLOR' => $_POST['typetag_color'],
      ));
  }
}

// +-----------------------------------------------------------------------+
// |                          Configuration                                |
// +-----------------------------------------------------------------------+
if (isset($_POST['save_config']))
{
  $conf['TypeTags'] = array(
    'show_all' => $_POST['show_all'] == 'true',
    );

  conf_update_param('TypeTags', $conf['TypeTags']);
  $page['infos'][] = l10n('Information data registered in database');
}

$template->assign('SHOW_ALL', $conf['TypeTags']['show_all']);

// +-----------------------------------------------------------------------+
// |                             template init                             |
// +-----------------------------------------------------------------------+
$template->assign('TYPETAGS_PATH', TYPETAGS_PATH);
$template->assign('F_ACTION', TYPETAGS_ADMIN);

// get all typetags
$query = 'SELECT * FROM ' . TYPETAGS_TABLE . ' ORDER BY name;';
$result = pwg_query($query);

while ($row = pwg_db_fetch_assoc($result))
{
  $row['color_text'] = get_color_text($row['color']);
  $row['u_edit'] = TYPETAGS_ADMIN . '&amp;edittypetag=' . $row['id'];
  $row['u_delete'] = TYPETAGS_ADMIN . '&amp;deletetypetag=' . $row['id'];

  $template->append('typetags', $row);
}

// edit form
if (isset($_GET['edittypetag']))
{
  $edited_typetag['id'] = intval($_GET['edittypetag']);
}

if (isset($edited_typetag['id']) and $edited_typetag['id']>0)
{
  $template->assign('edited_typetag', $edited_typetag['id']);

$query = '
SELECT *
  FROM ' . TYPETAGS_TABLE . '
  WHERE id = ' . $edited_typetag['id'] . '
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
$template->set_filename('typetags', realpath(TYPETAGS_PATH . 'template/admin.tpl'));
$template->assign_var_from_handle('ADMIN_CONTENT', 'typetags');
