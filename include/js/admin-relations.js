
function renderRelationships(entity_id, record_id, row){
	var html = '';
	html = '<div class="subpanels">';
	var rels_a = searchInArray(metadata._relationships, 'id_b', entity_id);
	var rels_b = searchInArray(metadata._relationships, 'id_a', entity_id);
	each(rels_a, function (i,r){
		// Si es una relacion de la misma entidad y lleva el mismo nombre de subpanel de ambos lados, es de equivalencia (se procesa diferente)
		// De lo contrario, se procesa igual que cualquier relacion.
		if ((r.id_a != r.id_b || r.label_a != r.label_b) && r.label_a != ''){
			html += renderRelationship(r.id, record_id, r.id_a, r.rel_table, r.entity_a_field, r.entity_b_field, r.label_a, r.button_a, r.remove_a, r.limit_a, r.selector_a, r.search_a, r.filter_a, r.type, 'id_b', row, false);
		}
	});
	each(rels_b, function (i,r){
		// idem.... IDEM?
		if ((r.id_a != r.id_b || r.label_a != r.label_b) && r.label_b != ''){
			html += renderRelationship(r.id, record_id, r.id_b, r.rel_table, r.entity_b_field, r.entity_a_field, r.label_b, r.button_b, r.remove_b, r.limit_b, r.selector_b, r.search_b, r.filter_b, r.type, 'id_a', row, false);
		}
	});
	// TODO: RELACIONES DE LA MISMA ENTIDAD: Son de equivalencia cuando tienen el mismo label, por ende utilizan solo un subpanel. (deben tener label no vacio)
	each(rels_a, function (i,r){
		// Si es una relacion de la misma entidad y lleva el mismo nombre de subpanel de ambos lados, es de equivalencia (se procesa diferente)
		if ((r.id_a == r.id_b && r.label_a == r.label_b) && r.label_a != ''){
			html += renderRelationship(r.id, record_id, r.id_a, r.rel_table, r.entity_a_field, r.entity_b_field, r.label_a, r.button_a, r.remove_a, r.limit_a, r.selector_a, r.search_a, r.filter_a, r.type, 'id_a', row, true);
		}
	});

	html += '</div>';
	return html;
}



function renderRelationship(rel_id, record_id, r_entity_id, rel_table, field_a, field_b, label, create_button, remove_button, limit, selector, search, filter, type, side, row, same_entity){
	var html = '';
	if (label != ''){
		var rent = metadata._entities[r_entity_id];
		var rfilter = '';
		var rtable = '';
		var is_ent_rel = false;

		// Gather related data
		if (same_entity){
			if (type == 1){
				rfilter = 'id_a=' + record_id + '&' + 'id_b=' + record_id;
				rtable = rel_table;
			} else {
				rfilter = field_a + '=' + encodeURIComponent(row[field_b]) + '&' + field_b + '=' + encodeURIComponent(row[field_a]); 
				rtable = rent.table;
			}
		} else {
			if (type == 1){
				rfilter = side + '=' + record_id;
				rtable = rel_table;
			} else {
				rfilter = field_a + '=' + encodeURIComponent(row[field_b]); 
				rtable = rent.table;
			}
		}

		// Rel with entity? This is when the relationship table has an entity built over it:
		var ent_rel = searchInArray(metadata._entities, 'table', rel_table);
		if (Object.keys(ent_rel).length > 0 && type == 1){
			r_entity_id = ent_rel[Object.keys(ent_rel)[0]].id;
			is_ent_rel = true;
		}

		// DEFINE RENDER FUNCTION
		var render_do = function(){
			renderRelationshipSubpanel(rel_id, record_id, rtable, rfilter, r_entity_id, rel_table, remove_button, selector, filter, limit, type, side);
		}

		// Titulo
		html = '<h2 id="rel_title_'+ rel_id +'">' + label + '</h2>';
		
		// Boton crear
		if (create_button != '' && create_button != undefined){
			var extra = '';
			if (type != 1){
				// Auto-complete
				extra = ", '', '', 0, undefined, undefined, {"+field_a+":'"+ row[field_b] +"'}";
			}
			if (is_ent_rel){
				extra = ", '', '', 0, undefined, undefined, {id_a:'"+ row[field_b] +"'}";
			}

			var onclick = "return loadView('" + r_entity_id + "', '1'" + extra + "); ";

			if (extra == '' && type == 1 && record_id != ''){
				// After successfully create element, that element has to relate to side => record_id,  
				// console.log(side);
				// console.log(record_id);
				// Set Trigger rel save
				onclick = onclick + "this_status['main_body'].rel_save = { side:'"+side+"', record_id:'"+record_id+"', rel_id:'"+rel_id+"' };";
			}
			onclick += "this_status['main_body'].return = {record_id:'" + record_id + "', entity_id:'"+ this_status['main_body'].entity_id +"'};";

			// html += '<input type=button id="relcreate_'+ rel_id + '" value="' + create_button + '" onclick="' + onclick + '">'; 

			var href = script_name + '?entity=' + r_entity_id + '&view=1';
			if (type != 1){
				href += '&' + field_a+'='+ row[field_b];
			}
			if (is_ent_rel){
				href += '&id_a='+ row[field_b];
			}

			html += renderLink(create_button, onclick, true, href, false, '', 'relcreate_'+ rel_id);

		}

		// Dropdown selector
		if (selector == 2 && search != ''){
			html += renderRelationshipDropdownSelector(rel_id, record_id, rel_table, r_entity_id, field_a, field_b, limit, search, filter, type, side, row, render_do);
		}

		// TODO: Search (search is the ID of a field)
		if (selector == 3 && search != ''){
			html += renderRelationshipSearchSelector(rel_id, record_id, rel_table, r_entity_id, field_a, field_b, limit, search, filter, type, side, row, render_do);
		}

		// Subpanel Placeholder
		html += '<span id="relationship_'+ rel_id +'" class="relationship_panel listview"><span class="loader"></span></span>';
		
		// AND RENDER
		render_do();

	}
	return html;
}

function renderRelationshipDropdownSelector(rel_id, record_id, rel_table, r_entity_id, field_a, field_b, limit, search, filter, type, side, row, render_do){
	var rent = metadata._entities[r_entity_id];
	var html = '';
	html += '<div class="panel selector">';
	
	search.forEach(function(search_id, i){
		var search_field = metadata._fields[search_id];
		if (search_field != undefined){
			var input_id = 'relsearch_'+ rel_id + '_' + search_id;
			var button_id = 'relselect_'+ rel_id + '_' + search_id;

			html += search_field.label + ': ';
			html += '<select id="'+ input_id + '"><option value="">'+ global.lang.LBL_NONE + '</option>';
			html += '</select> <input type=button id="'+ button_id +'" value="'+ global.lang.LBL_SELECT +'" class="button-primary search_button"><br>';
			
			// 1. GENERATE DROPDOWN (Asynchronous fill)
			while_selector('#'+ input_id, function(){
				getData(rent.table, filter, 0, 100, search_field.name, function(data){
					var options_html = '';
					data.forEach(function(e){
						options_html += '<option value="'+e['id']+'">'+e[search_field.name]+'</option>';
					});
					appendHtml(input_id, options_html);
				}, true);
			});

			// 2. SELECT BUTTON CLICK HANDLER
			while_selector('#'+ button_id, function(){
				var btn = getEl(button_id);
				if(btn) {
					btn.addEventListener('click', function(){
					// if selected element is not empty relate!
						var selected_id = getEl(input_id).value; 
						var selected_count = document.querySelectorAll('#relationship_'+ rel_id +' .record').length;
						
						var current_count = document.querySelectorAll('#relationship_'+ rel_id +' .record#record'+ selected_id).length;

						if (selected_id != '' && (selected_count < limit || limit == 0 || limit == '') && current_count == 0){
						if (type == 1){
							// use link_do
							if (side == 'id_a'){
								link_do(rel_table, record_id, selected_id, function(){
									// UPDATE SUBPANEL
									render_do();
								});
							} else {
								link_do(rel_table, selected_id, record_id, function(){
									// UPDATE SUBPANEL
									render_do();
								});
							}
						} else {
							// use save_do
							save_do(field_a + '=' + encodeURIComponent(row[field_b]), rent.table, selected_id, function(){
								// UPDATE SUBPANEL
								render_do();
							});
						}
					}
				});
				}
			});
		}
	});
	html += '</div>';
	return html;
}

function renderRelationshipSearchSelector(rel_id, record_id, rel_table, r_entity_id, field_a, field_b, limit, search, filter, type, side, row, render_do){
	var html = '';
	var rent = metadata._entities[r_entity_id];
	html += '<div class="panel selector">';

	each(search, function (i, search_id){
		var search_field = metadata._fields[search_id];
		if (search_field !== undefined){
			var input_id_name = search_field.name + '_' + rel_id;
			html += search_field.label + ': ';
			html += renderBasicSingleField(search_field.id, 1, '', '_' + rel_id, '_' + rel_id);
			
			if (parseInt(i) + 1 == search.length){
				html += ' <input type=button id="relsearch_' + rel_id + '" value="' + global.lang.BTN_SEARCH + '" class="button-primary search_button">';
			} else {
				html += '<br>';
			}
		}	

		// Search callback:
		var search_callback = function (){
			var f = filter || '';

			each(search, function (j, sf_id){
				var sf = metadata._fields[sf_id];
				var iin = sf.name + '_' + rel_id;
				var v = getEl(iin)?.value?.trim() || '';
				if (v !== ''){
					if (f !== '') f = f + '&';
					f = f + sf.name + '=' + encodeURIComponent(v);
				}
			});
			
			getData(rent.table, f, 0, 10, rent.listview_order, function (data){
				// RENDER A LITTLE LIST VIEW
				var result_html = '';
				if (data.length == 0){
					result_html = '<span class="message_text">' + global.lang.LBL_SEARCH_NO_RESULTS + '</span>';
				} else {
					// Listener para el click del elemento seleccionado
					result_html = renderViewBody('search_results_' + rel_id, rent.id, '2', data, undefined, undefined, function (selected_id){
						// SELECT COUNT LIMIT!
						var sf = '';
						var sr = '';
						var st = '';
						if (type == 1){
							sf = side;
							sr = record_id;
							st = rel_table;
						} else {
							sf = field_a;
							sr = encodeURIComponent(row[field_b]);
							st = rent.table;
						}
						getData(st, sf + '=' + sr, 0, limit + 1, '', function (selected_data){
							var selected_count = selected_data.length;
							var is_selected = false;
							each(selected_data, function (i,e){
								if (e.id == selected_id) is_selected = true;
							});
							
							if (selected_id !== '' && (selected_count < limit || limit == 0 || limit == '') && is_selected == false){
								// RELATE!
								if (type == 1){
									// use link_do
									if (side == 'id_a'){
										link_do(rel_table, record_id, selected_id, function(){
											render_do();
										});
									} else {
										link_do(rel_table, selected_id, record_id, function(){
											render_do();
										});
									}
								} else {
									// use save_do
									save_do(field_a + '=' + encodeURIComponent(row[field_b]), rent.table, selected_id, function(){
										render_do();
									});
								}
							}
						});
					});
				}
				setHtml('search_results_' + rel_id, result_html);
			}, true);

		};

		// KEYPRESS ENTER LISTENER — Vanilla JS
		while_selector('#' + input_id_name, function(){
			var el = getEl(input_id_name);
			el?.addEventListener('keypress', function (event){
				if (event.key == 'Enter'){
					search_callback();
				}
			});
		});

		// SEARCH BUTTON LISTENER — Vanilla JS
		while_selector('#relsearch_' + rel_id, function(){
			var btn = getEl('relsearch_' + rel_id);
			btn?.addEventListener('click', search_callback);
		});
	});

	html += '<div id="search_results_' + rel_id + '" class="search_results"></div></div>';

	return html;
}

function renderRelationshipSubpanel(rel_id, record_id, rtable, rfilter, r_entity_id, rel_table, remove_button, selector, filter, limit, type, side) {
    var rent = metadata._entities[r_entity_id];
    getData(rtable, rfilter, 0, 1000, rent.listview_order, function(data, resp_table, resp_count) {
        // console.log(data);
        while_selector('#relationship_'+ rel_id +' .head .label.buttons', function() {
            var buttonsEl = document.querySelector('#relationship_'+ rel_id +' .head .label.buttons');
            if (buttonsEl) {
                buttonsEl.innerHTML = global.lang.LBL_COUNT + ' ' + resp_count;
            }
        });

        // expand-collapse subpanel
        if (parseInt(resp_count) > parseInt(global.config.subpanel_autocollapse_count)) {
            while_selector('#relationship_'+ rel_id, function() {
                getEl('relationship_'+ rel_id, function(rsp) {
                    rsp.classList.add('collapsed_panel');
                    rsp.addEventListener('click', function() {
                        this.classList.remove('collapsed_panel');
                    });
                });
            });

            while_selector('#rel_title_'+ rel_id, function() {
                getEl('rel_title_'+ rel_id, function(rst) {
                    rst.style.cursor = 'pointer';
                    rst.addEventListener('click', function() {
                        getEl('relationship_'+ rel_id, function(rsp) {
                            if (rsp.classList.contains('collapsed_panel')) {
                                rsp.classList.remove('collapsed_panel');
                            } else {
                                rsp.classList.add('collapsed_panel');
                            }
                        });
                    });
                });
            });
        }

        if (selector == 1) {
            // checkbox!
            var checkboxes = [];
            data.forEach(function(row) {
                checkboxes.push(row.id);
            });

            getData(rent.table, filter, 0, 1000, rent.listview_order, function(tdata) {
                var body = renderViewBody('relationship_'+ rel_id, r_entity_id, '2', tdata, undefined, checkboxes, undefined);
                getEl('relationship_'+ rel_id, function(relEl) {
                    relEl.innerHTML = body;
                });

                // ADD LISTENERS TO CHECKBOXES!
                var checkboxInputs = document.querySelectorAll('#relationship_'+ rel_id+' .checkboxes input');
                checkboxInputs.forEach(function(input) {
                    input.addEventListener('click', function(event) {
                        var c = event.target.checked;
                        var rid = event.target.parentElement.parentElement.getAttribute('record_id');
                        var selectedCount = document.querySelectorAll('#relationship_'+ rel_id+' .checkboxes input:checked').length;

                        if (c == true) {
                            if (selectedCount <= limit || limit == 0 || limit == '') {
                                // link!
                                if (side == 'id_a') {
                                    link_do(rel_table, record_id, rid);
                                } else {
                                    link_do(rel_table, rid, record_id);
                                }
                            } else {
                                // uncheck!!!
                                event.target.checked = false;
                            }

                            // DISABLE CREATORS AND SELECTORS
                            if (selectedCount >= limit && limit != 0 && limit != '') {
                                rel_select_create_disable(rel_id, true);
                            }
                        } else {
                            // unlink!
                            if (side == 'id_a') {
                                unlink_do(rel_table, record_id, rid);
                            } else {
                                unlink_do(rel_table, rid, record_id);
                            }

                            // ENABLE CREATORS AND SELECTORS
                            rel_select_create_disable(rel_id, false);
                        }
                    });
                });

                // Adjust Remove button
                renderRelationshipRemoveButton(rel_id, record_id, rel_table, remove_button, selector, type, side);
            }, true);
        } else {
            var body = renderViewBody('relationship_'+ rel_id, r_entity_id, '2', data, undefined, undefined, undefined);
            getEl('relationship_'+ rel_id, function(relEl) {
                relEl.innerHTML = body;
            });

            // Adjust Remove button
            renderRelationshipRemoveButton(rel_id, record_id, rel_table, remove_button, selector, type, side);
        }

        // ADD RETURN TO EDIT BUTTONS ON SUBPANEL
        var editButtons = document.querySelectorAll('#relationship_'+ rel_id +' .button.edit');
        editButtons.forEach(function(button) {
            var oc = button.getAttribute('onclick');
            oc = "this_status['main_body'].return = {record_id: '"+ this_status['main_body'].record_id +"', entity_id: '"+ this_status['main_body'].entity_id+"' }; " + oc;
            button.setAttribute('onclick', oc);
        });

        // ENABLE CREATORS AND SELECTORS
        var is_dis = (data.length >= limit && limit != 0 && limit != '');
        rel_select_create_disable(rel_id, is_dis);
    }, true);
}

function renderRelationshipRemoveButton(rel_id, record_id, rel_table, remove_button, selector, type, side){
	// Adjust remove button for a rendered relationship subpanel:
	// Except when this is the Side A of a dependent relationship.
	if (side == 'id_a' && metadata._relationships[rel_id].trigger_delete == 1) return;
	// REMOVE button label
	if (remove_button != '' && remove_button != undefined){
		// Replace jQuery with vanilla JS
		setHtml('relationship_'+ rel_id + ' a.delete.button', remove_button);
	}
	// REMOVE button: according to type
	if (type == 1){
		if (selector == 1){
			// CHECKBOX SELECTOR does not have unlink button
			// Replace jQuery with vanilla JS
			removeElement('relationship_'+ rel_id + ' a.delete.button');
		} else {
			// REMOVE button only removes the link
			// Replace jQuery with vanilla JS
			queryAll('#relationship_'+ rel_id + ' .record').forEach(function(e){
				var rid = e.getAttribute('record_id');
				var rsel = '#relationship_'+ rel_id + ' #record' + rid;
				var oc = '';
				if (side == 'id_a'){
					oc = "unlink_do('"+rel_table+"', '"+record_id+"', '"+rid+"', '"+rsel+"'); return rel_select_create_disable('" + rel_id + "', false);";
				} else {
					oc = "unlink_do('"+rel_table+"', '"+rid+"', '"+record_id+"', '"+rsel+"'); return rel_select_create_disable('" + rel_id + "', false);";
				}
				// DELETE FROM rel_table WHERE side + '=' + record_id AND the_other = rid
				var href = 'javascript:' + oc;
				// Replace jQuery with vanilla JS
				var deleteButton = query('a.delete.button', e);
				if (deleteButton) {
					deleteButton.setAttribute('href', href);
					deleteButton.setAttribute('onclick', oc);
				}
			});
		}
	}
}

function rel_select_create_disable(rel_id, is_dis){
	// ENABLE CREATORS AND SELECTORS
    //console.log(rel_id + ' ' + is_dis);
    setDisabled('relselect_' + rel_id, is_dis);
    setDisabled('relcreate_' + rel_id, is_dis);
    return false;
}

function link_do(table, id_a, id_b, callback){
	
	var url = script_name + '?action=save&table='+table;
	var data = 'id_a='+id_a+'&id_b='+id_b;

	return ajaxPostGUI(url, data, function(response){
		
		if (response.error != undefined && response.error == '') show_message(global.lang.LBL_SAVE_SUCCESSFUL, 1000);

		if (response.id != undefined){
			// UPDATE CACHE
			if (cache[table] != undefined && cache[table][response.id] != undefined) {
				// delete cache[table][response.id];
				cache[table][response.id] = response.record;
				// DO NOT CACHE _nextid and _previousid
				cache[table][response.id]['_nextid'] = '';
				cache[table][response.id]['_previousid'] = '';
			}

			// IF METADATA, update or create metadata
			if (metadata[table] != undefined) {
				metadata[table][response.id] = response.record;
				// UPDATE MENU
				renderMenu();
				updateMetadataCache();
			}
		} 

	}, true, callback);

}

function unlink_do(table, id_a, id_b, record_selector){
	if (record_selector == undefined || confirm(global.lang.LBL_UNLINK_CONFIRM)){
		
		var url = script_name + '?action=delete&table='+table +'&id_a='+id_a+'&id_b='+id_b;
		
		return ajaxPostGUI(url, '', function(response){

			if (response.error != undefined && response.error == '') show_message(global.lang.LBL_SAVE_SUCCESSFUL, 1000);

			// REDIRECT
			if (response.id != undefined){
				// REMOVE FROM CACHE
				if (cache[table] != undefined && cache[table][response.id] != undefined) {
					delete cache[table][response.id];
				}

				// IF METADATA, update metadata
				if (metadata[table] != undefined) {
					delete metadata[table][response.id];
					// UPDATE MENU
					renderMenu();
					updateMetadataCache();
				}
				
				// UPDATE VIEW
				if (record_selector != undefined){
					query(record_selector)?.remove();
				} 
			} 
		});
	}
	return false;
}