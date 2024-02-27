
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

	if(typeof(loading_text) == 'undefined') {
		setHtml(target, '');
	} else {
		setHtml(target, loading_text);
	}

	$.ajax({
	  url: url,
	  success: function(data){
			setHtml(target, data);
			if (callback != undefined) callback();
		},
	  type: method,
	  data: post_data,
	  dataType: "text"
	});

}

function setHtml(target, html){
	if (target != ''){
		$('#'+target).html(html);
		/*var e = document.getElementById(target);
		if (e != undefined && e != null){
			e.innerHTML = html;
		}*/
	}
}

function appendHtml(target, html){
	if (target != ''){
		/*var e = document.getElementById(target);
		if (e != undefined && e != null){
			e.innerHTML += html;
		}*/
		$('#'+target).append(html);
	}
}

function moveHtml(source, target){
	setHtml(target, $('#'+source).html());
}

function cloneContent(source, target){
	$('#'+target).append($('#'+source).html());
}

function setStyle(target, attribute, value){
	$('#'+target).css(attribute, value);
}

function setClass(target, clss){
	$('#'+target).removeAttr('class');
	$('#'+target).attr('class', clss);
}

function confirmar(url, mensaje){
	if (confirm(mensaje))
	{
		getUrl(url);
	}
}

function refresh(){
	document.location.href=document.location.href;
}

function setValue(value, target){
	$('#'+target).val(value);
}

function getValue(target){
	return $('#'+target).val();
}

function appendValue(value, target){
	setValue(getValue(target) + value, target);
}


function toggleElement(elementId, buttonId, visibleText, invisibleText, isLink){
	var e = $('#'+elementId);
	var b = $('#'+buttonId);
	if (e.css('display') == 'none'){
		e.show();
		if (isLink){ 
			b.html(visibleText); 
		} else { 
			b.val(visibleText); 
		}
	} else {
		e.hide();
		if (isLink){ 
			b.html(invisibleText); 
		} else { 
			b.val(invisibleText); 
		}
	}
}

function setAttr(target, attribute, value){
	$('#'+target).attr(attribute, value);
}

function safetextarea(){
	$('textarea').each(function(i,t){
		$(t).val($(t).val().replace(/\/safetextarea/g, '/textarea'));
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

// TEMP: to deprecate
function addConfigRecord () {
  var v = getValue('_count'); 

  appendHtml('configrecords', '<div class="view row" id=newrecord' + v + '><div class="view three columns"><input type="text" id=label' + v + ' onchange="setAttr(\'value' + v + '\', \'name\', this.value)" style=width:140px></div><div class="view nine columns"><input type="text" id=value'+v+' name="" style="vertical-align:top"> <input type=button onclick="setHtml(\'newrecord' + v + '\', \'\')" value=Remove class="small button"></div></div>'); 

  setValue(parseInt(v) + 1, '_count');
}

function setData(table, id, field, value){
	getUrlAjax('admin.php?action=save&table='+table+'&id='+id, '', field+'='+value, '');
}

function topFind(element_selector, top_selector) {
	var e;
	e = $(element_selector).parent();
	while (!e.is(top_selector) && e.length > 0){
		e = $(e).parent();
	}
	return e;
}

// LISTVIEW IMPROVEMENTS:
function getUrlAjaxLV(url, post_data, loading_text){
	
	var method = 'GET';
	if(typeof(post_data) != 'undefined') {
		method = 'POST';
	} else {
		post_data = '';
	}

	setHtml('_load_more_span', loading_text);

	$.ajax({
		url: url,
		success: function(data){
		    setHtml('_load_more_span', '');
			setHtml('_load_more_result', data);
			$('#listViewForm table').append($('#_load_more_result tr'));
			$('#_load_more_span').append($('#_load_more_result #_load_more_button'));
			setHtml('_load_more_result', '');
		},
		type: method,
		data: post_data,
		dataType: "text"
	});

}

function listview_delete(id, table, confirm_msg){
	if (confirm(confirm_msg)){
		var url = 'admin.php?action=remove&id='+ id +'&table='+table;
		$.ajax(url);
		$('#_record'+id).remove();
	}

}

// 
// #listViewForm table
// _load_more_span
// _load_more_button
// _load_more_result




function listViewToggleSelectAll(){
	var val = $('#select_all').is(':checked');
	$('input[name="id[]"]').prop('checked', val);
	listViewEnableButtons()
}

function listViewEnableButtons(){
	var val = ($('input[name="id[]"]:checked').length == 0);
	$('input[name=edit_button]').prop('disabled', val);
	$('input[name=delete_button]').prop('disabled', val);
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
		if ($(selector).length > 0){
			clearInterval(i);
			callback();
		}
	}, 100);
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