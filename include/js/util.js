
function getUrl(url){
	document.location.href = url;
}

function getUrlAjax(url, target, post_data, loading_text, unk, callback){

	var method = 'GET';
	if(typeof(post_data) != 'undefined') {
		method = 'POST';
	} else {
		post_data = '';
	}

	setHtml(target, loading_text || '');

	var options = { method: method };
	if (method === 'POST') {
		options.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
		options.body = post_data;
	}

	fetch(url, options)
		.then(function(response){ return response.text(); })
		.then(function(data){
			setHtml(target, data);
			if (callback != undefined) callback();
		})
		.catch(function(error){ console.error('getUrlAjax error:', error); });

}

function setHtml(target, html){
	if (target != ''){
		getEl(target, function (el){
			setHtmlTo(el, html);
		});
	}
}

function setHtmlTo(el, html){
	el.innerHTML = '';
	appendHtmlTo(el, html);
}

function appendHtml(target, html, prepend = false){
	if (target != ''){
		getEl(target, function(el){
			appendHtmlTo(el, html, prepend);
		});
	}
}

function appendHtmlTo(targetEl, html, prepend = false){
	
	if (!targetEl) return;

	var temp = document.createElement('div');
	temp.innerHTML = html;

	// Ejecutar scripts incluidos en el HTML insertado
	var scripts = temp.querySelectorAll('script');
	scripts.forEach(function(oldScript){
		var newScript = document.createElement('script');

		// Copiar atributos (src, type, async, defer, etc.)
		Array.from(oldScript.attributes).forEach(function(attr){
			newScript.setAttribute(attr.name, attr.value);
		});

		// Copiar contenido inline
		if (oldScript.innerHTML) {
			newScript.textContent = oldScript.innerHTML;
		}

		// Reemplazar dentro del contenedor temporal para que se ejecute al insertarse en el DOM
		oldScript.parentNode.replaceChild(newScript, oldScript);
	});

	// Insertar nodos: al inicio si prepend es true, al final si no
	var refNode = prepend ? targetEl.firstChild : null;
	while (temp.firstChild) {
		targetEl.insertBefore(temp.firstChild, refNode);
	}
}

function prependHtml(target, html){
	appendHtml(target, html, true);
}

function prependHtmlTo(el, html){
	appendHtmlTo(el, html, true);
}

function moveHtml(source, target){
	var src = document.getElementById(source);
	setHtml(target, src ? src.innerHTML : '');
}

function cloneContent(source, target){
	var src = document.getElementById(source);
	if (src){
		appendHtml(target, src.innerHTML);
	}
}

function setStyle(target, attribute, value){
	var el = document.getElementById(target);
	if (el) el.style[attribute] = value;
}

function getStyle(target, attribute){
	var el = document.getElementById(target);
	return el ? el.style[attribute] : '';
}

function setClass(target, clss){
	var el = document.getElementById(target);
	if (el) el.className = clss;
}

function hide(target) {
	setStyle(target, 'display', 'none');
}

function show(target, displayType = '') {
	setStyle(target, 'display', displayType);
}

function confirmar(url, mensaje){
	if (confirm(mensaje))
	{
		getUrl(url);
	}
}

function refresh(){
	document.location.href = document.location.href;
}

function setValue(value, target){
	getEl(target, function (el){ el.value = value; });
}

function getValue(target){
	var el = document.getElementById(target);
	return el ? el.value : '';
}

function appendValue(value, target){
	setValue(getValue(target) + value, target);
}


function toggleElement(elementId, buttonId, visibleText, invisibleText, isLink){
	var e = document.getElementById(elementId);
	var b = document.getElementById(buttonId);
	if (!e || !b) return;

	var display = window.getComputedStyle(e).display;
	if (display == 'none'){
		e.style.display = '';
		if (isLink){ 
			setHtml(buttonId, visibleText); 
		} else { 
			setValue(visibleText, buttonId); 
		}
	} else {
		e.style.display = 'none';
		if (isLink){ 
			setHtml(buttonId, invisibleText); 
		} else { 
			setValue(invisibleText, buttonId); 
		}
	}
}

function setAttr(target, attribute, value){
	var el = document.getElementById(target);
	if (el) el.setAttribute(attribute, value);
}

function safetextarea(){
	queryAll('textarea').forEach(function(t){
		t.value = t.value.replace(/\/safetextarea/g, '/textarea');
	});
}

function guid() {
	return s4() + s4() + '-' + s4() + '-' + s4() + '-' + s4() + '-' + s4() + s4() + s4();
}

function s4() {
	return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);

}

function empty(value) {
	return (typeof(value) == 'undefined' || value == undefined || (typeof(value) == 'string' && value == '') || (typeof(value) == 'number' && value == 0) || (typeof(value) == 'boolean' && value == false) || (typeof(value) == 'object' && value.length == 0) || ( typeof(value) == 'object' && Object.keys(value).length == 0 ) );
}

function topFind(element_selector, top_selector) {
	var e = document.querySelector(element_selector);
	if (!e) return null;
	e = e.parentElement;
	while (e && !e.matches(top_selector)){
		e = e.parentElement;
	}
	return e;
}

function removeTags(str) {
    if ((str===null) || (str===''))
        return false;
    else
        str = str.toString();
          
    // Regular expression to identify HTML tags in 
    // the input string. Replacing the identified 
    // HTML tag with a null string.
    return str.replace( /(<([^>]+)>)/ig, '');
}

function uniqueId(){
	return Date.now().toString()+Math.random().toString().substr(2);
}

function serialize(obj) {
	var str = [];
	for (var p in obj)
	  if (obj.hasOwnProperty(p)) {
		str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
	  }
	return str.join("&");
  }

function unserialize(str){
	var match,
        pl     = /\+/g,  // Regex for replacing addition symbol with a space
        search = /([^&=]+)=?([^&]*)/g,
        decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
		query  = str,
		urlParams = {};

    while (match = search.exec(query))
	   urlParams[decode(match[1])] = decode(match[2]);
	   
	return urlParams;
}

function while_selector(selector, callback){
	var i = setInterval(function (){
		if (queryAll(selector).length > 0){
			clearInterval(i);
			callback();
		}
	}, 100);
}

function formatNumber(num, thousands_sep, decimal_sep, is_amount, dollar_sign){
	var r = num.toString();
	
	// decimal separator
	if (decimal_sep == undefined){
		r = r.replace('.', global.lang.DECIMAL_SEPARATOR);
	} else {
		r = r.replace('.', decimal_sep);
	}
	
	// thousands separator
	if (thousands_sep == undefined){
		// TODO: from language
		r = r.replace(/\B(?=(\d{3})+(?!\d))/g, global.lang.THOUSANDS_SEPARATOR);
	} else {
		r = r.replace(/\B(?=(\d{3})+(?!\d))/g, thousands_sep);
	}	

	// dollar sign
	if (is_amount != undefined && is_amount == true){
		if (dollar_sign == undefined){
			r = global.lang.DOLLAR + r;
		} else {
			r = dollar_sign + r;
		}		
	}

	return r;
}

var decodeEntities = (function() {
	// this prevents any overhead from creating the object each time
	var element = document.createElement('div');
  
	function decodeHTMLEntities (str) {
	  if(str && typeof str === 'string') {
		// strip script/html tags
		str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
		str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
		element.innerHTML = str;
		str = element.textContent;
		element.textContent = '';
	  }
  
	  return str;
	}
  
	return decodeHTMLEntities;
  })();

// --- Funciones recomendadas para animaciones y DOM ---

function fadeIn(el, duration, callback) {
	var element = (typeof el === 'string') ? document.getElementById(el) : el;
	if (!element) return;

	duration = duration || 400;
	element.style.opacity = 0;
	element.style.display = '';

	var start = performance.now();
	function step(now) {
		var elapsed = now - start;
		var progress = Math.min(elapsed / duration, 1);
		element.style.opacity = progress;
		if (progress < 1) {
			requestAnimationFrame(step);
		} else if (typeof callback === 'function') {
			callback();
		}
	}
	requestAnimationFrame(step);
}

function fadeOut(el, duration, callback) {
	var element = (typeof el === 'string') ? document.getElementById(el) : el;
	if (!element) return;

	duration = duration || 400;
	var initialOpacity = parseFloat(window.getComputedStyle(element).opacity) || 1;
	var start = performance.now();

	function step(now) {
		var elapsed = now - start;
		var progress = Math.min(elapsed / duration, 1);
		element.style.opacity = initialOpacity * (1 - progress);
		if (progress < 1) {
			requestAnimationFrame(step);
		} else {
			element.style.display = 'none';
			if (typeof callback === 'function') callback();
		}
	}
	requestAnimationFrame(step);
}

function query(selector, context = document) {
	// wrapper sencillo de querySelector 
	// retorna el elemento que coincide con el selector
	const el = (context || document).querySelector(selector);
	return el;
}

function queryAll(selector, context = document) {
	// wrapper sencillo de querySelectorAll
	// retorna array nativo para hacer .forEach(...) directamente
	return Array.from((context || document).querySelectorAll(selector));
} 

function removeElement(idOrEl) {
	// remoción segura del elemento sin requerir verificar nulidad previa en cada llamada
	const el = typeof idOrEl === 'string' ? document.getElementById(idOrEl) : idOrEl;
	el?.remove();
}

function getEl(element_id, callback){
	var el = document.getElementById(element_id);
	if (el && typeof callback === 'function'){
		callback(el);
	}
	return el;
}

/**
 * Crea un elemento DOM con atributos, eventos y contenido de forma limpia
 */
function createEl(tag, attrs = {}, children = []) {
	const el = document.createElement(tag);
	for (const [key, val] of Object.entries(attrs)) {
		if (key === 'className') {
			el.className = val;
		} else if (key === 'style' && typeof val === 'object') {
			Object.assign(el.style, val);
		} else if (key.startsWith('on') && typeof val === 'function') {
			el.addEventListener(key.slice(2).toLowerCase(), val);
		} else {
			el.setAttribute(key, val);
		}
	}
	(Array.isArray(children) ? children : [children]).forEach(child => {
		if (typeof child === 'string' || typeof child === 'number') {
			el.appendChild(document.createTextNode(child));
		} else if (child instanceof Node) {
			el.appendChild(child);
		}
	});
	return el;
}

/**
 * Agrega un nodo DOM directamente a un elemento por id.
 * Útil para evitar parsear strings HTML cuando ya se tiene un elemento creado con createEl.
 */
function appendNode(targetId, node) {
	var el = document.getElementById(targetId);
	if (el && node) el.appendChild(node);
}

/**
 * Vacía el contenido de un elemento por id.
 * Reemplaza patrones tipo jQuery(elem).html('') o this.parentElement.innerHTML = ''.
 */
function removeChildren(targetId) {
	var el = document.getElementById(targetId);
	if (el) el.innerHTML = '';
}

/**
 * Crea un enlace <a> con texto, onclick y atributos opcionales.
 * Puede sustituir a renderLink si este último devuelve strings HTML.
 */
function createLink(text, onclick, attrs = {}) {
	var a = createEl('a', Object.assign({ href: '#', onclick: onclick }, attrs));
	a.textContent = text;
	return a;
}

/**
 * Itera sobre un array u objeto (sustituto de $.each).
 */
function each(col, callback) {
    if (Array.isArray(col)) {
        col.forEach((element, index) => {
            callback(index, element);
        });
    } else if (col && typeof col === 'object') {
        Object.entries(col).forEach(function(pair) {
            callback(pair[0], pair[1]);
        });
    }
}

/**
 * Convierte una cadena HTML en el primer elemento DOM.
 * Útil junto con createEl para trabajar con nodos en lugar de strings.
 */
function htmlToElement(html) {
	var temp = document.createElement('div');
	temp.innerHTML = html;
	return temp.firstElementChild;
}

/**
 * Establece múltiples atributos en un elemento DOM.
 */
function setAttributes(el, attrs) {
	for (var key in attrs) {
		if (attrs.hasOwnProperty(key)) {
			if (key === 'className') {
				el.className = attrs[key];
			} else {
				el.setAttribute(key, attrs[key]);
			}
		}
	}
}

function setDisabled(element_id, dis) {
    getEl(element_id, function (el){
		el.disabled = dis;
	});
}