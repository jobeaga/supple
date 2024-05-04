
// ENTITY FIELD
function entityFieldCreate(name, id, value, field_def, table, record_id){
	var parent = '';
	var row = getFromCache(table, record_id);
	var entity_select_id = '_entity_select_' + id;
	if (field_def.entity_id_field != ''){
		entity_select_id = field_def.entity_id_field;
	}
	// Search for the parent of the selected field
	if (value != '' && metadata._fields[value] != undefined){
		parent = metadata._fields[value].parent;
	} else if (field_def.entity_id_field != '' && this_status["main_body"].pre_filled != undefined && this_status["main_body"].pre_filled[field_def.entity_id_field] != undefined){
		// Special case when pre_filled sets a value for parent field:
		parent = this_status["main_body"].pre_filled[field_def.entity_id_field];
	}
	// Create dropdown entity_select
	extra = '';
	if (parent == '') extra = 'selected=selected';
	$('#'+entity_select_id).append('<OPTION value="" table="" '+extra+'> -- '+ global.lang.LBL_NONE +' -- </OPTION>');
	$.each(sortData(metadata._entities, 'name', false), function (i, rr){
		if (rr['id'] == parent) {
			extra = 'selected=selected';
		} else {
			extra = '';
		}
		$('#'+entity_select_id).append('<OPTION value="' + rr['id'] + '" table="' + rr['table'] + '" '+extra+'>'+ rr['name'] +'</OPTION>');
	});
	// Create dropdown of fields
	extra = '';
	if (value == '') extra = 'selected=selected';
	$('#'+id).append('<OPTION value="" table="" '+extra+'> -- '+ global.lang.LBL_NONE +' -- </OPTION>');
	$.each(sortData(metadata._fields, 'name', false), function (i, rr){
		if (rr['id'] == value) {
			extra = 'selected=selected';
		} else {
			extra = '';
		}
		$('#'+id).append('<OPTION value="' + rr['id'] + '" parent="' + rr['parent'] + '" '+extra+'>'+ rr['name'] +'</OPTION>');
	});
	// Fill hiddens
	if (field_def.field_name_field != ''){
		$('#'+field_def.field_name_field).val(row[field_def.field_name_field]);
	}
	if (field_def.table_name_field != ''){
		$('#'+field_def.table_name_field).val(row[field_def.table_name_field]);
	}
	// Event listeners
	$('#'+entity_select_id).change(function(){
		entityFieldChange(entity_select_id, id);
	});
	entityFieldChange(entity_select_id, id);

	$('#'+id).change(function(){
		saveOptionName(id,field_def.field_name_field);
	});
	saveOptionName(id,field_def.field_name_field);
	setTimeout(function(){ saveOptionName(id,field_def.field_name_field); }, 1000);

	if (field_def.table_name_field != ''){
		$('#'+entity_select_id).change(function(){
			saveOptionAttribute(entity_select_id,field_def.table_name_field, 'table');
		});
		saveOptionAttribute(entity_select_id,field_def.table_name_field, 'table');
	}
}

function entityFieldChange(parent_field, child_field) {
	var parent = $('.record #'+parent_field).val();
	entityFieldChangeFor(parent, child_field);
}

function entityFieldChangeFor(parent, child_field) {
	$('.record #'+child_field+' option').each(function (i, e){ 
		if ($(e).attr('parent') == parent){
			$(e).show();
		} else {
			$(e).hide();
		}
	});
	if ($('.record #'+child_field+' option:selected').attr('parent') != parent){
		$('.record #'+child_field).val('');
	}
}
// SIMPLE DROPDOWN
function createOptions(element_id, list_id, selected){
	var options = searchInArray(metadata._dropdown_options, 'list', list_id);
	options = sortData(options, 'order');
	var r = '';
	r += '<option value=""></option> '; // TODO: option to skip empty value

	var s = '';
	$(options).each(function (i,e){
		if (e.key == selected){
			s = " SELECTED";
		} else {
			s = "";
		}

		r += '<option value="' + e.key + '" ' + s + '>' + e.value + '</option> ';
	});
	document.querySelectorAll('[id="'+element_id+'"]').forEach(function (e){ e.innerHTML = r; });
	// console.log(element_id);
	// $('#'+element_id).html(r);
}

function translateOptions(element_id, list_id, selected){
	var options = searchInArray(metadata._dropdown_options, 'list', list_id);
	options = sortData(options, 'order'); // Converts to Array
	var r = '';
	$(options).each(function (i,e){
		if (e.key == selected){
			r = e.value;
		} 
	});
	$('#'+element_id).html(r);
}

function createRelatedDropdown(element_id, value, related_table, related_field, order_field, filter_from, filter_to, fixed_filter){
	var onchange_handler = function(){
		var extra = '';
		var filter = '';
		if (filter_to != undefined && filter_to != ''){
			if (metadata._fields[filter_to] != undefined){
				var v = '';
				if (document.getElementById(filter_from) != undefined && document.getElementById(filter_from).value != undefined){
					// Obtener valor para el primer filtro
					v = document.getElementById(filter_from).value;
				}
				filter = metadata._fields[filter_to].name + '=' + v;
			}
		}
		if (fixed_filter != undefined && fixed_filter != ''){
			if (filter == ''){
				filter = decodeEntities(fixed_filter);
			} else {
				filter = filter + '&' + decodeEntities(fixed_filter);
			}
		}
		getData(related_table, filter, 0, 10000, order_field, function(data){
			// $('#'+element_id).html('<OPTION value=""> -- '+ global.lang.LBL_NONE+' -- </option>');
			$('#'+element_id).html('<OPTION value=""></option>');
			$.each(data, function (i, row){

				if (row['id'] == value) {
					extra = 'selected=selected';
				} else {
					extra = '';
				}

				$('#'+element_id).append('<OPTION value="' + row['id'] + '" '+extra+'>'+ row[related_field] +'</option>');

			});
			// $('#'+element_id).change();
			const e = new Event("change");
			const element = document.querySelector('#'+element_id);
			element.dispatchEvent(e);
		});
	}

	// Ejecutar una vez el handler ni bien se crea el campo.
	onchange_handler();
	// Y ejecutar también cada vez que el filtro cambie:
	if (filter_to != undefined && filter_to != '' && metadata._fields[filter_to] != undefined && document.getElementById(filter_from) != undefined && document.getElementById(filter_from).value != undefined){
		document.getElementById(filter_from).addEventListener('change', function(){ 
			if (document.getElementById(element_id) != undefined && document.getElementById(element_id).value != undefined && document.getElementById(element_id).value != ''){
				value = document.getElementById(element_id).value;
			}			
			onchange_handler(); 
		});
	}

}
// LAS USAN EntityField, RelationshipDropdown, Field y FlexRelationshipDropdown
function saveOptionName(option_id, hidden_name){
	// WHAT IF NONE SELECTED???
	if (option_id == '' || hidden_name == '') return;
	var value = $('.record #'+option_id+' option:selected').val();
	if (value == ''){
		$('.record #'+hidden_name).val('');
	} else {
		$('.record #'+hidden_name).val($('.record #'+option_id+' option:selected').html());
	}
}

function saveOptionAttribute(option_id, hidden_name, attribute){
	// console.log('saveOptionAttribute: ' + option_id + ' - ' + hidden_name + ' - ' + attribute);
	var value = $('.record #'+option_id+' option:selected').val();
	if (value == ''){
		$('.record #'+hidden_name).val('');
	} else {
		$('.record #'+hidden_name).val($('.record #'+option_id+' option:selected').attr(attribute));
	}
}

function renderRelationshipLinkTo(related_entity, view_id, related_id, table, record_id, field_name, related_name){
	// RelationshipDropdown: fetch the name displayed on the link from cache
	var name = ''; 
	if (related_id == undefined || related_id == '') return name;
	var related_table = metadata['_entities'][related_entity]['table'];
	if (metadata[related_table] != undefined){
		if (related_id != '' && related_id != undefined){
			name = metadata[related_table][related_id][related_name];
		}
	} else {
		if (record_id != ''){
			if (metadata[table] != undefined){
				// Get from cache:
				name = metadata[table][record_id][field_name + '_' + related_name];
			} else if (cache[table] != undefined && cache[table][record_id] != undefined){
				// Get from cache:
				name = cache[table][record_id][field_name + '_' + related_name];
				// console.log(cache[table][record_id]);
			} else {
				// Try to get the name from the table 
				// Does this ever happens?
				var placeholder_id = uniqueId();
				name = '<span id="'+ placeholder_id +'"></span>';
				//console.log(related_table, related_id);
				getRecord(related_table, related_id, function (data){ 
					//console.log(data[related_name]); 
					// console.log(data, related_name);
					while_selector('#'+placeholder_id, function(){
						document.getElementById(placeholder_id).innerHTML = data[related_name];
					});					
				});
			}
		}
	}
//	if (related_id == undefined) return name;
	if (user_has_permission(current_user.id, related_entity) || current_user.isadmin == 1){
		return renderLinkTo(related_entity, view_id, related_id, name);
	} else {
		return name;
	}
	
}

function getBeanFunctionData(table, field_name, record_id, element_id, value){
	if (value === ''){
		executeData(table, [field_name], 'id='+record_id, '', function(data){ 
			setHtml('_bean_function_' + element_id, data[0][field_name]);
			if (document.getElementById(element_id) != null){
				document.getElementById(element_id).value = data[0][field_name];
			}
		});
	}
}

function generateCopyButton(element_id){
	var button_html = '<input type=button value="' + global.lang.LBL_COPY + '" class="button copy_button" onclick="copyToClipboardValue(\''+ element_id + '\')">';
	
	if (document.getElementById(element_id).parentNode.tagName.toUpperCase() == 'A'){
		document.getElementById(element_id).parentNode.parentNode.innerHTML += button_html;
	} else {
		document.getElementById(element_id).parentNode.innerHTML += button_html;
	}
	
}
function copyToClipboardValue(element_id){
	/* Get the text field */
	var copyText = document.getElementById(element_id).value;

	// Create dummy textarea
	const textarea = document.createElement('textarea');
	document.body.appendChild(textarea);
	textarea.value = copyText;

	/* Select the text field */
	textarea.select()
	textarea.setSelectionRange(0, 99999); /* For mobile devices */
  
	/* Copy the text inside the text field */
	document.execCommand("copy");

	// remove textarea
	document.body.removeChild(textarea);
  
	// Notify to the user:
	show_message(global.lang.LBL_COPIED_TO_CLIPBOARD);
	
}

// ORDER DATATYPE:
function order_swap_next(id, fname) {
	var order_next_id;
	order_next_id = $('#_ordernext'+fname+id).val();
	order_swap(id, order_next_id, fname);
}

function order_swap_previous(id, fname) {
	var order_previous_id;
	order_previous_id = $('#_orderprevious'+fname+id).val();
	order_swap(id, order_previous_id, fname);
}

function order_swap(from_id, to_id, fname) {
	var from_value;
	var from_prev;
	var from_next;
	var to_value;
	var to_prev;
	var to_next;
	var from_row;
	var to_row;
	var aux;
	var table;

	// Target out of the screen?
	if ($('#_order'+fname+to_id).length == 0) return false;

	// GET VALUES
	from_value = $('#_order'+fname+from_id).val();
	from_prev = $('#_orderprevious'+fname+from_id).val();
	from_next = $('#_ordernext'+fname+from_id).val();
	
	to_value = $('#_order'+fname+to_id).val();
	to_prev = $('#_orderprevious'+fname+to_id).val();
	to_next = $('#_ordernext'+fname+to_id).val();

	table = $('#_ordertable'+fname+to_id).val();

	// CHANGE AT DB
	save_do(fname+'='+to_value, table, from_id, function (r){
		// Evitar el doble/multiple mensaje de guardado
		$('#message').html('');
	});
	save_do(fname+'='+from_value, table, to_id);

	// CHANGE AT CLIENT: swapped elements
	$('#_order'+fname+from_id).val(to_value); // 
	if (to_prev == from_id){
		$('#_orderprevious'+fname+from_id).val(to_id);
	} else {
		$('#_orderprevious'+fname+from_id).val(to_prev);
	}
	if (to_next == from_id){
		$('#_ordernext'+fname+from_id).val(to_id);
	} else {
		$('#_ordernext'+fname+from_id).val(to_next);
	}
	
	$('#_order'+fname+to_id).val(from_value); //
	if (from_prev == to_id){
		$('#_orderprevious'+fname+to_id).val(from_id);
	} else {
		$('#_orderprevious'+fname+to_id).val(from_prev);
	}
	if (from_next == to_id){
		$('#_ordernext'+fname+to_id).val(from_id);
	} else {
		$('#_ordernext'+fname+to_id).val(from_next);
	}

	// CHANGE AT CLIENT: adjacent elements
	from_prev = $('#_orderprevious'+fname+from_id).val();
	from_next = $('#_ordernext'+fname+from_id).val();
	to_prev = $('#_orderprevious'+fname+to_id).val();
	to_next = $('#_ordernext'+fname+to_id).val();

	if (from_prev != ''){ $('#_ordernext'+fname+from_prev).val(from_id); }
	if (from_next != ''){ $('#_orderprevious'+fname+from_next).val(from_id); }
	if (to_prev != ''){ $('#_ordernext'+fname+to_prev).val(to_id); }
	if (to_next != ''){ $('#_orderprevious'+fname+to_next).val(to_id); }

	// view: order value
	$('#_orderv'+fname+from_id).html(to_value);
	$('#_orderv'+fname+to_id).html(from_value);

	// view: buttons	
	if ($('#_orderprevious'+fname+from_id).val() == ''){
		$('#_orderbutprevious'+fname+from_id).attr('disabled','disabled');
	} else {
		$('#_orderbutprevious'+fname+from_id).removeAttr('disabled');
	}
	
	if ($('#_ordernext'+fname+from_id).val() == ''){
		$('#_orderbutnext'+fname+from_id).attr('disabled','disabled');
	} else {
		$('#_orderbutnext'+fname+from_id).removeAttr('disabled');
	}

	if ($('#_orderprevious'+fname+to_id).val() == ''){
		$('#_orderbutprevious'+fname+to_id).attr('disabled','disabled');
	} else {
		$('#_orderbutprevious'+fname+to_id).removeAttr('disabled');
	}
	
	if ($('#_ordernext'+fname+to_id).val() == ''){
		$('#_orderbutnext'+fname+to_id).attr('disabled','disabled');
	} else {
		$('#_orderbutnext'+fname+to_id).removeAttr('disabled');
	}

	// view: swap records (not cells!)
	frow = topFind('#_order'+fname+from_id, '.record');
	trow = topFind('#_order'+fname+to_id, '.record');

	if (parseInt(to_value) < parseInt(from_value)){
		frow.insertBefore(trow);
	} else {
		trow.insertBefore(frow);
	}

	// view: animate
	// HIDE ORIGINAL: this keeps the space
	frow.css('opacity', 0);
	trow.css('opacity', 0);

	// CREATE COPIES: this shows the ghosts
	from_clone = frow.clone();
	to_clone = trow.clone();
	parent = frow.parent();

	// style
	if (trow.offset().top < frow.offset().top){
		if (parseInt(to_value) < parseInt(from_value)){
			from_clone.css('opacity', '0.5').css('position', 'absolute').offset({top: trow.offset().top + frow.height() - 6});
			to_clone.css('opacity', '0.5').css('position', 'absolute').offset({top: trow.offset().top - 6});
		} else {
			from_clone.css('opacity', '0.5').css('position', 'absolute').offset({top: trow.offset().top - 6});
			to_clone.css('opacity', '0.5').css('position', 'absolute').offset({top: trow.offset().top + frow.height() - 6});
		}
	} else {
		if (parseInt(to_value) < parseInt(from_value)){
			from_clone.css('opacity', '0.5').css('position', 'absolute').offset({top: frow.offset().top + trow.height() - 6});
			to_clone.css('opacity', '0.5').css('position', 'absolute').offset({top: frow.offset().top - 6});
		} else {
			from_clone.css('opacity', '0.5').css('position', 'absolute').offset({top: frow.offset().top - 6});
			to_clone.css('opacity', '0.5').css('position', 'absolute').offset({top: frow.offset().top + trow.height() - 6});
		}
	}

	// append
	parent.append(from_clone);
	parent.append(to_clone);
	
	// AND ANIMATE!!!
	var op1 = '+='+trow.height();
	var op2 = '-='+trow.height();
	if (frow.offset().top < trow.offset().top){
		op1 = '-='+frow.height();
		op2 = '+='+frow.height();
	}

	$(from_clone).animate({
			top: op1,
		}, 300, function(){
			from_clone.remove();
			frow.css('opacity', '1');
		}
	); 
	$(to_clone).animate({
			top: op2,
		}, 300, function(){
			to_clone.remove();
			trow.css('opacity', '1');
		}
	); 

}


// DATATYPE PARAMETERS:
function populateDataTypeParameters(){
	// REMOVE PREVIOUS PARAMETERS
	/* $('form .parameter').each(function (i,e){
		$(e).detach().appendTo('#parameters');
	});*/
	document.querySelectorAll('form .parameter').forEach(function (e){
		e.remove();
	});
	// ADD PARAMETERS OF TYPE
	var type = $('#type').val();
	if (type != ''){
		// OLD!
		/* $('#parameters .parameter[parent='+type+']').each(function (i,e){
			$(e).detach().insertBefore('.post.buttons');
		});*/
		var field_id = document.querySelector('form.record').attributes['record_id'].value;
		var field;
		if (field_id != undefined && field_id != '') 
			field = metadata._fields[field_id];
	

		var post_buttons = document.querySelector('form div.post.buttons');

		var fs = searchInArray(metadata._datatypes_parameters, 'parent', type);
		for (var fi in fs){ 
			f = fs[fi];
			// GENERATE HTML
			var div = document.createElement('div');
					div.className = 'field parameter';
					div.setAttribute('parent', f.parent);
			div.innerHTML = '<label>'+ f.label +'</label><span class="group" id="parameter_'+f.name+'"></span>';
			post_buttons.parentNode.insertBefore(div, post_buttons);
			
		}

		setTimeout(function(){
			for (var fi in fs){ 
				f = fs[fi];
				// VALOR:
				var v = '';
				if (field != undefined){
					if (field[f.name] != undefined){
						v = field[f.name]; // el que ya tenia el campo
					} else {
						v = f.default_value; // el valor por defecto
					}
				}
				// console.log('VALOR:'+v);
				// document.getElementById('parameter_'+f.name).innerHTML = renderBasicSingleFieldFor(f.type, f.name, f.name, 1, v, f, 9);
				$('#parameter_'+f.name).append(renderBasicSingleFieldFor(f.type, f.name, f.name, 1, v, f, 9));
			}
		},1);

	}
}

function getIPinfo(value, field_id){
	var url = 'http://ip-api.com/json/' + value;
	if (value != ''){
		$.get(url, '', function(data){
			var popup_content = data.country + '<br>' + data.regionName + '<br>' + data.city + '<br>ISP: ' + data.isp;
			create_popup(popup_content, field_id);
		}, 'json');
	}
	return false;
}
