<?php
defined('TYPETAGS_PATH') or die('Hacking attempt!');

function get_color_text($color)
{
  if (empty($color))
  {
    return '';
  }

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

function check_color($hex)
{
  global $page;

  $hex = ltrim($hex, '#');

  if (strlen($hex) == 3)
  {
    $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
  }
  else if (strlen($hex) != 6)
  {
    return false;
  }

  if (!ctype_xdigit($hex))
  {
    return false;
  }

  return '#'.$hex;
}

function get_typetag_id($input)
{
  if (preg_match('#^~~([0-9]+)~~$#', $input, $matches))
  {
    return $matches[1];
  }
  else if (strpos($input, '|') !== false)
  {
    list($color, $name) = explode('|', $input, 2);

    if ( ($color = check_color($color)) === false)
    {
      return false;
    }

    $query = '
SELECT id FROM ' . TYPETAGS_TABLE . '
  WHERE color = "' . $color . '"
  LIMIT 1
;';
    $result = pwg_query($query);

    if (pwg_db_num_rows($result))
    {
      list($tt_id) = pwg_db_fetch_row($result);
      return $tt_id;
    }

    $query = '
INSERT INTO ' . TYPETAGS_TABLE . '(
    name,
    color
  )
  VALUES(
    "' . pwg_db_real_escape_string($name) . '",
    "' . $color . '"
  )
;';
    pwg_query($query);

    return pwg_db_insert_id();
  }
  else
  {
    return false;
  }
}