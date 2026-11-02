// --- Dashboard, Widgets y Listas ---
window.current_dashboard = window.current_dashboard !== undefined ? window.current_dashboard : '';
window.dashboard_list_data_cache = window.dashboard_list_data_cache || {};
window.dashboard_list_order = window.dashboard_list_order || {};

// DASHBOARD
function dashboardInit(){
	var s = (current_dashboard == '')?' selected':'';

	setHtml('dashboard_tabs', '');
	var mainTabBtn = createEl('input', {
		type: 'button',
		id: 'dashboard_tab_',
		className: 'tab' + s,
		value: global.lang.LBL_MAIN_DASHBOARD,
		onClick: function() {
			current_dashboard = '';
			dashboardInit();
		}
	});
	getEl('dashboard_tabs')?.appendChild(mainTabBtn);
	
	setHtml('dashboard_buttons', '');
	setHtml('dashboard_widgets', '');
	setHtml('dashboard_panels', '');
	if (current_user.readonly == 1){
		hide('dashboard_add_button');
		hide('dashboard_edit_button');
	}

	// DASHBOARD TABS
	getData('_dashboard', '', 0, 100000, 'order', function (dashboard_data){
		var tabs = [];
		dashboard_data.forEach(function (dash){
			if (tabs.includes(dash.tab) == false && dash.tab != '' && (user_has_permission(current_user.id, dash.entity_id) || current_user.isadmin == 1 || dash.user_id == current_user.id)){
				tabs.push(dash.tab);
				var tab_id = dash.tab.replace(' ', '_');

				var tabBtn = createEl('input', {
					type: 'button',
					id: 'dashboard_tab_' + tab_id,
					className: 'tab' + (current_dashboard == tab_id ? ' selected' : ''),
					value: dash.tab,
					onClick: function() {
					current_dashboard = tab_id;
					dashboardInit();
					}
				});
				getEl('dashboard_tabs')?.appendChild(tabBtn);
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
				var html_extra = '';
				if (dash.style != undefined){
					html_extra = ' style="'+ dash.style +'"';
				}
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
					html += '<div class="dashboard_widget" id="widget_'+ dash.id +'"'+ html_extra +'>'
					if (dash.widget.indexOf('$') >= 0){
						html += '<span class="loader"></span>';
						renderParsed('_dashboard', 'widget', dash.id, dash.entity_id, dash.view_id, '', 'widget_'+dash.id, true);
					} else {
						html += dash.widget;
					}
					html += '</div>';
					appendHtml('dashboard_widgets', html);
				} else {
					html = '<div class="dashboard_panel"'+ html_extra +'><div class="dashboard_title">'+dash.title+'<a href="' + script_name + '?entity=36&amp;view=1&amp;id=' + dash.id + '" onclick="return loadView(\'36\', \'1\', \''+ dash.id +'\', \'\', 0, false, undefined, {});" class="button button-primary" style="float:right; margin-top: -3px">' + global.lang.BTN_EDIT + '</a>';

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
								html += '<span class="loader"></span>';
							}
						} else if (dash.entity_id != '' && dash.view_id != ''){
							// LOAD VIEW
							loadView(dash.entity_id, dash.view_id, '', dash.filter, 0, true, undefined, {}, 'dashboard_panel_' + dash.id);
							html += '<span class="loader"></span>';
						} else if (dash.widget.trim() != ''){ // TODO: TRIM!
							if (dash.widget.indexOf('$') >= 0){
								html += '<span class="loader"></span>';
								renderParsed('_dashboard', 'widget', dash.id, dash.entity_id, dash.view_id, '', 'dashboard_panel_'+dash.id, true);
							} else {
								html += dash.widget;
							}
						}
					} else if (dash.type == 'list'){
						if (dash.entity_id != ''){
							// RENDER LIST
							renderDashboardList(dash.id);
							html += '<span class="loader"></span>';
						}
					} else if (dash.type == 'chart'){
						if (dash.entity_id != ''){
							// RENDER LIST
							renderDashboardChart(dash.id);
							html += '<span class="loader"></span>';
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
	queryAll('#dashboard_panel_' + dash_id + ' .record').forEach(removeElement);

	// add records in new order
	var h = renderDashboardListBody(data, dash_id);
	// document.querySelector('#dashboard_panel_'+ dash_id+' .listview').innerHTML += h;
	appendHtml('listviewForm_'+ dash_id, h);	

	// the arrows!
	if (old_order == field_name){
		// reverse! &#8593;
		setHtml('dash_order_' + field_name + dash_id, '&#8593;');
	} else {
		if (old_order.slice(-8) == ' REVERSE'){
			old_order = old_order.slice(0,-8);
		}
		hide('dash_order_' + old_order + dash_id);
		setHtml('dash_order_' + field_name + dash_id, '&#8595;');
	}
	show('dash_order_' + field_name + dash_id);
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