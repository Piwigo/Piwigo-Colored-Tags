<?php

/**
 * triggered by 'render_tag_name'
 */
function typetags_render($tag)
{
  global $pwg_loaded_plugins, $page;
  
  if ( defined('IN_ADMIN') and in_array($page['page'], array('photo', 'batch_manager')) )
  {
    return $tag;
  }
  
  $query = '
SELECT color
  FROM '.typetags_TABLE.' AS tt
    INNER JOIN '.TAGS_TABLE.' AS t
      ON t.id_typetags = tt.id
  WHERE t.name = "'.mysql_real_escape_string($tag).'"
;';;
  list($color) = pwg_db_fetch_row(pwg_query($query));
  
  if ($color === null) 
  {
    return $tag;
  }
  elseif (isset($pwg_loaded_plugins['ExtendedDescription']))
  {
    return "[lang=all]<span style='color:".$color.";'>[/lang]".$tag."[lang=all]</span>[/lang]";
  }
  else
  {
    return "<span style='color:".$color.";'>".$tag."</span>";
  }
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
  FROM '.typetags_TABLE.' AS tt
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
  global $template;

  $query = '
SELECT
    t.id ,
    tt.color
  FROM '.typetags_TABLE.' AS tt
    INNER JOIN '.TAGS_TABLE.' AS t
      ON t.id_typetags = tt.id
  WHERE t.id_typetags IS NOT NULL
;';
  $tagsColor = simple_hash_from_query($query, 'id', 'color');
  if (empty($tagsColor)) return;

  $display = $template->get_template_vars('display_mode');
  if ($display == 'letters')
  {
    $letters = $template->get_template_vars('letters');
    if (empty($letters)) return;

    foreach ($letters as $k1 => $letter)
    {
      foreach ($letter['tags'] as $k2 => $tag)
      {
        if (isset($tagsColor[ $tag['id'] ]))
        {
          $letters[$k1]['tags'][$k2]['URL'].= '" style="color:'.$tagsColor[ $tag['id'] ].';';
        }
      }
    }
    
    $template->clear_assign('letters');
    $template->assign('letters', $letters);
  }
  elseif ($display == 'cloud')
  {
    $tags = $template->get_template_vars('tags');
    if (empty($tags)) return;

    foreach ($tags as $key => $tag)
    {
      if (isset($tagsColor[ $tag['id'] ]))
      {
        $tags[$key]['URL'].= '" style="color:'.$tagsColor[ $tag['id'] ].';';
      }
    }
    
    $template->clear_assign('tags');
    $template->assign('tags', $tags);
  }
  elseif ($display == 'cumulus')
  {
    $tags = $template->get_template_vars('tags');
    if (empty($tags)) return;

    foreach ($tags as $key => $tag)
    {
      if (isset($tagsColor[ $tag['id'] ]))
      {
        $tagsColor[ $tag['id'] ] = str_replace('#', '0x', $tagsColor[ $tag['id'] ]);
        $tags[$key]['URL'].= '\' color=\''.$tagsColor[ $tag['id'] ].'\' hicolor=\''.$tagsColor[ $tag['id'] ];
      }
    }
    
    $template->clear_assign('tags');
    $template->assign('tags', $tags);
  }
}

?>