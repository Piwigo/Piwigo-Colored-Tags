<?php
defined('TYPETAGS_PATH') or die('Hacking attempt!');

/**
 * menu link
 */
function typetags_admin_menu($menu)
{
  $menu[] = array(
    'NAME' => 'Colored Tags',
    'URL' => TYPETAGS_ADMIN,
    );
  return $menu;
}

/**
 * tags page
 */
function typetags_admin()
{
  global $template, $page;

  if ($page['page'] != 'tags')
  {
    return;
  }

  include_once(TYPETAGS_PATH . 'include/functions.inc.php');

  load_language('plugin.lang', TYPETAGS_PATH);

  // save
  if (isset($_POST['typetags_submit']))
  {
    if ($_POST['mode'] == 'global')
    {
      $query = '
UPDATE ' . TAGS_TABLE . '
  SET id_typetags = ' . ($_POST['tag_color-all']!=-1 ? get_typetag_id($_POST['tag_color-all']) : 'NULL') . '
  WHERE id IN ('.$_POST['edit_list'].')
;';
      pwg_query($query);
    }
    else
    {
      $updates = $deletes = array();

      foreach (explode(',', $_POST['edit_list']) as $tag_id)
      {
        if ($_POST['tag_color-'.$tag_id]!=-1)
        {
          $updates[] = array(
            'id' => $tag_id,
            'id_typetags' => get_typetag_id($_POST['tag_color-'.$tag_id]),
            );
        }
        else
        {
          $deletes[] = $tag_id;
        }
      }

      mass_updates(
        TAGS_TABLE,
        array('primary' => array('id'), 'update' => array('id_typetags')),
        $updates
        );

      if (!empty($deletes))
      {
        $query = '
UPDATE ' . TAGS_TABLE . '
  SET id_typetags = NULL
  WHERE id IN('. implode(',', $deletes) .')
;';
        pwg_query($query);
      }
    }
  }

  // enter edit
  if (isset($_POST['typetags']) and isset($_POST['tags']))
  {
    $template->assign('TYPETAGS_LIST', implode(',', $_POST['tags']));

    $query = '
SELECT
    t.id,
    t.name,
    id_typetags,
    color
  FROM ' . TAGS_TABLE . ' AS t
    LEFT JOIN ' . TYPETAGS_TABLE . ' AS tt
    ON t.id_typetags = tt.id
  WHERE t.id IN ('.implode(',', $_POST['tags']).')
;';
    $result = pwg_query($query);
    while ($row = pwg_db_fetch_assoc($result))
    {
      $row['color_text'] = get_color_text($row['color']);
      $template->append('tags', $row);
    }

    $query = 'SELECT * FROM ' . TYPETAGS_TABLE . ' ORDER BY name;';
    $result = pwg_query($query);

    while ($row = pwg_db_fetch_assoc($result))
    {
      $row['color_text'] = get_color_text($row['color']);
      $template->append('typetags', $row);
    }
  }

  $query = '
SELECT
    t.id,
    id_typetags,
    color
  FROM ' . TAGS_TABLE . ' AS t
    LEFT JOIN ' . TYPETAGS_TABLE . ' AS tt
    ON t.id_typetags = tt.id
;';
  $template->assign('tags_color', query2array($query, 'id'));

  $template->assign('TYPETAGS_PATH', TYPETAGS_PATH);
  $template->set_prefilter('tags', 'typetags_admin_prefilter');
}

function typetags_admin_prefilter($content)
{
  // add button
  $search[0] = '<input type="submit" name="delete"';
  $replace[0] = '<input type="submit" name="typetags" value="{\'Set tags color\'|translate}"> ' . $search[0];

  // add form part
  $search[1] = '<form action="{$F_ACTION}" method="post">';
  $replace[1] = $search[1] . file_get_contents(realpath(TYPETAGS_PATH . 'template/tags.tpl'));

  // color on main list
  $search[2] = '<input type="checkbox" name="tags[]" value="{$tag.id}"> {$tag.name}';
  $replace[2] = '<input type="checkbox" name="tags[]" value="{$tag.id}"> <span style="color:{$tags_color[$tag.id].color};">{$tag.name}</span>';

  return str_replace($search, $replace, $content);
}
