// --- Plantillas y Campos Dinámicos ---
window.template_cache = window.template_cache || {};
window.multi_count = window.multi_count || {};
window.multi_row = window.multi_row || {};


function getFirstField(entity_id, view_id){
	// Calculate first field of the entity
	var fields = Object.values(searchInArray(searchInArray(metadata._fields, 'parent', entity_id), 'view' + view_id, '1')).sort(function (a, b) { return a.order - b.order; });
	return fields[0];
}

function renderField(field_id, edit, value, suffix, row){
	var html = '';
	var field = metadata._fields[field_id];

	if (value == undefined) value = '';

	if (field.access == 'writeonce' && !empty(value)) edit = 0;
	if (field.access == 'onlyadmin' && current_user.isadmin == 0) edit = 0;
	if (field.access == 'readonly') edit = 0;
	// if (field.access == 'listedit') edit = 1;

	// MULTIFIELD
	// var multifield;???
	if (field.multiple == 1){
		var mempty = false; // first one doesn't have remove button?
		multi_count[field.id] = 0;
		var remove_button;
		if (edit == 1){
			// jQuery(this).parent().html('')  ->  vanilla JS
			remove_button = renderLink('-', "this.parentElement.innerHTML=''; return false;", true, undefined, false, field.name + ' remove');

			// ADD BUTTON:
			html += '<span class="add_button">' + renderLink('+', "addMultifield('"+ field.id +"', '"+ suffix +"'); return false;", true, undefined, false, 'multi_add') + '</span>';

		} else {
			remove_button = '';
			html += '<ul>';
		}
		
		// DB DATA
		Object.entries(value || {}).forEach(function(pair){
			var i = pair[0];
			var e = pair[1];
			multi_count[field.id]++;
			if (edit == 0) html += '<li>'
			html += '<span>' + renderSingleField(field_id, edit, e, suffix + '_' + multi_count[field.id], row, i);
			if (mempty) html += remove_button;
			mempty = true;
			html += '</span>';
			if (edit == 0){
				html += '</li>';
			} else {
				html += '<br>';
			}
		});

		if (edit == 1){

			multi_row[field.id] = row;

			// SPAN FOR NEW VALUES
			html += '<span id="_new_'+ field.id +'">';
			if (!mempty){
				multi_count[field.id]++;
				html += '<span>' + renderSingleField(field_id, edit, '', suffix + '_' + multi_count[field.id], row) + '<br></span>';
			} 
			html += '</span>';
			

		} else {
			html += '</ul>';
		}

	} else {
		html = renderSingleField(field_id, edit, value, suffix, row);
	}

	return html;

}

function addMultifield(field_id, suffix){
	var html = '';
	var remove_button;
	var field = metadata._fields[field_id];
	remove_button = renderLink('-', "this.parentElement.innerHTML=''; return false;", true, undefined, false, field.name + ' remove');

	multi_count[field_id]++;

	// Se crea un contenedor con createEl y se vuelca a string para appendHtml
	var span = createEl('span');
	span.innerHTML = renderSingleField(field_id, 1, '', suffix + '_' + multi_count[field_id], multi_row[field_id], 'new') + remove_button + '<br>';



	getEl('_new_' + field_id)?.appendChild(span);

	return false;
}

function getTemplateCache(type, edit){
	var template;
	var source = '';

	if (template_cache[type + '_' + edit] != undefined){
		template = template_cache[type + '_' + edit];
	} else {
		if (metadata._datatypes[type] == undefined){
			console.log('Undefined TYPE '+ type);
		}
		if (edit == 1){
			source = metadata._datatypes[type].js_edittemplate;
		} else {
			source = metadata._datatypes[type].js_viewtemplate;
		}
		if (!empty(source)){
			template = Handlebars.compile(source); // ONDEMAND
		}
		template_cache[type + '_' + edit] = template;
	}
	return template;
}

function renderSingleField(field_id, edit, value, suffix, row, db_index){
	var html = '';
	var id = '';
	var name = '';
	var id_suffix = '';
	var name_suffix = '';
	var db_name;
	var field = metadata._fields[field_id];	

	// MULTILANGUAGE
	var langs;
	if (field.multilanguage == 1 && edit == 1) {
		langs = getLanguageCodes();
		// ADD HIDDEN
		html += '<input type="hidden" name="'+ field.name +'" value="">';
	} else {
		langs = [''];
	}
	// FOREACH MULTIFIELD
		// FOREACH LANGS...
		langs.forEach(function (code){
			name_suffix = code;
			id_suffix = code + suffix;
			id = field.name + id_suffix;
			name = field.name + name_suffix;
			db_name = name;
			if (db_index != 'new' && db_index != undefined){
				db_name = db_name + '_' + (db_index + 1);
			}

			if (!empty(code)){
				html += '<span class="translation multilanguage"><span class="code">' + (code.substring(1)) + '</span>';
				if (row != undefined && row[name] != undefined) value = row[name];
				// MULTI MULTI
				if (field.multiple == 1 && db_index != undefined && row != undefined && row[name] != undefined && row[name][db_index] != undefined){
					value = row[name][db_index];
				}
				if (db_index == 'new') value = '';
			}

			if (field.multiple == 1){
				name_suffix += '[]';
				name += '[]';
			} 
			
			html += renderBasicSingleField(field_id, edit, value, id_suffix, name_suffix, row, db_name);

			if (!empty(code)){ 
				html += '</span>';
			}
			
			if (edit == 1){
				if (field.required==1) html += "<script>setAttr('"+ id +"', 'required', 'required');</script>";
				if (!empty(field.regex)) html += "<script>setAttr('"+ id +"', 'pattern', '"+field.regex+"');</script>";
				if (empty(field.placeholder)){
					html += "<script>setAttr('"+ id +"', 'placeholder', '"+field.label+"');</script>";
				} else {
					html += "<script>setAttr('"+ id +"', 'placeholder', '"+field.placeholder+"');</script>";
				} 
			}
		});
	// END FOREACH

	return html;
}

function renderBasicSingleField(field_id, edit, value, id_suffix, name_suffix, row, db_name) {
	var html = '';
	var id = '';
	var name = '';
	var field = metadata._fields[field_id];
	var entity = metadata._entities[field.parent];
	var view_id = this_status['main_body'].view_id;
	if (row != undefined && row['id'] != undefined){
		var record_id = row['id'];
	}

	if (id_suffix == undefined) id_suffix = '';
	if (name_suffix == undefined) name_suffix = '';

	id = field.name + id_suffix;
	name = field.name + name_suffix;

	var template = getTemplateCache(field.type, edit);

	if (typeof value == 'string') {
		value_htmlentities_quotes = value.replace(/"/g, '&quot;');
	} else {
		value_htmlentities_quotes = value;
	}

	if (empty(template)){
		if (edit == 1){
			html += field.type + '<input type=text name="'+ name +'" id="' + id + '" value="' +value +'">';
		} else {
			html += value;
		}
	} else {
		var context = {
			name: name, 
			id: id, 
			value: value, 
			value_htmlentities_quotes: value_htmlentities_quotes,
			f: field, 
			e: entity,
			view: view_id, 
			lang: global.lang, 
			record_id: record_id, 
			table: entity.table,
			name_suffix: name_suffix,
			id_suffix: id_suffix,
			global: global,
			row: row,
			db_name: db_name
		};
		// console.log(context);
		html += template(context);
	}
	var rid;
	if (record_id == undefined){
		rid = 'undefined';
 	} else {
		rid = "'"+record_id+"'";
	}
	if (edit == 1 && view_id == 2 && field.access == 'listedit'){
		html += "<script> addOnchangeListener('"+id+"', '"+name+"', '"+entity.table+"', "+rid+"); </script>";
	}
	if (field.unique == 1 && edit == 1){
		html += "<script> addUniqueListener('"+id+"', '"+name+"', '"+entity.table+"', '"+entity.id+"', "+rid+"); </script>";
	}
	return html;
}

function renderBasicSingleFieldFor(field_type, name, id, edit, value, params, entity_id, row){
	var html = '';
	var field = Object.assign({}, params);
	var record_id;
	var entity;
	field.name = name;
	field.type = field_type;
	if (row != undefined && row['id'] != undefined){
		record_id = row['id'];
	}
	if (entity_id != undefined){
		entity = metadata._entities[entity_id];
	} else {
		entity = metadata._entities[this_status['main_body'].entity_id];
	}

	var template = getTemplateCache(field.type, edit);

	if (typeof value == 'string') {
		value_htmlentities_quotes = value.replace(/"/g, '&quot;');
	} else {
		value_htmlentities_quotes = value;
	}

	if (empty(template)){
		if (edit == 1){
			html += field.type + '<input type=text name="'+ name +'" id="' + id + '" value="' +value +'">';
		} else {
			html += value;
		}
	} else {
		var context = {
			name: name, 
			id: id, 
			value: value, 
			value_htmlentities_quotes: value_htmlentities_quotes,
			f: field, 
			e: entity,
			view: this_status['main_body'].view_id, 
			lang: global.lang, 
			record_id: record_id, 
			table: entity.table,
			name_suffix: '',
			id_suffix: '',
			global: global,
			row: row
		};
		html += template(context);
	}
	return html;

}

function addOnchangeListener(element_id, field_name, table, record_id){
	// document.getElementById -> getEl de util.js
	var e = getEl(element_id);
	var saveCallback = function (){
		// TODO: validate unique?
		if (record_id != undefined && record_id != 'undefined'){
			save_field(element_id, field_name, table, record_id);
		}		
	};
	if (e != null){
		if (e.tagName == 'SELECT'){
			// solo engancho el onchange
			e.addEventListener('change', saveCallback);
			// El click no?
		} else if (e.tagName == 'INPUT' && e.type.toUpperCase() == 'CHECKBOX'){
			// solo engancho el onchange
			e.addEventListener('change', saveCallback);
		} else if (e.tagName == 'INPUT'){
			// engancho el onchange
			e.addEventListener('change', saveCallback);
			e.addEventListener('keydown', function(event){
				if (event.key == 'Enter'){
					event.preventDefault();
					saveCallback();
					return false;
				}
			});
		}
	} // else??? 
}

function addUniqueListener(element_id, field_name, table, entity_id, record_id){
	var e = getEl(element_id);
	var validationCallback = function (){
		// generate a form with this value:
		var post_data = field_name + '=' + encodeURIComponent(e.value);
		validate_form(post_data, table, entity_id, record_id, function (){
			// IS VALID... soo... show must go on!
		}, function (messages){
			if (messages[field_name] != undefined){ // discard all other messages
				showFieldError(element_id, messages[field_name]);
			}
		});
	};
	if (e != null){
		if (e.tagName == 'SELECT'){
			// solo engancho el onchange
			e.addEventListener('change', validationCallback);
			// El click no?
		} else if (e.tagName == 'INPUT' && e.type.toUpperCase() == 'CHECKBOX'){
			// solo engancho el onchange
			e.addEventListener('change', validationCallback);
		} else if (e.tagName == 'INPUT'){
			// engancho el onchange
			e.addEventListener('change', validationCallback);
			// Return (y a evitar el comportamiento default del submit)
			e.addEventListener('keydown', function(event){
				if (event.key == 'Enter'){
					event.preventDefault();
					validationCallback();
					return false;
				}
			});
		}
	} // else??? 
}

