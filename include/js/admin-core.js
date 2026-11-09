// --- Variables de Estado Global y UI ---
window.this_status = window.this_status || {};
window.return_status = window.return_status || {};
window.active_requests = window.active_requests || {};
window.submit_lock_count = window.submit_lock_count !== undefined ? window.submit_lock_count : 0;

// Permisos/capacidades de la vista activa en pantalla
window.editable = window.editable !== undefined ? window.editable : 0;
window.listable = window.listable !== undefined ? window.listable : 0;
window.deleteable = window.deleteable !== undefined ? window.deleteable : 0;
window.viewable = window.viewable !== undefined ? window.viewable : 0;

function searchInArray(a, f, v){
	// a: array u objeto
	// f: field name
	// v: value
	// r: returns: record
	var r = {};
	if (a == undefined) return r;
	for (var i in a) {
		if (Object.prototype.hasOwnProperty.call(a, i) && a[i] && a[i][f] == v) {
			r[i] = a[i];
		}
	}
	return r;
}

function getLanguageCodes() {
	var r = [];
	if (!metadata || !metadata._languages) return r;
	for (var i in metadata._languages) {
		if (Object.prototype.hasOwnProperty.call(metadata._languages, i)) {
			r.push('_' + metadata._languages[i].code);
		}
	}
	return r;
}

// BLOCK SCREEN
function block_screen(){
	// var el = document.getElementById('_screen_lock');
	// if (el) fadeIn(el, 500);
	getEl('_screen_lock', function(el){ fadeIn(el, 500); });
}

function unblock_screen(){
	// var el = document.getElementById('_screen_lock');
	// if (el) fadeOut(el, 100);
	getEl('_screen_lock', function(el){ fadeOut(el, 100); });
}

// MESSAGES
function show_message(str, time){
	if (time == undefined) time = 5000;
	var id = 'message_' + guid();
	var html = '<div class="message" id="' + id + '" style="display:none;"><a class="message_close" onclick="this.parentNode.remove()">&times;</a>' + str + '</div>';
	appendHtml('message', html);

	// var el = document.getElementById(id);
	// if (el) {
	//     fadeIn(el, 500);
	//     ...
	// }
	getEl(id, function(el){
		fadeIn(el, 500);
		setTimeout(function(){
				fadeOut(el, 500, function(){
					el.remove();
			});
		}, time);
	});
}

function show_progress(){
	var time = 1000;
	var id = 'message_progress';
	
	// var existing = document.getElementById(id);
	// if (existing != null){
	//     existing.remove();
	// }
	getEl(id, function(existing){
		existing.remove();
	});
	
	appendHtml('message', '<div class="message" id="' + id + '" style="display:none;">0%</div>');
	
	// var el = document.getElementById(id);
	// if (el) fadeIn(el, 500);
	getEl(id, function(el){ fadeIn(el, 500); });
	
	var i = setInterval(function(){
		var p = 0;
		var r = {};
		// fetch progress
		const xhttp = new XMLHttpRequest();
		xhttp.onload = function() {
			// console.log(this.responseText, this.status);
			if (this.status == 200){
				r = JSON.parse(this.responseText);
				if (typeof(r) != 'undefined' && r.description != undefined && r.progress != undefined){
					p = parseInt(r.progress);
					
					// var currentEl = document.getElementById(id);
					// if (currentEl) currentEl.innerHTML = r.description + ' ' + p + '%';
					getEl(id, function(currentEl){
						currentEl.innerHTML = r.description + ' ' + p + '%';
					});
					
					// terminate
					if (p == 100){
						// if (currentEl) {
						//     fadeOut(currentEl, 500, function(){
						//         currentEl.remove();
						//         clearInterval(i);
						//     });
						// } else {
						//     clearInterval(i);
						// }
						// LOGICA: Si el elemento ya fue eliminado manualmente o no existe, el callback de getEl no se ejecutará, por lo que debemos asegurar que clearInterval se llame en ambos casos (dentro y fuera) o gestionar la lógica de cierre.
						getEl(id, function(currentEl){
							fadeOut(currentEl, 500, function(){
								currentEl.remove();
							});
						});
						clearInterval(i);
					}
				}
			}
		};
		
		xhttp.open('GET', 'cache/progress.txt');
		xhttp.send();
		
	}, time);
}

// SUBMIT LOCK
function submitLockStart(){
	//submit_lock_count
	setTimeout(function(){
		if (submit_lock_count == 0){
			document.querySelectorAll('input[type="submit"]').forEach(function(btn){
				btn.disabled = true;
			});
		} 
		submit_lock_count = submit_lock_count + 1;
	}, 1);
}

function submitLockEnd(){
	submit_lock_count = submit_lock_count - 1;
	if (submit_lock_count <= 0){
		submit_lock_count = 0;
		document.querySelectorAll('input[type="submit"]').forEach(function(btn){
			btn.disabled = false;
		});
	} 
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
	var popup = document.createElement('div');
	var isCentered = relative_to_id === undefined;

	if (isCentered) {
		popup.classList.add('popup', 'center');
	} else {
		popup.classList.add('popup');
	}

	var closeBtn = document.createElement('a');
	closeBtn.href = '#';
	closeBtn.classList.add('close');
	closeBtn.innerHTML = '&times;';
	closeBtn.onclick = function (e) {
		e.preventDefault();
		this.parentElement.remove();
		return false;
	};

	popup.appendChild(closeBtn);

	if (content instanceof Node) {
		popup.appendChild(content);
	} else {
		popup.innerHTML = content;
		popup.appendChild(closeBtn);
	}

	if (isCentered) {
		document.body.appendChild(popup);
	} else {
		// var relEl = document.getElementById(relative_to_id);
		// if (relEl && relEl.parentElement) {
		//     relEl.parentElement.appendChild(popup);
		// }
		getEl(relative_to_id, function(relEl){
			if (relEl.parentElement) {
				relEl.parentElement.appendChild(popup);
			}
		});
	}
}



// HANDLEBARS
var count_handlebars_retries = 0;
function initHandlebarsHelpers() {
    if (typeof Handlebars === 'undefined'){
		count_handlebars_retries++;
		if (count_handlebars_retries < 10){
			// retry later
			setTimeout(initHandlebarsHelpers, 2000);
		}		
		return;
	}	 

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
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHandlebarsHelpers);
} else {
    initHandlebarsHelpers();
}

