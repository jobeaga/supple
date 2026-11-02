// --- Caché de Registros y Metadatos ---
window.next_cache = window.next_cache || {};
window.prev_cache = window.prev_cache || {};
window.update_metadata_cache_waiting = window.update_metadata_cache_waiting || false;
window.system_ready = window.system_ready !== undefined ? window.system_ready : false;
window.system_ready_callbacks = window.system_ready_callbacks || [];


function whenReady(callback) {
    if (typeof callback != 'function') return;

    if (system_ready) {
        callback();
    } else {
        system_ready_callbacks.push(callback);
    }
}

function setReady() {
    if (system_ready) return;

    system_ready = true;

    while (system_ready_callbacks.length) {
        var callback = system_ready_callbacks.shift();

        try {
            callback();
        } catch (e) {
            console.error('Error in system_ready callback:', e);
        }
    }
}

function ajaxPost(url, post_data, callback){
	var current_url = window.location.href;
	var controller = new AbortController();

	// ABORT requests from previous views
	for (const old_url in active_requests){
		if (old_url != current_url){
			// POP AND abort
			while ( active_requests[old_url].length > 0 ){
				var r = active_requests[old_url].pop();
				// request.abort(); // <= get this object from previous views
				r.abort();
			}
			// And unset aborted requests.
			delete active_requests[old_url];
		}
	}
	if (active_requests[current_url] == undefined) active_requests[current_url] = [];
	
	var suc = function (response){
		if (current_url != window.location.href){
			// console.log('Discard old request: ' + url);
		} else {
			// error handle
			if (response.error != undefined && response.error != ''){
				show_message(response.error);
			}
			if (response.login == true || response.login == undefined){
				// perform callback:
				callback(response);
			} else if (response.login != undefined && response.login == false) {
				window.refresh();
			}
			
		}
	};

	var err = function(jqXHR, textStatus, errorThrown){
		// UNBLOCK screen
		unblock_screen(); // just in case
		// INFORM error
		if (textStatus == 'parsererror'){
			textStatus = textStatus + ' SERVER RESPONSE: ' + (jqXHR ? jqXHR.responseText : '');
		}
		if (textStatus != 'abort'){
			show_message('ERROR: ' + textStatus);
			console.log('ERROR: ' + textStatus);
		}
	};

	if  (post_data == undefined) post_data = '';

	// ADD AUTH_KEY TO REQUEST
	var auth_key = getAuthKey();

	var body;
	var headers = {};

	if (typeof post_data === 'string') {
		if (post_data != '') post_data += '&';
		post_data += '_auth_key=' + encodeURIComponent(auth_key);
		body = post_data;
		headers['Content-Type'] = 'application/x-www-form-urlencoded';
	} else if (post_data instanceof HTMLFormElement || (post_data && post_data.nodeType === 1)) {
		var formData = new FormData(post_data);
		formData.append('_auth_key', auth_key);
		body = formData;
	} else if (post_data instanceof FormData) {
		post_data.append('_auth_key', auth_key);
		body = post_data;
	}

	fetch(url, {
		method: 'POST',
		headers: headers,
		body: body,
		signal: controller.signal
	})
	.then(function(res) {
		return res.text();
	})
	.then(function(responseText) {
		var data;
		try {
			data = JSON.parse(responseText);
		} catch (e) {
			err({ responseText: responseText }, 'parsererror', e);
			return;
		}
		suc(data);
	})
	.catch(function(error) {
		if (error.name === 'AbortError') {
			err(null, 'abort', error);
		} else {
			err({ responseText: error.message }, error.message || 'error', error);
	}
	})
	.finally(function(){
    	removeActiveRequest(current_url, controller);
	});

	// SAVE AbortController in request history (it has .abort() like jqXHR)
	active_requests[current_url].push(controller);

	return false;

}

function removeActiveRequest(url, controller){

    var requests = active_requests[url];

    if (requests == undefined) return;

    var index = requests.indexOf(controller);

    if (index != -1) requests.splice(index, 1);

}

function ajaxPostGUI(url, post_data, success, blockscreen, callback){

	if (blockscreen == undefined || blockscreen == true) block_screen();

	return ajaxPost(url, post_data, function(response){

		if (success != undefined) success(response); 

		if (blockscreen == undefined || blockscreen == true) unblock_screen();

		if (callback != undefined && response.error == '') callback(response);

	});

}

function executeAction(action, get_data, post_data, success, blockscreen, callback){

	var url = script_name + '?action=' + action + '&'+ get_data;
	ajaxPostGUI(url, post_data, success, blockscreen, callback);

}

function getData(table, filter, offset, count, order, callback, isRel){

	if (!system_ready) {
        whenReady(function(){
            getData(table, filter, offset, count, order, callback, isRel);
        });
        return;
    }
	
	if (metadata[table] != undefined && isSimpleFilter(filter) && (isRel == undefined || isRel == false)) {

		return getMetadata(table, offset, count, order, callback, filter); // problema: getMetadata no trae relaciones

	} else if (cache[table] != undefined && isCacheTable(table) && isSimpleFilter(filter) && (isRel == undefined || isRel == false)) {

		return getMetadata(table, offset, count, order, callback, filter, true); 

	} else {
		
		var url = script_name + '?action=read&table=' + table + '&offset='+ offset + '&count='+ count;

		if (order != undefined && order != ''){
			url = url + '&order=' + order; 
		}

		ajaxPost(url, filter, function (response){
			// cache data:
			if (response.error == ''){
				response.data.forEach(function (record){

					if (cache[response.table] == undefined){
						cache[response.table] = {};
					}

					cache[response.table][record.id] = Object.assign({}, record);
					cache[response.table][record.id]._nextid = '';
					cache[response.table][record.id]._previousid = '';

				});

				if (next_cache[response.table] == undefined) next_cache[response.table] = {};
				if (prev_cache[response.table] == undefined) prev_cache[response.table] = {};

				response.data.forEach(function (record){

					next_cache[response.table][record.id] = record._nextid;
					prev_cache[response.table][record.id] = record._previousid;

				});
				callback(response.data, response.table, response.count);
			}
		});
	}
}

function isSimpleFilter(f){
	var t = unserialize(f);
	for (const i in t){ 
		if (typeof i != 'string') return false;
		if (i.substring(0,1) == '_') return false;
	}
	return true;
}

function isCacheTable(table){
	var tables = global.config.cache_tables.split(',');
	return tables.includes(table);
}

function executeData(table, methods, filter, order, callback){
	var url = script_name + '?action=execute&table=' + table;
	if (order != undefined && order != ''){
		url += '&order=' + order; 
	}
	// Methods:
	if (typeof methods == 'string'){
		url += '&method[]=' + methods; 
	} else {
		for (const i in methods){ 
			url += '&method[]=' + methods[i]; 
		}
	}
	// TODO: receive ids
	ajaxPost(url, filter, function (response){
		callback(response.data, response.table, response.count);
	});

}

function getMetadata(table, offset, count, order, callback, filter, usecache){
	// 1: get data
	var data = [];
	if (usecache == undefined || usecache == false){
		data = Object.values(metadata[table]);
	} else {
		data = Object.values(cache[table]);
	}
	
	// deep clone data
	data = JSON.parse(JSON.stringify(data));

	if (order == undefined) order = '';
	// order reverse:
	var order_parts = order.split(' ');
	var reverse = false;
	if (order_parts.length > 1){
		order = order_parts[0];
		if (order_parts[1].toUpperCase() == 'REVERSE') reverse = true;
	}

	// 1b: filter data
	if (filter != undefined){
		//console.log(data);
		//console.log(filter);
		var f = unserialize(filter);
		for (const i in f){ 
			data = searchInArray(data, i, f[i]);
		}
		data = Object.values(data);
		// console.log(data);
	}
	
	// 2: order data
	if (order != ''){
		data = data.sort(function (a, b) {
			var a_value = a[order];
			var b_value = b[order];
			// console.log(typeof(a[order])+" vs "+typeof(b[order]));
			if (!isNaN(a_value) && !isNaN(b_value)){
				a_value = +a_value;
				b_value = +b_value;
			}

			if (reverse){
				if (a_value < b_value)
					return 1;
				if (a_value > b_value)
					return -1;
			} else {
				if (a_value < b_value)
					return -1;
				if (a_value > b_value)
					return 1;
			}
			return 0;
		});
	}
	// 3: _nextid AND _previousid
	var prev_id = '';
	var prev_i = undefined;
	data.forEach(function (e, i){
		e._previousid = prev_id;
		e._nextid = '';
		if (prev_i != undefined) {
			data[prev_i]._nextid = e.id;
		}	
		prev_id = e.id;
		prev_i = i;
	});
	data.forEach(function (e, i){
		if (next_cache[table] == undefined) next_cache[table] = {};
		if (prev_cache[table] == undefined) prev_cache[table] = {};
		next_cache[table][e.id] = e._nextid;
		prev_cache[table][e.id] = e._previousid;
	});

	// 4: slice data
	var return_data = data.slice(offset, parseInt(offset) + parseInt(count));
	
	// 5: invoke callback
	callback(return_data, table, data.length);
}

function sortData(data, order, reverse){
	// if (data == undefined || data == null) return data;
	var data = Object.values(data);
	var data = JSON.parse(JSON.stringify(data));
	return data.sort(function (a, b) {
		var a_value = a[order];
		var b_value = b[order];
		// console.log(typeof(a[order])+" vs "+typeof(b[order]));
		if (!isNaN(a_value) && !isNaN(b_value)){
			a_value = +a_value;
			b_value = +b_value;
		}

		if (reverse){
			if (a_value < b_value)
				return 1;
			if (a_value > b_value)
				return -1;
		} else {
			if (a_value < b_value)
				return -1;
			if (a_value > b_value)
				return 1;
		}
		return 0;
	});
}

function getFromCache(table, id){

    if (id == '') return {};

    if (!system_ready) return undefined;

    if (metadata[table] != undefined) {
        return metadata[table][id];
    }

    if (cache[table] != undefined) {
        return cache[table][id];
    }

    return undefined;
}

function setData(table, id, field, value, callback){
	getUrlAjax('admin.php?action=save&table='+table+'&id='+id, '', field+'='+value, '', '', callback);
}

function setDataTo(element_id, table, field, id){
	getRecord(table, id, function(data){
		if (data != undefined && field != undefined && field != ''){
			setHtml(element_id, data[field]);
		}
	});
}

function getRecord(table, id, callback, index) {
	if (!system_ready) {
        whenReady(function(){
            getRecord(table, id, callback, index);
        });
        return;
    }

	if (metadata[table] != undefined && metadata[table][id] != undefined){
		if (index != undefined){
			callback(metadata[table][id], index);
		} else {
			callback(metadata[table][id]);
		}
	} else {
		if (cache[table] == undefined || cache[table][id] == undefined){
			getData(table, 'id='+id, 0, 1, '', function (data, table, count){
				if (index != undefined){
					callback(data[0], index);
				} else {
					callback(data[0]);	
				}
				
			});
		} else {
			if (index != undefined){
				callback(cache[table][id], index);
			} else {
				callback(cache[table][id]);	
			}
			
		}
	}
}

function updateCache(table, id, row){
	if (table == undefined) return;
	if (id == undefined) return;
	if (row == undefined) return;

	// UPDATE CACHE
	if (cache[table] != undefined/* && cache[table][id] != undefined*/) {
		// delete cache[table][response.id];
		cache[table][id] = row;
		// DO NOT CACHE _nextid and _previousid
		cache[table][id]['_nextid'] = '';
		cache[table][id]['_previousid'] = '';
	}

	// IF METADATA, update or create metadata
	if (typeof(metadata) != 'undefined' && metadata[table] != undefined) {
		metadata[table][id] = row;
		// UPDATE MENU
		renderMenu();
		updateMetadataCache();
	}
}

function updateMetadataCache(wait, callback){

    var w = wait != undefined ? wait : 10000;

    if (update_metadata_cache_waiting) {
        clearTimeout(update_metadata_cache_waiting);
    }

    update_metadata_cache_waiting = setTimeout(function(){

        update_metadata_cache_waiting = false;

        executeAction('update_metadata_cache', '', '', undefined, false, callback);

    }, w);
}

function loadMetadata(callback){
	ajaxPostGUI('cache/suppleCache/metadata_cache.json', '', function (data){ 
		metadata = data; 
		renderMenu();
		setReady();
		if (callback != undefined) callback();		
	});
}

function prev_cache_order(table, id){
	if (prev_cache[table] == undefined){
		return 0;
	} else if (prev_cache[table][id] == undefined || prev_cache[table][id] == ''){
		return 0;
	} else {
		return prev_cache_order(table, prev_cache[table][id]) + 1;
	}
}

function reload_do(parent_element_id){
	if (!system_ready) {
        whenReady(function(){
            reload_do(parent_element_id);
        });
        return false;
    }

	if (parent_element_id == undefined) parent_element_id = 'main_body';
	var entity_id = this_status[parent_element_id].entity_id;
	var view_id = this_status[parent_element_id].view_id;
	var record_id = this_status[parent_element_id].record_id;
	var filter = this_status[parent_element_id].filter;
	var offset = this_status[parent_element_id].offset;
	var pre_filled = this_status[parent_element_id].pre_filled;
	var order = this_status[parent_element_id].order;

	getEl(parent_element_id, function (el){
		el.innerHTML = '';
		el.appendChild(createEl('span', {className: 'loader'}));
	}); 

	// clear cache? (for all tables?)
	cache = {};
	// load default data:
	var tables = global.config.cache_tables.split(',');
	reload_cache(tables, function(){
		loadView(entity_id, view_id, record_id, filter, offset, undefined, undefined, pre_filled, parent_element_id, order);
	});

	// TODO: clear metadata cache???
	// loadMetadata(function () {  ...  });

	return false;
}

function reload_cache(tables, callback){
	if (tables.length == 0) {
		setTimeout(callback, 1);
	} else {
		var t = tables.pop();
		if (t == ''){
			reload_cache(tables, callback);
		} else {
			getData(t, '', 0, global.config.cache_tables_row_limit, '', function(){
				reload_cache(tables, callback);
			});
		}
	}
}