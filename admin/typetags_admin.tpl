{combine_script id="jquery.ui.draggable"}
{combine_script id="jquery.ui.droppable"}
{combine_script id="farbtastic" require="jquery" path=$ROOT_URL|@cat:"plugins/typetags/admin/farbtastic/farbtastic.js"}
{combine_css path=$ROOT_URL|@cat:"plugins/typetags/admin/farbtastic/farbtastic.css"}
{combine_css path=$ROOT_URL|@cat:"plugins/typetags/admin/typetags_style.css"}

{footer_script}
{literal}
	$(document).ready(function(){
		// colorpicker
		$('#colorpicker').farbtastic('#hexval');

		// déplace chaque élément dans la bonne case
		jQuery('ul#tt-NULL li').each(function() {
			var $target = jQuery('ul#' + jQuery(this).attr('title'));
			jQuery(this).appendTo($target).css('float', 'left');
		});
		
		// initialise les déplacement
		jQuery("li").draggable({
			revert: "invalid",
			helper: "clone",
			cursor: "move"
		});

		// initialise le dépôt
		jQuery('.tt-container').droppable({
			accept: "li",
			hoverClass: "active",
			drop: function(event, ui) {
				var $gallery = this;
				ui.draggable.fadeOut(function() {
					jQuery(this).appendTo($gallery).fadeIn();
					equilibrate(); // on rééquilibre les colonnes à chaque déplacement
				});			
			}
		});
		
		// équilibrage des colonnes
		equilibrate();
	});
			
	function equilibrate() {
		jQuery("#associations").each(function(){
			var h=0;
			jQuery("> ul", this).css('height', 'auto')
				.each(function(){ h=Math.max(h,jQuery(this).height()); })
				.css({'height': h+'px'});
		});
	}
	
	// génération des couples tag:typetag avant de valider le formulaire
	function save_datas(form) {
		var out = '';
		
		jQuery(".tt-container").each(function(){
			var section = jQuery(this).attr('id');
			jQuery("> li", this).each(function(){ 
				out += jQuery(this).attr('id') + ':' + section + ';';
			});
		});
		
		jQuery('#assoc-input').val(out);
		submit(form);
	}
{/literal}
{/footer_script}

<div class="titrePage">
	<h2>TypeT@gs</h2>
</div>

<form action="{$typetags_ADMIN}" method="post" name="form">
	<fieldset>
	{if isset($edited_typetag)}
		<legend>{'Edit typetag'|@translate}</legend>
		<input type="hidden" name="edited_typetag" value="{$edited_typetag}" />
		<div class="edit-container">
			<div id="colorpicker" style="float:right;"></div>
			<p><b>{'Edited TypeTag'|@translate} : <input type="text" readonly="readonly" size="18" style="background-color:{$typetag.OLD_COLOR};color:{$typetag.COLOR_TEXT};" value="{$typetag.OLD_NAME}"></b></p>
			<p>&nbsp;</p>
			<p>{'New name'|@translate} : <input type="text" size="18" name="typetag_name" value="{$typetag.NAME}"/></p>
			<p>{'New color'|@translate} : <input type="text" id="hexval" name="typetag_color" size="7" maxlength="7" value="{$typetag.COLOR}"/></p>
			<p>&nbsp;</p>
			<p>
				<input class="submit" type="submit" name="edittypetag" value="{'Modify'|@translate}"/>
				<input class="submit" type="submit" name="cancel" value="{'Reset'|@translate}"/>
			</p>
		</div>
	{else}
		<legend>{'Create a Typetag'|@translate}</legend>
		<div class="edit-container">
			<div id="colorpicker" style="float:right;"></div>
			<p>&nbsp;</p>
			<p>{'New TypeTag'|@translate} : <input type="text" size="18" name="typetag_name" value="{if isset($typetag.NAME)}{$typetag.NAME}{/if}"/></p>
			<p>{'Color TypeTag'|@translate} : <input type="text" id="hexval" name="typetag_color" size="7" maxlength="7" value="{if isset($typetag.COLOR)}{$typetag.COLOR}{else}#444444{/if}"/></p>
			<p>&nbsp;</p>
			<p>
				<input class="submit" type="submit" name="addtypetag" value="{'Create a Typetag'|@translate}"/>
			</p>
		</div>
	{/if}
	</fieldset>
</form>

	{if !empty($typetags_selection)}
	<fieldset>
		<legend>{'TypeTag selection'|@translate}</legend>
		<ul class="tagSelection">
			{foreach from=$typetags_selection item=typetag}
			<li>
				<input type="text" readonly="readonly" style="background-color:{$typetag.color};color:{$typetag.color_text};margin:5px 0;" value="{$typetag.name}">
				<a href="{$typetag.u_edit}" title="{'edit'|@translate}"><img src="{$themeconf.icon_dir}/edit.png" class="button" alt="{'edit'|@translate}"/></a>
				<a href="{$typetag.u_delete}" title="{'delete'|@translate}" onclick="return confirm('{'Are you sure?'|@translate}');"><img src="{$themeconf.admin_icon_dir}/plug_delete.png" class="button" alt="{'delete'|@translate}"/></a>
			</li>
			{/foreach}
		</ul>
	</fieldset>
	{/if}

<form action="{$typetags_ADMIN}" method="post" name="form" onsubmit="save_datas(this);">
	{if !empty($typetags_association) and !empty($typetags_selection)}
	<fieldset>
		<legend>{'TypeTag association'|@translate}</legend>
		
		<ul id="tt-NULL" class="tt-container NULL">
			<h5>Non associés</h5>
			{foreach from=$typetags_association item=tag}
			<li id="t-{$tag.tagid}" title="tt-{$tag.typetagid}">
				{$tag.tagname}
			</li>
			{/foreach}
		</ul>
		
		<div id="associations">
		{foreach from=$typetags_selection item=typetag}
			<ul id="tt-{$typetag.id}" class="tt-container" style="box-shadow:inset 0 0 5px {$typetag.color};">
				<h5 style="background-color:{$typetag.color};color:{$typetag.color_text};">{$typetag.name}</h5>
			</ul>
		{/foreach}
		</div>
		
		<p style="clear:both;">
			<input type="hidden" name="associations" id="assoc-input"/>
			<input class="submit" type="submit" name="associate" value="{'Validate'|@translate}"/>
			<input class="submit" type="submit" name="delete_all_assoc" value="{'Delete all associations'|@translate}" onclick="return confirm('{'Are you sure?'|@translate}');"/>
		</p>
	</fieldset>
	{/if}
</form>
