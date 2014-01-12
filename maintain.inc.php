<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

class typetags_maintain extends PluginMaintain
{
  private $installed = false;

  private $default_conf = array(
    'show_all'=>true,
    );

  function install($plugin_version, &$errors=array())
  {
    global $conf, $prefixeTable;

    if (empty($conf['TypeTags']))
    {
      $conf['TypeTags'] = serialize($this->default_conf);
      conf_update_param('TypeTags', $conf['TypeTags']);
    }

    $result = pwg_query('SHOW COLUMNS FROM `' . TAGS_TABLE . '` LIKE "id_typetags";');
    if (!pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `' . TAGS_TABLE . '` ADD `id_typetags` SMALLINT(5) DEFAULT NULL;');
    }

    $query = '
CREATE TABLE IF NOT EXISTS `' . $prefixeTable . 'typetags` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8
;';
    pwg_query($query);

    $this->installed = true;
  }

  function activate($plugin_version, &$errors=array())
  {
    if (!$this->installed)
    {
      $this->install($plugin_version, $errors);
    }
  }

  function deactivate()
  {
  }

  function uninstall()
  {
    global $prefixeTable;

    conf_delete_param('TypeTags');

    pwg_query('ALTER TABLE `' . TAGS_TABLE . '` DROP `id_typetags`');
    pwg_query('DROP TABLE `' . $prefixeTable . 'typetags`;');
  }
}
