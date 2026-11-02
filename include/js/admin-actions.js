
function export_do(){
	var parent_element_id = 'main_body';
	var entity_id = this_status[parent_element_id].entity_id;
	var entity = metadata._entities[entity_id];
	var filter = getSearchFilter(parent_element_id);
	var url = script_name + '?action=export&table='+entity.table;
	if (filter != undefined && filter != ''){
		url += '&' + filter;
	}
	window.location.href = url;
	return false;
}

// SAVE!!
function save_form(form, parent_element_id) {
	// BLOCK SCREEN
	block_screen();

	var record_id = form.getAttribute('record_id');
	var entity_id = form.getAttribute('entity_id');
	var entity = metadata._entities[entity_id];
	var return_id;
	var return_entity;
	if (this_status['main_body'].return != undefined){
		return_id = this_status['main_body'].return.record_id;
		return_entity = this_status['main_body'].return.entity_id;
	} 
	this_status['main_body'].return = undefined;

	// VALIDATE, on false unblock_screen();
	validate_form(form, entity.table, entity_id, record_id, function(){
		save_do(form, entity.table, record_id, function(response) {

			// REDIRECT
			if (response.id != undefined){
				if (return_id == undefined) return_id = response.id;
				if (return_entity == undefined) return_entity = entity_id;
				// LOAD VIEW
				loadView(return_entity, 4, return_id, '', 0, false, function(){
					// UNBLOCK screen
					unblock_screen();
					return_status[parent_element_id].entity_id = return_entity;
					return_status[parent_element_id].view_id = '2';
					return_status[parent_element_id].record_id = '';
					return_status[parent_element_id].filter = ''; // todo: keep the filter?
				});
			} else {
				menugoto(entity_id, function(){
					// UNBLOCK screen
					unblock_screen();
				});
			}
			
		});

	}, function (messages){ // NOT VALID:
		// Inform errors
		showFieldErrors(messages, form);
		// UNBLOCK
		unblock_screen();
	});

	return false;
}

function showFieldError(field_name, message){
	var c = document.getElementById(field_name);
	c.setCustomValidity(message);
	c.oninput = function(e) { e.target.setCustomValidity(""); };
	c.form.reportValidity();
}

function showFieldErrors(messages, form){
	for (const field_name in messages){
		var c = document.getElementById(field_name);
		c.setCustomValidity(messages[field_name]);
		c.oninput = function(e) { e.target.setCustomValidity(""); };
	}
	form.reportValidity();
}

function save_do(form, table, record_id, callback) {

	// console.log(form);

	var url = script_name + '?action=save&table=' + table;
	
	if (record_id != undefined && record_id != ''){
		url += '&id=' + record_id;
	}

	var suc = function(response){
		// INFORM status/errors
		if (response.error != undefined && response.error != ''){
			show_message(response.error);
		} else {
			show_message(global.lang.LBL_SAVE_SUCCESSFUL, 1000);
		}

		// Trigger rel save
		// link_do(rel_table, id_a, id_b, callback)
		if (this_status['main_body'].rel_save != undefined && response.id != undefined && response.id != ''){
			var rel_id = this_status['main_body'].rel_save.rel_id;
			var side = this_status['main_body'].rel_save.side;
			var rr_id = this_status['main_body'].rel_save.record_id;

			var rel = metadata._relationships[rel_id];
			var id_a = '';
			var id_b = '';
			if (side == 'id_a'){
				id_a = rr_id;
				id_b = response.id;
			} else {
				id_a = response.id;
				id_b = rr_id;
			}
			var rel_url = script_name + '?action=save&table='+ rel.rel_table;
			var rel_data = 'id_a='+id_a+'&id_b='+id_b;

			ajaxPostGUI(rel_url, rel_data, function(response){
				// console.log(response);
			}, false);
		}

		// Unset Trigger rel save
		this_status['main_body'].rel_save = undefined;

		// UPDATE CACHE
		updateCache(table, response.id, response.record);

		if (response.update_metadata){
			console.log('UPDATE METADATA');
			loadMetadata(function (){
				if (callback != undefined) callback(response);
			});			
		} else {
			if (callback != undefined) callback(response);
		}	

	};

	var err = function(jqXHR, textStatus, errorThrown){
		// UNBLOCK screen
		unblock_screen();
		// INFORM error
		if (textStatus == 'parsererror'){
			// console.log(jqXHR.responseText);
			textStatus = textStatus + ' SERVER RESPONSE: ' + jqXHR.responseText;
		}
		show_message('ERROR: ' + textStatus);
		console.log('ERROR: ' + textStatus);
	};

	var befores = function (){
		// console.log('0%');
		var sl = document.getElementById('_screen_lock');
		var slp = document.createElement('span');
		slp.id = '_screen_lock_progress';
		slp.innerHTML = '0%';
		sl.appendChild(slp);
	};
	var uploads = function(event, position, total, percentComplete) {
		var percentVal = percentComplete + '%';
		// console.log(percentVal);
		var slp = document.getElementById('_screen_lock_progress');
		slp.innerHTML = percentVal;
	};
	var completes = function (){
		// console.log('100%');
		var slp = document.getElementById('_screen_lock_progress');
		slp.innerHTML = '100%';
		slp.remove();
	};

	if (typeof(form) == 'string'){
		fetch(url, {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: form
		})
		.then(response => response.json())
		.then(suc)
		.catch(error => {
			err({}, 'error', error.message || error);
		});

	} else {
		var formData = new FormData(form);
		var xhr = new XMLHttpRequest();
		xhr.open("POST", url, true);
		
		befores();
		
		xhr.upload.addEventListener("progress", function(event) {
			if (event.lengthComputable) {
				var percentComplete = Math.round((event.loaded / event.total) * 100);
				uploads(event, event.loaded, event.total, percentComplete);
			}
		});
		
		xhr.onload = function() {
			completes();
			if (xhr.status >= 200 && xhr.status < 300) {
				try {
					suc(JSON.parse(xhr.responseText));
				} catch(e) {
					err(xhr, 'parsererror', '');
				}
			} else {
				err(xhr, 'error', '');
			}
		};
		
		xhr.onerror = function() {
			completes();
			err(xhr, 'error', '');
		};
		
		xhr.send(formData);
	}

}

function save_field(element_id, field_name, table, record_id){
	var e = document.getElementById(element_id);
	var p = e.parentNode;
	while (p.classList != undefined && p.classList.contains('field') == false && p != null){
		p = p.parentNode;
	}
	var value = p.querySelector('[name=' + field_name + ']').value;
	
	save_do(field_name +'=' + value, table, record_id);
	// console.log(element_id, field_name, table, record_id, value);
}

function validate_form(form, table, entity_id, record_id, success, error){
	var do_validate = false;
	
	do_validate = metadata._entities[entity_id].needs_async_validation;

	if (do_validate == 1){
		// perform server-side-validation if needed (submit full submit, without attachments)
		executeAction('validate', 'table=' + table + '&id='+record_id, form, function(response){ 

			if (response.valid){
				success();
			} else {
				error(response.messages);
			}

		}, false);

		// return response error, if any occurs
		// error(response.messages);

	} else {
		success();
	}

	return false;
}

function apply_do(){
	var record_id = this_status['main_body'].record_id;
	var entity_id = this_status['main_body'].entity_id;
	var table = metadata._entities[entity_id].table;
	var return_id;
	var return_entity;
	
	if (record_id == undefined){
		record_id = '';
	}

	var form = document.getElementById('record'+record_id);
	
	// BLOCK SCREEN
	block_screen();

	save_do(form, table, record_id, function (response){
		
		// No need to redirect, only unblock screen:
		unblock_screen();

		// LOAD SAVED RECORD (if it is new)
		if (record_id == ''){
			if (response.id != undefined){
				if (return_id == undefined) return_id = response.id;
				if (return_entity == undefined) return_entity = entity_id;
				// LOAD VIEW
				loadView(return_entity, 1, return_id, '', 0, false, function(){
					// UNBLOCK screen
					unblock_screen();
				});
			} else {
				menugoto(entity_id, function(){
					// UNBLOCK screen
					unblock_screen();
				});
			}
		}

	});

	return false;
	
}

function save_config(form) {
	// BLOCK SCREEN
	block_screen();
	
	var url = script_name + '?action=save_config';

	var suc = function(response){
		// INFORM status/errors
		if (response.error != undefined && response.error != ''){
			show_message(response.error);
		} else {
			show_message(global.lang.LBL_SAVE_SUCCESSFUL, 1000);
		}

		// console.log(response['config']);
		global.config = response['config'];

		// ES NECESARIO RECARGAR LA PAGINA PARA QUE LA CONFIGURACION TENGA EFECTO?

		// UNBLOCK screen
		unblock_screen();
	}

	var err = function(jqXHR, textStatus, errorThrown){
		// UNBLOCK screen
		unblock_screen();
		// INFORM error
		if (textStatus == 'parsererror'){
			// console.log(jqXHR.responseText);
			textStatus = textStatus + ' SERVER RESPONSE: ' + jqXHR.responseText;
		}
		show_message('ERROR: ' + textStatus);
		console.log('ERROR: ' + textStatus);
	};

	if (typeof(form) == 'string'){
		fetch(url, {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded" },
			body: form
		})
		.then(response => response.json())
		.then(suc)
		.catch(error => {
			err({}, 'error', error.message || error);
		});

	} else {
		var formData = new FormData(form);
		var xhr = new XMLHttpRequest();
		xhr.open("POST", url, true);
		
		xhr.onload = function() {
			if (xhr.status >= 200 && xhr.status < 300) {
				try {
					suc(JSON.parse(xhr.responseText));
				} catch(e) {
					err(xhr, 'parsererror', '');
				}
			} else {
				err(xhr, 'error', '');
			}
		};
		
		xhr.onerror = function() {
			err(xhr, 'error', '');
		};
		
		xhr.send(formData);
	}
}

// DELETE
function delete_do(id, table, view_id, entity_id){ 
	if (confirm(global.lang.LBL_DELETE_CONFIRM)){

		block_screen();

		var view = metadata._viewdefs[view_id];
		var entity = metadata._entities[entity_id];

		var url = script_name + '?action=remove&id='+ id +'&table='+table;

		ajaxPostGUI(url, '', function(response){
			if (response.error != undefined && response.error == '') show_message(global.lang.LBL_SAVE_SUCCESSFUL, 1000);

			// REDIRECT
			if (response.id != undefined){
				// REMOVE FROM CACHE
				if (cache[entity.table] != undefined && cache[entity.table][response.id] != undefined) {
					delete cache[entity.table][response.id];
				}

				// IF METADATA, update metadata
				if (metadata[entity.table] != undefined) {
					delete metadata[entity.table][response.id];
					// UPDATE MENU
					renderMenu();
					updateMetadataCache();
				}

				removeBreadCrumb(entity_id, response.id);
				
				// LOAD VIEW
				if (view.records > 1){
					var el = document.getElementById('record' + response.id);
					if (el) el.remove();
					unblock_screen();
				} else {
					menugoto(entity_id, function(){
						// UNBLOCK screen
						unblock_screen();
					});
				}
				
			} else {
				menugoto(entity_id, function(){
					// UNBLOCK screen
					unblock_screen();
				});
			}
		}, false);
		
	}
	return false;

}

function listview_delete(id, table, confirm_msg){
	if (confirm(confirm_msg)){
		var url = 'admin.php?action=remove&id='+ id +'&table='+table;
		fetch(url);
		var el = document.getElementById('_record'+id);
		if (el) el.remove();

		// get entity_id using table
		const entity_id = Object.keys(searchInArray(metadata._entities, 'table', table))[0];
		removeBreadCrumb(entity_id, id);
		
		if (metadata[table] != undefined) {
			delete metadata[table][id];
			// UPDATE MENU
			renderMenu();
			updateMetadataCache();
		}
	}

}