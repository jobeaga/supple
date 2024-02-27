
function multiSave(){
    var check = true;
    var parent_element_id = 'main_body';
    
    // CHECK FORMS:
    $('form.record').each(function (i,f){
        var c = f.reportValidity(); // check with report!
        check = check && c;
    });
    if (check){
        block_screen();
        var entity_id = this_status[parent_element_id].entity_id;
        var entity = metadata._entities[entity_id];
        var total = $('form.record').length;
        var count = 0;
        var errors = 0;
        // SUBMIT!!!
        $('form.record').each(function (i,f){
            var element_id = $(f).prop('id');
            var url = script_name + '?action=save&table=' + entity.table;

            $(f).ajaxSubmit({
                method: "POST",
                url: url, 
                success: function (response){
                    if (response.error != undefined && response.error != ''){
                        $('#'.element_id).html(response.error);
                        errors++;
                    } else {
                        $('#'.element_id).html('');
                    }

                    // UPDATE CACHE
                    updateCache(entity.table, response.id, response.record);
                    
                    // EL ULTIMO CIERRA LA PUERTA!
                    count++;
                    if (count == total){
                        unblock_screen();
                        if (errors == 0){
                            // REDIRECT!
                            // console.log('REDIRECT!');
                            loadView(entity_id, 2, '', '', 0);
                        }
                    } 
                },
                error: function (jqXHR, textStatus, errorThrown){
                    unblock_screen();
                    $('#'.element_id).html('ERROR: ' + textStatus + ' SERVER RESPONSE: ' + jqXHR.responseText);
                    errors++;
                },
                dataType: "json"
            });

        });
    }
    console.log(check);
}

function multiAddRecord(element){
    var parent_element_id = 'main_body';
    var entity_id = this_status[parent_element_id].entity_id;
    var view_id = this_status[parent_element_id].view_id;
    var pre_filled = this_status[parent_element_id].pre_filled;
    var suffix = '_' + Date.now();

    var entity = metadata._entities[entity_id];
	var view = metadata._viewdefs[view_id];
	var fields = Object.values(searchInArray(searchInArray(metadata._fields, 'parent', entity_id), 'view'+ view_id, '1')).sort(function (a, b) { return a.order - b.order; });

    // COUNT Limit!
    if (view.records > $('.record').length){
        // Default values
        var empty_row = getEmptyRecord(entity_id, view_id, pre_filled);
            
        // parent_element_id = 'main_body';
        $('form#record').parent().append(renderViewBodyRecord('', empty_row, view, fields, entity, parent_element_id, undefined, undefined, true, suffix));
        
        // Button Remove
        while_selector('#record' + suffix, function(){
            $('#record' + suffix).append(renderLink('-', "jQuery(this).parent().remove(); jQuery('.masscreate a.disabled').removeClass('disabled'); return false;", true, undefined, false, 'multi_remove'));
        });
        
    } else {
        $(element).addClass('disabled');
    }

    return false;
}
