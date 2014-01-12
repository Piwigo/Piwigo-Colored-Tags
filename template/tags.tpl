{if isset($TYPETAGS_LIST)}
{combine_script id="farbtastic" require="jquery" path=$TYPETAGS_PATH|cat:"template/farbtastic/farbtastic.js"}
{combine_css id="farbastic" path=$TYPETAGS_PATH|cat:"template/farbtastic/farbtastic.css"}
{combine_css path=$TYPETAGS_PATH|cat:"template/style.css"}

{footer_script}
(function($){
  var $error = $('#colorpicker-container .error'),
      $info = $('#colorpicker-container .info'),
      $all_select = $('select[name^=tag_color]');

  var typetags_names = [{strip}
    {foreach from=$typetags item=typetag name=loop}
      {if !$smarty.foreach.loop.first},{/if}
      '{$typetag.name|escape:javascript}'
    {/foreach}
  {/strip}];

  function checkColor(color) {
    if (color[0] == '#') {
      color = color.substr(1);
    }

    if (color.length == 3) {
      color = color[0]+color[0]+color[1]+color[1]+color[2]+color[2];
    }
    else if (color.length != 6 || isNaN(parseInt(color, 16))) {
      return false;
    }

    return '#'+color;
  }

  function duplicateStyle($from, $to) {
    $to.attr('style', $from.attr('style'));
  }


  // init select and first option with an explicit style attribute
  $all_select.find('option:first-child').attr('style', 'color:'+ $all_select.css('color') +';background:'+ $all_select.css('background-color') +';');

  // change select color on change
  $all_select.on('change', function() {
    duplicateStyle($(this).find(':selected'), $(this));
    $('input[name=mode]').prop('checked', false).filter('[value='+ $(this).data('mode') +']').prop('checked', true);
  });

  // init colorpicker
  $('#colorpicker').farbtastic('#hexval');

  // add color
  $('input[name=addtypetag]').on('click', function(e) {
    e.preventDefault();

    $error.hide();
    $info.hide();

    var name = $('input[name=typetag_name]').val(),
        color = $('input[name=typetag_color]').val(),
        color_text = $('input[name=typetag_color]').css('color');

    if (name == '' || color == '') {
      $error.show().html('{'You must fill all fields (name and color)'|translate|escape:javascript}');
    }
    else if (typetags_names.indexOf(name) != -1) {
      $error.show().html('{'This name is already used'|translate|escape:javascript}');
    }
    else if ( (color = checkColor(color)) === false) {
      $error.show().html('{'Invalid color'|translate|escape:javascript}');
    }
    else {
      typetags_names.push(name);

      $('select[name^=tag_color]').append('<option value="'+ color +'|'+ name +'" style="color:'+ color_text +';background:'+ color +';">'+ name +' ('+ color +')</option>');

      $('input[name=typetag_name]').val(''),
      $('input[name=typetag_color]').val('#444444').trigger('keyup');
      $info.show().html('{'Color added'|translate|escape:javascript}');
    }
  });
}(jQuery));
{/footer_script}


<fieldset>
  <legend>{'Set tags color'|translate}</legend>
  <input type="hidden" name="edit_list" value="{$TYPETAGS_LIST}">

  <fieldset id="colorpicker-container">
    <legend>{'Add a new color'|translate}</legend>

    <div id="colorpicker"></div>
    <p>&nbsp;</p>
    <p>{'Name'|translate} : <input type="text" size="18" name="typetag_name"></p>
    <p>{'Color'|translate} : <input type="text" id="hexval" name="typetag_color" size="7" maxlength="7" value="#444444"></p>
    <p>
      <span class="error"></span> <span class="info"></span><br>
      <input class="submit" type="submit" name="addtypetag" value="{'Add'|translate}">
    </p>
  </fieldset>

  <p>
    <label><input type="radio" name="mode" value="global"> {'Apply the same color to all tags'|translate}</label>

    <select name="tag_color-all" style="" data-mode="global">
      <option value="-1" style="" selected>{'None'|translate}</option>
    {foreach from=$typetags item=typetag}
      <option value="~~{$typetag.id}~~" style="color:{$typetag.color_text};background:{$typetag.color};">{$typetag.name} ({$typetag.color})</option>
    {/foreach}
    </select>
  </p>

  <p>
    <label><input type="radio" name="mode" value="unit" checked> {'Set a different color for each tag'|translate}</label>

    <table class="table2" style="margin:0;">
      <tr class="throw">
        <th>{'Tag'|translate}</th>
        <th>{'New color'|translate}</th>
      </tr>
    {foreach from=$tags item=tag}
      <tr>
        <td>{$tag.name}</td>
        <td>
          <select name="tag_color-{$tag.id}" style="color:{$tag.color_text};background:{$tag.color};" data-mode="unit">
            <option value="-1" style="">{'None'|translate}</option>
          {foreach from=$typetags item=typetag}
            <option value="~~{$typetag.id}~~" style="color:{$typetag.color_text};background:{$typetag.color};" {if $tag.id_typetags==$typetag.id}selected{/if}>{$typetag.name} ({$typetag.color})</option>
          {/foreach}
          </select>
        </td>
      </tr>
    {/foreach}
    </table>
  </p>

  <p>
    <input type="submit" name="typetags_submit" value="{'Submit'|translate}">
    <input type="submit" name="typetags_cancel" value="{'Cancel'|translate}">
  </p>
</fieldset>
{/if}
