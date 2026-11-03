// --- Control de Autoload en Vistas ---
window.autoloading = window.autoloading !== undefined ? window.autoloading : 0;
window.autoload_enabled = window.autoload_enabled !== undefined ? window.autoload_enabled : 0;
window.autoload_offset = window.autoload_offset !== undefined ? window.autoload_offset : 0;

// VIEW
function loadView(entity_id, view_id, record_id, filter, offset, dont_push_state, callback, pre_filled, parent_element_id, order) {

	if (Object.keys(metadata).length == 0){
		loadMetadata(function(){
			loadView(entity_id, view_id, record_id, filter, offset, dont_push_state, callback, pre_filled, parent_element_id, order);
		});
		return;
	}
	
	if (parent_element_id == undefined) parent_element_id = 'main_body';

	var entity = metadata._entities[entity_id];

	if (offset == undefined) offset = 0;
	//console.log(filter);
	if (filter == undefined) {
		//console.log('UNDEFINED FILTER');
		//console.log(entity.default_filter);
		if (record_id == undefined && entity.default_filter == undefined) filter = '';
		if (record_id == undefined && entity.default_filter != undefined) filter = entity.default_filter;
		if (record_id == undefined && entity.fixed_filter != undefined && entity.fixed_filter != '') {
			if (filter == ''){
				filter = entity.fixed_filter;
			} else {
				filter += '&' + entity.fixed_filter;
			}
		}
		if (record_id != undefined) filter = 'id='+record_id;
	} else {
		if (entity.fixed_filter != undefined && entity.fixed_filter != '') {
			if (filter == ''){
				filter = entity.fixed_filter;
			} else {
				filter += '&' + entity.fixed_filter;
			}
		}
	}
	if (record_id == undefined) record_id = '';
	if (view_id == undefined) return false;
	if (entity_id == undefined) return false;

	showRibbon(entity_id);

	if (view_id != '' || record_id != ''){ 
		// REDIRECT TO...
		if (entity['view' + view_id] == 0){
			if (entity.view2 == 1) {
				view_id = 2;
			} else if (first_id[entity_id] != '' && entity.view4 == 1){
				view_id = 4;
				filter = 'id=' + first_id[entity_id];
				record_id = first_id[entity_id];
			} else if (entity.view1 == 1 && current_user.readonly != 1) {
				view_id = 1;
				filter = 'id=' + first_id[entity_id];
				record_id = first_id[entity_id];
			}
		}
	}

	if (record_id != ''){
		if (entity.view4 == 1){
			addBreadcrumb(entity_id, 4, record_id);
		} else if (entity.view1 == 1){
			addBreadcrumb(entity_id, 1, record_id);
		}
	}

	// PREVIOUS STATE:
	return_status[parent_element_id] = this_status[parent_element_id];

	// PUSH STATE: ADD [parent_element_id] to this_status
	this_status[parent_element_id] = { entity_id: entity_id, view_id: view_id, record_id: record_id, filter: filter, offset: offset, pre_filled:pre_filled, order: order};
	if ((dont_push_state == undefined || dont_push_state == false) && parent_element_id == 'main_body'){
		var url = script_name + '?entity=' + entity_id;
		if (view_id != '') url = url + '&view=' + view_id;
		if (record_id != '') url = url + '&id=' + record_id;
		// if (filter != '') url = url + '&' + filter;
		
		// PRE-FILLED:
		if (pre_filled != undefined)
			$.each(pre_filled, function (f, v){
				if (f != 'entity' && f != 'view' && f != 'id') url = url + '&'+ f +'=' + v;
			});
		history.pushState(this_status[parent_element_id], document.title, url);
		// RETURN FOR RELATIONSHIPS
		if (return_status[parent_element_id] != undefined){
			if (return_status[parent_element_id].entity_id != this_status[parent_element_id].entity_id && return_status[parent_element_id].view_id == 4 && this_status[parent_element_id].view_id == 1){
				this_status[parent_element_id].return = {record_id: return_status[parent_element_id].record_id, entity_id: return_status[parent_element_id].entity_id};
			}
		}
	}

	document.getElementById('menuselector').value = entity_id;

	showRibbon(entity_id);

	if (view_id == '' && record_id == ''){ // OLD ENTITY CUSTOM VIEW!
		var data = [];
		renderView(entity_id, view_id, data, record_id, pre_filled, offset, parent_element_id, 1);
		if (callback != undefined) callback();
		return false;
	}

	var view = metadata._viewdefs[view_id];

	// GETDATA
	// cuando es solo un registro, usar getRecord que tiene cache, puede ser mas eficiente.
	if (view.records == 1)
	{
		//console.log(record_id);
		if (record_id == '') {
			var data = [];
			renderView(entity_id, view_id, data, record_id, pre_filled, offset, parent_element_id, 1);
			if (callback != undefined) callback();
		} else {
			getRecord(entity.table, record_id, function (record){
				var data = [];
				data.push(record);
				renderView(entity_id, view_id, data, record_id, pre_filled, offset, parent_element_id, 1);
				//
				if (callback != undefined) callback();
			});
		}
	} else {
		if (view.fetch_data == 1){
			// De que sirve pre_filled? No sera un filtro?
			/*if (pre_filled != undefined && filter == '') {
				filter = serialize(pre_filled);
			}*/
			// DEFAULT ORDER FOR ENTITY
			if (order == undefined){
				order = entity.listview_order;
			} 
			// DEFAULT FILTER
			getData(entity.table, filter, offset, view.records, order, function (data, table, count){
				// RENDER VIEW
				renderView(entity_id, view_id, data, record_id, pre_filled, offset, parent_element_id, count);
				//
				if (callback != undefined) callback();
			});
		} else {
			renderView(entity_id, view_id, {}, record_id, pre_filled, offset, parent_element_id, 0);
		}
	}

	return false;

}

// RENDER!
function renderView(entity_id, view_id, data, record_id, pre_filled, offset, parent_element_id, record_count){
	/* 
	(if not editable, put edit button and delete button on each record)
	(if is single record, put cancel button)
	(if is not single record and editable, put add button)
	(if autoload then hide nav buttons and activate autoload)
	DONT FORGET:
	Searchform (multilanguage and multivalue???)
	Page!
	BUTTONS:
	- Create button & other buttons
	- To custom views
	IF MULTIPLE: header
	FIELDS:
		IF SIMPLE: Label
		IF EDITABLE: Editable, else Viewable. multiple, multilanguage, link (iffirst), case of first empty value (global.lang.LBL_NO_NAME), 
	- Page again
	- Buttons again
	- AUTOLOAD?
	*/

	if (view_id == ''){
		renderCustomEntity(parent_element_id, entity_id);
		// TODO: y la URL?
		return false;
	}

	if (view_id == 5){
		// console.log(pre_filled);
		renderCustomView(parent_element_id, custom_view_id, entity_id, view_id, record_id, pre_filled);
		return false;
	}

	var entity = metadata._entities[entity_id];
	var view = metadata._viewdefs[view_id];
	var data_keys = Object.keys(data);
	if (data_keys.length > 0) {
		var first_record = data[data_keys[0]];
		var last_record = data[data_keys[data_keys.length - 1]];
	} else {
		var first_record = {};
		var last_record = {};
	}

	editable = entity.view1;
	listable = entity.view2;
	deleteable = entity.view3;
	viewable = entity.view4;

	var html = '<span class="'+ view.class +'">';

	// CHECK PERMISSIONS
	if (current_user.readonly == 1){
		if (view_id == '1' && (record_id != undefined && record_id != '')){
			return renderView(entity_id, '4', data, record_id, pre_filled, offset, parent_element_id, record_count);
		}
		if (view_id == '1' && (record_id == undefined || record_id == '')){
			return renderView(entity_id, '2', data, record_id, pre_filled, offset, parent_element_id, record_count);
		}
		editable = 0; deleteable = 0;
	}

	// ADMIN TOOLS:
	if (current_user.isadmin == 1 && parent_element_id == 'main_body'){
		html +=  '<span class="editarentity">' + renderLinkTo('8', '1', view_id, global.lang.LNK_VIEW_DEF, false, false) + ' ' + renderLinkTo('1', '4', entity_id, global.lang.LNK_ENTITY_DEF, false, false) + '</span>';
	}

	// TITLE
	html +=  '<h1>' + entity.name + '</h1>';

	// SEARCH FORM
	if (view.search == 1){
		html +=  renderSearch(parent_element_id, entity_id);
	}

	// EXTEND VIEWS!
	html +=  renderExtendViews(entity_id, view_id, 0, record_id);

	// NEXT AND PREV BUTTONS
	//console.log(data);
	//console.log(data.length);

	if (view.autoload == 0){
		var prev_id = '';
		var next_id = '';
		var record_offset = offset;
		if (prev_cache[entity.table] != undefined && prev_cache[entity.table][first_record.id] != undefined) {
			prev_id = prev_cache[entity.table][first_record.id];
		}
		if (next_cache[entity.table] != undefined && next_cache[entity.table][last_record.id] != undefined) {
			next_id = next_cache[entity.table][first_record.id];
		}	
		if (data.length == 1){
			record_offset = prev_cache_order(entity.table, first_record.id);
		}
		html +=  renderNavButtons(parent_element_id, record_offset, prev_id, next_id, data.length, 'top');
	}

	html += '<div class="viewbuttons">';

		// COUNT!?
		html += '<span class="record_count">' + global.lang.LBL_COUNT + ': ' + record_count + '</span>';

		// BACK TO PARENT ENTITY:
		var rels = searchInArray(searchInArray(metadata._relationships, 'id_b', entity_id), 'trigger_delete', 1);
		var rel = Object.values(rels);
		if (rel[0] != undefined && rel[0].id_a != undefined && metadata._entities[rel[0].id_a] != undefined){
			var rel_entity = metadata._entities[rel[0].id_a];
			var button_label = global.lang.LBL_LIST_OF;
			if (rel[0].label_a == undefined || rel[0].label_a == ''){
				button_label += rel_entity.name;
			} else {
				button_label += rel[0].label_a;
			}			
			if (rel_entity.view2 == 1){
				// html += renderLinkTo(rel_entity.id, 2, '', button_label, true);
				html += '<a href="admin.php?entity='+ rel_entity.id +'&view=2" onclick="return menugoto(\''+ rel_entity.id +'\');" class="button">'+ button_label +'</a>';
			}
			// 
		}

		// VIEW BUTTONS: OUTSIDE THE FORM???
		if (view.id != menuviewfor(entity_id) && view.records > 1) {
			html +=  renderCancelButton(parent_element_id);
		}
		html +=  renderViewButtons(view_id, entity_id);
		// CUSTOM BUTTONS to CUSTOM VIEWS. OUTSIDE THE FORM???
		html +=  renderCustomButtons(parent_element_id, entity_id, view_id, record_id);
	
	html += '</div>';

	// RENDER VIEW
	html +=  renderViewBody(parent_element_id, entity_id, view_id, data, pre_filled);

	// NEXT AND PREV BUTTONS
	if (view.autoload == 0){
		html +=  renderNavButtons(parent_element_id, record_offset, prev_id, next_id, data.length, 'bottom');
	}

	
	if (view.relationships == 1){
		html +=  renderRelationships(entity_id, record_id, data[Object.keys(data)[0]]);
	}

	// EXTEND VIEWS!
	html +=  renderExtendViews(entity_id, view_id, 1, record_id);

	html +=  '</span>';

	$('#' + parent_element_id).html(html);

	// AND SCROLL TO THE TOP
	window.scrollTo(0,0);

	// AUTOLOAD
	if (view.autoload == 1 && view.fetch_data == 1){
		// ENABLE AUTOLOAD
		// console.log('AUTOLOAD');
		autoload_offset = 0;
		autoload_enabled = 1;
		setTimeout(autoload_next, 500);
	} else {
		// DISABLE AUTOLOAD
		autoload_enabled = 0;
	}
	
}

// AUTOLOAD
function autoload_next(){
    if (autoloading != 0 || autoload_enabled != 1) return;

    autoloading = 1;

    var parent_element_id = 'main_body';

    try {
        var status = this_status[parent_element_id];

        if (status == undefined) {
            autoloading = 0;
            return;
        }

        var entity_id = status.entity_id;
        var view_id = status.view_id;
        var filter = status.filter;
        var order = status.order;

        if (view_id == '' || entity_id == '') {
            autoloading = 0;
            return;
        }

        var entity = metadata._entities[entity_id];
        var view = metadata._viewdefs[view_id];

        if (entity == undefined || view == undefined) {
            autoloading = 0;
            return;
        }

        var fields = Object.values(
            searchInArray(
                searchInArray(metadata._fields, 'parent', entity_id),
                'view' + view_id,
                '1'
            )
        ).sort(function (a, b) {
            return a.order - b.order;
        });

        var next_offset = autoload_offset + parseInt(view.records);

        if (!Number.isFinite(next_offset) ||
            !Number.isFinite(parseInt(view.records)) ||
            parseInt(view.records) <= 0) {
            autoloading = 0;
            return;
        }

        if (order == undefined) {
            order = entity.listview_order;
        }

        getData(entity.table, filter, next_offset, view.records, order, function (data) {
                try {
                    var html = '';

                    $.each(data, function (i, row) {
                        if (document.querySelectorAll(
                            '#' + parent_element_id + ' #' +
                            view.class + 'Form #record' + row.id
                        ).length == 0) {
                            html += renderViewBodyRecord(
                                row.id, row, view, fields,
                                entity, parent_element_id
                            );
                        }
                    });

                    $('#' + parent_element_id + ' #' +
                        view.class + 'Form').append(html);

                    autoload_offset = next_offset;

                    if (data.length < view.records) {
                        autoload_enabled = 0;
						console.log('data.length < view.records');
                    }
                } catch (error) {
                    console.error('Error processing autoload:', error);
                    autoloading = 0;
                } finally {
                    autoloading = 0;
                }
            }
        );

    } catch (error) {
        console.error('Error in autoload_next:', error);
        autoloading = 0;
    }
}

function inicializarScrollAutoload() {
    window.addEventListener('scroll', function() {
        if (window.scrollY + window.innerHeight > document.documentElement.scrollHeight - 120) {
            autoload_next();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarScrollAutoload);
} else {
    inicializarScrollAutoload();
}

 // END AUTOLOAD

function renderViewBody(parent_element_id, entity_id, view_id, data, pre_filled, checkboxes, search_result) {
	var html = '';

	var entity = metadata._entities[entity_id];
	var view = metadata._viewdefs[view_id];
	var fields = Object.values(searchInArray(searchInArray(metadata._fields, 'parent', entity_id), 'view'+ view_id, '1')).sort(function (a, b) { return a.order - b.order; });

	if (view.editable == 0 && editable == 1 && search_result == undefined) {
		html +=  '<form id="'+ view.class +'Form" class="'+ view.class +' form" entity_id="'+ entity_id +'" record_id="'+ data.id +'" action="void.php" autocomplete="off">';
	} else {
		html +=  '<div id="'+ view.class +'Form" class="'+ view.class +' form" entity_id="'+ entity_id +'" record_id="'+ data.id +'">';
	}

	var fl = fields.length; if (fl > 10) fl = 10;

	// LABELS
	html +=  '<div class="head fieldcount'+fl+'">';
	html +=  '<div class="label prev buttons"></div>';
	if (checkboxes != undefined){
		html +=  '<div class="label checkboxes"></div>';
	}
	for (const i in fields){ 
		const f = fields[i];

		if (search_result != undefined && f.type == '21') return;  // Do not show order fields on subpanel search
		let extra = '';
		let next_f = fields[parseInt(i)+1];
		let join_with_next = false;

		if (next_f != undefined && next_f.join_with_previous != undefined && next_f.join_with_previous == 1) join_with_next = true;

		// just in case:
		if (f.join_with_previous == undefined) f.join_with_previous = 0;
		
		if (i > 9) extra = ' above10';
		if (f.join_with_previous == 1){
			html += ' ';
		} else {
			html +=  '<div class="label label'+ i + extra +'" onclick="sortByField(\'' + f.id + '\', \'' + parent_element_id + '\')">';
			html += f.label;
		}
				
		if (!join_with_next){
			html += '</div>';
		}
	}
	if (search_result == undefined) html +=  '<div class="label post buttons"></div>';
	html +=  '</div>';  // END head

	// RECORDS
	//html +=  '<div class="records">';
	var count = 0;
	each(data, function (i, row){
		html +=  renderViewBodyRecord(row.id, row, view, fields, entity, parent_element_id, checkboxes, search_result, undefined, undefined);
		count++;
	});
	// EMPTY RECORDS:
	if (count < view.min_records) {
		var empty_row = getEmptyRecord(entity_id, view_id, pre_filled);
	}
	while (count < view.min_records) {
		html +=  renderViewBodyRecord('', empty_row, view, fields, entity, parent_element_id);
		count++;
	}
	//html +=  '</div>';

	if (view.editable == 0) {
		html +=  '</form>';
		html +=  "<script> $('#" + view.class + "').ajaxForm(); </script>";
	} else {
		html +=  '</div>';
	}

	return html;

}

function getEmptyRecord(entity_id, view_id, pre_filled){
	var fields = Object.values(searchInArray(searchInArray(metadata._fields, 'parent', entity_id), 'view'+ view_id, '1')).sort(function (a, b) { return a.order - b.order; });
	var empty_row = {};
	$.each(fields, function (i,f) {
		// SET pre-filled DATA
		if (pre_filled != undefined && pre_filled[f.name] != undefined) {
			empty_row[f.name] = pre_filled[f.name];
		} else {
			var dv;
			if (f.default_value == undefined){
				dv = '';
			} else {
				dv = f.default_value;
			}
			if (dv.substr(0,2) == '$_'){
				v = dv.substr(2);
				p = v.split('.');
				if (p.length == 2){
					if (global[p[0]] != undefined && global[p[0]][p[1]] != undefined){
						dv = global[p[0]][p[1]];
					}
				} else {
					// TODO: normal variable or supple expression

				}
			} else if (dv.substr(0,1) == '$'){
				// TODO: normal variable or supple expression

			}
			empty_row[f.name] = dv;
		}
	});
	return empty_row;
}

function renderViewBodyRecord(id, row, view, fields, entity, parent_element_id, checkboxes, search_result, form_only, suffix){
	// BUTTONS
	var cancel_button = '';
	if (view.id != menuviewfor(entity.id) && view.records == 1) {
		cancel_button = renderCancelButton(parent_element_id);
	}
	var buttons = '';
	// extra fields button
	if (id != ''){
		buttons += '<input type="button" class="extra_button" value="'+global.lang.LBL_EXTRA_BUTTON+'" onclick="extra_button_click(this, \''+id+'\');">';
	}
	// EDIT AND DELETE BUTTONS:
	if (view.editable == 1 && editable == 1 && search_result == undefined){
		buttons = buttons + '<input class="button-primary" type=submit value="' + global.lang.BTN_SAVE + '">'; // renderLink(global.lang.BTN_SAVE, "return save_form('record"+ id +"')", true, undefined, true);
		buttons = buttons + cancel_button;
	} else {
		if (id != ''){
			if (editable == 1 && search_result == undefined){
				buttons = buttons + renderLinkTo(entity.id, '1', id, global.lang.LNK_EDIT, true, true, 'edit');
			}
			buttons = buttons + cancel_button;
			if (deleteable == 1 && search_result == undefined){
				// buttons = buttons + renderLinkTo(entity.id, '3', id, global.lang.LNK_DELETE, true);
				buttons = buttons + renderLink(global.lang.LNK_DELETE, "return delete_do('"+id+"', '"+entity.table+"', '"+view.id+"', '"+entity.id+"')", true, undefined, undefined, 'delete'); 
			}
		}
	}
	
	// END BUTTONS
	if (suffix == undefined) suffix = '';
	var html = '';
	var fl = fields.length; if (fl > 10) fl = 10;
	if (view.editable == 1 && editable == 1 && search_result == undefined){
		html +=  '<form class="record fieldcount'+fl+'" id="record'+ suffix + id +'" enctype="multipart/form-data" accept-charset="' + global.config.admin_charset + '" entity_id="'+ entity.id +'" record_id="'+ id +'" action="void.php" autocomplete="off">';
	} else {
		html +=  '<div class="record fieldcount'+fl+'" id="record'+ id +'" record_id="'+ id +'"';
		if (view.id == 2){
			// (this, &#039;{{record_id}}&#039;, &#039;{{value}}&#039;, &#039;{{f.name}}&#039; )
			html += ' ondrop="orderDragStop(event, this, \''+ id +'\')" ondragover="orderOnDrag(event, this, \''+ id +'\')" ';
		}
		html += '>';
	}

	if (form_only == undefined) html +=  '<div class="prev buttons">' + buttons +'</div>';

	if (checkboxes != undefined){
		html +=  '<div class="checkboxes">';
		if (checkboxes.includes(id)){
			html +=  '<input type=checkbox checked=checked>';
		} else {
			html +=  '<input type=checkbox>';
		}
		html +=  '</div>';
	}

	// FIELDS
	var field_groups = searchInArray(metadata._field_groups, 'entity_id', entity.id);
	var max_type = 0;
	var max_order = 0;
	each(field_groups, function (i,e){
		max_type = Math.max(e.type, max_type);
		max_order = Math.max(e.order, max_order);
		e.fields_html = undefined;
	});
	field_groups[''] = {
		label:global.lang.LBL_OTHER_FIELDS_GROUP,
		order:max_order + 1,
		type:max_type,
		id:'others'
	};
	if (view.records > 1 && row.id != undefined){
		suffix = suffix + '_' + row.id;
	} 
	
	for (const i in fields){ 
		const f = fields[i];

		if (search_result != undefined && f.type == '21') return; // Do not show order fields on subpanel search
		let field_html = '';
		let f_html = '';
		let extra = '';
		let next_f = fields[parseInt(i)+1];
		let join_with_next = false;

		if (next_f != undefined && next_f.join_with_previous != undefined && next_f.join_with_previous == 1) join_with_next = true;

		// just in case:
		if (f.join_with_previous == undefined) f.join_with_previous = 0;

		const field_editable = ((view.editable == 1 && editable == 1 && search_result == undefined) || (f.access == 'listedit' && view.id == 2));
		
		if (i > 9) extra += ' above10';
		if (f.multiple == 1) extra += ' multiple';
		if (f.required == 1) extra += ' required';

		if (f.join_with_previous == 1){
			// f_html += '<strong>'+ f.label +': </strong>';
		} else { // this field joins with previous
			f_html += '<div class="field field'+ i + extra + '">';
			f_html += '<label>'+ f.label +': </label>';
			f_html += '<span class="group" alt="' + f.label + '" title="' + f.label + '"';
			if (field_editable == false && f.type != '21'){
				f_html += ' onclick="labelClick(this)" onmouseout="labelMoveOut(this)"';
			}
			f_html += '>';
		}

		// FIELD!!!
		field_html =  renderField(f.id, field_editable, row[f.name], suffix, row);

		if (i == 0 && (view.editable == 0 || editable == 0) && view.id != '4') { // first!
			if (search_result == undefined){
				f_html +=  renderLinkTo(entity.id, '4', id, field_html);
			} else {
				var el_id = 'rel_search_'+ parent_element_id +'_' + id;
				f_html +=  '<a id="'+ el_id +'" href="#">' + field_html + '</a>'; 
				while_selector('#'+el_id, function (){
					getEl(el_id, function(el){
						el.addEventListener('click', function(){
						search_result(id);
						return false;
						});
					});					
				});
			}
		} else {
			f_html +=  field_html;
		}

		// joining with next
		if (!join_with_next){
			f_html += '</span></div>';
		}		

		// Add it to the right group:
		var fg = '';
		if (f.field_group != undefined && field_groups[f.field_group] != undefined && view.group_fields == '1'){
			fg = f.field_group;
		} 
		if (field_groups[fg].fields_html == undefined) {
			field_groups[fg].fields_html = '';
		}
		field_groups[fg].fields_html += f_html;
	
	}

	// ORDER GROUPS:
	field_groups = Object.values(field_groups).sort(function (a,b){ return a.order - b.order; });

	// GENERATE TABS
	if (field_groups.length > 1 && view.group_fields == '1' && max_type == 3){
		var ft = true;
		html += '<div id="dashboard_tabs">';
		field_groups.forEach(function (e){
			if (e.fields_html != undefined){
				if (ft == true || e.type == 3){
					var extra = '';
					if (ft) extra = 'selected';
					ft = false;
					html += '<input type="button" class="tab '+ extra + '" value="' + e.label + '" onclick="tab_click(this)" field_group_id="' + e.id + '">';
				}
			}
		});
		html += '</div>';
	}

	// GENERATE HTML FROM field_groups
	if (field_groups.length == 1 || view.group_fields != '1'){
		field_groups.forEach(function (e){
			if (e.fields_html != undefined){
				html += e.fields_html; // NO HEADER, NO EXTRA TAGS
			}
		});
	} else {
		var first_tab = true;
		var second_tab = false;
		var first_panel = true;
		var tab_id = '';
		field_groups.forEach(function (e){
			if (e.fields_html != undefined){
				// HEADER
				var extra = '';
				if (e.type == 2){
					extra = 'collapsed';
				}
				if (e.type == 0){
					extra = 'invisible';
				}
				
				if (e.type == 3 || (first_panel && max_type == 3)){
					tab_id = e.id;
					if (first_tab){
						first_tab = false;
					} else {
						second_tab = true;
					}
					extra = 'tab';
				}
				if (max_type == 3 && second_tab == true){
					extra += ' hiddentab';
				}
				first_panel = false;
				html += '<span class="field_group '+ extra +'" tab_id="' + tab_id + '">';
				html += '<label class="group_title" onclick="group_click(this);">' + e.label + '</label>';
				html += e.fields_html;
				html += '</span>';
			}
		});
	}

	if (form_only == undefined) html +=  '<div class="post buttons">' + buttons +'</div>';

	if (view.editable == 1 && editable == 1 && search_result == undefined){
		html +=  renderExtendViews(entity.id, view.id, 2, id);
		html +=  '</form>';
		html +=  "<script> $('#record" + id + "').ajaxForm(); </script>";
		html +=  "<script> $('#record" + id + "').submit(function (e){ save_form(this, '" + parent_element_id + "'); e.preventDefault(); }); </script>";
	} else {
		html +=  '</div>';
	}
	return html;
}

function labelClick(element){
	if (element.classList.contains('show_title')){
		element.classList.remove('show_title');
	} else {
		element.classList.add('show_title');
	}
	document.querySelectorAll('.show_title').forEach(function (e){ 
		if (e != element) e.classList.remove('show_title'); 
	});
}

function labelMoveOut(element){
	element.classList.remove('show_title');
}

function renderCancelButton(parent_element_id){
	return renderLink(global.lang.BTN_CANCEL, "return nav_cancel('"+parent_element_id+"')", true);
}

function renderLinkTo(entity_id, view_id, id, label, button, primary, css_class, filter, pre_filled, button_id) {
	var f = '';
	var pf = '{}';
	var pf_url = '';
	if (filter != undefined && filter != '') f = filter;
	if (pre_filled != undefined) {
		pf = JSON.stringify(pre_filled).replaceAll('"', "'");
		pf_url = '&' + serialize(pre_filled);
	}

	return renderLink(label, "return loadView('" + entity_id + "', '" + view_id + "', '" + id + "', '" + f + "', 0, false, undefined, "+ pf + ");", button, script_name + '?entity=' + entity_id + '&view=' + view_id + '&id=' + id + pf_url, primary, css_class, button_id);

}

function renderLink(label, onclick, button, href, primary, css_class, button_id) {

	var cls = '';
	var extra = '';
	if (button != undefined && button == true) cls += 'button';
	if (primary != undefined && primary == true) cls += ' button-primary';
	if (css_class != undefined) cls += ' ' + css_class;

	if (href == undefined) {
		href = 'javascript:' + onclick;
	}

	if (button_id != undefined){
		extra += ' id="'+button_id+'" ';
	}

	/*if (removeTags(label).trim() == ''){
		label += global.lang.LBL_EMPTY;
	}*/

	return '<a href="'+ href +'" onclick="' + onclick + '" class="'+ cls +'" '+extra+'>' + label + '</a>';

}

function renderNavButtons(parent_element_id, offset, _previousid, _nextid, count, css_class) {
	var html = '';
	var extra = '';
	
	html +=  '<div class="nav_panel '+ css_class +'">';

	if (_previousid != undefined && _previousid != '') {
		extra = 'class="nav_button" onclick="nav_prev(\''+ parent_element_id + '\', \''+ _previousid + '\')"';
	} else {
		extra = 'class="nav_button disable" onclick="" disabled=disabled';
	}
	html +=  '<input type=button name="nav_prev" value="&ltrif;" '+extra+'>';

	// NUMBERS:
	if (count == 1){
		html +=  ' <span class="nav_label">' + (offset + 1) + '</span> ';
	} else {
		html +=  ' <span class="nav_label">' + (offset + 1) + ' - ' + (parseInt(offset) + parseInt(count))+ '</span> ';
	}

	if (_nextid != undefined && _nextid != '') {
		extra = 'class="nav_button" onclick="nav_next(\''+ parent_element_id + '\', \''+ _nextid + '\')"';
	} else {
		extra = 'class="nav_button disable" onclick="" disabled=disabled';
	}
	html +=  '<input type=button name="nav_next" value="&rtrif;" '+extra+'>';

	html +=  '</div>';

	return html;
}

function renderExtendViews(entity_id, view_id, position, record_id){
	var html = '';
	var extend_views = searchInArray(searchInArray(searchInArray(metadata._extend_views, 'parent', entity_id), 'view', view_id), 'position', position);
	// TODO: SORT

	if (Object.keys(extend_views).length > 0) {
		// START 'extend_views'+ position
		html +=  '<span id="extend_views'+ position +'" class="extend_view">'; 
		// TODO: enclose each extend_view in a single span. For renderParsed place a  inside initially.
		each(extend_views, function (i, extend){
			// START 'extend_view'+ extend.id
			html +=  '<span id="extend_view'+ extend.id +'">'; 
			if (extend.type == 0){
				// HTML
				while_selector('#extend_view'+ extend.id, function (){
					appendHtml('extend_view'+ extend.id, extend.template);
				});
			} else if (extend.type == 1){
				// JAVASCRIPT
				while_selector('#extend_view'+ extend.id, function (){
					appendHtml('extend_view'+ extend.id, '<script>'+ extend.template+'</script>');
				});
			} else if (extend.type == 2){
				html += '<span class="loader"></span>'; // LOADER
				// RENDER SERVER SIDE
				renderParsed('_extend_views', 'template', extend.id, entity_id, view_id, record_id, 'extend_view'+ extend.id, true);
			}
			html +=  '</span>'; // END 'extend_view'+ extend.id
			
		});
		html +=  '</span>'; // END 'extend_views'+ position
	}

	return html;
}

function renderParsed(table, field, template_id, entity_id, view_id, record_id, target_element_id, override, pre_filled, callback){
	var url = script_name + '?action=parse&table=' + table + '&field=' + field + '&template_id=' + template_id;
	var extra_data = 'entity=' + entity_id + '&view=' + view_id + '&id=' + record_id;
	if (pre_filled != undefined){
		for (const k in pre_filled){
			extra_data += '&' + k + '=' + pre_filled[k];
		}
	}
	submitLockStart();

	ajaxPost(url + '&' + extra_data, '', function (response) {
		if (override == undefined){
			$('#'+target_element_id).append(response.parsed);
		} else {
			$('#'+target_element_id).html(response.parsed);
		}
		submitLockEnd();
		if (callback != undefined) callback();
	});

}

function renderViewButtons(view_id, entity_id) {
	var buttons = searchInArray(metadata._viewbuttons, 'view_id', view_id);
	var global_buttons = searchInArray(metadata._viewbuttons, 'view_id', '');
	for(var k in global_buttons) buttons[k]=global_buttons[k];
	var entity = metadata._entities[entity_id];
	var html = '';
	var href = '';
	each(buttons, function (i, button){
		if (button.target_view == '' || entity['view'+button.target_view] == 1){
			if (button.js_code == undefined || button.js_code == ''){
				// loadView(entity_id, button.target_view, '', button.filter);
				href = script_name + '?entity=' + entity_id;
				if (button.target_view != ''){ href += '&view=' + button.target_view; }
				if (button.filter != ''){ href += '&' + button.filter; }
				html +=  renderLink(button.label, 'return nav_viewbutton(\''+ button.id +'\', \''+ entity_id +'\')', true, href, button.primary, 'viewbutton' + button.id, 'viewbutton' + button.id);
			} else if (button.js_code != undefined) {
				html +=  renderLink(button.label, button.js_code, true, undefined, button.primary, 'viewbutton' + button.id, 'viewbutton' + button.id);
			}
		}
	});
	return html;
}

function renderCustomButtons(parent_element_id, entity_id, view_id, record_id) {
	var buttons = searchInArray(searchInArray(metadata._custom_views, 'parent', entity_id), 'view', view_id);

	var html = '';
	$.each(buttons, function (but_id, button){
		html +=  renderLink(button.name, 'return nav_customviewbutton(\''+ parent_element_id +'\', \''+ but_id +'\', \''+ entity_id +'\', \''+ view_id +'\', \''+ record_id +'\')', true, script_name + '?entity='+entity_id+'&view=5&custom_view_id='+but_id, false, 'customviewbutton' + button.id);
	});
	return html;

}

function renderCustomView(parent_element_id, custom_view_id, entity_id, view_id, record_id, pre_filled) {

	var custom_view = metadata._custom_views[custom_view_id];

	// REDIRECTION?
	if (custom_view.type == 2 || custom_view.type == 3){
		var url = custom_view.template;
		if (custom_view.type == 3){
			url = url + record_id;
		}
		// REDIRECT TO URL
		getUrl(url);

	} else {

		showRibbon(entity_id);

		var html = '';

		// PREVIOUS STATE:
		return_status[parent_element_id] = this_status[parent_element_id];
		// THIS STATUS:
		this_status[parent_element_id] = { entity_id: entity_id, view_id: view_id, record_id: record_id, filter: '', offset: 0, pre_filled:{'custom_view_id':custom_view_id}};
		var url = script_name + '?entity=' + entity_id + '&view=5&custom_view_id=' + custom_view_id;
		if (record_id != '') url = url + '&id=' + record_id;
		// ADD pre-filled
		for (const p in pre_filled){
			if (p!='custom_view_id'){
				url += '&'+p+'='+encodeURIComponent(pre_filled[p]);
				this_status[parent_element_id].pre_filled[p] = pre_filled[p];
			}
		}
		// PUSH!
		history.pushState(this_status[parent_element_id], document.title, url);
		
		// ADMIN TOOLS:
		if (current_user.isadmin == 1){
			html +=  '<span class="editarentity">' + renderLinkTo('19', '1', custom_view_id, global.lang.LNK_VIEW_DEF, false, false) + ' ' + renderLinkTo('1', '4', entity_id, global.lang.LNK_ENTITY_DEF, false, false) + '</span>';
		}

		

		if (custom_view.type == 0){
			// OUTPUT RAW HTML
			html += custom_view.template;
		} else {
			// Placeholder for parsed content
			html += '<span id="'+ parent_element_id + '_custom_view"><span class="loader"></span></span>';
		}

		$('#'+parent_element_id).html(html);

		if (custom_view.type == 1){
			// PARSE TEMPLATE
			renderParsed('_custom_views', 'template', custom_view_id, entity_id, view_id, record_id, parent_element_id + '_custom_view', true, pre_filled);
		}

	}

	return false;

}

function renderCustomEntity(parent_element_id, entity_id){
	var html = '';
	// ADMIN TOOLS:
	if (current_user.isadmin == 1){
		html +=  '<span class="editarentity">' + renderLinkTo('1', '4', entity_id, global.lang.LNK_ENTITY_DEF, false, false) + '</span>';
	}
	// Placeholder for parsed content
	html += '<span id="'+ parent_element_id + '_custom_view"><span class="loader"></span></span>';
	$('#'+parent_element_id).html(html);
	
	if (metadata._entities[entity_id].template.indexOf('$') >= 0){
		// PARSE TEMPLATE
		renderParsed('_entities', 'template', entity_id, entity_id, '', '', parent_element_id + '_custom_view', true);
	} else {
		$('#'+parent_element_id + '_custom_view').html(metadata._entities[entity_id].template);
	}
}

function group_click(e){
	if (e.parentElement.classList.contains('collapsed')){
		e.parentElement.classList.remove('collapsed');
	} else if (e.parentElement.classList.contains('tab')){

	} else {
		e.parentElement.classList.add('collapsed');
	}
}

function tab_click(e){
	var tab_id = e.attributes['field_group_id'].value;
	document.querySelectorAll('.tab').forEach(function (t){
		t.classList.remove('selected');
	});
	e.classList.add('selected');
	document.querySelectorAll('.field_group').forEach(function (p){
		if (p.attributes['tab_id'].value == tab_id){
			p.classList.remove('hiddentab');
		} else {
			p.classList.add('hiddentab');
		}		
	});
}

// LISTVIEW IMPROVEMENTS:
function getUrlAjaxLV(url, post_data, loading_text){
	
	var method = 'GET';
	if(typeof(post_data) != 'undefined') {
		method = 'POST';
	} else {
		post_data = '';
	}

	setHtml('_load_more_span', loading_text);

	var options = { method: method };
	if (method === 'POST') {
		options.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
		options.body = post_data;
	}

	fetch(url, options)
		.then(function(response){ return response.text(); })
		.then(function(data){
		    setHtml('_load_more_span', '');
			setHtml('_load_more_result', data);

			var table = document.querySelector('#listViewForm table');
			var result = document.getElementById('_load_more_result');
			var span = document.getElementById('_load_more_span');

			if (result && table) {
				var rows = result.querySelectorAll('tr');
				rows.forEach(function(row){
					table.appendChild(row);
				});

				var btn = result.querySelector('#_load_more_button');
				if (btn && span) span.appendChild(btn);
			}

			setHtml('_load_more_result', '');
		});

}

function listViewToggleSelectAll(){
	var selectAll = document.getElementById('select_all');
	var val = selectAll ? selectAll.checked : false;
	document.querySelectorAll('input[name="id[]"]').forEach(function(input){
		input.checked = val;
	});
	listViewEnableButtons()
}

function listViewEnableButtons(){
	var val = (document.querySelectorAll('input[name="id[]"]:checked').length == 0);
	document.querySelectorAll('input[name=edit_button]').forEach(function(btn){
		btn.disabled = val;
	});
	document.querySelectorAll('input[name=delete_button]').forEach(function(btn){
		btn.disabled = val;
	});
}

// TEMP: to deprecate
function addConfigRecord () {
  var v = getValue('_count'); 

  appendHtml('configrecords', '<div class="view row" id=newrecord' + v + '><div class="view three columns"><input type="text" id=label' + v + ' onchange="setAttr(\'value' + v + '\', \'name\', this.value)" style=width:140px></div><div class="view nine columns"><input type="text" id=value'+v+' name="" style="vertical-align:top"> <input type=button onclick="setHtml(\'newrecord' + v + '\', \'\')" value=Remove class="small button"></div></div>'); 

  setValue(parseInt(v) + 1, '_count');
}