<?php
if (!defined('PHPWG_ROOT_PATH')) die('Hacking attempt!');

load_language('plugin.lang', typetags_PATH);

function get_color_text($color)
{
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

// +-----------------------------------------------------------------------+
// |                                edit typetags                          |
// +-----------------------------------------------------------------------+

if (isset($_POST['edittypetag']) and (empty($_POST['typetag_name']) or empty($_POST['typetag_color'])))
{
	$edited_typetag = array(
		'id' => $_POST['edited_typetag'],
		'name' => $_POST['typetag_name'],
		'color' => $_POST['typetag_color'],
	);
	$page['errors'][] = l10n('typetag_error');
}
else if (isset($_POST['edittypetag']))
{
	$typetag = mysql_escape_string($_POST['edited_typetag']);
	$typetag_name = mysql_escape_string($_POST['typetag_name']);
	$typetag_color = mysql_escape_string($_POST['typetag_color']);

	$all_typetags = pwg_query("
		SELECT 
			id, 
			name, 
			color
		FROM  `". typetags_TABLE ."`;
	");
	
	while ($row = mysql_fetch_array($all_typetags))
	{
		$existing_names[] = $row['name'];
		if ($typetag == $row['id'])
		{
			$current_name = $row['name'];
			$current_color = $row['color'];
		}
	}

	// we must not rename typetag with an already existing name
	if ($typetag_name != $current_name AND in_array($typetag_name, $existing_names))
	{
		$edited_typetag = array(
			'id' => $typetag,
			'name' => $typetag_name,
			'color' => $typetag_color,
		);
		
		$page['errors'][] = l10n('typetag_already_exists');
	}
	else
	{
		pwg_query("
			UPDATE `". typetags_TABLE ."` SET 
				`name` = '". $typetag_name ."',
				`color` = '". $typetag_color ."'
			WHERE `id` = ". $typetag .";
		");
		
		$page['infos'][] = l10n('typetag_saved');
	}
}

// +-----------------------------------------------------------------------+
// |                           delete typetags                             |
// +-----------------------------------------------------------------------+

if (isset($_GET['deletetypetag']))
{
	$query = "
		SELECT name
		FROM `". typetags_TABLE ."`
		WHERE id = ". $_GET['deletetypetag'] .";
	";
	$typetag_name = array_from_query($query, 'name');
	
	if (count($typetag_name) != 0)
	{
		pwg_query("
			UPDATE `". TAGS_TABLE ."`
			SET id_typetags = NULL
			WHERE id_typetags = ". $_GET['deletetypetag'] .";
		");

		pwg_query("
			DELETE FROM `". typetags_TABLE ."`
			WHERE id = ". $_GET['deletetypetag'] .";
		");

		$page['infos'][] = l10n('typetag_suppr').' : '.$typetag_name[0];
	}
	else
	{
		$page['errors'][] = l10n('typetag_unknown').' : '.$_GET['deletetypetag'];
	}
}

// +-----------------------------------------------------------------------+
// |                               add a typetag                           |
// +-----------------------------------------------------------------------+

if (isset($_POST['addtypetag']) and (empty($_POST['typetag_name']) or empty($_POST['typetag_color'])))
{
	$template->assign('typetag', array(
		'NAME' => isset($_POST['typetag_name']) ? $_POST['typetag_name'] : '',
		'COLOR' => isset($_POST['typetag_color']) ? $_POST['typetag_color'] : '',
	));
	
	$page['errors'][] = l10n('typetag_error');
}
else if (isset($_POST['addtypetag']))
{
	$typetag_name = $_POST['typetag_name'];
	$typetag_color = $_POST['typetag_color'];

	// does the tag already exists?
	$query = "
		SELECT id
		FROM `". typetags_TABLE ."`
		WHERE name = '". $typetag_name ."';
	";
	$existing_tags = array_from_query($query, 'id');

	if (count($existing_tags) == 0)
	{
		pwg_query("
			INSERT INTO `". typetags_TABLE ."`(
				`name`,
				`color`
			)
			VALUES(
				'". $typetag_name ."',
				'". $typetag_color ."'
			);
		");

		$page['infos'][] = l10n('typetag_saved');
	}
	else
	{
		$template->assign('typetag', array(
			'NAME' => $typetag_name,
			'COLOR' => $typetag_color,
		));
		
		$page['errors'][] = l10n('typetag_already_exists');
	}
}

// +-----------------------------------------------------------------------+
// |                           Associate Tag to Typetage                   |
// +-----------------------------------------------------------------------+

if (isset($_POST['delete_all_assoc'])) 
{
	pwg_query("UPDATE `". TAGS_TABLE ."` SET id_typetags = NULL;");
	$page['infos'][] = l10n('All associations have been removed');

} 
else if (isset($_POST['associations'])) 
{
		// beautify the parameters array
		$string = preg_replace('#[;]$#', '', $_POST['associations']);
		$associations = array();
		$a = explode(';', $string);
		
		foreach ($a as $s) 
		{
		   $v = explode(':', $s);
		   $associations[ltrim($v[0],'t-')] = ltrim($v[1],'tt-');
		}

		// save associations
		foreach ($associations AS $tag => $typetag) 
		{
			pwg_query("
				UPDATE `". TAGS_TABLE ."`
				SET id_typetags = ". $typetag ."
				WHERE id = ". $tag .";
			");
		}
		
		$page['infos'][] = l10n('typetags_associated');
}


// +-----------------------------------------------------------------------+
// |                             template init                             |
// +-----------------------------------------------------------------------+

$template->set_filenames(array('plugin_admin_content' => dirname(__FILE__) . '/admin/typetags_admin.tpl'));

// Récupère tous les tags
$all_tags = pwg_query("
	SELECT 
		t.id as tagid,
		t.name as tagname, 
		tt.id as typetagid,
		tt.name as typetagname
	FROM `". TAGS_TABLE ."` as t 
	LEFT JOIN `". typetags_TABLE ."` as tt
	ON  t.id_typetags = tt.id
	ORDER BY t.name ASC;
");

while ($row = pwg_db_fetch_assoc($all_tags)) {
	if ($row['typetagname'] == null) $row['typetagid'] = 'NULL';
	$template->append('typetags_association', $row);
}

// Récupère tous les typetags
$all_typetags = pwg_query("SELECT * FROM `". typetags_TABLE ."` ORDER BY `name`;");

while ($row = mysql_fetch_assoc($all_typetags))
{
	$row['color_text'] = get_color_text($row['color']);
	$row['u_edit'] = typetags_ADMIN . '&edittypetag=' . $row['id'];
	$row['u_delete'] = typetags_ADMIN . '&deletetypetag=' . $row['id'];
	
	$template->append('typetags_selection', $row);
}

// formualire d'édition
if (isset($_GET['edittypetag'])) {
	$edited_typetag['id'] = $_GET['edittypetag'];
}

if (isset($edited_typetag['id']))
{
	$template->assign('edited_typetag', $edited_typetag['id']);

	$tag = pwg_query("
		SELECT 
			id,
			name,
			color
		FROM `". typetags_TABLE ."`
		WHERE id = ".$edited_typetag['id'].";
	");
	$row = pwg_db_fetch_assoc($tag);

	$template->assign('typetag', array(
		'ID' => $row['id'],
		'OLD_NAME' => $row['name'],
		'OLD_COLOR' => $row['color'],
		'COLOR_TEXT' => get_color_text($row['color']),
		'NAME' => isset($edited_typetag['name']) ? $edited_typetag['name'] : $row['name'],
		'COLOR'=> isset($edited_typetag['color']) ? $edited_typetag['color'] : $row['color'],
	));
}

// +-----------------------------------------------------------------------+
// |                           sending html code                           |
// +-----------------------------------------------------------------------+

$template->assign('typetags_ADMIN', typetags_ADMIN);
$template->assign_var_from_handle('ADMIN_CONTENT', 'plugin_admin_content');

?>