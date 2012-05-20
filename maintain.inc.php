<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

function plugin_install()
{
  global $prefixeTable;

  $query = 'SHOW FULL COLUMNS FROM ' . TAGS_TABLE . ';';
  $result = array_from_query($query, 'Field');
  if (!in_array('id_typetags', $result))
  {
    pwg_query('ALTER TABLE '.TAGS_TABLE.' ADD COLUMN `id_typetags` SMALLINT(5);');
  }

  $result = pwg_query('SHOW TABLES LIKE "' . $prefixeTable .'typetags"');
  if (!mysql_fetch_row($result))
  {
    $query = '
CREATE TABLE `'. $prefixeTable .'typetags` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8
;';
    pwg_query($query);
  }
  
  conf_update_param('TypeTags', serialize(array('show_all'=>true)));
}

function plugin_activate()
{
  global $conf;
  
  if (!isset($conf['TypeTags']))
  {
    conf_update_param('TypeTags', serialize(array('show_all'=>true)));
  }
}

function plugin_uninstall()
{
  global $prefixeTable;

  pwg_query('ALTER TABLE '.TAGS_TABLE.' DROP COLUMN `id_typetags`');
  pwg_query('DROP TABLE '. $prefixeTable .'typetags;');
  pwg_query('DELETE FROM '.CONFIG_TABLE.' WHERE param = "TypeTags" LIMIT 1;');
}

?>