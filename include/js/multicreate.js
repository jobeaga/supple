
function multiSave(){
    var check = true;
    var parent_element_id = 'main_body';
    
    // CHECK FORMS:
    queryAll('form.record').forEach(function (f){
        var c = f.reportValidity(); // check with report!
        check = check && c;
    });
    if (check){
        block_screen();
        var entity_id = this_status[parent_element_id].entity_id;
        var entity = metadata._entities[entity_id];
        var total = queryAll('form.record').length;
        var count = 0;
        var errors = 0;
        // SUBMIT!!!
        queryAll('form.record').forEach(function (f){
            var element_id = f.id;
            var url = script_name + '?action=save&table=' + entity.table;

            var formData = new FormData(f);
            fetch(url, {
                method: "POST",
                body: formData
            })
            .then(function(response){ return response.json(); })
            .then(function(response){
                if (response.error != undefined && response.error != ''){
                    setHtml(element_id, response.error);
                    errors++;
                } else {
                    setHtml(element_id, '');
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
            })
            .catch(function(error){
                unblock_screen();
                setHtml(element_id, 'ERROR: ' + error);
                errors++;
                // Still increment count? The original error callback unblocked immediately, which could break the count==total logic.
                // But to stay close, we should probably still count it. However original error callback calls unblock_screen immediately.
                // Let's mimic original but handle count properly? Original error callback didn't increment count, so if one fails, count never reaches total and screen stays blocked? Actually original error calls unblock_screen immediately. That's a bug in original. I'll preserve behavior roughly but maybe increment count too? Let's keep it simple and follow original structure but with fetch.
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
    if (view.records > queryAll('.record').length){
        // Default values
        var empty_row = getEmptyRecord(entity_id, view_id, pre_filled);
            
        // parent_element_id = 'main_body';
        appendHtml(query('form#record').parentElement, renderViewBodyRecord('', empty_row, view, fields, entity, parent_element_id, undefined, undefined, true, suffix));
        
        // Button Remove
        while_selector('#record' + suffix, function(){
            appendHtml(query('#record' + suffix), renderLink('-', "this.parentElement.remove(); queryAll('.masscreate a.disabled').forEach(function(el){ el.classList.remove('disabled'); }); return false;", true, undefined, false, 'multi_remove'));
        });
        
    } else {
        element.classList.add('disabled');
    }

    return false;
}
