// init colorpicker
$('#colorpicker').farbtastic('#TypetagColor');

let typetagsHasValidate = false; // if the color has changed, save it for deselect tags when the popin is closed

// Tag selected for the color change
let selectedForTypetags = [];

// change color for the given id list on the page
async function updateTagsColor(tagIds) {
    for (let i = 0; i < tagIds.length; i++) {
        let tagId = tagIds[i];
        await updateTagColor(tagId);
    }
}

// change color of one tag on the page
async function updateTagColor(tagId) {
    let colorSample = $('.tag-box[data-id=' + tagId + '] .typetag-color-sample');

    if (!(tagId in tagColor)) // if the tag just created
        tagColor[tagId] = { id_typetags: null, color: null }; // create an entry in tagColor

    if (tagColor[tagId].id_typetags != null) {
        if (colorSample.length == 0) {
            colorSample = $('<span class="typetag-color-sample tiptip"></span>');
            $('.tag-box[data-id=' + tagId + ']').prepend(colorSample);
        }
        colorSample.prop('title', typeOfTags[tagColor[tagId].id_typetags].name);
        colorSample.css('background', tagColor[tagId].color);
    } else if (colorSample.length = !0) {
        colorSample.remove();
    }
}

// get tag displayed on page
function getIdsOnPage() {return $('.tag-box').map((n, el) => parseInt(el.getAttribute('data-id'))) }

// get tag displayed and selected on page
function getIdsSelectedOnPage() { return $('.tag-box[data-selected=1]').map((n, el) => el.getAttribute('data-id'))}

// show tags color at the load of the page
updateTagsColor(getIdsOnPage());

// when we click on the "color" button, show the popin and display selected tag in it
$('#TypetagsChangeColor').click(() => {
    selectedForTypetags = selected;

    showTypetagsPopin();
});

function showTypetagsPopin() {
    $('#TypetagsOption .typetags-tags-container').html('');
    const container = $('#TypetagsOption .typetags-tags-container');
    const idsOnDisplay = getIdsSelectedOnPage();
    const numberDisplayed = Math.min(idsOnDisplay.length, 10);

    for (let i = 0; i < numberDisplayed; i++) {
        const id = idsOnDisplay[i];
        const name = $('.tag-box[data-id=' + id + '] .tag-name').html();
        const tagNode = $('<span class="typetags-tag"><i class="icon-tags"></i>' + name + '</span>');
        container.append(tagNode);
        if (id in tagColor)
            tagNode.find('i').css('color', tagColor[id].color)
        else // if the tag just created
            tagColor[id] = { id_typetags: null, color: null }; // create an entry in tagColor
    }

    if (selectedForTypetags.length > numberDisplayed) {
        container.append($('<span class="icon-plus-circled">' + (selectedForTypetags.length - numberDisplayed) + '</span>'));
    }

    $('#TypetagsOption').show();
}

// close the popin
$('.CloseTypetagsOption').each((n, elem) => $(elem).click(() => {
    closeTypetagsOption();
}));

// when the user click on a color in the popin, allow him to validate his choice and change colors of tag's icons
$('#TypetagsOption .color-option input').each(
    (n, input) => $(input).bind('change', () => {
        onColorClick();
    }
))

function onColorClick() {
    $('#TypetagsValidate').removeClass('disabled');
    let selectedId = $('.color-option-container input:checked').val();
    let option = $('.color-option[data-id=' + selectedId + ']');
    let style = selectedId == 0 ? '' : 'color:' + option.attr('data-color');
    $('.typetags-tags-container .icon-tags').each((n, icon) => icon.style = style);
}

// when the user validate his choice slice request in max 996 tags information and send it
$('#TypetagsValidate').click(() => {
    if ($('#TypetagsValidate.loading, #TypetagsValidate.disabled').length == 1) return;

    $('#TypetagsValidate').addClass('loading');
    const selectedTypeId = parseInt($('.color-option-container input:checked').val());
    const promises = [];
    const nbSlice = 996;

    // Slice requests in max 1000 array size
    for (let i = 0; i < selectedForTypetags.length; i += nbSlice) {
        promises.push(
            APIUpdateColorsTags(
                selectedForTypetags.slice(i, Math.min(i + nbSlice,selectedForTypetags.length)),
                selectedTypeId
            )
        );
    }

    Promise.all(promises)
    .then(() => {
        $('.typetags-actions .typetag-message').show();
        // change tagColor
        let typeTag = {
            id_typetags: (selectedTypeId == 0) ? null : selectedTypeId,
            color: (selectedTypeId == 0) ? null : typeOfTags[selectedTypeId].color,
        };
        selectedForTypetags.forEach(id => {
            tagColor[id] = typeTag;
        });

        // update new color
        updateTagsColor(getIdsSelectedOnPage())
            .then(() => {
                $('#TypetagsValidate').removeClass('loading');
            })

        typetagsHasValidate = true;
    })
    .catch(e => console.error(e));
});

$('#TypetagsCreate').click(() => {
    APICreateColor(
        $('#TypetagName').val(),
        $('#TypetagColor').val().slice(1,7) // remove the #
    )
    .then(data => {
        if (data.stat == 'ok') {
            $('.typetags-create-actions .typetag-error').hide();
            $('.typetags-create-actions .typetag-message').show();
            addColorOption(data.result)
        } else {
            let str_error = data.message;
            if (data.err == 1002)
                str_error = str_missing_field;
            else if (data.message == 'Invalid color')
                str_error = str_invalid_color;
            else if (data.message == 'This name is already used')
                str_error = str_name_used;

            $('.typetags-create-actions .typetag-message').hide();
            $('.typetags-create-actions .typetag-error').html(str_error);
            $('.typetags-create-actions .typetag-error').show();
        }
    })
    .catch(e => console.error(e));
})

function addColorOption(typetag) {
    const newOption = $('.color-option[data-id=n]').clone();
    newOption.attr('data-id', typetag.id);
    newOption.attr('data-color', typetag.color);
    newOption.attr('title', typetag.color);

    newOption.find('input').bind('change',onColorClick);
    newOption.find('input').attr('value', typetag.id);
    newOption.find('input').attr('id', 'tag-color-' + typetag.id);

    newOption.find('label').attr('for', 'tag-color-' + typetag.id);

    newOption.find('.color-name').html(typetag.name);

    newOption.find('label').css({ 'border-color': typetag.color, 'background': typetag.color });
    newOption.find('.color-sample').css({ 'background': typetag.color });
    newOption.find('.color-sample i').css({ 'color': typetag.color });
    newOption.find('.color-name').css({ 'color': typetag.color_text });
    
    typeOfTags[typetag.id] = typetag;

    $('.color-option-container').append(newOption);
}

// when the popin closes, reset the popin and if the user change a color deselect tags
function closeTypetagsOption() {
    $('#TypetagsOption').hide();
    if ($('.color-option-container input:checked').length != 0)
        $('.color-option-container input:checked')[0].checked = false
    $('#TypetagsValidate').addClass('disabled');
    $('.typetag-error, .typetag-message').hide();
    if (typetagsHasValidate) {
        // clear selection
        clearSelection();
        $(".tag-box").attr("data-selected", '0');
        typetagsHasValidate = false;
        $('.tag-select-message').slideUp();
    }
};

// call the api for change a color
function APIUpdateColorsTags(tagIds, typeId) {
    return new Promise((res, rej) => {
        jQuery.ajax({
            url: 'ws.php?format=json',
            type: 'POST',
            dataType: 'json',
            data: {
                method: 'typetags.tags.setType',
                tag_id: tagIds,
                typetag_id: typeId,
            },
            success: function (data) {
                res(data);
            },
            error: function (message) {
                rej(message);
            }
        })
    })
}

// call the api to create a color 
function APICreateColor(name, color) {
    return new Promise((res, rej) => {
        jQuery.ajax({
            url: 'ws.php?format=json',
            type: 'GET',
            dataType: 'json',
            data: {
                method: 'typetags.type.add',
                typetag_name: name,
                typetag_color: color,
            },
            success: function (data) {
                res(data);
            },
            error: function (message) {
                rej(message);
            }
        })
    })
}


// Here, we will do what's called a pro gamer move : 
// To update the color on page change, we save updatePage in realUpdatePage and redefine updatePage
// Then, in the newly defined updatePage, we call realUpdatePage and wait it's done for update colors (and return a promise)

var realUpdatePage = updatePage;

updatePage = function() {
    return new Promise(res => {
        realUpdatePage()
        .then(async () => {
            await updateTagsColor(getIdsOnPage());
            res()
        })
    })
}