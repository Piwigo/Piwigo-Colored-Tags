<?php

if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function plugin_install()
{
  global $prefixeTable;

  $query = 'SHOW FULL COLUMNS FROM ' . TAGS_TABLE . ';';
  $result = array_from_query($query, 'Field');
  if (!in_array('id_typetags', $result))
  {
    pwg_query('ALTER TABLE '.TAGS_TABLE.' ADD COLUMN `id_typetags` SMALLINT(5)');
  }

  $result = pwg_query('SHOW TABLES LIKE "' . $prefixeTable .'typetags"');
  if (!mysql_fetch_row($result))
  {
    $q = 'CREATE TABLE '. $prefixeTable .'typetags(
      id smallint(5) unsigned NOT NULL auto_increment,
      name VARCHAR(255) NOT NULL,
      color VARCHAR(255) NOT NULL,
      PRIMARY KEY  (id));';
    pwg_query($q);
  }
}
  
function plugin_uninstall()
{
  global $prefixeTable;

  $q = ' ALTER TABLE '.TAGS_TABLE.' DROP COLUMN `id_typetags`';
  pwg_query( $q );

  $q = ' DROP TABLE '. $prefixeTable .'typetags;';
  pwg_query($q);
}
  
?>