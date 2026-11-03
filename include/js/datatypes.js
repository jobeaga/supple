
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
	getEl(entity_select_id, function(el) {
		el.innerHTML = '<OPTION value="" table="" '+extra+'> -- '+ global.lang.LBL_NONE +' -- </OPTION>';
	});
	each(sortData(metadata._entities, 'name', false), function (i, rr){
		if (rr['id'] == parent) {
			extra = 'selected=selected';
		} else {
			extra = '';
		}
		getEl(entity_select_id, function(el) {
			el.innerHTML += '<OPTION value="' + rr['id'] + '" table="' + rr['table'] + '" '+extra+'>'+ rr['name'] +'</OPTION>';
		});
	});
	// Create dropdown of fields
	extra = '';
	if (value == '') extra = 'selected=selected';
	getEl(id, function(el) {
		el.innerHTML = '<OPTION value="" table="" '+extra+'> -- '+ global.lang.LBL_NONE +' -- </OPTION>';
	});
	each(sortData(metadata._fields, 'name', false), function (i, rr){
		if (rr['id'] == value) {
			extra = 'selected=selected';
		} else {
			extra = '';
		}
		getEl(id, function(el) {
			el.innerHTML += '<OPTION value="' + rr['id'] + '" parent="' + rr['parent'] + '" '+extra+'>'+ rr['name'] +'</OPTION>';
		});
	});
	// Fill hiddens
	if (field_def.field_name_field != ''){
		getEl(field_def.field_name_field, function(el) {
			el.value = row[field_def.field_name_field];
		});
	}
	if (field_def.table_name_field != ''){
		getEl(field_def.table_name_field, function(el) {
			el.value = row[field_def.table_name_field];
		});
	}
	// Event listeners
	getEl(entity_select_id, function(el) {
		el.addEventListener('change', function(){
		entityFieldChange(entity_select_id, id);
		});
	});
	entityFieldChange(entity_select_id, id);

	getEl(id, function(el) {
		el.addEventListener('change', function(){
		saveOptionName(id,field_def.field_name_field);
		});
	});
	saveOptionName(id,field_def.field_name_field);
	setTimeout(function(){ saveOptionName(id,field_def.field_name_field); }, 1000);

	if (field_def.table_name_field != ''){
		getEl(entity_select_id, function(el) {
			el.addEventListener('change', function(){
			saveOptionAttribute(entity_select_id,field_def.table_name_field, 'table');
			});
		});
		saveOptionAttribute(entity_select_id,field_def.table_name_field, 'table');
	}
}

function entityFieldChange(parent_field, child_field) {
	var parent = query('.record #' + parent_field).value;
	entityFieldChangeFor(parent, child_field);
}

function entityFieldChangeFor(parent, child_field) {
	queryAll('.record #' + child_field + ' option').forEach(function (e){
		if (e.getAttribute('parent') == parent){
			e.style.display = '';
		} else {
			e.style.display = 'none';
		}
	});
	if (query('.record #' + child_field + ' option:selected').getAttribute('parent') != parent){
		query('.record #' + child_field).value = '';
	}
}
// SIMPLE DROPDOWN
function createOptions(element_id, list_id, selected){
	var options = searchInArray(metadata._dropdown_options, 'list', list_id);
	options = sortData(options, 'order');
	var r = '';
	r += '<option value=""></option> '; // TODO: option to skip empty value

	var s = '';
	options.forEach(function (e){
		if (e.key == selected){
			s = " SELECTED";
		} else {
			s = "";
		}

		r += '<option value="' + e.key + '" ' + s + '>' + e.value + '</option> ';
	});
	queryAll('[id="'+element_id+'"]').forEach(function (e){
		e.innerHTML = r;
	});
	// console.log(element_id);
	// getEl(element_id)?.innerHTML = r;
}

function translateOptions(element_id, list_id, selected){
	getEl(element_id, function(el) {
		el.innerHTML = translateValue(list_id, selected);
	});
}

function translateValueFromList(list_name, value){
	var lists = searchInArray(metadata._dropdown_lists, 'name', list_name);
	for (const l in lists){ 
		return translateValue(lists[l].id, value);
	}
	return '';
}

function translateValue(list_id, value){
	var options = searchInArray(metadata._dropdown_options, 'list', list_id);
	options = sortData(options, 'order'); // Converts to Array
	var r = '';
	options.forEach(function(e){
		if (e.key == value){
			r = e.value;
		} 
	});
	return r;
}

function createRelatedDropdown(element_id, value, related_table, related_field, order_field, filter_from, filter_to, fixed_filter){
	var onchange_handler = function(){
		var extra = '';
		var filter = '';
		if (filter_to != undefined && filter_to != ''){
			if (metadata._fields[filter_to] != undefined){
				var v = '';
				getEl(filter_from, function (el){
					if  (el.value != undefined){
						v = el.value;
					}					
				});
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
			// query('#'+element_id).innerHTML = '<OPTION value=""> -- '+ global.lang.LBL_NONE+' -- </option>';
			getEl(element_id, function(el) {
				el.innerHTML = '<OPTION value=""></option>';
				data.forEach(function (row){
					if (row['id'] == value) {
						extra = 'selected=selected';
					} else {
						extra = '';
					}

					el.innerHTML += '<OPTION value="' + row['id'] + '" '+extra+'>'+ row[related_field] +'</option>';

				});
			});
			
			const e = new Event("change");
			const element = getEl(element_id);
			element.dispatchEvent(e);
		});
	}

	// Ejecutar una vez el handler ni bien se crea el campo.
	onchange_handler();
	// Y ejecutar también cada vez que el filtro cambie:
	if (filter_to != undefined && filter_to != '' && metadata._fields[filter_to] != undefined) {
		getEl(filter_from, function(el) {
			if (el.value != undefined) {
				el.addEventListener('change', function() {
					getEl(element_id, function(el) {
						if (el.value != undefined && el.value != '') {
							value = el.value;
						}
					});
					onchange_handler();
				});
			}
		});
	}
}
// LAS USAN EntityField, RelationshipDropdown, Field y FlexRelationshipDropdown
function saveOptionName(option_id, hidden_name){
	// WHAT IF NONE SELECTED???
	if (option_id == '' || hidden_name == '') return;
	var value = query('.record #'+option_id+' option:selected').value;
	if (value == ''){
		getEl(hidden_name, function(el) {
			el.value = '';
		});
	} else {
		getEl(hidden_name, function(el) {
			el.value = query('.record #'+option_id+' option:selected').innerHTML;
		});
	}
}

function saveOptionAttribute(option_id, hidden_name, attribute){
	// console.log('saveOptionAttribute: ' + option_id + ' - ' + hidden_name + ' - ' + attribute);
	var value = query('.record #'+option_id+' option:selected').value;
	if (value == ''){
		getEl(hidden_name, function(el) {
			el.value = '';
		});
	} else {
		getEl(hidden_name, function(el) {
			el.value = query('.record #'+option_id+' option:selected').getAttribute(attribute);
		});
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
						getEl(placeholder_id, function(el) {
							el.innerHTML = data[related_name];
						});
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
			getEl(element_id, function (el){
				el.value = data[0][field_name];
			});
		});
	}
}

function generateCopyButton(element_id){
	var button_html = '<input type=button value="' + global.lang.LBL_COPY + '" class="button copy_button" onclick="copyToClipboardValue(\''+ element_id + '\')">';
	getEl(element_id, function (el){
		if (el.parentNode.tagName.toUpperCase() == 'A'){
			el.parentNode.parentNode.innerHTML += button_html;
		} else {
			el.parentNode.innerHTML += button_html;
		}
	});
	
	
}
function copyToClipboardValue(element_id){
	/* Get the text field */
	var copyText = getEl(element_id).value;

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

// ORDER DATATYPE: old
function order_swap_next(id, fname) {
	var order_next_id;
	order_next_id = getEl('_ordernext'+fname+id).value;
	order_swap(id, order_next_id, fname);
}

function order_swap_previous(id, fname) {
	var order_previous_id;
	order_previous_id = getEl('_orderprevious'+fname+id).value;
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
	if (queryAll('#_order'+fname+to_id).length == 0) return false;

	// GET VALUES
	from_value = getEl('_order'+fname+from_id).value;
	from_prev = getEl('_orderprevious'+fname+from_id).value;
	from_next = getEl('_ordernext'+fname+from_id).value;
	
	to_value = getEl('_order'+fname+to_id).value;
	to_prev = getEl('_orderprevious'+fname+to_id).value;
	to_next = getEl('_ordernext'+fname+to_id).value;

	table = getEl('_ordertable'+fname+to_id).value;

	// CHANGE AT DB
	save_do(fname+'='+to_value, table, from_id, function (r){
		// Evitar el doble/multiple mensaje de guardado
		setHtml('message', '');
	});
	save_do(fname+'='+from_value, table, to_id);

	// CHANGE AT CLIENT: swapped elements
	getEl('_order'+fname+from_id).value = to_value;
	if (to_prev == from_id){
		getEl('_orderprevious'+fname+from_id).value = to_id;
	} else {
		getEl('_orderprevious'+fname+from_id).value = to_prev;
	}
	if (to_next == from_id){
		getEl('_ordernext'+fname+from_id).value = to_id;
	} else {
		getEl('_ordernext'+fname+from_id).value = to_next;
	}
	
	getEl('_order'+fname+to_id).value = from_value;
	if (from_prev == to_id){
		getEl('_orderprevious'+fname+to_id).value = from_id;
	} else {
		getEl('_orderprevious'+fname+to_id).value = from_prev;
	}
	if (from_next == to_id){
		getEl('_ordernext'+fname+to_id).value = from_id;
	} else {
		getEl('_ordernext'+fname+to_id).value = from_next;
	}

	// CHANGE AT CLIENT: adjacent elements
	from_prev = getEl('_orderprevious'+fname+from_id).value;
	from_next = getEl('_ordernext'+fname+from_id).value;
	to_prev = getEl('_orderprevious'+fname+to_id).value;
	to_next = getEl('_ordernext'+fname+to_id).value;

	if (from_prev != ''){ getEl('_ordernext'+fname+from_prev).value = from_id; }
	if (from_next != ''){ getEl('_orderprevious'+fname+from_next).value = from_id; }
	if (to_prev != ''){ getEl('_ordernext'+fname+to_prev).value = to_id; }
	if (to_next != ''){ getEl('_orderprevious'+fname+to_next).value = to_id; }

	// view: order value
	setHtml('_orderv'+fname+from_id, to_value);
	setHtml('_orderv'+fname+to_id, from_value);

	// view: buttons	
	if (getEl('_orderprevious'+fname+from_id).value == ''){
		setDisabled('_orderbutprevious'+fname+from_id, true);
	} else {
		setDisabled('_orderbutprevious'+fname+from_id, false);
	}
	
	if (getEl('_ordernext'+fname+from_id).value == ''){
		setDisabled('_orderbutnext'+fname+from_id, true);
	} else {
		setDisabled('_orderbutnext'+fname+from_id, false);
	}

	if (getEl('_orderprevious'+fname+to_id).value == ''){
		setDisabled('_orderbutprevious'+fname+to_id, true);
	} else {
		setDisabled('_orderbutprevious'+fname+to_id, false);
	}
	
	if (getEl('_ordernext'+fname+to_id).value == ''){
		setDisabled('_orderbutnext'+fname+to_id, true);
	} else {
		setDisabled('_orderbutnext'+fname+to_id, false);
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
	frow.style.opacity = 0;
	trow.style.opacity = 0;

	// CREATE COPIES: this shows the ghosts
	from_clone = frow.cloneNode(true);
	to_clone = trow.cloneNode(true);
	parent = frow.parentNode;

	// style
	if (trow.offsetTop < frow.offsetTop){
		if (parseInt(to_value) < parseInt(from_value)){
			from_clone.style.opacity = '0.5';
			from_clone.style.position = 'absolute';
			from_clone.style.top = (trow.offsetTop + frow.offsetHeight - 6) + 'px';
			to_clone.style.opacity = '0.5';
			to_clone.style.position = 'absolute';
			to_clone.style.top = (trow.offsetTop - 6) + 'px';
		} else {
			from_clone.style.opacity = '0.5';
			from_clone.style.position = 'absolute';
			from_clone.style.top = (trow.offsetTop - 6) + 'px';
			to_clone.style.opacity = '0.5';
			to_clone.style.position = 'absolute';
			to_clone.style.top = (trow.offsetTop + frow.offsetHeight - 6) + 'px';
		}
	} else {
		if (parseInt(to_value) < parseInt(from_value)){
			from_clone.style.opacity = '0.5';
			from_clone.style.position = 'absolute';
			from_clone.style.top = (frow.offsetTop + trow.offsetHeight - 6) + 'px';
			to_clone.style.opacity = '0.5';
			to_clone.style.position = 'absolute';
			to_clone.style.top = (frow.offsetTop - 6) + 'px';
		} else {
			from_clone.style.opacity = '0.5';
			from_clone.style.position = 'absolute';
			from_clone.style.top = (frow.offsetTop - 6) + 'px';
			to_clone.style.opacity = '0.5';
			to_clone.style.position = 'absolute';
			to_clone.style.top = (frow.offsetTop + trow.offsetHeight - 6) + 'px';
		}
	}

	// append
	parent.appendChild(from_clone);
	parent.appendChild(to_clone);
	
	// AND ANIMATE!!!
	var op1 = '+='+trow.offsetHeight;
	var op2 = '-='+trow.offsetHeight;
	if (frow.offsetTop < trow.offsetTop){
		op1 = '-='+frow.offsetHeight;
		op2 = '+='+frow.offsetHeight;
	}

	// Using requestAnimationFrame for animation
	function animate(element, property, from, to, duration, callback) {
		var start = performance.now();
		var range = to - from;

		function step(now) {
			var elapsed = now - start;
			var progress = Math.min(elapsed / duration, 1);
			element.style[property] = (from + range * progress) + 'px';
			if (progress < 1) {
				requestAnimationFrame(step);
			} else if (callback) {
				callback();
			}
		}
		requestAnimationFrame(step);
	}

	animate(from_clone, 'top', parseInt(from_clone.style.top), parseInt(from_clone.style.top) + (op1 === '+='+trow.offsetHeight ? trow.offsetHeight : -frow.offsetHeight), 300, function(){
			from_clone.remove();
		frow.style.opacity = '1';
	});

	animate(to_clone, 'top', parseInt(to_clone.style.top), parseInt(to_clone.style.top) + (op2 === '+='+frow.offsetHeight ? frow.offsetHeight : -trow.offsetHeight), 300, function(){
			to_clone.remove();
		trow.style.opacity = '1';
	});
}

// ORDER DATATYPE: new
var order_drag = {
	element: null,
	tr_element: null,
	record_id: '',
	value: '',
	field_name: '',
	order: '',
	isReverse: false,
	tableSelector: ''
};
function orderDragStart(ev, element, record_id, field_name, entity_id){
	// console.log('DragStart', record_id, value, field_name);
	// quito el estilo de drag:
	orderClearStyle();

	order_drag.element = element;
	order_drag.entity_id = entity_id;
	order_drag.tr_element = orderGetParentWithClass(element, 'record');
	order_drag.record_id = record_id;
	order_drag.field_name = field_name;
	order_drag.isReverse = orderIsReverse();
	if (order_drag.isReverse){
		order_drag.order = orderGetViewOrder().split(' ')[0]; // first half
	} else {
		order_drag.order = orderGetViewOrder(); 
	}
	order_drag.tableSelector = '.' + order_drag.tr_element.parentElement.className.replaceAll(' ', '.');
	order_drag.value = orderGetValue(record_id);

	// console.log('DRAGSTART', value);
}

function orderOnDrag(ev, element, record_id){
	ev.preventDefault();
	if (order_drag.element != null){
		let this_tr = orderGetParentWithClass(element, 'record');
		// quito estilos a todos los TR
		orderClearStyle();

		// decido si el elemento está
		if (this_tr.parentElement == orderGetParentWithClass(order_drag.element, 'record').parentElement){

			// obtengo el value de este elemento
			let value = orderGetValue(record_id); 
			let css = '';
			// compare with order_drag.value
			// console.log(value, order_drag.value);

			if (parseInt(value) < parseInt(order_drag.value)){
				if (order_drag.isReverse){
					css = 'drag_target_bottom';
				} else {
					css = 'drag_target_top';
				}				
			} else {
				if (order_drag.isReverse){
					css = 'drag_target_top';
				} else {
					css = 'drag_target_bottom';
				}				
			}

			// agrego estilo al TR actual
			this_tr.classList.add(css);

			// console.log('DRAG', value);

		}

	}
	
}

function orderDragStop(ev, element, record_id){
	ev.preventDefault();
	
	if (order_drag.element != null && order_drag.record_id != record_id){
		let table = orderGetTableName();
		let source_tr = orderGetParentWithClass(order_drag.element, 'record');
		let target_tr = orderGetParentWithClass(element, 'record');
		let source_value = order_drag.value;
		let target_value = orderGetValue(record_id);
		let table_element = source_tr.parentElement;

		// invoco reorden
		executeAction('orderSwapAndShift', 'table='+table+'&source_id='+order_drag.record_id+ '&target_id='+record_id+ '&order='+order_drag.field_name, '', function (response){
			
			// on success, acomodo gui
			if (parseInt(target_value) < parseInt(source_value)){
				if (order_drag.isReverse){
					// insertAfter
					table_element.insertBefore(source_tr, target_tr.nextSibling);
				} else {
					table_element.insertBefore(source_tr, target_tr);
				}
			} else {
				if (order_drag.isReverse){
					table_element.insertBefore(source_tr, target_tr);
				} else {
					table_element.insertBefore(source_tr, target_tr.nextSibling);
				}
			}
			// console.log(source_tr, target_tr);		
			// console.log(response);

			// actualizo cache, sólo elementos existentes en cache.
			for (const id in response.data){ 
				
				if (metadata[table] != undefined && metadata[table][id] != undefined){
					metadata[table][id][order_drag.order] = response.data[id];
				} else if (cache[table] != undefined && cache[table][id] != undefined){
					cache[table][id][order_drag.order] = response.data[id];
				}
				
			}

		}, false /*?*/);
		
		// console.log('DRAGSTOP', source_value, target_value);
	}
}

function orderDragEnd(){
	
	// quito el estilo de drag:
	orderClearStyle();
	// "destroy" order_drag
	order_drag.element = null;
	
}

function orderGetViewOrder(){
	let entity_id = orderGetEntityId();
	var order;
	if (this_status['main_body'].order != undefined && this_status['main_body'].entity_id == entity_id){
		order = this_status['main_body'].order; // list order
	} else {
		order = metadata._entities[entity_id].listview_order; // default and subpanel order
	}
	return order;
}

function orderGetEntityId(){
	if (order_drag.entity_id != undefined){
		return order_drag.entity_id;
	} else {
		return this_status['main_body'].entity_id;
	}	
}

function orderIsReverse(){
	let o = orderGetViewOrder();
	return (o.toUpperCase().split(' ')[1] == 'REVERSE');
}

function orderGetParentWithClass(element, css){
	// escalo en los ancestros
	if (element == null){
		return null;
	} else if (element.classList.contains(css)){
		return element;
	} else {
		return orderGetParentWithClass(element.parentElement, css);
	}
}

function orderGetValue(id){
	if (order_drag.element != null){
		let table = orderGetTableName();
		if (metadata[table] != undefined && metadata[table][id] != undefined){
			return metadata[table][id][order_drag.order];
		} else if (cache[table] != undefined && cache[table][id] != undefined){
			return cache[table][id][order_drag.order];
		}
	}
	return '';
}

function orderGetTableName(){
	let entity_id = orderGetEntityId();
	return metadata._entities[entity_id].table;
}

function orderClearStyle(){
	if (order_drag.element != null){
		queryAll(order_drag.tableSelector + ' .record').forEach(function (tr){
			tr.classList.remove('drag_target_bottom');
			tr.classList.remove('drag_target_top');
		});
	}
}

// DATATYPE PARAMETERS:
function populateDataTypeParameters(){
	// REMOVE PREVIOUS PARAMETERS
	document.querySelectorAll('form .parameter').forEach(function (e){
		e.remove();
	});
	// ADD PARAMETERS OF TYPE
	var type = query('#type').value;
	if (type != ''){
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
				getEl('parameter_'+f.name, function(el) {
					el.innerHTML = renderBasicSingleFieldFor(f.type, f.name, f.name, 1, v, f, 9);
				});
			}
		},1);

	}
}

function getIPinfo(value, field_id){
	var url = 'http://ip-api.com/json/' + value;
	if (value != ''){
		fetch(url)
			.then(function(response){ return response.json(); })
			.then(function(data){
			var popup_content = data.country + '<br>' + data.regionName + '<br>' + data.city + '<br>ISP: ' + data.isp;
			create_popup(popup_content, field_id);
			})
			.catch(function(error){ console.error('getIPinfo error:', error); });
	}
	return false;
}
