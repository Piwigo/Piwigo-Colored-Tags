{combine_script id="farbtastic" require="jquery" path=$ROOT_URL|@cat:"plugins/typetags/admin/farbtastic/farbtastic.js"}
{combine_css path=$ROOT_URL|@cat:"plugins/typetags/admin/farbtastic/farbtastic.css"}

{footer_script}
{literal}
  $(document).ready(function(){
    $('#colorpicker').farbtastic('#hexval');
  });
{/literal}
{/footer_script}

<div class="titrePage">
	<h2>TypeT@gs</h2>
</div>

<form action="" method="post" name="form">
  {if isset($edit_tags)}
    <input type="hidden" name="edit_list" value="{$edit_tags}" />
  {/if}

<fieldset>
{if isset($edited_typetag)}
    <legend>{'Edit typetags'|translate}</legend>
    <input type="hidden" name="edited_typetag" value="{$edited_typetag}" />
    
    <div style="width: 500px; margin: auto;">
    <div id="colorpicker" style="float: right;"></div>
      <p><b>{'Edited TypeTag'|translate} : &nbsp;<input type="text" readonly="readonly"  size="20" style="background-color: {$typetag.OLD_COLOR}; color: {$typetag.COLOR_TEXT};" value="{$typetag.OLD_NAME}"></b>
      <p>&nbsp;</p>
      <p>{'New name'|translate}&nbsp;&nbsp;<input type="text" size="20" maxlength="15" name="typetag_name" value="{$typetag.NAME}"/></p>
			<p>{'New color'|translate}&nbsp;&nbsp;<input type="text" id="hexval" name="typetag_color" size="9" maxlength="7" value="{$typetag.COLOR}"/></p>
      <p>&nbsp;</p>
      <p><input class="submit" type="submit" name="submit" value="{'Modify'|translate}"/>
         <input class="submit" type="submit" name="cancel" value="{'Reset'|translate}" /></p>
    </div>
{else}
    <legend>{'Create a Typetag'|translate}</legend>
    <div style="width: 500px; margin: auto;">
    <div id="colorpicker" style="float: right;"></div>
      <p>&nbsp;</p>
      <p>{'New TypeTag'|translate}&nbsp;&nbsp;<input type="text" size="20" maxlength="15" name="add_typetag" value="{if isset($typetag.NAME)}{$typetag.NAME}{/if}"/></p>
			<p>{'ColorTypeTag'|translate}&nbsp;&nbsp;<input type="text" id="hexval" name="hexval" size="9" value="{if isset($typetag.COLOR)}{$typetag.COLOR}{else}#444444{/if}"/></p>
      <p>&nbsp;</p>
      <p><input class="submit" type="submit" name="addtypetag" value="{'Create a Typetag'|translate}"/></p>
    </div>
{/if}
</fieldset>

{if !empty($typetags_selection)}
<fieldset>
    <legend>{'TypeTag selection'|translate}</legend>
    <ul class="tagSelection">
    {foreach from=$typetags_selection item=typetag}
      <li>
        <label><input type="checkbox" name="typetags[]" value="{$typetag.id}" style="margin: 5px 0;"/>&nbsp;
          <input type="text" readonly="readonly" size="18" style="background-color: {$typetag.color}; color: {$typetag.color_text}; margin: 5px 0;" value="{$typetag.name}"></label></li>
    {/foreach}
    </ul>
    <p><br>
     <input class="submit" type="submit" name="edittypetag" value="{'Edit selected typetags'|translate}"/>
     <input class="submit" type="submit" name="deletetypetag" value="{'Delete selected typetags'|translate}" onclick="return confirm('{'Are you sure?'|translate}');"/>
    </p>
</fieldset>
{/if}

{if !empty($typetags_association) and !empty($typetags_selection)}
<fieldset>
    <legend>{'TypeTag association'|translate}</legend>
      <ul class="tagSelection">
      {foreach from=$typetags_association item=tag}
        <li style="margin: 10px 0;">
          <label><input type="checkbox" name="assoc_tags[]" value="{$tag.id}" /><span style="color: {$tag.color}; font-weight: bold;"> {$tag.name}</span></label></li>
      {/foreach}
      </ul>
 	    <p><br>{'existing_typetag_list'|translate}
			  <select class="categoryDropDown" name="typetaglist">
        <option value=""> {'choose_typetag'|@translate}</option>
			  {foreach from=$typetags_selection item=typetag}
		     	<option value="{$typetag.id}"> {$typetag.name}</option>
			  {/foreach}
			  </select>
			  <input class="submit" type="submit" name="associate" value="{'associate'|translate}" onclick="return confirm('{'Are you sure?'|translate}');"/></p>
</fieldset>
{/if}

{if !empty($typetags_dissociation)}
<fieldset>
    <legend>{'TypeTag dissociation'|translate}</legend>
      <ul class="tagSelection">
      {foreach from=$typetags_dissociation item=tag}
        <li style="margin: 10px 0;">
          <label><input type="checkbox" name="dissoc_tags[]" value="{$tag.id}" /><span style="color: {$tag.color}; font-weight: bold;"> {$tag.name}</span></label></li>
      {/foreach}
      </ul>
      <p><br><input class="submit" type="submit" name="dissociate" value="{'dissociate'|translate}"onclick="return confirm('{'Are you sure?'|translate}');"/></p>
</fieldset>
{/if}
</form>
