// --- Navegación, Menú y Ribbon ---
window.urls = window.urls || {};
window.ribbon_submenu = window.ribbon_submenu || {};

// MENU:
function menugo() {
	var entity_id = getValue('menuselector');
	if (entity_id == ''){
		var opt = query('#menuselector option:checked');
		renderTabGroup(opt ? opt.attributes.tab_group_id.value : '');
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
	menuHide();
	showRibbon(i);
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
		// COUNT entities of this group
		var count_tg_entities = 0;
		for (const tge_id in metadata._tab_group_entities){  
			var tge = metadata._tab_group_entities[tge_id];
			if ((tge.id_b == tab_group.id) && (current_user.isadmin == 1 || user_has_permission(current_user.id, tge.id_a))){
				count_tg_entities++;
			}
		}
		// and go on...
		if (count_tg_entities > 0 && (tab_group.adminonly == 0 || tab_group.adminonly == current_user.isadmin)){
			// RENDER TAB CONTENT
			menuselector_content = '';
			ulmenu_content = '';
			entity_tab_count = 0;

			// Gather entities, and sort them by order
			var tab_group_entities = [];
			for (const tge_id in metadata._tab_group_entities){  
				var tge = metadata._tab_group_entities[tge_id];
				if (tge.id_b == tab_group.id){
					tab_group_entities.push(metadata._entities[tge.id_a]);
				}
			}
			tab_group_entities = sortData(tab_group_entities, 'order', false);
			
			// Render each entity
			for (let entity of tab_group_entities){

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

						ribbon_submenu[entity.id] = '';
						if (entity.view2 == 1){
							ribbon_submenu[entity.id] += renderLinkTo(entity.id, '2', '', entity.name, true, true);
						}

						var option_selected = '';
						var li_selected = '';
						if (this_status['main_body'] != undefined && entity.id == this_status['main_body'].entity_id){
							option_selected = ' selected="selected" ';
							li_selected = ' class="selected" ';
						}

						if (tab_group.id != '1'){
							menuselector_content += '<option value="' + entity.id + '"' + option_selected + '> &nbsp;&nbsp; '+ entity.name + ' </option>';
						}

						ulmenu_content += '<li>';

						// submenu actions count?
						var submenu_actions_count = getSubmenuActionsCount(entity.id);

						// Expand/collapse arrow
						if (submenu_actions_count > 0){
							ulmenu_content += '<span class="expand_submenu_actions" onclick="expand_submenu(\'' + entity.id + '\')">&#8897;</span>';
						}
						
						// Entity button:
						ulmenu_content += '<a href="'+ script_name +'?entity=' + entity.id + extra + '" onclick="return menugoto(\'' + entity.id + '\');" ' + li_selected + '>' + entity.name +'</a>';

						// SUBMENU: ACTIONS 
						ulmenu_content += '<div id="_submenu_actions_' + entity.id + '" class="submenu_actions">';
						var submenu_html;
						// GENERAL view buttons (if applies)
						for (const vb_id in metadata._viewbuttons){
							submenu_html = '';
							var vb = metadata._viewbuttons[vb_id];
							if (vb.view_id == 2 && entity.view2 == 1){
								var vv = 'view'+vb.target_view;
								if (entity[vv] == 1 || vb.target_view == ''){
									if (vb.js_code == '' || vb.js_code == undefined){
										submenu_html += '<a href="' + script_name + '?entity='+entity.id;
										if (vb.target_view != '') submenu_html += '&view='+vb.target_view;
										if (vb.filter != '') submenu_html += '&'+vb.filter;
										submenu_html += '" onclick="return nav_viewbutton(\'' + vb.id + "', '" + entity.id + '\')">' + vb.label + '</a>';
									} else {
										var extra_js = "this_status['main_body'].entity_id='"+ entity.id +"';this_status['main_body'].view_id='"+ vb.target_view+"';this_status['main_body'].filter='"+vb.filter+"';this_status['main_body'].record_id='';";
										submenu_html += '<a href="javascript: menuHide();showRibbon(\'' + entity.id + '\'); ' + extra_js + vb.js_code + '" onclick="menuHide(); showRibbon(\'' + entity.id + '\'); ' + extra_js + vb.js_code + '">' + vb.label + '</a>';
									}
								}
							}
							// ADD
							ulmenu_content += submenu_html;
							ribbon_submenu[entity.id] += submenu_html; // ADD TO RIBBON TOO
						}
						// Buttons for custom views of THIS entity
						for (const cb_id in metadata._custom_views){
							submenu_html = '';
							var cb = metadata._custom_views[cb_id];
							if (cb.view==2 && cb.parent == entity.id){
								submenu_html += '<a href="' + script_name + '?entity='+entity.id+'&view=5&custom_view_id='+cb.id+'" onclick="return nav_customviewbutton(\'main_body\', \''+cb.id+'\', \''+entity.id+'\', \'2\', \'\')">' + cb.name + '</a>';
							}
							// ADD
							ulmenu_content += submenu_html;
							ribbon_submenu[entity.id] += submenu_html; // ADD TO RIBBON TOO
						}							
						// END:
						ulmenu_content += '</div></li>';

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
				ulmenu_html += '<li class="menuitem"><a href="javascript:'+menuitem_js+'" onclick="menuHide(); return '+menuitem_js+'">' + tab_group.name + '</a>' + ulmenu_content + '</li>';
			//}
		}
	}
	// LOGOUT:
	ulmenu_html += '<li class="menuitem" id="menuitem_logout"><a href="'+script_name+'?action=logout&redirect='+script_name+'" alt="'+global.lang.LNK_LOGOUT+'" title="'+global.lang.LNK_LOGOUT+'"><svg width="32px" height="32px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle;"><path d="M12 7V12M8 8.99951C7.37209 9.83526 7 10.8742 7 12C7 14.7614 9.23858 17 12 17C14.7614 17 17 14.7614 17 12C17 10.8742 16.6279 9.83526 16 8.99951M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg> '+global.lang.LNK_LOGOUT+'</a></li>';

	// TO GUI:
	setHtml('menuselector', menuselector_html);
	setHtml('ulmenu', ulmenu_html);
}

function showMenu(){
	const element = getEl('ulmenu');
	if (element.style.display == 'block'){
		menuHide();
	} else {
		element.style.display = 'block';
		var s = createEl('span', { 
			id: 'menu_backscreen', 
			onclick: function(){ menuHide(); } 
		});
		element.parentElement.appendChild(s);
	}	
}

function showRibbon(entity_id){
	// SUBMENU RIBBON
	if (ribbon_submenu[entity_id] != undefined){
		setHtml('_subribbon', ribbon_submenu[entity_id]);
	} else {
		setHtml('_subribbon', '');
	}
}

function menuHide(){
	getEl('ulmenu', function (el){
		el.style.display = '';
	});
	getEl('menu_backscreen')?.remove();
}

function expand_submenu(entity_id){
	const element_id = '_submenu_actions_'+entity_id;
	const element = getEl(element_id);

	// close all
	queryAll('.submenu_actions').forEach(function (s){ if (s.id != element_id) s.style.display = ''; });

	if (element && element.style.display != 'block'){
		// open this one:
		element.style.display = 'block';
	} else if (element){
		// close this one
		element.style.display = '';
	}
	
}

function getSubmenuActionsCount(entity_id){
	var count = 0;
	for (const vb_id in metadata._viewbuttons){
		var vb = metadata._viewbuttons[vb_id];
		var entity = metadata._entities[entity_id];
		if (vb.view_id == 2 && entity.view2 == 1){
			var vv = 'view'+vb.target_view;
			if (entity[vv] == 1 || vb.target_view == ''){
				count++;
			}
		}
	}
	for (const cb_id in metadata._custom_views){
		var cb = metadata._custom_views[cb_id];
		if (cb.view==2 && cb.parent == entity_id){
			count++;
		}
	}
	return count;
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
		
		var html = '<div id="' + bc_id +'"';
		if (entity.name != undefined && entity.name != ''){
			label += ' <span class="breadEntityName">' + entity.name + '</span>';
			html += ' title="' + entity.name +'"';
		}
		html += '>';
		// Remove previous breadcrumbs of same record
		removeBreadCrumb(entity_id, record_id);
		// create link
		html += renderLinkTo(entity_id, view_id, record_id, label, false) + '</div>';
		prependHtml('_breadcrumbs', html);
	});
}

function removeBreadCrumb(entity_id, record_id){
	if  (entity_id != undefined && record_id != undefined){
		var bc_id = '_breadcrumb_'+entity_id+'_'+ record_id;
		removeElement(bc_id);
	}
}

function renderTabGroup(tab_group_id){
	var html = '';
	var tg;

	menuHide();
	showRibbon('');
	
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
				// GENERAL view buttons (if applies)
				for (const vb_id in metadata._viewbuttons){
					var vb = metadata._viewbuttons[vb_id];
					var entity = metadata._entities[entity_id];
					if (vb.view_id == 2 && entity.view2 == 1){
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
	setHtml('main_body', html);
	setHtml('_subribbon', '');

	return false;
}

// NAV!
function nav_next(parent_element_id, _nextid) {
	queryAll('.nav_button').forEach(function(btn) { btn.disabled = true; });
	var searchform_backup = getSearchformBackup(parent_element_id);
	var view = metadata._viewdefs[this_status[parent_element_id].view_id];
	var offset = parseInt(this_status[parent_element_id].offset) + parseInt(view.records);
	if (view.records > 1) _nextid = '';
	loadView(this_status[parent_element_id].entity_id, this_status[parent_element_id].view_id, _nextid, this_status[parent_element_id].filter, offset, false, function (){
		restoreSearchformBackup(parent_element_id, searchform_backup);
	});
}

function nav_prev(parent_element_id, _previousid) {
	queryAll('.nav_button').forEach(function(btn) { btn.disabled = true; });
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

	menuHide();
	showRibbon(entity_id);

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

	menuHide();
	showRibbon(entity_id);

	renderCustomView(parent_element_id, custom_view_id, entity_id, '5', record_id, {custom_view_id:custom_view_id});

	return false;
}


function inicializarPopstate() {
    window.onpopstate = function (event) {
        if (event.state == null) {
            getUrl(script_name);
        } else {
            loadView(event.state.entity_id, event.state.view_id, event.state.record_id, event.state.filter, event.state.offset, true);
        }
    };
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', inicializarPopstate);
} else {
    inicializarPopstate();
}