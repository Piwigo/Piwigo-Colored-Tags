<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

class typetags_maintain extends PluginMaintain
{
  private $default_conf = array(
    'show_all'=>true,
    );

  private $table;

  function __construct($plugin_id)
  {
    parent::__construct($plugin_id);

    global $prefixeTable;
    $this->table = $prefixeTable . 'typetags';
  }

  function install($plugin_version, &$errors=array())
  {
    global $conf;

    if (empty($conf['TypeTags']))
    {
      conf_update_param('TypeTags', $this->default_conf, true);
    }

    $result = pwg_query('SHOW COLUMNS FROM `' . TAGS_TABLE . '` LIKE "id_typetags";');
    if (!pwg_db_num_rows($result))
    {
      pwg_query('ALTER TABLE `' . TAGS_TABLE . '` ADD `id_typetags` SMALLINT(5) DEFAULT NULL;');
    }

    $query = '
CREATE TABLE IF NOT EXISTS `' . $this->table . '` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8
;';
    pwg_query($query);
  }

  function update($old_version, $new_version, &$errors=array())
  {
    $this->install($new_version, $errors);
  }

  function uninstall()
  {
    conf_delete_param('TypeTags');

    pwg_query('ALTER TABLE `' . TAGS_TABLE . '` DROP `id_typetags`');
    pwg_query('DROP TABLE `' . $this->table . '`;');
  }
}
