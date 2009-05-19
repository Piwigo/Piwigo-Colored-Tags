<?php

global $page, $template;

$query = '
SELECT  t.id , tt.color
FROM ' . typetags_TABLE . ' AS tt
INNER JOIN ' . TAGS_TABLE . ' AS t
ON  t.id_typetags = tt.id
WHERE  t.id_typetags IS NOT NULL ;';

$result = pwg_query($query);
$tagsColor = array();
while ($row = mysql_fetch_assoc($result))
{
  $tagsColor[$row['id']] = $row['color'];
}
$display = $template->get_template_vars('display_mode');

if ($display == 'letters')
{
  $letters = $template->get_template_vars('letters');
  if (empty($letters)) return;
  $template->clear_assign('letters');

  foreach ($letters as $k1 => $letter)
  {
    foreach ($letter['tags'] as $k2 => $tag)
    {
      if (isset($tagsColor[$tag['id']]))
      {
        $letters[$k1]['tags'][$k2]['URL'] .= '" style="color:' . $tagsColor[$tag['id']];
      }
    }
  }
  $template->assign('letters', $letters);
}
elseif ($display == 'cloud')
{
  $tags = $template->get_template_vars('tags');
  if (empty($tags)) return;
  $template->clear_assign('tags');

  foreach ($tags as $key => $tag)
  {
    if (isset($tagsColor[$tag['id']]))
    {
      $tags[$key]['URL'] .= '" style="color:' . $tagsColor[$tag['id']];
    }
  }
  $template->assign('tags', $tags);
}
elseif ($display == 'cumulus')
{
  $tags = $template->get_template_vars('tags');
  if (empty($tags)) return;
  $template->clear_assign('tags');

  foreach ($tags as $key => $tag)
  {
    if (isset($tagsColor[$tag['id']]))
    {
      $tagsColor[$tag['id']] = str_replace('#', '0x', $tagsColor[$tag['id']]);
      $tags[$key]['URL'] .= '\' color=\'' . $tagsColor[$tag['id']] . '\' hicolor=\'' . $tagsColor[$tag['id']];
    }
  }
  $template->assign('tags', $tags);
}

// Suppression des liens soulignés
$template->block_html_head('', '<style type="text/css">#fullTagCloud a, .tagLetter a { border: none; }</style>', $smarty, $repeat);

?>