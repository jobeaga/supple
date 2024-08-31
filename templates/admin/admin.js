var template_cache = {};
var this_status = {}; // inicialmente undefined
var return_status = {}; // inicialmente undefined 
var editable = 0;
var listable = 0;
var deleteable = 0;
var viewable = 0;
var submit_lock_count = 0;
var multi_count = {};
var multi_row = {};
var next_cache = {};
var prev_cache = {};
var active_requests = {};
var urls = {};


// MENU:
function menugo() {
	var entity_id = getValue('menuselector');
	if (entity_id == ''){
		renderTabGroup(document.querySelector('#menuselector option:checked').attributes.tab_group_id.value);
	} else {
		menugoto(entity_id);
	}
}

function menugoto(i, callback) {
	var view = menuviewfor(i);
	var id = menuidfor(i);
	// console.log(view + ' ' + id);
	/*if (view == "" && id == ""){
		getUrl(menugotourl(i));
	} else {*/
		//getUrl(menugotourl(i));
		loadView(i, view, id, undefined, 0, false, callback);
	//}
	return false;
}

function menugotourl(i){
	return urls[i];
}

function menuviewfor(i){
	/*  si tiene view2, 2 
		else si tiene view4, y hay un registro, 4 
		else si tiene view1, 1   */
	var entity_row = metadata._entities[i];
	if (entity_row.view2 == 1) return 2;
	if (entity_row.view4 == 1 && menuidfor(i) != '') return 4;
	if (entity_row.view1 == 1) return 1;
	return '';
}

function menuidfor(i){
	var entity_row = metadata._entities[i];
	if (entity_row.view2 == 1) return undefined;
	if (entity_row.view4 == 1 || entity_row.view1 == 1) return first_id[i];
	return "";
}

// GENERIC
function searchInArray(a, f, v){
	// a: array
	// f: field name
	// v: value
	// r: returns: record
	var r = {};
	if (a == undefined) return r;
	$.each(a, function (i,e){
		if (e[f] == v) r[i] = e;
	});
	return r;
}

// GENERIC AJAX AND SUPPLEDATA
function ajaxPost(url, post_data, callback){
	var current_url = window.location.href;
	var request = null; // El objeto que se puede abortar

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
			// console.log(jqXHR.responseText);
			textStatus = textStatus + ' SERVER RESPONSE: ' + jqXHR.responseText;
		}
		if (textStatus != 'abort'){
			show_message('ERROR: ' + textStatus);
			console.log('ERROR: ' + textStatus);
		}
	};

	if  (post_data == undefined) post_data = '';

	// ADD AUTH_KEY TO REQUEST
	var auth_key = getAuthKey();

	if (typeof(post_data) == 'string'){
		// auth_key
		if (post_data != '') post_data += '&';
		post_data += '_auth_key='+auth_key;

		request = $.ajax({
			type: "POST",
			url: url,
			data: post_data,
			success: suc,
			error: err,
			dataType: "json"
		});
	} else {
		request = $(post_data).ajaxSubmit({
			method: "POST",
			url: url, 
			success: suc,
			error: err,
			dataType: "json"
		}).data().jqxhr;
	}
	// SAVE request object on request history
	active_requests[current_url].push(request);

	return false;

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
				$.each(response.data, function (i, record){
					response_table = response.table;
					if (cache[response_table] == undefined){
						cache[response_table] = {};	
					}
					cache[response_table][record.id] = jQuery.extend({}, record);
					// DO NOT CACHE _nextid and _previousid
					cache[response_table][record.id]['_nextid'] = '';
					cache[response_table][record.id]['_previousid'] = '';
					// NEW: CACHE NEXT AND PREV
					$.each(response.data, function (i, e){
						if (next_cache[table] == undefined) next_cache[table] = {};
						if (prev_cache[table] == undefined) prev_cache[table] = {};
						next_cache[table][e.id] = e._nextid;
						prev_cache[table][e.id] = e._previousid;
					});
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
	$.each(data, function (i, e){
		e._previousid = prev_id;
		e._nextid = '';
		if (prev_i != undefined) {
			data[prev_i]._nextid = e.id;
		}	
		prev_id = e.id;
		prev_i = i;
	});
	// NEW: CACHE NEXT AND PREV
	$.each(data, function (i, e){
		if (next_cache[table] == undefined) next_cache[table] = {};
		if (prev_cache[table] == undefined) prev_cache[table] = {};
		next_cache[table][e.id] = e._nextid;
		prev_cache[table][e.id] = e._previousid;
	});

	// 4: slice data
	return_data = data.slice(offset, parseInt(offset) + parseInt(count));
	
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
	if (id == ''){
		return {};
	} else {
		if (metadata[table] != undefined) {
			return metadata[table][id];
		} else {
			return cache[table][id];
		}
	}
}

function setDataTo(element_id, table, field, id){
	getRecord(table, id, function(data){
		if (data != undefined && field != undefined && field != ''){
			$('#'+element_id).html(data[field]);
		}
	});
}

function getRecord(table, id, callback, index) {
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
	if (metadata[table] != undefined) {
		metadata[table][id] = row;
		// UPDATE MENU
		renderMenu();
		updateMetadataCache();
	}
}

var update_metadata_cache_waiting = false;
function updateMetadataCache(wait, callback){
	// delay the action, and make sure it only executes once:
	var w = 10000;
	if (wait != undefined) w = wait;
	if (update_metadata_cache_waiting == false){
		update_metadata_cache_waiting = true;
		setTimeout(function(){
			executeAction('update_metadata_cache', '', '', undefined, false, callback);
			update_metadata_cache_waiting = false;
		}, w);
	}	
}

function getLanguageCodes() {
	var r = [];
	$.each(metadata._languages, function (i, lang){
		r.push('_' + lang.code);
	});
	return r;
}

function renderMenu(){
	var menuselector_html = '';
	var ulmenu_html = '';
	var menuselector_content = '';
	var ulmenu_content = '';
	var entity_tab_count;
	var xt;
	// TABGROUPS in order
	var tab_groups_order = sortData(metadata._tab_groups, 'order');
	// TABGROUP ENTITIES in order

	for (const tab_group of tab_groups_order){
		// var tab_group = metadata._tab_groups[tab_group_id];
		if (tab_group.adminonly == 0 || tab_group.adminonly == current_user.isadmin){
			// RENDER TAB CONTENT
			menuselector_content = '';
			ulmenu_content = '';
			entity_tab_count = 0;
			for (const tge_id in metadata._tab_group_entities){  // TODO: order by ENTITY order
				var tge = metadata._tab_group_entities[tge_id];
				if (tge.id_b == tab_group.id){
					entity = metadata._entities[tge.id_a];

					if ((entity.adminonly == 0 || entity.adminonly == current_user.isadmin) && entity.show == 1){

						// RENDER ENTITY ITEM
						if (current_user.isadmin == 1 || user_has_permission(current_user.id, entity.id)){

							entity_tab_count++;
							var extra = '';
							if (entity.view2 == 1){
								extra += '&view=2';
							} else {
								if (first_id[entity.id] == undefined){
									if (entity.view1 == 1){
										extra += '&view=1';
									} // else?
								} else {
									if (entity.view4 == 1){
										extra += '&view=4&id='+first_id[entity.id];
									} else {
										if (entity.view1 == 1){
											extra += '&view=1&id='+first_id[entity.id];
										} // else?
									}
								}
							}

							// URLS
							urls[entity.id] = script_name + "?entity=" + entity.id + extra;

							var option_selected = '';
							var li_selected = '';
							if (this_status['main_body'] != undefined && entity.id == this_status['main_body'].entity_id){
								option_selected = ' selected="selected" ';
								li_selected = ' class="selected" ';
							}

							if (tab_group.id != '1'){
								menuselector_content += '<option value="' + entity.id + '"' + option_selected + '> &nbsp;&nbsp; '+ entity.name + ' </option>';
							}

							ulmenu_content += '<li><a href="'+ script_name +'?entity=' + entity.id + extra + '" onclick="return menugoto(\'' + entity.id + '\');" ' + li_selected + '>' + entity.name +'</a>';

							// SUBMENU: ACTIONS 
							ulmenu_content += '<div>';
							// GENERAL view buttons (if applies)
							for (const vb_id in metadata._viewbuttons){
								var vb = metadata._viewbuttons[vb_id];
								if (vb.view_id == 2){
									var vv = 'view'+vb.target_view;
									if (entity[vv] == 1 || vb.target_view == ''){
										if (vb.js_code == '' || vb.js_code == undefined){
											ulmenu_content += '<a href="' + script_name + '?entity='+entity.id;
											if (vb.target_view != '') ulmenu_content += '&view='+vb.target_view;
											if (vb.filter != '') ulmenu_content += '&'+vb.filter;
											ulmenu_content += '" onclick="return nav_viewbutton(\'' + vb.id + "', '" + entity.id + '\')">' + vb.label + '</a>';
										} else {
											var extra_js = "this_status['main_body'].entity_id='"+ entity.id +"';this_status['main_body'].view_id='"+ vb.target_view+"';this_status['main_body'].filter='"+vb.filter+"';this_status['main_body'].record_id='';";
											ulmenu_content += '<a href="javascript:' + extra_js + vb.js_code + '" onclick="' + extra_js + vb.js_code + '">' + vb.label + '</a>';
										}
									}
								}
							}
							// Buttons for custom views of THIS entity
							for (const cb_id in metadata._custom_views){
								var cb = metadata._custom_views[cb_id];
								if (cb.view==2 && cb.parent == entity.id){
									ulmenu_content += '<a href="' + script_name + '?entity='+entity.id+'&view=5&custom_view_id='+cb.id+'" onclick="return nav_customviewbutton(\'main_body\', \''+cb.id+'\', \''+entity.id+'\', \'2\', \'\')">' + cb.name + '</a>';
								}
							}
							// END:
							ulmenu_content += '</div></li>';

						}

					}
				}
			}

			xt = '';
			if (entity_tab_count > 12) xt = 'style="columns:2"';
			ulmenu_content = '<ul id="tabgroup'+tab_group.id+'" ' + xt + '>' + ulmenu_content + '</ul>';

			// RENDER GROUP TAB
			//if (menuselector_content != ''){
				var menuitem_js = "renderTabGroup('"+tab_group.id+"')";
				
				menuselector_html += '<option value="" tab_group_id="'+ tab_group.id +'">' + tab_group.name + '</option>' + menuselector_content;
				ulmenu_html += '<li class="menuitem"><a href="javascript:'+menuitem_js+'" onclick="return '+menuitem_js+'">' + tab_group.name + '</a>' + ulmenu_content + '</li>';
			//}
		}
	}
	// TO GUI:
	document.getElementById('menuselector').innerHTML = menuselector_html;
	document.getElementById('ulmenu').innerHTML = ulmenu_html;
}

function loadMetadata(callback){
	ajaxPostGUI('cache/suppleCache/metadata_cache.json', '', function (data){ 
		metadata = data; 
		renderMenu();
		if (callback != undefined) callback();
	});
}

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
		if (record_id != undefined) filter = 'id='+record_id;
	}
	if (record_id == undefined) record_id = '';
	if (view_id == undefined) return false;
	if (entity_id == undefined) return false;

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

window.onpopstate = function (event) {
	if (event.state == null){
		getUrl(script_name);
	} else {
		loadView(event.state.entity_id, event.state.view_id, event.state.record_id, event.state.filter, event.state.offset, true);
	}
}

function addBreadcrumb(entity_id, view_id, record_id){
	var entity = metadata._entities[entity_id];
	var first_field = getFirstField(entity_id, '2');
	var bc_id = '_breadcrumb_'+entity_id+'_'+ record_id;
	
	getRecord(entity.table, record_id, function(record){
		// Get name:
		var label = '';
		if (record != undefined && first_field != undefined && record[first_field.name] != undefined){
			label = record[first_field.name];
		}
		var bct = parseInt(global.config.breadcrumb_trim);
		if (label.length > bct){
			label = label.slice(0, bct)+'...';
		}
		if (entity.name != undefined && entity.name != ''){
			label += ' <span class="breadEntityName">' + entity.name + '</span>';
		}
		// Remove previous breadcrumbs of same record
		removeBreadCrumb(entity_id, record_id);
		// create link
		var html = '<div id="' + bc_id +'">' + renderLinkTo(entity_id, view_id, record_id, label, false) + '</div>';
		document.getElementById('_breadcrumbs').innerHTML = html + document.getElementById('_breadcrumbs').innerHTML;
	});
}

function removeBreadCrumb(entity_id, record_id){
	var bc_id = '_breadcrumb_'+entity_id+'_'+ record_id;
	var elem = document.getElementById(bc_id);
	if (elem != undefined) {
		elem.parentNode.removeChild(elem);
	}
}

function getFirstField(entity_id, view_id){
	// Calculate first field of the entity
	var fields = Object.values(searchInArray(searchInArray(metadata._fields, 'parent', entity_id), 'view' + view_id, '1')).sort(function (a, b) { return a.order - b.order; });
	return fields[0];
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

function prev_cache_order(table, id){
	if (prev_cache[table] == undefined){
		return 0;
	} else if (prev_cache[table][id] == undefined || prev_cache[table][id] == ''){
		return 0;
	} else {
		return prev_cache_order(table, prev_cache[table][id]) + 1;
	}
}

// AUTOLOAD
var autoloading = 0;
var autoload_enabled = 0;
var autoload_offset;
function autoload_next(){
	if (autoloading == 0 && autoload_enabled == 1){
		// console.log('reach');
		autoloading = 1;
	
		var parent_element_id = 'main_body';
		var entity_id = this_status[parent_element_id].entity_id;
		var view_id = this_status[parent_element_id].view_id;
		var filter = this_status[parent_element_id].filter;
		var record_id = this_status[parent_element_id].record_id;
		var order = this_status[parent_element_id].order;

		if (view_id != '' && entity_id != ''){

			var entity = metadata._entities[entity_id];
			var view = metadata._viewdefs[view_id];
			var fields = Object.values(searchInArray(searchInArray(metadata._fields, 'parent', entity_id), 'view'+ view_id, '1')).sort(function (a, b) { return a.order - b.order; });

			// view.class + 'Form'
			
			// NEXT OFFSET:
			autoload_offset = autoload_offset + parseInt(view.records);

			if (order == undefined) {
				order = entity.listview_order;
			}

			// GET DATA
			getData(entity.table, filter, autoload_offset, view.records, order, function (data){
				var html = '';
				// RENDER DATA
				// console.log(data);
				$.each(data, function (i, row){
					// CHECK FOR DUPLICATED RECORDS
					if (document.querySelectorAll('#' + parent_element_id + ' #' + view.class + 'Form #record'+row.id).length == 0){
						html +=  renderViewBodyRecord(row.id, row, view, fields, entity, parent_element_id);
					}
				});
				$('#' + parent_element_id + ' #' + view.class + 'Form').append(html);
				// Stop when the end is reached:
				if (data.length < view.records){
					autoload_enabled = 0;
				}
				// And enable the next:
				autoloading = 0;
			});
		}

	}
}

$(window).scroll(function() {
	if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
		autoload_next();
	}
 });

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
		}
		html += f.label;
		if (!join_with_next){
			html += '</div>';
		}
	}
	if (search_result == undefined) html +=  '<div class="label post buttons"></div>';
	html +=  '</div>';  // END head

	// RECORDS
	//html +=  '<div class="records">';
	var count = 0;
	$.each(data, function (i, row){
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
	$.each(field_groups, function (i,e){ 
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
		
		if (i > 9) extra += ' above10';
		if (f.multiple == 1) extra += ' multiple';
		if (f.required == 1) extra += ' required';

		if (f.join_with_previous == 1){
			// f_html += '<strong>'+ f.label +': </strong>';
		} else { // this field joins with previous
			f_html += '<div class="field field'+ i + extra + '">';
			f_html += '<label>'+ f.label +': </label>';
			f_html += '<span class="group">';
		}

		// FIELD!!!
		field_html =  renderField(f.id, ((view.editable == 1 && editable == 1 && search_result == undefined) || (f.access == 'listedit' && view.id == 2)), row[f.name], suffix, row);

		if (i == 0 && (view.editable == 0 || editable == 0) && view.id != '4') { // first!
			if (search_result == undefined){
				f_html +=  renderLinkTo(entity.id, '4', id, field_html);
			} else {
				var el_id = 'rel_search_'+ parent_element_id +'_' + id;
				f_html +=  '<a id="'+ el_id +'" href="#">' + field_html + '</a>'; 
				while_selector('#'+el_id, function (){
					$('#'+el_id).click(function(){
						search_result(id);
						return false;
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

function renderTabGroup(tab_group_id){
	var html = '';
	var tg;
	
	if (tab_group_id != '' && metadata._tab_groups[tab_group_id] != undefined){
		tg = metadata._tab_groups[tab_group_id];
		// TITLE
		html += '<h1>'+tg.name+'</h1>';
		
		// GROUP ENTITIES:
		html += '<div class="tab_group_list">';
		var tge = searchInArray(metadata._tab_group_entities, 'id_b', tab_group_id);
		for (const tge_id in tge){ 
			var entity_id = tge[tge_id].id_a;
			var entity = metadata._entities[entity_id];
			var entity_url = script_name + '?entity=' + entity.id;

			if (urls[entity.id] != undefined){
				entity_url = urls[entity.id];
			}
			
			// PERMISSIONS
			var _permiso = (current_user.isadmin == 1 ||user_has_permission(current_user.id, entity.id));

			// PANEL
			if (_permiso == true && entity.show == 1){
				html += '<div class="tab_group_dashboard"><h2><a href="'+entity_url+'" onclick="return menugoto(\'' + entity.id + '\');">'+ entity.name + '</h2>';
				for (const vb_id in metadata._viewbuttons){
					var vb = metadata._viewbuttons[vb_id];
					if (vb.view_id == 2){
					}
				}
				// GENERAL view buttons (if applies)
				for (const vb_id in metadata._viewbuttons){
					var vb = metadata._viewbuttons[vb_id];
					if (vb.view_id == 2){
						var vv = 'view'+vb.target_view;
						if (entity[vv] == 1 || vb.target_view == ''){
							if (vb.js_code == '' || vb.js_code == undefined){
								html += '<a class="button" href="' + script_name + '?entity='+entity.id;
								if (vb.target_view != '') html += '&view='+vb.target_view;
								if (vb.filter != '') html += '&'+vb.filter;
								html += '" onclick="return nav_viewbutton(\'' + vb.id + "', '" + entity.id + '\')">' + vb.label + '</a>';
							} else {
								var extra_js = "this_status['main_body'].entity_id='"+ entity.id +"';this_status['main_body'].view_id='"+ vb.target_view+"';this_status['main_body'].filter='"+vb.filter+"';this_status['main_body'].record_id='';";
								html += '<a class="button" href="javascript:' + extra_js + vb.js_code + '" onclick="' + extra_js + vb.js_code + '">' + vb.label + '</a>';
							}
						}
					}
				}
				// Buttons for custom views of THIS entity
				for (const cb_id in metadata._custom_views){
					var cb = metadata._custom_views[cb_id];
					if (cb.view==2 && cb.parent == entity.id){
						html += '<a class="button" href="' + script_name + '?entity='+entity.id+'&view=5&custom_view_id='+cb.id+'" onclick="return nav_customviewbutton(\'main_body\', \''+cb.id+'\', \''+entity.id+'\', \'2\', \'\')">' + cb.name + '</a>';
					}
				}
				// ENTITY END:
				html += '</div>';
			}
		}
		html += '</div>';

	}
	document.getElementById('main_body').innerHTML = html;
	return false;
}

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
		document.querySelectorAll('.searchform input').forEach(function(e){ 
			e.addEventListener('keydown', function (ev){ 
				if(ev.key == 'Enter'){ search_do('main_body'); ev.preventDefault(); return false; } 
			}); 
		});
		// Prefill filter fields
		var f = unserialize(this_status["main_body"].filter);
		for (const i in f){ 
			var e = document.querySelector('.searchform #' + i);
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
	$.each(search_fields, function (i, field){
		html +=  '<div class="view row searchfield' + e + '"><div class="view three columns label">' + field.label + '</div><div class="view five columns">';
		e = '';

		var dt = metadata._datatypes[field.type]

		if (dt.search_ops != undefined){
			var first_so;
			// Construir un desplegable de opciones
			html += '<div class="search_op" id="search_op_'+field.id+'"><select name="_op_'+ field.name +'" id="_op_'+ field.name +'" onchange="renderSearchFieldChange(\''+ field.id + '\', $(this).val());">';
			$.each(dt.search_ops, function (i, so_id){
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

var searchform_collapse_toggle_status = false;
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
	var so_id = $('#_op_'+field.name+' option:selected').attr('so_id');
	var so = metadata._search_ops[so_id];
	var html = renderSearchField(field, so);
	$('#_param_'+field.name).html(html);

	// Enter as search button
	document.querySelectorAll('#_param_'+field.name +' input').forEach(function(e){ e.addEventListener('keydown', function (ev){ if(ev.key == 'Enter'){ search_do('main_body'); ev.preventDefault(); return false;  }; }); });
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

	if (Object.keys(extend_views).length > 0) {
		html +=  '<span id="extend_views'+ position +'" class="extend_view"></span>';
		$.each(extend_views, function (i, extend){
			if (extend.type == 0){
				// HTML
				while_selector('#extend_views'+ position, function (){
					$('#extend_views'+ position).append(extend.template);
				});
			} else if (extend.type == 1){
				// JAVASCRIPT
				while_selector('#extend_views'+ position, function (){
					$('#extend_views'+ position).append('<script>'+ extend.template+'</script>');
				});
			} else if (extend.type == 2){
				// RENDER SERVER SIDE
				renderParsed('_extend_views', 'template', extend.id, entity_id, view_id, record_id, 'extend_views'+ position);
			}
		});
	}

	return html;
}

function renderRelationships(entity_id, record_id, row){
	var html = '';
	html = '<div class="subpanels">';
	var rels_a = searchInArray(metadata._relationships, 'id_b', entity_id);
	var rels_b = searchInArray(metadata._relationships, 'id_a', entity_id);
	$.each(rels_a, function (i, r){
		// Si es una relacion de la misma entidad y lleva el mismo nombre de subpanel de ambos lados, es de equivalencia (se procesa diferente)
		// De lo contrario, se procesa igual que cualquier relacion.
		if ((r.id_a != r.id_b || r.label_a != r.label_b) && r.label_a != ''){
			// html += r.label_a+'<br>';
			html += renderRelationship(r.id, record_id, r.id_a, r.rel_table, r.entity_a_field, r.entity_b_field, r.label_a, r.button_a, r.remove_a, r.limit_a, r.selector_a, r.search_a, r.filter_a, r.type, 'id_b', row, false);
		}
	});
	$.each(rels_b, function (i, r){
		// idem.... IDEM?
		if ((r.id_a != r.id_b || r.label_a != r.label_b) && r.label_b != ''){
			// html += r.label_b+'<br>';
			html += renderRelationship(r.id, record_id, r.id_b, r.rel_table, r.entity_b_field, r.entity_a_field, r.label_b, r.button_b, r.remove_b, r.limit_b, r.selector_b, r.search_b, r.filter_b, r.type, 'id_a', row, false);
		}
	});
	//console.log(rels_a);
	//console.log(rels_b);
	// TODO: RELACIONES DE LA MISMA ENTIDAD: Son de equivalencia cuando tienen el mismo label, por ende utilizan solo un subpanel. (deben tener label no vacio)
	$.each(rels_a, function (i, r){
		// Si es una relacion de la misma entidad y lleva el mismo nombre de subpanel de ambos lados, es de equivalencia (se procesa diferente)
		if ((r.id_a == r.id_b && r.label_a == r.label_b) && r.label_a != ''){
			// html += r.label_a+' (SAME ENTITY)<br>';
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
		if (!$.isEmptyObject(ent_rel) && type == 1){
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
		html += '<span id="relationship_'+ rel_id +'" class="relationship_panel listview"><img class="loader" src="templates/admin/images/loader.gif"></span>';
		
		// AND RENDER
		render_do();

	}
	return html;
}

function renderRelationshipDropdownSelector(rel_id, record_id, rel_table, r_entity_id, field_a, field_b, limit, search, filter, type, side, row, render_do){
	var rent = metadata._entities[r_entity_id];
	var html = '';
	html += '<div class="panel selector">';
	
	$.each(search, function (i, search_id){
		var search_field = metadata._fields[search_id];
		html += search_field.label + ': ';
		html += '<select id="relsearch_'+ rel_id + '_' + search_id + '"><option value="">'+ global.lang.LBL_NONE + '</option>';
		html += '</select> <input type=button id="relselect_'+ rel_id + '_' + search_id +'" value="'+ global.lang.LBL_SELECT +'" class="button-primary search_button"><br>';
		// GENERATE DROPDOWN
		while_selector('#relsearch_'+ rel_id + '_' + search_id, function(){
			getData(rent.table, filter, 0, 100, search_field.name, function (data){
				$.each(data, function (i,e){
					$('#relsearch_'+ rel_id + '_' + search_id).append('<option value="'+e['id']+'">'+e[search_field.name]+'</option>');
				});
			}, true);
		});
		// SELECT BUTTON CLICK HANDLER
		while_selector('#relselect_'+ rel_id + '_' + search_id, function(){
			$('#relselect_'+ rel_id + '_' + search_id).click(function(){
				// if selected element is not empty relate!
				selected_id = $('#relsearch_'+ rel_id + '_' + search_id).val();
				selected_count = $('#relationship_'+ rel_id +' .record').length;
				if (selected_id != '' && (selected_count < limit || limit == 0 || limit == '') && $('#relationship_'+ rel_id +' .record#record'+ selected_id).length == 0){
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
		});
	});
	html += '</div>';
	return html;
}

function renderRelationshipSearchSelector(rel_id, record_id, rel_table, r_entity_id, field_a, field_b, limit, search, filter, type, side, row, render_do){
	var html = '';
	var rent = metadata._entities[r_entity_id];
	html += '<div class="panel selector">';

	$.each(search, function (i, search_id){
		var search_field = metadata._fields[search_id];
		if (search_field != undefined){
			var input_id_name = search_field.name + '_' + rel_id;
			html += search_field.label + ': ';
			html += renderBasicSingleField(search_field.id, 1, '', '_' + rel_id, '_' + rel_id);
			
			if (parseInt(i) + 1 == search.length){
				html += ' <input type=button id="relsearch_'+ rel_id + '" value="'+ global.lang.BTN_SEARCH +'" class="button-primary search_button">';
			} else {
				html += '<br>';
			}
		}	

		// Search callback:
		var search_callback = function (){
			var f = filter;
			var v = '';
			if (f == undefined) f = '';

			$.each(search, function (j, sf_id){
				var sf = metadata._fields[sf_id];
				var iin = sf.name + '_' + rel_id;
				v = $('#' + iin).val();
				if (v != ''){
					if (f != '') f = f + '&';
					f = f + sf.name + '=' + encodeURIComponent($.trim(v));
				}
			});
			
			getData(rent.table, f, 0, 10, rent.listview_order, function (data){
				// RENDER A LITTLE LIST VIEW
				var result_html = '';
				if (data.length == 0){
					result_html = '<span class="message_text">' + global.lang.LBL_SEARCH_NO_RESULTS + '</span>';
				} else {
					// Listener para el click del elemento seleccionado
					result_html = renderViewBody('search_results_'+ rel_id, rent.id, '2', data, undefined, undefined, function (selected_id){
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
							$.each(selected_data, function (i,e){
								if (e.id == selected_id) is_selected = true;
							});
							
							if (selected_id != '' && (selected_count < limit || limit == 0 || limit == '') && is_selected == false){
								// RELATE!
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
					});
				}
				// console.log(data);
				// console.log(record_id);
				$('#search_results_'+ rel_id).html(result_html);
			}, true);

		};
		// KEYPRESS ENTER LISTENER
		while_selector('#'+ input_id_name, function(){
			$('#'+ input_id_name).keypress(function (event){
				if (event.key == 'Enter'){
					search_callback();
				}
			});
		});
		// SEARCH BUTTON LISTENER
		while_selector('#relsearch_'+ rel_id, function(){
			$('#relsearch_'+ rel_id).click(search_callback);
		});
	});

	html += '<div id="search_results_'+ rel_id +'" class="search_results"></div></div>';

	return html;
}

function renderRelationshipSubpanel(rel_id, record_id, rtable, rfilter, r_entity_id, rel_table, remove_button, selector, filter, limit, type, side){ 

	var rent = metadata._entities[r_entity_id];
	getData(rtable, rfilter, 0, 1000, rent.listview_order, function(data, resp_table, resp_count){
		// console.log(data);
		while_selector('#relationship_'+ rel_id +' .head .label.buttons', function (){
			$('#relationship_'+ rel_id +' .head .label.buttons').html(global.lang.LBL_COUNT + ' ' + resp_count)
		});
		// expand-collapse subpanel
		if (parseInt(resp_count) > parseInt(global.config.subpanel_autocollapse_count)){
			while_selector('#relationship_'+ rel_id, function (){
				var rsp = document.getElementById('relationship_'+ rel_id);
				rsp.classList.add('collapsed_panel');
				rsp.addEventListener('click', function(){
					this.classList.remove('collapsed_panel');
				})
			});
			while_selector('#rel_title_'+ rel_id, function (){
				var rst = document.getElementById('rel_title_'+ rel_id);
				rst.style.cursor = 'pointer';
				rst.addEventListener('click', function(){
					var rsp = document.getElementById('relationship_'+ rel_id);
					if (rsp.classList.contains('collapsed_panel')){
						rsp.classList.remove('collapsed_panel');
					} else {
						rsp.classList.add('collapsed_panel');
					}					
				})
			});
			
		}
		if (selector == 1){
			// checkbox!
			var checkboxes = [];
			$.each(data, function (i, row){
				checkboxes.push(row.id);
			});
			getData(rent.table, filter, 0, 1000, rent.listview_order, function(tdata){
				var body = renderViewBody('relationship_'+ rel_id, r_entity_id, '2', tdata, undefined, checkboxes, undefined);
				$('#relationship_'+ rel_id).html(body);
				// ADD LISTENERS TO CHECKBOXES!
				$('#relationship_'+ rel_id+' .checkboxes input').click(function(event){
					var c = $(event.target).prop('checked');
					var rid = $(event.target).parent().parent().attr('record_id');
					var selected_count = $('#relationship_'+ rel_id+' .checkboxes input:checked').length;
					if (c == true){
						if (selected_count <= limit || limit == 0 || limit == ''){
							// link!
							if (side == 'id_a'){
								link_do(rel_table, record_id, rid);
							} else {
								link_do(rel_table, rid, record_id);
							}
						} else {
							// uncheck!!!
							$(event.target).prop('checked', false);
						}
						// DISABLE CREATORS AND SELECTORS
						if (selected_count >= limit && limit != 0 && limit != ''){
							rel_select_create_disable(rel_id, true);
						}
					} else {
						// unlink!
						if (side == 'id_a'){
							unlink_do(rel_table, record_id, rid);
						} else {
							unlink_do(rel_table, rid, record_id);
						}
						// ENABLE CREATORS AND SELECTORS
						rel_select_create_disable(rel_id, false);
					}
				});
				// Adjust Remove button
				renderRelationshipRemoveButton(rel_id, record_id, rel_table, remove_button, selector, type, side);
			}, true);
		} else {
			var body = renderViewBody('relationship_'+ rel_id, r_entity_id, '2', data, undefined, undefined, undefined);
			$('#relationship_'+ rel_id).html(body);
			// Adjust Remove button
			renderRelationshipRemoveButton(rel_id, record_id, rel_table, remove_button, selector, type, side);
		}
		// ADD RETURN TO EDIT BUTTONS ON SUBPANEL
		$('#relationship_'+ rel_id +' .button.edit').each(function (i,e){ 
			var oc = $(e).attr('onclick');
			oc = "this_status['main_body'].return = {record_id: '"+ this_status['main_body'].record_id +"', entity_id: '"+ this_status['main_body'].entity_id+"' }; " + oc;
			$(e).attr('onclick', oc); 
		});
		/*
		$('#relationship_'+ rel_id +' .button.edit').click(function(){
			console.log('TEST');
			this_status['main_body'].return = {record_id: this_status['main_body'].record_id, entity_id:this_status['main_body'].entity_id };
		});
		*/
		// console.log($('#relationship_'+ rel_id +' .button.edit'));

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
		$('#relationship_'+ rel_id + ' a.delete.button').html(remove_button);
	}
	// REMOVE button: according to type
	if (type == 1){
		if (selector == 1){
			// CHECKBOX SELECTOR does not have unlink button
			$('#relationship_'+ rel_id + ' a.delete.button').remove();
		} else {
			// REMOVE button only removes the link
			$('#relationship_'+ rel_id + ' .record').each(function(i,e){
				var rid = $(e).attr('record_id');
				var rsel = '#relationship_'+ rel_id + ' #record' + rid;
				var oc = '';
				if (side == 'id_a'){
					oc = "unlink_do('"+rel_table+"', '"+record_id+"', '"+rid+"', '"+rsel+"'); return rel_select_create_disable('" + rel_id + "', false);";
				} else {
					oc = "unlink_do('"+rel_table+"', '"+rid+"', '"+record_id+"', '"+rsel+"'); return rel_select_create_disable('" + rel_id + "', false);";
				}
				// DELETE FROM rel_table WHERE side + '=' + record_id AND the_other = rid
				var href = 'javascript:' + oc;
				$(e).find('a.delete.button').prop('href', href).prop('onclick', oc).attr('onclick', oc);
			});
		}
	}
}

function rel_select_create_disable(rel_id, is_dis){
	// ENABLE CREATORS AND SELECTORS
	//console.log(rel_id + ' ' + is_dis);
	$('#relselect_'+ rel_id).prop('disabled', is_dis);
	$('#relcreate_'+ rel_id).prop('disabled', is_dis);
	return false;
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
			remove_button = renderLink('-', "jQuery(this).parent().html(''); return false;", true, undefined, false, field.name + ' remove');

			// ADD BUTTON:
			html += '<span class="add_button">' + renderLink('+', "addMultifield('"+ field.id +"', '"+ suffix +"'); return false;", true, undefined, false, 'multi_add') + '</span>';

		} else {
			remove_button = '';
			html += '<ul>';
		}
		
		// DB DATA
		$.each(value, function (i,e){
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
	remove_button = renderLink('-', "jQuery(this).parent().html(''); return false;", true, undefined, false, field.name + ' remove');

	multi_count[field_id]++;

	html = '<span>' + renderSingleField(field_id, 1, '', suffix + '_' + multi_count[field_id], multi_row[field_id], 'new') + remove_button + '<br></span>';

	$('#_new_' + field_id).append(html);

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
		$.each(langs, function (i, code){
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
				if (field.required==1) html += "<script>jQuery('#"+ id +"').attr('required', 'required');</script>";
				if (!empty(field.regex)) html += "<script>jQuery('#"+ id +"').attr('pattern', '"+field.regex+"');</script>";
				if (empty(field.placeholder)){
					html += "<script>jQuery('#"+ id +"').attr('placeholder', '"+field.label+"');</script>";
				} else {
					html += "<script>jQuery('#"+ id +"').attr('placeholder', '"+field.placeholder+"');</script>";
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

function renderViewButtons(view_id, entity_id) {
	var buttons = searchInArray(metadata._viewbuttons, 'view_id', view_id);
	var global_buttons = searchInArray(metadata._viewbuttons, 'view_id', '');
	for(var k in global_buttons) buttons[k]=global_buttons[k];
	var entity = metadata._entities[entity_id];
	var html = '';
	var href = '';
	$.each(buttons, function (i, button){
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
			html += '<span id="'+ parent_element_id + '_custom_view"><img class="loader" src="templates/admin/images/loader.gif"></span>';
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
	html += '<span id="'+ parent_element_id + '_custom_view"><img class="loader" src="templates/admin/images/loader.gif"></span>';
	$('#'+parent_element_id).html(html);
	
	if (metadata._entities[entity_id].template.indexOf('$') >= 0){
		// PARSE TEMPLATE
		renderParsed('_entities', 'template', entity_id, entity_id, '', '', parent_element_id + '_custom_view', true);
	} else {
		$('#'+parent_element_id + '_custom_view').html(metadata._entities[entity_id].template);
	}
}

// SEARCH!
function getSearchformBackup(parent_element_id){
	var searchform_backup = {name:{},id:{}};
	$('#'+parent_element_id+' .searchform input, #'+parent_element_id+' .searchform textarea, #'+parent_element_id+' .searchform select').each(function (i,e) { 
		var name = $(e).attr('name');
		var id = $(e).attr('id');
		var value = $(e).val();
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
	$.each(searchform_backup.name, function (name, value){
		$('#'+parent_element_id+' .searchform [name="'+ name +'"]').val(value);
		$('#'+parent_element_id+' .searchform [name="'+ name +'"]').change();
	});
	$.each(searchform_backup.id, function (id, value){
		$('#'+parent_element_id+' .searchform [id="'+ id +'"]').val(value);
		$('#'+parent_element_id+' .searchform [id="'+ id +'"]').change();
	});
}

function search_do(parent_element_id) {

	var searchform_backup = getSearchformBackup(parent_element_id);

	// BUILD filter
	var filter = getSearchFilter(parent_element_id);

	// loadView with that filter
	loadView(this_status[parent_element_id].entity_id, this_status[parent_element_id].view_id, '', filter, 0, false, function (){
		restoreSearchformBackup(parent_element_id, searchform_backup);
	}, undefined, parent_element_id, this_status[parent_element_id].order);

	return false;
}

function getSearchFilter(parent_element_id){
	var filter = '';
	$('#'+parent_element_id+' .searchform input, #'+parent_element_id+' .searchform textarea, #'+parent_element_id+' .searchform select').each(function (i,e) { 
		var name = $(e).attr('name');
		var value = $(e).val();
		if (name != undefined && name != '' && value != undefined && value != '') {
			if (filter != '') filter = filter + '&';
			filter = filter + name + '=' + encodeURIComponent($.trim(value));
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

	var record_id = $(form).attr('record_id');
	var entity_id = $(form).attr('entity_id');
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

		if (callback != undefined) callback(response);

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
		$.ajax({
			type: "POST",
			url: url,
			data: form,
			success: suc,
			error: err,
			dataType: "json"
		});

	} else {
		$(form).ajaxSubmit({
			method: "POST",
			url: url, 
			success: suc,
			error: err,
			beforeSend: befores,
			uploadProgress: uploads,
			complete: completes,
			dataType: "json"
		});
	}

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

function addOnchangeListener(element_id, field_name, table, record_id){
	var e = document.getElementById(element_id);
	var saveCallback = function (){
		// TODO: validate unique?
		save_field(element_id, field_name, table, record_id);
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
			// Return (y a evitar el comportamiento default del submit)
			e.addEventListener('keydown', function(e){
				if (e.key == 'Enter'){
					e.preventDefault();
					saveCallback();
					return false;
				}
			});
		}
	} // else??? 
}

function addUniqueListener(element_id, field_name, table, entity_id, record_id){
	var e = document.getElementById(element_id);
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
			e.addEventListener('keydown', function(e){
				if (e.key == 'Enter'){
					e.preventDefault();
					validationCallback();
					return false;
				}
			});
		}
	} // else??? 
}

function save_field(element_id, field_name, table, record_id){
	var e = document.getElementById(element_id);
	var p = e.parentNode;
	while (p.classList.contains('field') == false && p != null){
		p = p.parentNode;
	}
	var value = p.querySelector('[name=' + field_name + ']').value;
	
	save_do(field_name +'=' + value, table, record_id);
	// console.log(element_id, field_name, table, record_id, value);
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

function reload_do(parent_element_id){
	if (parent_element_id == undefined) parent_element_id = 'main_body';
	var entity_id = this_status[parent_element_id].entity_id;
	var view_id = this_status[parent_element_id].view_id;
	var record_id = this_status[parent_element_id].record_id;
	var filter = this_status[parent_element_id].filter;
	var offset = this_status[parent_element_id].offset;
	var pre_filled = this_status[parent_element_id].pre_filled;
	var order = this_status[parent_element_id].order;

	document.getElementById(parent_element_id).innerHTML = '<img class="loader" src="templates/admin/images/loader.gif">';

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
		$.ajax({
			type: "POST",
			url: url,
			data: form,
			success: suc,
			error: err,
			dataType: "json"
		});

	} else {
		$(form).ajaxSubmit({
			method: "POST",
			url: url, 
			success: suc,
			error: err,
			dataType: "json"
		});
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
					$('#record' + response.id).remove();
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
					$(record_selector).remove();
				} 
			} 
		});
	}
	return false;
}

// BLOCK SCREEN
function block_screen(){
	$('#_screen_lock').show('fade', {}, 500);
}

function unblock_screen(){
	$('#_screen_lock').hide('fade', {}, 100);
}

// MESSAGES
function show_message(str, time){
	if (time == undefined) time = 5000;
	var id = 'message_' + guid();
	$('#message').append('<div class="message" id="' + id + '"><a class="message_close" onclick="this.parentNode.parentNode.removeChild(this.parentNode)">&times;</a>' + str + '</div>');
	$('#' + id).show('fade', {}, 500);

	setTimeout(function(){
		$('#' + id).hide('fade', {}, 500, function(){
			$('#' + id).remove();
		});
	}, time);
}

function show_progress(){
	var time = 1000;
	var id = 'message_progress';
	if (document.getElementById(id) != null){
		document.getElementById(id).remove();
	}
	$('#message').append('<div class="message" id="' + id + '">0%</div>');
	$('#' + id).show('fade', {}, 500);
	
	var i = setInterval(function(){
		var p = 0;
		var s = '';
		var r = {};
		// fetch progress
		const xhttp = new XMLHttpRequest();
		xhttp.onload = function() {
			// console.log(this.responseText, this.status);
			if (this.status == 200){
				r = JSON.parse(this.responseText);
				if (typeof(r) != 'undefined' && r.description != undefined && r.progress != undefined){
					p = parseInt(r.progress);
					// update on UI
					document.getElementById(id).innerHTML = r.description + ' ' + p + '%';
					// terminate
					if (p == 100){
						$('#' + id).hide('fade', {}, 500, function(){
							$('#' + id).remove();
							clearInterval(i);
						});
					}
				}
			}
		}
		xhttp.open('GET', 'cache/progress.txt');
		xhttp.send();
		
	}, time);
}

// SUBMIT LOCK
function submitLockStart(){
	//submit_lock_count
	setTimeout(function(){
		if (submit_lock_count == 0){
			$('input[type=submit]').attr('disabled', 'disabled');
			//console.log('disabled');
		} 
		submit_lock_count = submit_lock_count + 1;
	},1);
}

function submitLockEnd(){
	submit_lock_count = submit_lock_count - 1;
	if (submit_lock_count <= 0){
		submit_lock_count = 0;
		$('input[type=submit]').removeAttr('disabled');
		//console.log('enabled');
	} 
}

// NAV!
function nav_next(parent_element_id, _nextid) {
	$('.nav_button').prop('disabled', true);
	var searchform_backup = getSearchformBackup(parent_element_id);
	var view = metadata._viewdefs[this_status[parent_element_id].view_id];
	var offset = parseInt(this_status[parent_element_id].offset) + parseInt(view.records);
	if (view.records > 1) _nextid = '';
	loadView(this_status[parent_element_id].entity_id, this_status[parent_element_id].view_id, _nextid, this_status[parent_element_id].filter, offset, false, function (){
		restoreSearchformBackup(parent_element_id, searchform_backup);
	});
}

function nav_prev(parent_element_id, _previousid) {
	$('.nav_button').prop('disabled', true);
	var searchform_backup = getSearchformBackup(parent_element_id);
	var view = metadata._viewdefs[this_status[parent_element_id].view_id];
	var offset = parseInt(this_status[parent_element_id].offset) - parseInt(view.records);
	if (view.records > 1) _previousid = '';
	loadView(this_status[parent_element_id].entity_id, this_status[parent_element_id].view_id, _previousid, this_status[parent_element_id].filter, offset, false, function (){
		restoreSearchformBackup(parent_element_id, searchform_backup);
	});
}

function nav_cancel(parent_element_id) {
	// window.history.back();
	if (return_status[parent_element_id] == undefined) {
		if (this_status[parent_element_id].view_id == '1' && this_status[parent_element_id].record_id != '') {
			loadView(this_status[parent_element_id].entity_id, '4', this_status[parent_element_id].record_id, this_status[parent_element_id].filter, this_status[parent_element_id].offset, false, function (){
				return_status[parent_element_id] = undefined;
			});
		} else {
			menugoto(this_status[parent_element_id].entity_id);
		}
	} else {
		loadView(return_status[parent_element_id].entity_id, return_status[parent_element_id].view_id, return_status[parent_element_id].record_id, return_status[parent_element_id].filter, return_status[parent_element_id].offset, false, function(){
			return_status[parent_element_id] = undefined;
		});
	}
	return false;
}

function nav_viewbutton(button_id, entity_id) {
	var button = metadata._viewbuttons[button_id];
	if (button.target_view != ''){
		loadView(entity_id, button.target_view, '', button.filter);
	} else if (button.target_action != '') {
		// TODO: Execute action.
		// Concatenar, si existe, la variable filtro
		// Espero un valor de retorno? Que hago con el valor de retorno?
	}
	return false;
}

function nav_customviewbutton(parent_element_id, custom_view_id, entity_id, view_id, record_id) {

	renderCustomView(parent_element_id, custom_view_id, entity_id, '5', record_id, {custom_view_id:custom_view_id});

	
	/*
	<!-- Parse the custom view -->
$($_get.custom_view)[
  $_custom_views(id == $_get.custom_view)[
    $($type == 1)[
      $_parse[ $_custom_views.template ]
    ][
      $($type == 2)[
        <script> getUrl( '$_custom_views.template$($_get.id)[&id=$_get.id]' ); </script>
      ][
        $_custom_views.template 
      ]
    ]
  ]
]
	*/
	return false;
}

// HANDLEBARS
Handlebars.registerHelper("x", function (expression, options) {
	var fn = function(){}, result;
	try {
		fn = Function.apply(this,['window', 'return ' + expression + ';']);
	} catch (e) {
		console.warn('[Handlebars warning] {{x ' + expression + '}} is invalid javascript', e);
	}
	try {
		result = fn.call(this, window);
	} catch (e) {
		console.warn('[Handlebars warning] {{x ' + expression + '}} runtime error', e);
	}
	return result;
});
Handlebars.registerHelper("xif", function (expression, options) {
	return Handlebars.helpers["x"].apply(this, [expression, options]) ? options.fn(this) : options.inverse(this);
});
Handlebars.registerHelper("setVar", function(varName, varValue, options) {
	options.data.root[varName] = varValue;
});
Handlebars.registerHelper("concat", function(val1, val2, options) {
	return val1 + val2;
});

// DASHBOARD
var current_dashboard = '';
function dashboardInit(){
	var s = (current_dashboard == '')?' selected':'';
	setHtml('dashboard_tabs', '<input type=button id="dashboard_tab_" class="tab'+s+'" value="' + global.lang.LBL_MAIN_DASHBOARD + '" onclick="current_dashboard=\'\'; dashboardInit();">');
	setHtml('dashboard_buttons', '');
	setHtml('dashboard_widgets', '');
	setHtml('dashboard_panels', '');
	if (current_user.readonly == 1){
		if (document.getElementById('dashboard_add_button') != undefined) document.getElementById('dashboard_add_button').style.display = 'none';
		if (document.getElementById('dashboard_edit_button') != undefined)document.getElementById('dashboard_edit_button').style.display = 'none';
	}

	// DASHBOARD TABS
	getData('_dashboard', '', 0, 100000, 'order', function (dashboard_data){
		var tabs = [];
		dashboard_data.forEach(function (dash){
			if (tabs.includes(dash.tab) == false && dash.tab != '' && (user_has_permission(current_user.id, dash.entity_id) || current_user.isadmin == 1 || dash.user_id == current_user.id)){
				tabs.push(dash.tab);
				var tab_id = dash.tab.replace(' ', '_');
				var s = (current_dashboard == tab_id)?' selected':'';
				appendHtml('dashboard_tabs', '<input type=button id="dashboard_tab_' + tab_id + '" class="tab'+s+'" value="' + dash.tab + '" onclick="current_dashboard=\''+tab_id+'\'; dashboardInit();">');
			}
		});
		
	});

	// DASHBOARD CONTENTS
	getData('_dashboard', '', 0, 100000, 'order', function (dashboard_data){
		var dlimit = 0;
		dashboard_data.forEach(function (dash){
			if (((dash.user_id == '' && (user_has_permission(current_user.id, dash.entity_id) || current_user.isadmin == 1)) || dash.user_id == current_user.id) && (dash.visible == 1) && dlimit < global.config.dashboard_limit && dash.tab == current_dashboard){
				dlimit++;
				
				var html = '';
				if (dash.type == 'button'){
					if (dash.custom_view_id != ''){
						var cv = metadata._custom_views[dash.custom_view_id];
						// 'return nav_customviewbutton(\'main_body\', \''+ dash.custom_view_id +'\', \''+ cv.parent +'\', \'5\', \'\')'
						var js = 'return renderCustomView(\'main_body\', \''+ dash.custom_view_id +'\', \''+ cv.parent +'\', \'5\', \'\', {custom_view_id:\''+ dash.custom_view_id +'\'';
						var url = script_name + '?entity='+cv.parent+'&view=5&custom_view_id='+dash.custom_view_id;
						
						if (dash.filter != ''){
							url += '&' + dash.filter;
							dash.filter.split("&").forEach(function (part) { var item=part.split("="); js += ','+item[0]+':\''+item[1]+'\''; });
						}
						js += '})'; // cierro la definicion del JS

						html = renderLink(dash.title, js, true, url);
					} else if (dash.entity_id != '' && dash.view_id != ''){
						var id = '';
						// Use filter?
						// Filter point to single record? view is single record?
						if (metadata._viewdefs[dash.view_id].records == 1 && dash.filter.slice(0,3) == 'id='){
							// dashboardLinkToRecord
							html = renderLink(dash.title, "return dashboardLinkToRecord('" + dash.entity_id + "', '" + dash.view_id + "', '" + dash.filter + "');", true);
						} else {
							// ELSE: filter as parameter
							var pre_filled = {};
							dash.filter.split("&").forEach(function (part) { 
								var item=part.split("="); 
								pre_filled[item[0]] = item[1]; 
							});
							html = renderLinkTo(dash.entity_id, dash.view_id, '', dash.title, true, false, '', '', pre_filled);
						}
						// html = renderLinkTo(dash.entity_id, dash.view_id, record_id, dash.title, true, false, '');
						
					} else if (dash.entity_id != ''){
						html = renderLink(dash.title, "return menugoto('"+ dash.entity_id +"');", true, urls[dash.entity_id], false);
					}
					appendHtml('dashboard_buttons', html);
				} else if (dash.type == 'widget'){
					html += '<div class="dashboard_widget" id="widget_'+ dash.id +'">'
					if (dash.widget.indexOf('$') >= 0){
						html += '<img class="loader" src="templates/admin/images/loader.gif" style="height: 32px">';
						renderParsed('_dashboard', 'widget', dash.id, dash.entity_id, dash.view_id, '', 'widget_'+dash.id, true);
					} else {
						html += dash.widget;
					}
					html += '</div>';
					appendHtml('dashboard_widgets', html);
				} else {
					html = '<div class="dashboard_panel"><div class="dashboard_title">'+dash.title+'<a href="' + script_name + '?entity=36&amp;view=1&amp;id=' + dash.id + '" onclick="return loadView(\'36\', \'1\', \''+ dash.id +'\', \'\', 0, false, undefined, {});" class="button button-primary" style="float:right; margin-top: -3px">' + global.lang.BTN_EDIT + '</a>';

					if (dash.custom_view_id != ''){
						html += '<a href="admin.php?entity='+dash.entity_id+'&amp;view=5&amp;custom_view_id='+dash.custom_view_id+'" onclick="return nav_customviewbutton(\'main_body\', \''+dash.custom_view_id+'\', \''+dash.entity_id+'\', \'2\', \'\')" class="button" style="float:right; margin-top: -3px">FULL</a>';
					} // TODO: FOR REGULAR VIEWS

					html += '</div><div class="dashboard_content" id="dashboard_panel_' + dash.id + '">';
					
					if (dash.type == 'view'){
						if (dash.custom_view_id != ''){
							var cv = metadata._custom_views[dash.custom_view_id];
							if (cv.type == 0){
								// HTML TEMPLATE
								html += cv.template;
							} else if (cv.type == 1){
								// PARSE TEMPLATE
								renderParsed('_custom_views', 'template', cv.id, cv.parent, dash.view_id, '', 'dashboard_panel_' + dash.id, true, undefined);
								html += '<img class="loader" src="templates/admin/images/loader.gif">';
							}
						} else if (dash.entity_id != '' && dash.view_id != ''){
							// LOAD VIEW
							loadView(dash.entity_id, dash.view_id, '', dash.filter, 0, true, undefined, {}, 'dashboard_panel_' + dash.id);
							html += '<img class="loader" src="templates/admin/images/loader.gif">';
						} else if (dash.widget.trim() != ''){ // TODO: TRIM!
							if (dash.widget.indexOf('$') >= 0){
								html += '<img class="loader" src="templates/admin/images/loader.gif" style="height: 32px">';
								renderParsed('_dashboard', 'widget', dash.id, dash.entity_id, dash.view_id, '', 'dashboard_panel_'+dash.id, true);
							} else {
								html += dash.widget;
							}
						}
					} else if (dash.type == 'list'){
						if (dash.entity_id != ''){
							// RENDER LIST
							renderDashboardList(dash.id);
							html += '<img class="loader" src="templates/admin/images/loader.gif">';
						}
					} else if (dash.type == 'chart'){
						if (dash.entity_id != ''){
							// RENDER LIST
							renderDashboardChart(dash.id);
							html += '<img class="loader" src="templates/admin/images/loader.gif">';
						}
					}
					html += '</div></div>';
					appendHtml('dashboard_panels', html);
				} 
			}
		});
	});
}
// javascript:return dashboardLinkToRecord('59e7c013e0c67', '1', '');
// javascript:return dashboardLinkToRecord('5d8bcd6418063', '4', 'id=62afbec595178');
// metadata._entities['5d8bcd6418063'];
function dashboardLinkToRecord(entity_id, view_id, filter){
	// console.log(entity_id, view_id, filter);
	// apply filter
	if (filter != undefined && filter != ''){
		var table = metadata._entities[entity_id].table;
		getData(table, filter, 0, 1, '', function (data){ 
			if (data.length > 0){
				loadView(entity_id, view_id, data[0].id);
			}
		}, false);
	} else {
		loadView(entity_id, view_id);
	}
	return false;
}

var dashboard_list_data_cache = {};
var dashboard_list_order = {};
function renderDashboardList(dash_id){
	var dash = metadata._dashboard[dash_id];
	var entity = metadata._entities[dash.entity_id];
	var field_list = [];
	var dash_order = entity.listview_order;
	var order_field = '';
	var execute_data = false;

	if (dash.custom_order != undefined && dash.custom_order.trim() != ''){
		dash_order = dash.custom_order;
	}

	// Order field:
	if (dash_order.slice(-8) == ' REVERSE'){
		order_field = dash_order.slice(0,-8);
	} else {
		order_field = dash_order;
	}

	metadata._dashboard[dash_id].fields.forEach(function (fid){ 
		if (metadata._fields[fid].type == 29){ // Bean Function
			execute_data = true;
		}
		field_list.push(metadata._fields[fid].name); 
	});

	var callback = function (data, t, count){
		dashboard_list_data_cache[dash_id] = data;
		dashboard_list_order[dash_id] = dash_order;
		var h = '';
		// COUNT
		h += '<span style="float:right;">' + global.lang.LBL_COUNT + ': ' + count + '</span>';
		// FORM
		h += '<form id="listviewForm_'+ dash_id + '" class="listview form" entity_id="'+ entity.id +'" record_id="" action="void.php" autocomplete="off">';
		// HEAD
		c = 0;
		h += '<div class="head">';
		dash.fields.forEach(function (fid){
			var f = metadata._fields[fid];
			h += '<div class="label label'+ c +'" onclick="dash_sortBy(\''+ dash_id +'\', \''+ f.name +'\')">' + f.label + ' '; 
			if (f.name == dash_order){
				h += '<span id="dash_order_'+ f.name + dash_id +'">&#8595;</span>';
			} else {
				h += '<span id="dash_order_'+ f.name + dash_id +'" style="display:none">&#8595;</span>';
			}
			h += '</div>';
			c++;
		});
		if (dash.edit_button == 1){
			h += '<div class="label post buttons"></div>';
		}
		h += '</div>';
		// ORDER DATA
		if (dash_order.slice(-8) == ' REVERSE'){
			data = sortData(data, order_field, true);
		} else {
			data = sortData(data, order_field, false);
		}
		// RECORDS
		h += renderDashboardListBody(data, dash_id);
		// END
		h += '</form>';
		// document.getElementById('dashboard_panel_' + dash.id).innerHTML = h;
		setHtml('dashboard_panel_' + dash.id, h);
	};

	
	// ExecuteData vs getData
	// getData(table, filter, offset, count, order, callback, isRel)
	if (execute_data){
		// executeData always have delay
		executeData(entity.table, field_list, dash.filter, order_field, callback);
	} else {
		// getData(table, filter, offset, count, order, callback, isRel)
		getData(entity.table, dash.filter, 0, 100000, order_field, function (d,t,c){
			while_selector('#dashboard_panel_' + dash_id, function(){
				callback(d,t,c);
			}); // waiting for dashboard_panel_ to exist
		}, false);
	}
	
}

function dash_sortBy(dash_id, field_name){
	var data = dashboard_list_data_cache[dash_id];
	// sort data!
	old_order = dashboard_list_order[dash_id];
	if (old_order == field_name){
		dashboard_list_order[dash_id] = field_name + ' REVERSE';
		// REVERSE
		data = sortData(data, field_name, true);
	} else {
		// NEW ORDER
		dashboard_list_order[dash_id] = field_name;
		data = sortData(data, field_name, false);
	}

	// remove dashboard records
	document.querySelectorAll('#dashboard_panel_'+ dash_id+' .record').forEach(function (e){ e.remove(); });

	// add records in new order
	var h = renderDashboardListBody(data, dash_id);
	// document.querySelector('#dashboard_panel_'+ dash_id+' .listview').innerHTML += h;
	appendHtml('listviewForm_'+ dash_id, h);	

	// the arrows!
	if (old_order == field_name){
		// reverse! &#8593;
		document.getElementById('dash_order_'+ field_name + dash_id).innerHTML = '&#8593;';
	} else {
		if (old_order.slice(-8) == ' REVERSE'){
			old_order = old_order.slice(0,-8);
		}
		document.getElementById('dash_order_'+ old_order + dash_id).style.display = 'none';
		document.getElementById('dash_order_'+ field_name + dash_id).innerHTML = '&#8595;';
	}
	document.getElementById('dash_order_'+ field_name + dash_id).style.display = '';
	 
}

function renderDashboardListBody(data, dash_id){
	var dash = metadata._dashboard[dash_id];
	var entity = metadata._entities[dash.entity_id];
	var h = '';
	data.forEach(function (p){
		c = 0;
		first = true;
		h += '<div class="record" id="record'+ p.id +'" record_id="'+ p.id +'">';
		dash.fields.forEach(function (fid){
			var f = metadata._fields[fid]; // field def
			var v = (p[f.name] == undefined)?'':p[f.name]; // value
			var field_html = renderField(f.id, 0, v, dash_id + '_' + p.id, p);

			h += '<div class="field field'+ c +'">';
			if (first){
				h += renderLinkTo(entity.id, '4', p.id, field_html);
			} else {
				h += field_html;
			}
			h += '</div>';
			c++;
			first = false;
		});
		if (dash.edit_button == 1){
			h += '<div class="post buttons">' + renderLinkTo(entity.id, '1', p.id, global.lang.BTN_EDIT, true, true) + '</div>';
		}
		h += '</div>';
	});
	return h;
}

function renderDashboardChart(dash_id){
	var dash = metadata._dashboard[dash_id];
	var entity = metadata._entities[dash.entity_id];
	var field_list = [];
	metadata._dashboard[dash_id].fields.forEach(function (fid){ 
		field_list.push(metadata._fields[fid].name); 
	});
	var first_field_name = field_list[0];

	executeData(entity.table, field_list, dash.filter, entity.listview_order, function (data){
		var type = dash.chart_type; // TODO!!! column bar pie
		// GATHER categories (first column) GROUP!
		var categories = [];
		data.forEach(function (d){
			// TODO: no repetir categoria
			if (d[first_field_name] != undefined){
				categories.push(d[first_field_name]);
			} else {
				categories.push('');
			}
		});
		
		// GATHER series (every other column)
		// CALCULATE: SUM of every other column, COUNT if there's only one
		// GET yAxis label, AND every series label
		var series = [];
		dash.fields.forEach(function (fid){ 
			var f = metadata._fields[fid];
			if (f.name != first_field_name){
				var serie = {};
				// nombre:
				serie.name = f.label;
				serie.data = [];
				// TODO: no repetir categoria, acumular datos.
				data.forEach(function (d){
					serie.data.push({name: d[first_field_name], y: parseFloat(d[f.name])});
				});
				// push!
				series.push(serie);
			}
		});
		// CALCULATE: COUNT if there's only one column
		// console.log(categories);
		// console.log(series);
		
		Highcharts.chart('dashboard_panel_' + dash.id, {
			chart: {
				type: type
			},
			title: {
				text: ''
			},
			xAxis: {
				categories: categories,
				crosshair: true
			},
			yAxis: {
				min: 0,
				title: {
					text: 'Conceptos'
				}
			},
			plotOptions: {
				column: {
					pointPadding: 0.2,
					borderWidth: 0
				}
			},
			legend: {
				enabled: false
			},
			series: series,
			accessibility: {
				enabled: false
			}
		});

	});

}

// OTHERS
function user_has_permission(user_id, entity_id){
	var _permiso = false;
	for (const ep_id in metadata._entity_permissions){
		var ep = metadata._entity_permissions[ep_id];
		if (ep.id_a == user_id && ep.id_b == entity_id){
			_permiso = true;
		}
	}
	return _permiso;
}


function create_popup(content, relative_to_id) {
	var extra = '';
	var html = '';
	if (relative_to_id == undefined){
		extra = ' center';
	}
	html = '<div class="popup'+ extra +'">';
	// close button
	html += '<a href="#" class="close" onclick="$(this).parent().remove(); return false;">&times;</a>';
	html += content;
	html += '</div>';

	if (relative_to_id == undefined){
		$('body').append(html);
	} else {
		$('#' + relative_to_id).parent().append(html);
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
/* util.js
function while_selector(selector, callback){
	var i = setInterval(function (){
		if ($(selector).length > 0){
			clearInterval(i);
			callback();
		}
	}, 100);
}
*/

