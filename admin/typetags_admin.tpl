{combine_script id="jquery.ui.draggable"}
{combine_script id="jquery.ui.droppable"}
{combine_script id="farbtastic" require="jquery" path=$ROOT_URL|@cat:"plugins/typetags/admin/farbtastic/farbtastic.js"}
{combine_css path=$ROOT_URL|@cat:"plugins/typetags/admin/farbtastic/farbtastic.css"}
{combine_css path=$ROOT_URL|@cat:"plugins/typetags/admin/typetags_style.css"}

{footer_script}{literal}
// set all containers the same size
function equilibrate() {
  var h=0;
  jQuery("#associations ul")
    .css('height', 'auto')
    .each(function() { 
      h = Math.max(h, jQuery(this).height());
    })
    .promise().done(function() { 
      jQuery("#associations ul").css({'height': h+'px'});
    });
    
  //jQuery("#tt-NULL").css('height', 'auto');
}

// generate tag:typetag couples before submit the form
function save_datas(form) {
  var out = '';
  
  jQuery(".tt-container").each(function() {
    var section = jQuery(this).attr('id');
    jQuery("> li", this).each(function() { 
      out += jQuery(this).attr('id') + ':' + section + ';';
    });
  });
  
  jQuery('#assoc-input').val(out);
  submit(form);
}

// colorpicker
jQuery('#colorpicker').farbtastic('#hexval');

// move each tag in it's typetag container
jQuery('ul#tt-NULL li').each(function() {
  var $target = jQuery('ul#' + jQuery(this).attr('data'));
  jQuery(this).appendTo($target).css('float', 'left');
  if ($($target).attr('id') == 'tt-NULL') jQuery(this).css({'display':'inline-block','float':'none'});
});
equilibrate();

// init drag
jQuery("li").draggable({
  revert: "invalid",
  helper: "clone",
  cursor: "move"
});

// init drop
jQuery('.tt-container').droppable({
  accept: "li",
  hoverClass: "active",
  drop: function(event, ui) {
    var $gallery = this;
    ui.draggable.fadeOut(function() {
      jQuery(this).appendTo($gallery).css('float', 'left').css('display','').fadeIn();
      if ($($gallery).attr('id') == 'tt-NULL') jQuery(this).css({'display':'inline-block','float':'none'});
      equilibrate();
    });      
  }
});
{/literal}{/footer_script}

<div class="titrePage">
  <h2>TypeT@gs</h2>
</div>
 
<form action="{$typetags_ADMIN}" method="post" name="form">
  <fieldset>
  {if isset($IN_EDIT)}
    <legend>{'Edit typetag'|@translate}</legend>
    <div class="edit-container">
      <div id="colorpicker"></div>
      <p><b>{'Edited TypeTag'|@translate} : <input type="text" readonly="readonly" size="18" style="background-color:{$typetag.OLD_COLOR};color:{$typetag.COLOR_TEXT};" value="{$typetag.OLD_NAME}"></b></p>
      <p>&nbsp;</p>
      <p>{'New name'|@translate} : <input type="text" size="18" name="typetag_name" value="{$typetag.NAME}"></p>
      <p>{'New color'|@translate} : <input type="text" id="hexval" name="typetag_color" size="7" maxlength="7" value="{$typetag.COLOR}"></p>
      <p>&nbsp;</p>
      <p>
        <input type="hidden" name="edited_typetag" value="{$edited_typetag}">
        <input class="submit" type="submit" name="edittypetag" value="{'Modify'|@translate}">
        <input class="submit" type="submit" name="cancel" value="{'Reset'|@translate}">
      </p>
    </div>
  {else}
    <legend>{'Create a Typetag'|@translate}</legend>
    <div class="edit-container">
      <div id="colorpicker"></div>
      <p>{'New TypeTag'|@translate} : <input type="text" size="18" name="typetag_name" value="{if isset($typetag.NAME)}{$typetag.NAME}{/if}"></p>
      <p>{'Color TypeTag'|@translate} : <input type="text" id="hexval" name="typetag_color" size="7" maxlength="7" value="{if isset($typetag.COLOR)}{$typetag.COLOR}{else}#444444{/if}"></p>
      <p>&nbsp;</p>
      <p>
        <input class="submit" type="submit" name="addtypetag" value="{'Create a Typetag'|@translate}">
      </p>
    </div>
  {/if}
  </fieldset>
</form>

{if !empty($typetags_selection) and !isset($IN_EDIT)}
<form action="{$typetags_ADMIN}" method="post" name="form" onsubmit="save_datas(this);">
  <fieldset>
    <legend>{'Edit and associate TypeTags'|@translate}</legend>
    
    <ul id="tt-NULL" class="tt-container NULL">
      <h5>{'Not associated'|@translate}</h5>
      {foreach from=$typetags_association item=tag}
      <li id="t-{$tag.tagid}" data="tt-{$tag.typetagid}">
        {$tag.tagname}
      </li>
      {/foreach}
    </ul>
    
    <div id="associations">
    {foreach from=$typetags_selection item=typetag}
      <ul id="tt-{$typetag.id}" class="tt-container" style="box-shadow:inset 0 0 5px {$typetag.color};">
        <span class="buttons">
          <a href="{$typetag.u_edit}" title="{'edit'|@translate}"><img src="{$themeconf.icon_dir}/edit.png" class="button" alt="{'edit'|@translate}"></a>
          <a href="{$typetag.u_delete}" title="{'delete'|@translate}" onclick="return confirm('{'Are you sure?'|@translate}');"><img src="{$themeconf.icon_dir}/delete.png" class="button" alt="{'delete'|@translate}"></a>
        </span>
        <h5 style="background-color:{$typetag.color};color:{$typetag.color_text};">{$typetag.name}</h5>
      </ul>
    {/foreach}
    </div>
    
    <div style="clear:both;"></div>
    <p style="margin-top:20px;">
      <input type="hidden" name="associations" id="assoc-input">
      <input class="submit" type="submit" name="associate" value="{'Validate'|@translate}">
      <input class="submit" type="submit" name="delete_all_assoc" value="{'Delete all associations'|@translate}" onclick="return confirm('{'Are you sure?'|@translate}');">
    </p>
  </fieldset>
</form>
{/if}

{if !isset($IN_EDIT)}
<form action="{$typetags_ADMIN}" method="post" name="form">
<fieldset>
  <legend>{'Configuration'|@translate}</legend>
  <b>{'Display colored tags'|@translate}</b>
  <label><input type="radio" name="show_all" value="false" {if not $SHOW_ALL}checked="checked"{/if}> {'Only on tags page'|@translate}</label>
  <label><input type="radio" name="show_all" value="true" {if $SHOW_ALL}checked="checked"{/if}> {'Everywhere'|@translate}</label>
  <p><input class="submit" type="submit" name="save_config" value="{'Submit'|@translate}"></p> 
</fieldset>
</form>
{/if}
