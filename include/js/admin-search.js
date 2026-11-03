// --- Búsqueda y Filtros ---
window.searchform_collapse_toggle_status = window.searchform_collapse_toggle_status !== undefined ? window.searchform_collapse_toggle_status : false;

function renderSearch(parent_element_id, entity_id, related_field, related_id) {
	var html = ''

	var search_fields = searchInArray(searchInArray(metadata._fields, 'parent', entity_id), 'view6', '1');
	// ORDER!!!
	search_fields = sortData(search_fields, 'order');

	if (Object.keys(search_fields).length > 0) {
		html +=  '<h2 class=search_title>' + global.lang.LBL_SEARCH_TITLE + '</h2>';
		// html +=  '<p class=search_desc>' + global.lang.LBL_SEARCH_DESC + '</p>';
		html +=  '<form action="'+ script_name +'" method=post class=searchform accept-charset="' + global.config.admin_charset + '" entity_id="'+ entity_id +'" action="void.php">' + renderSearchForm(parent_element_id, search_fields);
		// RELATED TO:
		if (related_field != undefined && related_field != '' && related_id != undefined) {
			html +=  '<input type=hidden name="'+ related_field +'" value="'+ related_id +'">';
		}
		if (global.config.searchform_collapse == 1 && Object.keys(search_fields).length > 1){
			html += '<div id="searchform_expand" onclick="searchform_collapse_toggle()"><span></span></div>';
		}
		html +=  '</form>';
	}

	while_selector('.searchform', function (){
		// Enter as search button
		queryAll('.searchform input').forEach(function(e){
			e.addEventListener('keydown', function (ev){ 
				if(ev.key == 'Enter'){ search_do('main_body'); ev.preventDefault(); return false; } 
			}); 
		});
		// Prefill filter fields
		var f = unserialize(this_status["main_body"].filter);
		for (const i in f){ 
			var e = query('.searchform #' + i);
			if (e != null && e != undefined){
				e.value = f[i];
			}
			// document.querySelector('.searchform #activo').value = 1;
		}

		// searchform_collapse
		if (global.config.searchform_collapse == 1 && Object.keys(search_fields).length > 1){
			searchform_collapse(true);
		}

	});

	return html;
}

function renderSearchForm(parent_element_id, search_fields) {
	var html = '';
	var e = ' first';

	html +=  '<div class="panel container">';

	// FIELDS
	each(search_fields, function (i, field){
		html +=  '<div class="view row searchfield' + e + '"><div class="view three columns label">' + field.label + '</div><div class="view five columns">';
		e = '';

		var dt = metadata._datatypes[field.type]

		if (dt.search_ops != undefined){
			var first_so;
			// Construir un desplegable de opciones
			html += '<div class="search_op" id="search_op_'+field.id+'"><select name="_op_'+ field.name +'" id="_op_'+ field.name +'" onchange="renderSearchFieldChange(\''+ field.id + '\', this.value);">';
			each(dt.search_ops, function (i, so_id){
				var so = metadata._search_ops[so_id];
				if (first_so == undefined) first_so = so;
				if (field.multiple == 0 || so.code != 'empty'){
					html += '<option value="' + so.code + '" so_id="' + so.id + '">' + so.label + '</option>';
				}
			});
			html += '</select></div> <span id="_param_' + field.name + '">' + renderSearchField(field, first_so) + '</span>';
		} else {
			html +=  renderBasicSingleField(field.id, 1, '');
		}

		html +=  '</div></div>';

	});

	// SEARCH BUTTONS
	html +=  '<span class="search_buttons">';
	html +=  renderLink(global.lang.BTN_SEARCH, "return search_do('" + parent_element_id + "')", true);
	html +=  renderLink(global.lang.BTN_CLEAR, "return search_clear('" + parent_element_id + "')", true);
	html = html  + '</span>';

	html +=  '</div>';

	return html;
}

function searchform_collapse(boolean){
	searchform_collapse_toggle_status = boolean;
	if (boolean){
		var cv = 0;
		document.querySelectorAll('.searchfield').forEach(function (e){ 
			var st = 'none';
			if (e.querySelector('input') != null){
				if (e.querySelector('input').value != ''){
					st = '';
					cv++;
				}
			} else if (e.querySelectorAll('select').length == 1){
				if (e.querySelector('select').value != ''){
					st = '';
					cv++;
				}
			} else if (e.querySelectorAll('select').length == 2){
				var a = e.querySelectorAll('select');
				// console.log(a);
				if (a[1].value != ''){
					st = '';
					cv++;
				}
			}
			e.style.display = st; 
		});
		if (cv == 0){
			document.querySelectorAll('.searchfield.first').forEach(function (e){ e.style.display = ''; });
		}		
		// &#9660;
		document.querySelector('#searchform_expand span').innerHTML = '&#9660';
	} else {
		var t = 0;
		document.querySelectorAll('.searchfield').forEach(function (e){ 
			setTimeout(function(){ e.style.display = ''; }, t);
			t = t + 50;
		});
		// &#9650;
		document.querySelector('#searchform_expand span').innerHTML = '&#9650';
	}
}
function searchform_collapse_toggle(){
	searchform_collapse(!searchform_collapse_toggle_status);
}


function renderSearchFieldChange(field_id){
	var field = metadata._fields[field_id];
	var select = query('#_op_'+field.name);
	var selectedOption = select.options[select.selectedIndex];
	var so_id = selectedOption.getAttribute('so_id');
	var so = metadata._search_ops[so_id];
	var html = renderSearchField(field, so);
	setHtml('_param_'+field.name, html);

	// Enter as search button
	queryAll('#_param_'+field.name +' input').forEach(function(e){ e.addEventListener('keydown', function (ev){ if(ev.key == 'Enter'){ search_do('main_body'); ev.preventDefault(); return false;  }; }); });
}

function renderSearchField(field, search_op){
	var html = '';
	
	if (search_op != undefined && search_op.params == 0){
		// 0: None (just a checkbox???)
		// html += '<select name="'+ field.name +'" id="' + field.name + '"><option value=""></option><option value="1">'+global.lang.LBL_YES+'</option><option value="0">'+global.lang.LBL_NO+'</option></select>';
		html += '<input type=hidden name="'+ field.name +'" id="' + field.name + '" value="1">';
	} else if (search_op == undefined || search_op.params == 1){
		// 1: One
		html += renderBasicSingleField(field.id, 1, '');
	} else if (search_op.params == 2){
		// 2: Two
		html += renderBasicSingleField(field.id, 1, '');
		html += renderBasicSingleField(field.id, 1, '', '_2_', '_2_');
	} else if (search_op.params == 'T'){
		// T: Text
		html += '<input type=text name="'+ field.name +'" id="' + field.name + '" value="">';
	} else if (search_op.params == 'N'){
		// N: Number
		html += '<input type=number name="'+ field.name +'" id="' + field.name + '" value="">';
	} else if (search_op.params == 'M'){
		// M: Multi
		// html += 'MULTI FIELD ' + field.name; // TODO!
		var prev_count = 0;
		var remove_button;
		remove_button = renderLink('-', "jQuery(this).parent().html(''); multi_count['" + field.id + "']--; return false;", true, undefined, false, 'multi_sub');

		if (multi_count[field.id] != undefined){
			prev_count = multi_count[field.id];
		} else {
			multi_count[field.id] = 1;
			prev_count = 1;
		}
		var suffix = '_1_';
		// ADD BUTTON:
		html += '<span class="add_button">' + renderLink('+', "addMultiSearch('"+ field.id +"'); return false;", true, undefined, false, 'multi_add') + '</span>';
		// SPAN FOR NEW VALUES, and first one:
		html += '<span id="_new_'+ field.id +'">';
		html += '<span>' + renderBasicSingleField(field.id, 1, '', suffix, '') + '<br></span>';
		for (var i = 2; i < prev_count + 1; i++){
			html += '<span>' + renderBasicSingleField(field.id, 1, '', '_' + i + '_', '_' + i + '_') + remove_button + '<br></span>';
		}
		html += '</span>';

		$('.search_op').each(function(i,e){ $(e).css('height', $(e).parent().height() + 'px'); });

	 	setTimeout(function (){
			$('.search_op').each(function(i,e){ $(e).css('height', $(e).parent().height() + 'px'); });
		}, 10);
	}

	return html;
}

function addMultiSearch(field_id){
	var html = '';
	var remove_button;
	var field = metadata._fields[field_id];
	remove_button = renderLink('-', "jQuery(this).parent().html(''); multi_count['" + field.id + "']--; return false;", true, undefined, false, 'multi_sub');

	multi_count[field_id]++;
	var suffix = '_' + multi_count[field_id] + '_';

	html = '<span>' + renderBasicSingleField(field_id, 1, '', suffix, suffix) + remove_button + '<br></span>';

	$('#_new_' + field_id).append(html);

	$('.search_op').each(function(i,e){ $(e).css('height', $(e).parent().height() + 'px'); });

	return false;
}

// SEARCH!
function getSearchformBackup(parent_element_id){
	var searchform_backup = {name:{},id:{}};
	queryAll('#'+parent_element_id+' .searchform input, #'+parent_element_id+' .searchform textarea, #'+parent_element_id+' .searchform select').forEach(function (e) { 
		var name = e.getAttribute('name');
		var id = e.getAttribute('id');
		var value = e.value;
		if (name != undefined && name != '' && value != undefined && value != '') {
			searchform_backup.name[name] = value;
		}
		if (id != undefined && id != '' && value != undefined && value != '') {
			searchform_backup.id[id] = value;
		}
	});
	return searchform_backup;
}

function restoreSearchformBackup(parent_element_id, searchform_backup){
	each(searchform_backup.name, function (name, value){
		var el = query('#'+parent_element_id+' .searchform [name="'+ name +'"]');
		if (el) {
			el.value = value;
			el.dispatchEvent(new Event('change'));
		}
	});
	each(searchform_backup.id, function (id, value){
		var el = query('#'+parent_element_id+' .searchform [id="'+ id +'"]');
		if (el) {
			el.value = value;
			el.dispatchEvent(new Event('change'));
		}
	});
}

function search_do(parent_element_id) {

	var entity = metadata._entities[this_status[parent_element_id].entity_id];

	var searchform_backup = getSearchformBackup(parent_element_id);

	// BUILD filter
	var filter = getSearchFilter(parent_element_id);

	// ADD FIXED FILTER
	if (entity.fixed_filter != undefined && entity.fixed_filter != ''){
		if (filter == ''){
			filter = entity.fixed_filter;
		} else {
			filter += '&' + entity.fixed_filter;
		}
	}

	// loadView with that filter
	loadView(entity.id, this_status[parent_element_id].view_id, '', filter, 0, false, function (){
		restoreSearchformBackup(parent_element_id, searchform_backup);
	}, undefined, parent_element_id, this_status[parent_element_id].order);

	return false;
}

function getSearchFilter(parent_element_id){
	var filter = '';
	queryAll('#'+parent_element_id+' .searchform input, #'+parent_element_id+' .searchform textarea, #'+parent_element_id+' .searchform select').forEach(function (e) { 
		var name = e.getAttribute('name');
		var value = e.value;
		if (name != undefined && name != '' && value != undefined && value != '') {
			if (filter != '') filter = filter + '&';
			filter = filter + name + '=' + encodeURIComponent(value.trim());
		}
	});
	return filter;
}

function search_clear(parent_element_id) {
	// RENDER CLEAR FORM
	var search_fields = searchInArray(searchInArray(metadata._fields, 'parent', this_status[parent_element_id].entity_id), 'view6', '1');
	var html = renderSearchForm(parent_element_id, search_fields);
	$('#'+parent_element_id+' .searchform').html(html);
	search_do(parent_element_id); // DO de search
	return false;
}

function sortByField(field_id, parent_element_id){
	var field = metadata._fields[field_id];
	// console.log('SORT BY:');
	// console.log(field);
	if (this_status[parent_element_id].order == field.name){
		this_status[parent_element_id].order = field.name + ' REVERSE';
	} else {
		this_status[parent_element_id].order = field.name;
	}
	
	search_do(parent_element_id);
}