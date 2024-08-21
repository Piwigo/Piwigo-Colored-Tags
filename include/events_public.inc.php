<?php
defined('TYPETAGS_PATH') or die('Hacking attempt!');

/**
 * triggered by 'render_tag_name'
 */
function typetags_render($tag_name, $tag=array())
{
  global $pwg_loaded_plugins, $page, $typetags_cache;

  if (defined('IN_ADMIN') and in_array($page['page'], array('photo', 'batch_manager', 'tags')))
  {
    return $tag_name;
  }

  if (isset($typetags_cache['tags'][$tag_name]))
  {
    return $typetags_cache['tags'][$tag_name];
  }

  if (!isset($typetags_cache['colors']))
  {
    $query = '
SELECT id, color
  FROM ' . TYPETAGS_TABLE . '
;';
    $typetags_cache['colors'] = query2array($query, 'id', 'color');
  }

  if (!isset($typetags_cache['color_of_tag']))
  {
    $typetags_cache['color_of_tag'] = array(
      'by_id' => array(),
      'by_name' => array(),
    );

    $query = '
SELECT
    t.id,
    t.name,
    color
  FROM ' . TYPETAGS_TABLE . ' AS tt
    INNER JOIN ' . TAGS_TABLE . ' AS t ON t.id_typetags = tt.id
;';
    $rows = query2array($query);
    foreach ($rows as $row)
    {
      $typetags_cache['color_of_tag']['by_id'][ $row['id'] ] = $row['color'];
      $typetags_cache['color_of_tag']['by_name'][ $row['name'] ] = $row['color'];
    }
  }

  if (!empty($tag['id_typetags']))
  {
    $color = $typetags_cache['colors'][ $tag['id_typetags'] ];
  }
  elseif (isset($tag['id']))
  {
    $color = $typetags_cache['color_of_tag']['by_id'][ $tag['id'] ] ?? null;
  }
  else
  {
    $color = $typetags_cache['color_of_tag']['by_name'][ $tag_name ] ?? null;
  }

  if ($color === null)
  {
    $ret = $tag_name;
  }
  elseif (isset($pwg_loaded_plugins['ExtendedDescription']))
  {
    $ret = '[lang=all]<span style="color:' . $color . ';">[/lang]' . $tag_name . '[lang=all]</span>[/lang]';
  }
  else
  {
    $ret = '<span style="color:' . $color . ';">' . $tag_name . '</span>';
  }

  $typetags_cache['tags'][$tag_name] = $ret;
  return $ret;
}

/**
 * colors tags on picture page
 */
/*function typetags_picture()
{
  global $template;

  $tags = $template->get_template_vars('related_tags');
  if (empty($tags)) return;

  $query = '
SELECT
    t.id ,
    tt.color
  FROM '.TYPETAGS_TABLE.' AS tt
    INNER JOIN '.TAGS_TABLE.' AS t
      ON t.id_typetags = tt.id
  WHERE t.id_typetags IS NOT NULL
;';
  $tagsColor = simple_hash_from_query($query, 'id', 'color');
  if (empty($tagsColor)) return;

  foreach ($tags as $key => $tag)
  {
    if (isset($tagsColor[ $tag['id'] ]))
    {
      $tags[$key]['URL'].= '" style="color:'.$tagsColor[ $tag['id'] ].';';
    }
  }

  $template->clear_assign('related_tags');
  $template->assign('related_tags', $tags);
}*/

/**
 * colors tags on tags page
 */
function typetags_tags()
{
  global $template, $page, $tags;

  if (empty($tags))
  {
    return;
  }

  $query = '
SELECT
    t.id,
    tt.color
  FROM ' . TYPETAGS_TABLE . ' AS tt
    INNER JOIN ' . TAGS_TABLE . ' AS t
    ON t.id_typetags = tt.id
  WHERE t.id_typetags IS NOT NULL
;';
  $tagsColor = query2array($query, 'id', 'color');

  if (empty($tagsColor))
  {
    return;
  }

  // LETTERS
  if ($page['display_mode'] == 'letters')
  {
    $letters = $template->get_template_vars('letters');

    foreach ($letters as &$letter)
    {
      foreach ($letter['tags'] as &$tag)
      {
        if (isset($tagsColor[ $tag['id'] ]))
        {
          $tag['URL'].= '" style="color:' . $tagsColor[ $tag['id'] ] . ';';
        }
      }
      unset($tag);
    }
    unset($letter);

    $template->assign('letters', $letters);
  }
  // CLOUD
  else if ($page['display_mode'] == 'cloud')
  {
    $tags = $template->get_template_vars('tags');

    foreach ($tags as &$tag)
    {
      if (isset($tagsColor[ $tag['id'] ]))
      {
        $tag['URL'].= '" style="color:' . $tagsColor[ $tag['id'] ] . ';';
      }
    }
    unset($tag);

    $template->assign('tags', $tags);
  }
  // CUMULUS
  else if ($page['display_mode'] == 'cumulus')
  {
    $tags = $template->get_template_vars('tags');

    foreach ($tags as &$tag)
    {
      if (isset($tagsColor[ $tag['id'] ]))
      {
        $tagsColor[ $tag['id'] ] = str_replace('#', '0x', $tagsColor[ $tag['id'] ]);
        $tag['URL'].= '\' color=\'' . $tagsColor[ $tag['id'] ] . '\' hicolor=\'' . $tagsColor[ $tag['id'] ];
      }
    }
    unset($tag);

    $template->assign('tags', $tags);
  }
}

function typetags_escape()
{
  global $template;
  $template->set_prefilter('header', 'typetags_escape_prefilter');
}
function typetags_escape_prefilter($content)
{
  $search = '{$tag.name}';
  $replace = '{$tag.name|strip_tags}';
  return str_replace($search, $replace, $content);
}
