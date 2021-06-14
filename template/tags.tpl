{combine_script id="farbtastic" require="jquery" path=$TYPETAGS_PATH|cat:"template/farbtastic/farbtastic.js"}
{combine_css id="farbastic" path=$TYPETAGS_PATH|cat:"template/farbtastic/farbtastic.css"}
{combine_css path=$TYPETAGS_PATH|cat:"template/style.css"}
{combine_script id='typetags' load='footer' require="jquery" path=$TYPETAGS_PATH|cat:'template/tags.js'}

<script>
  {literal}

  const typeOfTags = {};

  {/literal}

  {if $typetags}
    JSON.parse('{json_encode($typetags)}').forEach(el => typeOfTags[el.id] = el);  
  {/if}

  const tagColor = {strip} JSON.parse('{json_encode($tags_color)}');{/strip}

  const str_name_used = "{'This name is already used'|translate|escape:javascript}";
  const str_invalid_color = "{'Invalid color'|translate|escape:javascript}";
  const str_missing_field = "{'You must fill all fields (name and color)'|translate|escape:javascript}";

</script>

<div id="TypetagsOption" class="UserListPopIn" style="display: none;"> {* Use the style of the popin in the group manager*}
  <div id="TypetagsPopin" class="UserListPopInContainer">
    <a class="icon-cancel CloseUserList CloseTypetagsOption"></a>
    <div class="popin-title"> <span class="icon icon-brush"></span> <span class='title'>Colored Tags</span></div>

    <div class="typetags-tags">
      <span class="typetags-tags-container"></span>
    </div>

    <div class="typetags-main-container">
      <div class="typetags-color-choose">
        <p>{'Set tags color'|translate}</p>
        <div class="color-option-container">
          <div class="color-option" data-id="n" title="No Color">
              <input type="radio" id="tag-color-n" name="typetags" value=0 style="display: none;">
              <label for="tag-color-n" style="border-color:black; background: black">
                  <span class="color-sample" style="background:black"><i class="icon-ok" style="color:black"></i></span>
                  <span class="color-name" style="color:white">{'Remove color'|translate}</span>
              </label>
          </div>
        {foreach from=$typetags item=typetag}
          <div class="color-option" data-id={$typetag.id} data-color="{$typetag.color}" title="{$typetag.color}">
              <input type="radio" id="tag-color-{$typetag.id}" value={$typetag.id} name="typetags" style="display: none;">
              <label for="tag-color-{$typetag.id}" style="border-color:{$typetag.color};background:{$typetag.color}">
                  <span class="color-sample" style="background:{$typetag.color}">
                    <i class="icon-ok" style="color:{$typetag.color}"></i>
                  </span>
                  <span class="color-name" style="color:{$typetag.color_text}">{$typetag.name}</span>
              </label>
          </div>
        {/foreach}
        </div>
      </div>

      <div class="typetag-color-create">
        <p>{'Add a new color'|translate}</p>
        <div class="typetag-color-create-container">
          <div id="colorpicker"></div>
          <div class="typetag-color-create-form">
            <div class="typetags-input-container"> {* Use the style of the input in the user manager popin*}
              <p>{'Name'|translate}</p>
              <input type="text" size="18" id="TypetagName">
            </div>
            <div class="typetags-input-container">
              <p>{'Color'|translate}</p>
              <input type="text" id="TypetagColor" size="7" maxlength="7" value="#444444">
            </div>
            <div class="typetags-create-actions">
              <span id="TypetagsCreate" class="typetag-button icon-plus">Créer</span>
              <span class="typetag-error icon-cancel"></span>
              <span class="typetag-message icon-ok">{'Color added'|translate}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="typetags-actions"> {* Use the style of the buttons in the user manager popin*}
      <span id="TypetagsValidate" class="icon-ok typetag-button disabled">{'Set tags color'|translate}<i class="icon-spin6 animate-spin"></i></span> {* Use the style of the button in the user manager popin*}
      <span class="close-button CloseTypetagsOption">{'Close'|translate}</span>
      <span class="typetag-error icon-cancel"></span>
      <span class="typetag-message icon-ok">{'Color saved'|translate}</span>
    </div>
    <span class="typetags-info">{'Go to <a href="%s">plugin page</a> to edit colors'|translate:($ROOT_URL|cat:'admin.php?page=plugin-typetags')}</span>
  </div>
</div>
