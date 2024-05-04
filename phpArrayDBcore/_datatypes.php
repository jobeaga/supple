<?php $_datatypes = array('0' => array('typename' => 'TextField', 'template' => '<input type=text size="\$size" name="\$_name" value="\$_value">', 'metatemplate' => '<label class=view>Size:</label> <input type=text size=5 name=size value=$($size)[$size][20]><br>', 'id' => 1, 'viewtemplate' => '$_value
<input type=hidden name="$_name" value="$_htmlentities(")[$_value]" id="$_name">', 'edittemplate' => '<input type=text size="$size" name="$_name" value="$_htmlentities(")[$_value]" id="$_name">', 'date_modified' => '2020-04-28 02:15', 'modified_by' => 2, 'js_viewtemplate' => '{{value}} <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'js_edittemplate' => '<input type="text" {{#xif " this.view == &#039;2&#039; && this.f.size > 19 "}}{{else}}size="{{f.size}}"{{/xif}} name="{{name}}" id="{{id}}" value="{{value}}"> ', 'search_ops' => '', 'search_ops_1' => '1', 'search_ops_2' => '2', 'search_ops_3' => '3', 'search_ops_4' => '4', 'search_ops_5' => '5', 'search_ops_6' => '10', 'search_ops_7' => '11')
, '1' => array('typename' => 'TextArea', 'viewtemplate' => '<span style="white-space:pre-line">
$($_ismobile)[
  $($_get.view == 2)[
    $($_value)[...][]
  ][$_value]
][$_value]
</span>
<input type=hidden name="$_name" value="$_htmlentities(")[$_value]" id="$_name">', 'edittemplate' => '<textarea cols=50 rows=$($f.size)[$f.size][6] name="$_name" id="$_name">$_value</textarea>', 'metatemplate' => '<label class=view>Rows:</label> <input type=text size=5 name=size value="$r.size"><br>', 'id' => 2, 'date_modified' => '2017-12-01 17:57', 'modified_by' => 2, 'js_viewtemplate' => '{{#xif " this.view == &#039;2&#039; "}}...{{else}}{{value}}{{/xif}} <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'js_edittemplate' => '<textarea cols="50" rows="{{f.size}}" name="{{name}}" id="{{id}}">{{value}}</textarea>')
, '2' => array('typename' => 'BooleanDropdown', 'viewtemplate' => '$($_value)[$_lang.LBL_YES][$_lang.LBL_NO]
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<select name="$_name" id="$_name"><option value=1 $($_value)[selected]> $_lang.LBL_YES  </option><option value=0 $($_value)[][selected]> $_lang.LBL_NO </option></select>', 'metatemplate' => '', 'id' => 3, 'date_modified' => '2022-04-26 02:45', 'modified_by' => 2, 'js_viewtemplate' => '{{#xif " this.value == &#039;1&#039; "}}{{lang.LBL_YES}}{{else}}{{lang.LBL_NO}}{{/xif}}
<input type=hidden name="{{name}}" value="{{value}}" id="{{id}}">', 'js_edittemplate' => '<select name="{{name}}" id="{{id}}"> {{#xif " this.view == &#039;2&#039; "}} </option><option value="" {{#xif " this.value == &#039;&#039; "}}selected{{/xif}}> </option> {{/xif}}  <option value="0" {{#xif " this.value == &#039;0&#039; "}}selected{{/xif}}> {{lang.LBL_NO}} </option><option value="1" {{#xif " this.value == &#039;1&#039; "}}selected{{/xif}}> {{lang.LBL_YES}}  </option></select>')
, '4' => array('typename' => 'FileUpload', 'viewtemplate' => '$_eval[ $_name . &#039;_size&#039;](=size_field)
$[$$size_field](=size) 
$($size > 0)[ 
<a href=admin.php?action=download&id=$record_id&field=$_name&table=$table> $_lang.LBL_DOWNLOAD </a>
]', 'edittemplate' => '$($_value)[ <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=hidden name=$_name id=$_name value> $_lang.BTN_DELETE&#039;)" value="$_lang.BTN_REMOVE"> <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=file name=$_name id=$_name>&#039;)" value="$_lang.BTN_MODIFY"> ][<input type=file name="$_name" id="$_name">]', 'metatemplate' => '', 'id' => 5, 'assigned_user_id' => '', 'date_modified' => '2017-12-01 18:00', 'modified_by' => 2, 'js_viewtemplate' => '{{#xif " this.value != &#039;&#039; "}}<a href="admin.php?action=download&id={{record_id}}&field={{f.name}}&table={{table}}"> {{lang.LBL_DOWNLOAD}} </a>{{/xif}}', 'js_edittemplate' => '{{#xif " this.value != &#039;&#039; "}} <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=hidden name={{name}} id={{id}} value> {{lang.BTN_DELETE}}&#039;)" value="{{lang.BTN_REMOVE}}"> <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=file name={{name}} id={{id}}>&#039;)" value="{{lang.BTN_MODIFY}}"> {{else}} <input type=file name="{{name}}" id="{{id}}"> {{/xif}}')
, '5' => array('typename' => 'ImageUpload', 'viewtemplate' => '$_eval[ &#039;$_name&#039; . &#039;_size&#039;](=size_field)
$[$$size_field](=size) 
$_eval[ &#039;$_name&#039; . &#039;_thumb&#039;](=thumb_field) 
$_entities(id==$_fields.parent)[$table](=_table)  

$($_value)[
<a href=admin.php?action=download&image=1&id=$_record_id&field=$_name&table=$_table> <img src=admin.php?action=download&image=1&id=$_record_id&field=$thumb_field&table=$_table height=50px> </a> 
]', 'edittemplate' => '$($_value)[ <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=hidden name=$_name id=$_name value> $_lang.BTN_DELETE&#039;)" value="$_lang.BTN_REMOVE"> <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=file name=$_name id=$_name>&#039;)" value="$_lang.BTN_MODIFY"> ][<input type=file name="$_name" id="$_name">]', 'metatemplate' => '', 'id' => 6, 'date_modified' => '2016-08-13 14:47', 'modified_by' => '2', 'js_viewtemplate' => '{{#xif " this.value != &#039;&#039; "}}<a href="admin.php?action=download&image=1&id={{record_id}}&field={{db_name}}&table={{table}}"> <img src="admin.php?action=download&image=1&id={{record_id}}&field={{db_name}}_thumb&table={{table}}" height=50px> </a>{{/xif}}', 'js_edittemplate' => '{{#xif " this.value != &#039;&#039; "}} <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=hidden name={{name}} id={{id}} value> {{lang.BTN_DELETE}}&#039;)" value="{{lang.BTN_REMOVE}}"> <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=file name={{name}} id={{id}}>&#039;)" value="{{lang.BTN_MODIFY}}"> {{else}} <input type=file name="{{name}}" id="{{id}}"> {{/xif}}')
, '6' => array('typename' => 'RelationshipDropdown', 'viewtemplate' => '<!--  -->
$[$_fields.related_table](=rtt_reltable) 
$$rtt_reltable(id==&#039;$_value&#039;)[  
$_entities:re(table==&#039;$rtt_reltable&#039;)[<a href="admin.php?view=4&entity=$re.id&id=$_value">]
$[$$related_field]
$_entities(table==&#039;$rtt_reltable&#039;)[</a>]
]
<input type=hidden name="$_name" value="$_value" id="$_name">', 'js_viewtemplate' => '<span id="span_{{id}}_{{f.id}}"></span><script> jQuery(&#039;#span_{{id}}_{{f.id}}&#039;).html(renderLinkTo(&#039;{{f.related_entity}}&#039;, &#039;4&#039;, &#039;{{value}}&#039;, &#039;&#039;));</script><script> setDataTo(&#039;span_{{id}}_{{f.id}} a&#039;, &#039;{{f.related_table}}&#039;, &#039;{{f.related_field}}&#039;, &#039;{{value}}&#039;);</script> <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'edittemplate' => '<!-- -->
<select name="$_name" id="$_name" $($f.save_related_name)[onchange="saveOptionName(&#039;$_name&#039;, &#039;$f.save_related_name&#039;)"] style="max-width:90%">
<option  $(&#039;&#039;==$_value)[selected=selected] value=""> -- $_lang.LBL_NONE -- </option> 
$$f.related_table{ 
  <option  $($id==$_value)[selected=selected] value="$id">$[$$f.related_field]</option> 
}($f.order_field)
</select> 

$($f.save_related_name)[
<input type=hidden name="$f.save_related_name" id="$f.save_related_name">
<script>saveOptionName(&#039;$_name&#039;, &#039;$f.save_related_name&#039;);</script>
]', 'js_edittemplate' => '<select name="{{name}}" id="{{id}}" {{#xif " this.f.save_related_name != &#039;&#039; "}}onchange="saveOptionName(&#039;{{id}}&#039;, &#039;{{f.save_related_name}}{{id_suffix}}&#039;)"{{/xif}} style="max-width:90%"></select><script>createRelatedDropdown(&#039;{{id}}&#039;, &#039;{{value}}&#039;, &#039;{{f.related_table}}&#039;, &#039;{{f.related_field}}&#039;, &#039;{{f.order_field}}&#039;);</script>

{{#xif " this.f.save_related_name != &#039;&#039; "}} <input type="hidden" name="{{f.save_related_name}}{{name_suffix}}" id="{{f.save_related_name}}{{id_suffix}}">{{/xif}}

{{#xif " this.f.createButton == &#039;1&#039; "}} CREATE {{/xif}}
{{#xif " this.f.editButton == &#039;1&#039; "}} EDIT {{/xif}}', 'metatemplate' => '<label class=view>Table:</label> <input type=text size=20 name=related_table value=$_e_related_table><br>        <label>Name Field:</label> <input type=text size=20 name=related_field value=$_e_related_field><br>       <label>Order Field:</label> <input type=text size=20 name=order_field value=$_e_order_field><br>', 'id' => 7, 'date_modified' => '2020-05-18 03:08', 'modified_by' => 2, 'search_ops' => '', 'search_ops_1' => '')
, '8' => array('typename' => 'Hidden', 'viewtemplate' => '<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<input type=hidden name="$_name" value="$_value" id="$_name">', 'metatemplate' => '', 'id' => 9, 'date_modified' => '2019-04-14 19:54', 'modified_by' => 2, 'js_viewtemplate' => '<input type=hidden name="{{name}}" value="{{value}}" id="{{id}}">', 'js_edittemplate' => '<input type=hidden name="{{name}}" value="{{value}}" id="{{id}}">')
, '9' => array('typename' => 'URL', 'viewtemplate' => '$($_value)[<span style="text-overflow:ellipsis;white-space:nowrap;overflow: hidden;width: 95%;display:inline-block;vertical-align:bottom;"><a href="$_value"> $_value </a></span>][$_lang.LBL_NO_LINK]
<input type=hidden name="$_name" value="$_htmlentities(")[$_value]" id="$_name">', 'edittemplate' => '<input type="url" name="$_name" id="$_name" value="$_value" style="width:96%">', 'metatemplate' => '', 'id' => 10, 'date_modified' => '2019-04-18 14:28', 'modified_by' => 2, 'js_viewtemplate' => '{{#xif " this.value == &#039;&#039; "}}{{lang.LBL_NO_LINK}}{{else}}{{value}}{{/xif}} <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'js_edittemplate' => '<input type="url" name="{{name}}" id="{{id}}" value="{{value}}" style="width:96%">')
, '10' => array('typename' => 'ID Link', 'viewtemplate' => '<a href="$[$link]$($_get.view==2)[$e.id][$_get.id]" id="$_name">$_name</a>', 'edittemplate' => '<a href="$[$link]$($_get.view==2)[$e.id][$_get.id]" id="$_name">$_name</a>', 'metatemplate' => '<label class=view>Link:</label> <input type=text size=20 name=link value=><br>', 'id' => 11, 'assigned_user_id' => '', 'date_modified' => '2019-04-18 18:08', 'modified_by' => 2, 'js_viewtemplate' => '<a href="{{f.link}}{{record_id}}" id="{{id}}" target="_blank">{{name}}</a> <input type=hidden id="{{id}}"  value="{{f.link}}{{record_id}}">', 'js_edittemplate' => '<a href="{{f.link}}{{record_id}}" id="{{id}}" target="_blank">{{name}}</a> <input type=hidden id="{{id}}"  value="{{f.link}}{{record_id}}">')
, '11' => array('typename' => 'Password', 'viewtemplate' => '$($_value)[$_lang.LBL_YES][$_lang.LBL_NO]
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<input type=password id="$_name" name="$_name" value="$_value" size=40 $($_value)[style="display:none"]> 
$($_value)[ <input type=button id="button$_name" value="$_lang.BTN_EDIT" onclick="setStyle(&#039;$_name&#039;, &#039;display&#039;, &#039;inline&#039;); setStyle(&#039;button$_name&#039;, &#039;display&#039;, &#039;none&#039;);setValue(&#039;&#039;,&#039;$_name&#039;);"> ]', 'js_viewtemplate' => '{{#xif " this.value != &#039;&#039; "}}{{lang.LBL_YES}}{{else}}{{lang.LBL_NO}}{{/xif}} <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'js_edittemplate' => '<input type="password" size="40" name="{{name}}" id="{{id}}" value="{{value}}" autocomplete="new-password" {{#xif " this.value != &#039;&#039; "}}style="display:none"{{/xif}} >
{{#xif " this.value != &#039;&#039; "}}<input type=button id="button{{id}}" value="{{lang.BTN_EDIT}}" onclick="setStyle(&#039;{{id}}&#039;, &#039;display&#039;, &#039;inline&#039;); setStyle(&#039;button{{id}}&#039;, &#039;display&#039;, &#039;none&#039;);setValue(&#039;&#039;,&#039;{{id}}&#039;);">{{/xif}}
', 'metatemplate' => '', 'id' => 12, 'date_modified' => '2016-07-27 04:58', 'modified_by' => '2')
, '12' => array('typename' => 'BooleanCheckbox', 'viewtemplate' => '<!-- -->
$($_value)[$_lang.LBL_YES][$_lang.LBL_NO]
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '$($_ismobile)[<select name="$_name" id="$_name"><option value=1 $($_value)[selected]> $_lang.LBL_YES  </option><option value=0 $($_value)[][selected]> $_lang.LBL_NO </option></select>][<input type=hidden name="$_name" value=0>
<input type=checkbox name="$_name" id="$_name" value=1 $($_value)[checked]>]', 'js_viewtemplate' => '{{#xif " this.value == &#039;1&#039; "}}{{lang.LBL_YES}}{{else}}{{lang.LBL_NO}}{{/xif}}
<input type=hidden name="{{name}}" value="{{value}}" id="{{id}}">', 'js_edittemplate' => '{{#xif " this.view == &#039;2&#039; "}}<select name="{{name}}" id="{{id}}">  </option><option value="" {{#xif " this.value == &#039;&#039; "}}selected{{/xif}}> </option> <option value="1" {{#xif " this.value == &#039;1&#039; "}}selected{{/xif}}> {{lang.LBL_YES}}  </option><option value="0" {{#xif " this.value == &#039;0&#039; "}}selected{{/xif}}> {{lang.LBL_NO}} </option></select>     {{else}}  <input type=hidden name="{{name}}" value=0>
<input type=checkbox name="{{name}}" id="{{id}}" value=1 {{#xif " this.value == &#039;1&#039; "}}checked{{else}}{{/xif}}> {{/xif}}', 'metatemplate' => '', 'id' => 13, 'date_modified' => '2016-07-27 04:45', 'modified_by' => '2')
, '13' => array('typename' => 'Dropdown', 'viewtemplate' => '$_eval[ translateOptions(chr($sep2), chr($sep1), "$options", "$_value" ) ]
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<select name="$_name" id="$_name">
$_eval[ createOptions(chr($sep2), chr($sep1), "$_htmlentities(")[$options]", "$_htmlentities(")[$_value]" ) ]
</select>', 'js_edittemplate' => '<select name="{{name}}" id="{{id}}"></select>
<script> createOptions("{{id}}", "{{f.option_list}}", "{{value}}"); </script>', 'js_viewtemplate' => '<span id="{{id}}"></span><script> translateOptions("{{id}}", "{{f.option_list}}", "{{value}}"); </script>', 'metatemplate' => '<label class=view>Opciones:</label> <textarea name=options rows=6 cols=40>$($options)[$options]</textarea><br> <label>Separador de Items:</label> <select name=sep1> <option value=124 $($sep1==&#039;124&#039;)[SELECTED]> Pipe (|) </option> <option value=59 $($sep1==&#039;59&#039;)[SELECTED]> Punto y Coma (;) </option> </select> <br> <label>Separador de Clave y Valor:</label> <select name=sep2> <option value=58 $($sep2==&#039;58&#039;)[SELECTED]> Dos puntos (:) </option> <option value=44 $($sep2==&#039;44&#039;)[SELECTED]> Coma (,) </option> <option value=61 $($sep2==&#039;61&#039;)[SELECTED]> Igual (=) </option> </select>', 'id' => 14, 'date_modified' => '2020-05-18 02:19', 'modified_by' => 2, 'search_ops' => '', 'search_ops_1' => '1', 'search_ops_2' => '5', 'search_ops_3' => '7', 'search_ops_4' => '10', 'search_ops_5' => '11')
, '15' => array('typename' => 'RichText', 'viewtemplate' => '<span class="richtext">$_value</span>
<input type=hidden name="$_name" value="$_htmlentities(")[$_value]" id="$_name">', 'edittemplate' => '<textarea cols=50 rows="$($size)[$size][5]" name="$_name" id="$_name">$_value</textarea>
<script>
  tinymce.init({
    selector:&#039;#$_name&#039;,
    statusbar: false,
    plugins: "code link",
    menu: {
$($f.show_menu)[      edit: {title: &#039;Edit&#039;, items: &#039;undo redo | cut copy paste | selectall&#039;},
      format: {title: &#039;Format&#039;, items: &#039;bold italic underline strikethrough&#039;}, ]
    },

    toolbar: &#039;$($f.show_toolbar_undo)[undo redo | ] $($f.show_toolbar_copypaste)[cut copy paste pastetext | ] $($f.show_toolbar_basicformat)[bold italic underline strikethrough | ] $($f.show_toolbar_styleformat)[fontselect fontsizeselect | ] $($f.show_toolbar_align)[alignleft alignright aligncenter alignjustify | ] $($f.show_toolbar_lists)[bullist numlist outdent indent | ]  $($f.allow_free_html)[code | ]  $($f.show_toolbar_link)[link]&#039;
  });
</script>', 'metatemplate' => '<label class=view>Rows:</label> <input type=text size=5 name=size value=5><br>', 'id' => 16, 'date_modified' => '2019-04-20 00:48', 'modified_by' => 2, 'assigned_user_id' => '', 'js_viewtemplate' => '<span class="richtext" id="{{id}}_richtext"></span>
<input type=hidden name="{{name}}" value="{{value}}" id="{{id}}">
<script>
$(&#039;#{{id}}_richtext&#039;).html($(&#039;#{{id}}&#039;).val());
</script>
', 'js_edittemplate' => '<textarea cols=50 rows="{{f.size}}" name="{{name}}" id="{{id}}">{{value}}</textarea>
<script>
$(&#039;#{{id}}&#039;).trumbowyg({
  removeformatPasted: true,
  btns: [
        {{#xif " this.f.allow_free_html != &#039;&#039; "}}[&#039;viewHTML&#039;],{{/xif}}
        {{#xif " this.f.show_toolbar_undo != &#039;&#039; "}}[&#039;undo&#039;, &#039;redo&#039;],{{/xif}}
        {{#xif " this.f.show_toolbar_basicformat != &#039;&#039; "}}[&#039;strong&#039;, &#039;em&#039;, &#039;del&#039;],{{/xif}}
        {{#xif " this.f.show_toolbar_link != &#039;&#039; "}}[&#039;link&#039;],{{/xif}}
        {{#xif " this.f.allow_free_html != &#039;&#039; "}}[&#039;insertImage&#039;],{{/xif}}
        {{#xif " this.f.show_toolbar_align != &#039;&#039; "}}[&#039;justifyLeft&#039;, &#039;justifyCenter&#039;, &#039;justifyRight&#039;, &#039;justifyFull&#039;],{{/xif}}
        {{#xif " this.f.show_toolbar_lists != &#039;&#039; "}}[&#039;unorderedList&#039;, &#039;orderedList&#039;],{{/xif}}
        [&#039;removeformat&#039;],
        [&#039;fullscreen&#039;]
    ]
});
</script>')
, '16' => array('typename' => 'EmailAddress', 'viewtemplate' => '$_value
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<input type=email size="$size" name="$_name" value="$_value" id="$_name">', 'js_viewtemplate' => '{{value}} <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'js_edittemplate' => '<input type="email" size="{{f.size}}" name="{{name}}" id="{{id}}" value="{{value}}&quot autocomplete="off";>', 'metatemplate' => '', 'id' => 17, 'date_modified' => '2016-07-27 04:53', 'modified_by' => '2')
, '17' => array('typename' => 'Number', 'viewtemplate' => '$_value
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<input type="number" size="$size" name="$_name" value="$_value" id="$_name" $($max)[max=$max] $($min)[min=$min]>', 'js_viewtemplate' => '<span id="{{id}}_number">{{value}}</span> <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}"><script>document.getElementById("{{id}}_number").innerHTML = formatNumber("{{value}}");</script>', 'js_edittemplate' => '<input type="number" size="{{f.size}}" name="{{name}}" id="{{id}}" value="{{value}}" {{#xif " this.f.max != &#039;&#039; "}}max={{f.max}}{{/xif}} {{#xif " this.f.min != &#039;&#039; "}}min={{f.min}}{{/xif}} >', 'metatemplate' => '<label class=view>Size:</label> <input type=text size=5 name=size value="$($r.size)[$r.size][10]"><br><label class=view>Min:</label> <input type=text size=5 name=min value="$r.min"><br><label class=view>Max:</label> <input type=text size=5 name=max value="$r.max"><br>', 'id' => 18, 'date_modified' => '2016-07-27 04:56', 'modified_by' => '2')
, '18' => array('typename' => 'Date', /*'viewtemplate' => '$_eval[ formatDate(&#039;$_value&#039;, &#039;$_config.php_date_format&#039;) ]
<input type=hidden value="$_eval[ formatDate(&#039;$_value&#039;, &#039;$_config.php_date_format&#039;) ]" id="$_name">
<input type="hidden" value="$_value" id="_date_$_name" name="$_name">', 'edittemplate' => '<input type="text" id="$_name" value="$_eval[ formatDate(&#039;$_value&#039;, &#039;$_config.php_date_format&#039;) ]">
<input type="hidden" value="$_value" id="_date_$_name" name="$_name">
<script> 
jQuery.datetimepicker.setLocale(&#039;$_config.js_datetime_locale&#039;);
jQuery(&#039;#$_name&#039;).datetimepicker({
  timepicker:false,
  format: &#039;$_config.php_date_format&#039;,
  onChangeDateTime: function(dp,input) {
    var v = moment(input.val(), &#039;$_config.js_date_format&#039;).format(&#039;YYYY-MM-DD&#039;);
    if (v == &#039;Invalid date&#039;) v = &#039;&#039;;
    jQuery(&#039;#_date_$_name&#039;).val(v);
  }
});</script>',*/  'js_edittemplate' => '  <input type="text" id="{{id}}" value="" autocomplete="off" size=10>
  <input type="hidden" value="{{value}}" id="_date_{{id}}" name="{{name}}">
  <script>
    jQuery(document).ready(function(){ 
      jQuery.datetimepicker.setLocale(global.config.js_datetime_locale);
      jQuery("#{{id}}").datetimepicker({
        timepicker:false,
        format: global.config.php_date_format,
        onChangeDateTime: function(dp,input) {
          var v = moment(input.val(), global.config.js_date_format).format("YYYY-MM-DD");
          if (v == "Invalid date") v = "";
          jQuery("#_date_{{id}}").val(v);
        }
      });
      var v = moment(jQuery("#_date_{{id}}").val(), "YYYY-MM-DD").format(global.config.js_date_format);
      if (v == "Invalid date") v = "";
      jQuery("#{{id}}").val(v);
    });
  </script>
',
/*'js_edittemplate' => '<input type="date" id="{{id}}" name="{{name}}" value="{{value}}">',*/
'js_viewtemplate' => '    <span id="{{id}}_{{f.id}}"></span>
    <input type="hidden" value="{{value}}" id="_date_{{id}}_{{f.id}}" name="{{name}}">
    <script>
      var v = moment(jQuery("#_date_{{id}}_{{f.id}}").val(), "YYYY-MM-DD").format(global.config.js_date_format);
      if (v == "Invalid date") v = "";
      jQuery("#{{id}}_{{f.id}}").html(v);
    </script>
', 'metatemplate' => '', 'id' => 19, 'date_modified' => '2020-04-28 02:14', 'modified_by' => 2, 'search_ops' => '', 'search_ops_1' => '1', 'search_ops_2' => '8', 'search_ops_3' => '9', 'search_ops_4' => '6', 'search_ops_5' => '10', 'search_ops_6' => '11', 'search_ops_7' => '12', 'search_ops_8' => '13', 'search_ops_9' => '14')
/*, '19' => array('typename' => 'DateTime', 'viewtemplate' => '$_eval[ formatDate(&#039;$_value&#039;, &#039;$_config.php_datetime_format&#039;) ]
<input type="hidden" value="$_eval[ formatDate(&#039;$_value&#039;, &#039;$_config.php_datetime_format&#039;) ]" id="$_name">
<input type="hidden" value="$_value" id="_date_$_name" name="$_name">', 'edittemplate' => '<input type="text" id="$_name" value="$_eval[ formatDate(&#039;$_value&#039;, &#039;$_config.php_datetime_format&#039;) ]">
<input type="hidden" value="$_value" id="$_eval[&#039;$_name&#039;.&#039;_date&#039;]" name="$_name">
<script> 
jQuery.datetimepicker.setLocale(&#039;$_config.js_datetime_locale&#039;);
jQuery(&#039;#$_name&#039;).datetimepicker({
  timepicker:true,
  format: &#039;$_config.php_datetime_format&#039;,
  step: 15,
  onChangeDateTime: function(dp,input) {
    var v = moment(input.val(), &#039;$_config.js_datetime_format&#039;).format(&#039;YYYY-MM-DD HH:mm&#039;);
    if (v == &#039;Invalid date&#039;) v = &#039;&#039;;
    jQuery(&#039;#$_eval[&#039;$_name&#039;.&#039;_date&#039;]&#039;).val(v);
  }
});</script>', 'metatemplate' => '', 'id' => 20, 'date_modified' => '2019-04-18 16:32', 'modified_by' => 2, 'js_viewtemplate' => '    <span id="{{id}}"></span>
    <input type="hidden" value="{{value}}" id="_date_{{id}}" name="{{name}}">
    <script>
      var v = moment(jQuery("#_date_{{id}}").val(), "YYYY-MM-DD HH:mm").format(global.config.js_datetime_format);
      if (v == "") v = global.lang.LBL_EMPTY;
      jQuery("#{{id}}").html(v);
    </script>', 'js_edittemplate' => '  <input type="text" id="{{id}}" value="" autocomplete="off" size=16>
  <input type="hidden" value="{{value}}" id="_date_{{id}}" name="{{name}}">
  <script>
  jQuery(document).ready(function(){ 
    jQuery.datetimepicker.setLocale(global.config.js_datetime_locale);
    jQuery("#{{id}}").datetimepicker({
      timepicker:true,
      format: global.config.php_datetime_format,
      step: 15,
      onChangeDateTime: function(dp,input) {
        var v = moment(input.val(), global.config.js_datetime_format).format("YYYY-MM-DD HH:mm");
        if (v == "Invalid date") v = "";
        jQuery("#_date_{{id}}").val(v);
      }
    });
    var v = moment(jQuery("#_date_{{id}}").val(), "YYYY-MM-DD HH:mm").format(global.config.js_datetime_format);
    if (v == "Invalid date") v = "";
    jQuery("#{{id}}").val(v);
  });
  </script>
')*/
, '20' => array('typename' => 'Order', 'viewtemplate' => '$($_current_user.readonly)[][
<span class="order">
$($e._previousid || $e._nextid)[
<input type=hidden id="$_eval[&#039;_orderprevious&#039;.&#039;$_name&#039;.&#039;$e.id&#039;]" value="$e._previousid"> 
<input type="button" id="$_eval[&#039;_orderbutprevious&#039;.&#039;$_name&#039;.&#039;$e.id&#039;]" class="order_previous" onclick="order_swap_previous(&#039;$e.id&#039;, &#039;$_name&#039;)" $($e._previousid)[][disabled="disabled"]>

<span id="$_eval[&#039;_orderv&#039;.&#039;$_name&#039;.&#039;$e.id&#039;]" style="display:none">$_value</span>
<input type=hidden id="$_eval[&#039;_order&#039;.&#039;$_name&#039;.&#039;$e.id&#039;]" value="$_value">
<input type=hidden id="$_eval[&#039;_ordertable&#039;.&#039;$_name&#039;.&#039;$e.id&#039;]" value="$_entitytable">

<input type=hidden id="$_eval[&#039;_ordernext&#039;.&#039;$_name&#039;.&#039;$e.id&#039;]" value="$e._nextid">  
<input type="button" id="$_eval[&#039;_orderbutnext&#039;.&#039;$_name&#039;.&#039;$e.id&#039;]" class="order_next" onclick="order_swap_next(&#039;$e.id&#039;, &#039;$_name&#039;)" $($e._nextid)[][disabled="disabled"]> 
][
  $_value
  <input type=hidden name="$_name" value="$_value" id="$_name">
]
</span>
]', 'js_viewtemplate' => '{{#xif " this.global.current_user.readonly == &#039;1&#039; "}}&nbsp;{{else}}

<span class="order">

<input type=hidden id="_orderprevious{{f.name}}{{record_id}}" value="{{row._previousid}}"> 
<input type="button" id="_orderbutprevious{{f.name}}{{record_id}}" class="order_previous" onclick="order_swap_previous(&#039;{{record_id}}&#039;, &#039;{{f.name}}&#039;)" {{#xif " this.row._previousid == &#039;&#039; "}}disabled="disabled"{{/xif}}>

<span id="_orderv{{f.name}}{{record_id}}" style="display:none;">{{value}}</span>
<input type=hidden id="_order{{f.name}}{{record_id}}" value="{{value}}">
<input type=hidden id="_ordertable{{f.name}}{{record_id}}" value="{{table}}">

<input type=hidden id="_ordernext{{f.name}}{{record_id}}" value="{{row._nextid}}">  
<input type="button" id="_orderbutnext{{f.name}}{{record_id}}" class="order_next" onclick="order_swap_next(&#039;{{record_id}}&#039;, &#039;{{f.name}}&#039;)"  {{#xif " this.row._nextid == &#039;&#039; "}}disabled="disabled"{{/xif}} > 

</span>

{{/xif}} ', 'edittemplate' => '<input type="hidden" name="$_name" value="$_value" id="$_name"> $_value', 'js_edittemplate' => '<input type="hidden" name="{{name}}" id="{{id}}"  value="{{value}}"> {{value}}', 'metatemplate' => '', 'id' => 21, 'date_modified' => '2016-12-12 23:14', 'modified_by' => '2')
, '21' => array('typename' => 'IP', 'viewtemplate' => '$_value', 'edittemplate' => '$_value', 'metatemplate' => '$_lang.LBL_IP_DESCRIPTION', 'id' => 22, 'js_viewtemplate' => '<a href="#" onclick="return getIPinfo(&#039;{{value}}&#039;, &#039;{{id}}&#039;)">{{value}}</a> <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'js_edittemplate' => '<input type="text" size="15" name="{{name}}" id="{{id}}" value="{{value}}">', 'date_modified' => '2019-04-19 14:24', 'modified_by' => 2)
, '22' => array('typename' => 'EntityField', 'viewtemplate' => '$($_value)[
  $_fields:rf(id==$_value)[
    $_entities(id==$rf.parent)[<a href="admin.php?entity=$id&view=2">$name</a>] - $rf.name
  ]
]
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<!-- -->
$($_value)[$_fields(id==$_value)[$parent]][](=_parent)

<select  $($f.entity_id_field)[name="$f.entity_id_field" id="$f.entity_id_field"][ id="_entity_select_$_name"] onchange="entityFieldChange(&#039;$($f.entity_id_field)[$f.entity_id_field][_entity_select_$_name]&#039;,&#039;$_name&#039;); $($table_name_field)[saveOptionAttribute(&#039;$($f.entity_id_field)[$f.entity_id_field][_entity_select_$_name]&#039;,&#039;$table_name_field&#039;, &#039;table&#039;);]">
<option  $($_value)[][selected=selected] value=""> -- $_lang.LBL_NONE -- </option> 
$_entities:re{
  <option  $($re.id==$_parent)[selected=selected] value="$re.id" table="$re.table"> $re.name </option> 
}(name)
</select> 
<select name="$_name" id="$_name" $($f.field_name_field)[onchange="saveOptionName(&#039;$_name&#039;,&#039;$f.field_name_field&#039;)"]>
<option  $($_value)[][selected=selected] value=""> -- $_lang.LBL_NONE -- </option> 
$_fields:rf{ 
  <option  $($rf.id==$_value)[selected=selected] value="$rf.id" parent="$rf.parent">$rf.name</option> 
}(name)
</select>

$($f.field_name_field)[<input type=hidden id="$f.field_name_field" name="$f.field_name_field"  value="$$f.field_name_field">
<script> saveOptionName(&#039;$_name&#039;,&#039;$f.field_name_field&#039;); </script>] 

$($table_name_field)[ <input type=hidden id="$table_name_field" name="$table_name_field"  value="$$table_name_field">
<script> saveOptionAttribute(&#039;$($f.entity_id_field)[$f.entity_id_field][_entity_select_$_name]&#039;,&#039;$table_name_field&#039;, &#039;table&#039;); </script>]
 
<script> entityFieldChange(&#039;$($f.entity_id_field)[$f.entity_id_field][_entity_select_$_name]&#039;,&#039;$_name&#039;); </script>', 'js_edittemplate' => '<select {{#xif " this.f.entity_id_field != &#039;&#039;"}}name="{{f.entity_id_field}}" id="{{f.entity_id_field}}"{{else}}id="_entity_select_{{id}}"{{/xif}}></select> 
<select name="{{name}}" id="{{id}}"></select> 
{{#xif " this.f.field_name_field != &#039;&#039; "}}<input type=hidden id="{{f.field_name_field}}" name="{{f.field_name_field}}"  value="">{{/xif}}
{{#xif " this.f.table_name_field != &#039;&#039; "}}<input type=hidden id="{{f.table_name_field}}" name="{{f.table_name_field}}"  value="">{{/xif}}
<script> entityFieldCreate("{{name}}", "{{id}}", "{{value}}", {entity_id_field: "{{f.entity_id_field}}", table_name_field: "{{f.table_name_field}}", field_name_field: "{{f.field_name_field}}"}, "{{table}}", "{{record_id}}"); </script>', 'js_viewtemplate' => ' {{#xif " this.value != &#039;&#039; "}}<span id="{{id}}"></span><script> 
  var parent = metadata._fields["{{value}}"].parent; 
  var entity_name = metadata._entities[parent].name;
  var field_name = metadata._fields["{{value}}"].name;
  $("#{{id}}").append(&#039;<a href="admin.php?entity=1&view=4&id=&#039;+parent+&#039;">&#039;+entity_name+&#039;</a> - &#039;+field_name);
  $("#{{id}} a").click(function(){ return loadView(&#039;1&#039;, &#039;4&#039;, parent); });
</script>{{/xif}}', 'metatemplate' => '', 'id' => 23, 'date_modified' => '2019-04-18 16:28', 'modified_by' => 2)
, '24' => array('typename' => 'Field', 'viewtemplate' => '$($_value)[
  $_fields:rf(id==$_value)[
    $rf.name
  ]
]
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<select name="$_name" id="$_name" $($f.field_name_field)[onchange="saveOptionName(&#039;$_name&#039;,&#039;$f.field_name_field&#039;)"]>
<option  $($_value)[][selected=selected] value=""> -- $_lang.LBL_NONE -- </option> 
$_fields:rf{ 
  <option  $($rf.id==$_value)[selected=selected] value="$rf.id" parent="$rf.parent">$rf.name</option> 
}(name)
</select>

$($f.field_name_field)[<input type=hidden id="$f.field_name_field" name="$f.field_name_field"  value="$$f.field_name_field">
<script> saveOptionName(&#039;$_name&#039;,&#039;$f.field_name_field&#039;); </script>] 

$($f.fixed_entity)[
  <script> entityFieldChangeFor(&#039;$f.fixed_entity&#039;, &#039;$_name&#039;); </script> 
][
  <script> entityFieldChange(&#039;$f.entity_field&#039;,&#039;$_name&#039;); </script>
  <script> jQuery(document).ready(function () { jQuery(&#039;#$f.entity_field&#039;).change(function () { 
      entityFieldChange(&#039;$f.entity_field&#039;,&#039;$_name&#039;);
  }); });</script>
]', 'metatemplate' => '', 'id' => 25, 'date_modified' => '2020-04-28 02:33', 'modified_by' => 2, 'js_viewtemplate' => ' {{#xif " this.value != &#039;&#039; "}}<span id="{{id}}_{{f.id}}"></span><script> 
  if (metadata._fields["{{value}}"] != undefined){
    var field_name = metadata._fields["{{value}}"].name;
    $("#{{id}}_{{f.id}}").html(field_name);
  }
</script>{{/xif}}
<input type=hidden name="{{name}}" value="{{value}}" id="{{id}}">', 'js_edittemplate' => '{{#xif " this.f.fixed_entity != &#039;&#039; "}}<select id="_entity_select_{{id}}" style="display:none"></select>{{/xif}} 
<select name="{{name}}" id="{{id}}"></select> 

{{#xif " this.f.field_name_field != &#039;&#039; "}}<input type=hidden id="{{f.field_name_field}}" name="{{f.field_name_field}}"  value="">{{/xif}}
<script> entityFieldCreate("{{name}}", "{{id}}", "{{value}}", {entity_id_field: "", table_name_field: "", field_name_field: "{{f.field_name_field}}"}, "{{table}}", "{{record_id}}"); </script>
{{#xif " this.f.fixed_entity != &#039;&#039; "}}
<script> jQuery(&#039;#_entity_select_{{id}}&#039;).val(&#039;{{f.fixed_entity}}&#039;).change(); </script>
{{/xif}} 
{{#xif " this.f.entity_field != &#039;&#039; "}}
<script>
jQuery(&#039;#{{f.entity_field}}&#039;).change(function () { 
      entityFieldChange(&#039;{{f.entity_field}}&#039;,&#039;{{id}}&#039;);
  });
entityFieldChange(&#039;{{f.entity_field}}&#039;,&#039;{{id}}&#039;);
 </script>
{{/xif}} 
<script> jQuery(&#039;#{{id}}&#039;).val(&#039;{{value}}&#039;); </script>', 'search_ops' => '', 'search_ops_1' => '')
, '25' => array('typename' => 'FlexRelationshipDropdown', 'viewtemplate' => '<!--  -->
$[$_fields.related_table](=rtt_reltable) 
$$rtt_reltable(id==&#039;$_value&#039;)[  
$_entities:re(table==&#039;$rtt_reltable&#039;)[<a href="admin.php?view=4&entity=$re.id&id=$_value">]
$[$$related_field]
$_entities(table==&#039;$rtt_reltable&#039;)[</a>]
]
', 'edittemplate' => '<!-- -->
<select name="$_name" id="$_name" $($f.save_related_name)[onchange="saveOptionName(&#039;$_name&#039;, &#039;$f.save_related_name&#039;)"]>
<option  $(&#039;&#039;==$_value)[selected=selected] value=""> -- $_lang.LBL_NONE -- </option> 
$$f.related_table{ 
  <option  $($id==$_value)[selected=selected] value="$id">$[$$f.related_field]</option> 
}($f.order_field)
</select> 

$($f.save_related_name)[
<input type=hidden name="$f.save_related_name" id="$f.save_related_name">
<script>saveOptionName(&#039;$_name&#039;, &#039;$f.save_related_name&#039;);</script>
]', 'id' => 26)
, '26' => array('typename' => 'Color', 'viewtemplate' => '<div style="width:50px;height:24px;background-color: #$_value;border:1px solid black"></div>
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<input type=text size="10" name="$_name" value="$_value" id="$_name">
<script>
jQuery(&#039;#$_name&#039;).colpick({color:&#039;$_value&#039;, onSubmit: function (hsb, cal) { jQuery(&#039;#$_name&#039;).val(cal); jQuery(&#039;.colpick&#039;).hide(); }});
</script>', 'date_entered' => '2016-08-13 14:53', 'created_by' => '2', 'date_modified' => '2019-04-20 01:22', 'modified_by' => 2, 'id' => 27, 'js_viewtemplate' => '<div style="width:50px;height:24px;background-color: #{{value}};border:1px solid black; margin-top: 8px; margin-bottom: 8px;"></div>
<input type=hidden name="{{name}}" value="{{value}}" id="{{id}}">', 'js_edittemplate' => '<input type=text size="10" name="{{name}}" value="{{value}}" id="{{id}}">
<script>
jQuery(&#039;#{{id}}&#039;).colpick({color:&#039;{{value}}&#039;, onSubmit: function (hsb, cal) { jQuery(&#039;#{{id}}&#039;).val(cal); jQuery(&#039;.colpick&#039;).hide(); }});
</script>')
)
; ?><?php 

$_datatypes['1'] = array('typename' => 'TextArea', 'viewtemplate' => '<span style="white-space:pre-line">
$($_ismobile)[
  $($_get.view == 2)[
    $($_value)[...][]
  ][$_value]
][$_value]
</span>
<input type=hidden name="$_name" value="$_htmlentities(")[$_value]" id="$_name">', 'edittemplate' => '<textarea cols=50 rows=$($f.size)[$f.size][6] name="$_name" id="$_name">$_value</textarea>', 'metatemplate' => '<label class=view>Rows:</label> <input type=text size=5 name=size value="$r.size"><br>', 'id' => 2, 'date_modified' => '2020-11-05 13:17', 'modified_by' => 2, 'js_viewtemplate' => '{{#xif " this.view == &#039;2&#039; "}}...{{else}}<span style="white-space:pre-line;background-color:white;display:block;padding:8px;border-radius:5px;">{{value}}</span>{{/xif}} <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'js_edittemplate' => '<textarea cols="50" rows="{{f.size}}" name="{{name}}" id="{{id}}">{{value}}</textarea>', 'search_ops' => '', 'search_ops_1' => '2', 'search_ops_2' => '3', 'search_ops_3' => '4', 'search_ops_4' => '10', 'search_ops_5' => '11');

$_datatypes['6'] = array('typename' => 'RelationshipDropdown', 'viewtemplate' => '<!--  -->
$[$_fields.related_table](=rtt_reltable) 
$$rtt_reltable(id==&#039;$_value&#039;)[  
$_entities:re(table==&#039;$rtt_reltable&#039;)[<a href="admin.php?view=4&entity=$re.id&id=$_value">]
$[$$related_field]
$_entities(table==&#039;$rtt_reltable&#039;)[</a>]
]
<input type=hidden name="$_name" value="$_value" id="$_name">', 'js_viewtemplate' => '<span id="span_{{id}}_{{f.id}}"></span><script> jQuery(&#039;#span_{{id}}_{{f.id}}&#039;).html(renderRelationshipLinkTo(&#039;{{f.related_entity}}&#039;, &#039;4&#039;, &#039;{{value}}&#039;, &#039;{{table}}&#039;, &#039;{{record_id}}&#039;, &#039;{{f.name}}&#039;, &#039;{{f.related_field}}&#039;));</script> <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'edittemplate' => '<!-- -->
<select name="$_name" id="$_name" $($f.save_related_name)[onchange="saveOptionName(&#039;$_name&#039;, &#039;$f.save_related_name&#039;)"] style="max-width:90%">
<option  $(&#039;&#039;==$_value)[selected=selected] value=""> -- $_lang.LBL_NONE -- </option> 
$$f.related_table{ 
  <option  $($id==$_value)[selected=selected] value="$id">$[$$f.related_field]</option> 
}($f.order_field)
</select> 

$($f.save_related_name)[
<input type=hidden name="$f.save_related_name" id="$f.save_related_name">
<script>saveOptionName(&#039;$_name&#039;, &#039;$f.save_related_name&#039;);</script>
]', 'js_edittemplate' => '<select name="{{name}}" id="{{id}}" {{#xif " this.f.save_related_name != &#039;&#039; "}}onchange="saveOptionName(&#039;{{id}}&#039;, &#039;{{f.save_related_name}}{{id_suffix}}&#039;)"{{/xif}} style="max-width:90%"></select><script>createRelatedDropdown(&#039;{{id}}&#039;, &#039;{{value}}&#039;, &#039;{{f.related_table}}&#039;, &#039;{{f.related_field}}&#039;, &#039;{{f.order_field}}&#039;, &#039;{{f.filter_from}}&#039;, &#039;{{f.filter_to}}&#039;, &#039;{{f.filter}}&#039;);</script>

{{#xif " this.f.save_related_name != &#039;&#039; "}} <input type="hidden" name="{{f.save_related_name}}{{name_suffix}}" id="{{f.save_related_name}}{{id_suffix}}">{{/xif}}

{{#xif " this.f.createButton == &#039;1&#039; "}} CREATE {{/xif}}
{{#xif " this.f.editButton == &#039;1&#039; "}} EDIT {{/xif}}', 'metatemplate' => '<label class=view>Table:</label> <input type=text size=20 name=related_table value=$_e_related_table><br>        <label>Name Field:</label> <input type=text size=20 name=related_field value=$_e_related_field><br>       <label>Order Field:</label> <input type=text size=20 name=order_field value=$_e_order_field><br>', 'id' => 7, 'date_modified' => '2020-11-10 13:39', 'modified_by' => 2, 'search_ops' => '', 'search_ops_1' => '1', 'search_ops_2' => '7', 'search_ops_3' => '10', 'search_ops_4' => '11', 'search_ops_5' => '5', 'search_ops_label' => '');

$_datatypes['16'] = array('typename' => 'EmailAddress', 'viewtemplate' => '$_value
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<input type=email size="$size" name="$_name" value="$_value" id="$_name">', 'js_viewtemplate' => '{{value}} <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'js_edittemplate' => '<input type="email" size="{{f.size}}" name="{{name}}" id="{{id}}" value="{{value}}" autocomplete="nope">', 'metatemplate' => '', 'id' => 17, 'date_modified' => '2020-11-05 13:12', 'modified_by' => 2, 'search_ops' => '', 'search_ops_1' => '1', 'search_ops_2' => '2', 'search_ops_3' => '3', 'search_ops_4' => '4', 'search_ops_5' => '10', 'search_ops_6' => '11');

$_datatypes['17'] = array('typename' => 'Number', 'viewtemplate' => '$_value
<input type=hidden name="$_name" value="$_value" id="$_name">', 'edittemplate' => '<input type="number" size="$size" name="$_name" value="$_value" id="$_name" $($max)[max=$max] $($min)[min=$min]>', 'js_viewtemplate' => '<span id="{{id}}_number">{{value}}</span> <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}"><script>document.getElementById("{{id}}_number").innerHTML = formatNumber("{{value}}");</script>', 'js_edittemplate' => '<input type="number" size="{{f.size}}" name="{{name}}" id="{{id}}" value="{{value}}" {{#xif " this.f.max != &#039;&#039; "}}max={{f.max}}{{/xif}} {{#xif " this.f.min != &#039;&#039; "}}min={{f.min}}{{/xif}} >', 'metatemplate' => '<label class=view>Size:</label> <input type=text size=5 name=size value="$($r.size)[$r.size][10]"><br><label class=view>Min:</label> <input type=text size=5 name=min value="$r.min"><br><label class=view>Max:</label> <input type=text size=5 name=max value="$r.max"><br>', 'id' => 18, 'date_modified' => '2020-11-05 13:40', 'modified_by' => 2, 'search_ops' => '', 'search_ops_1' => '1', 'search_ops_2' => '8', 'search_ops_3' => '9', 'search_ops_4' => '10', 'search_ops_5' => '11', 'search_ops_6' => '6');

$_datatypes['19'] = array('typename' => 'DateTime', 'viewtemplate' => '$_eval[ formatDate(&#039;$_value&#039;, &#039;$_config.php_datetime_format&#039;) ]
<input type="hidden" value="$_eval[ formatDate(&#039;$_value&#039;, &#039;$_config.php_datetime_format&#039;) ]" id="$_name">
<input type="hidden" value="$_value" id="_date_$_name" name="$_name">', 'edittemplate' => '<input type="text" id="$_name" value="$_eval[ formatDate(&#039;$_value&#039;, &#039;$_config.php_datetime_format&#039;) ]">
<input type="hidden" value="$_value" id="$_eval[&#039;$_name&#039;.&#039;_date&#039;]" name="$_name">
<script> 
jQuery.datetimepicker.setLocale(&#039;$_config.js_datetime_locale&#039;);
jQuery(&#039;#$_name&#039;).datetimepicker({
  timepicker:true,
  format: &#039;$_config.php_datetime_format&#039;,
  step: 15,
  onChangeDateTime: function(dp,input) {
    var v = moment(input.val(), &#039;$_config.js_datetime_format&#039;).format(&#039;YYYY-MM-DD HH:mm&#039;);
    if (v == &#039;Invalid date&#039;) v = &#039;&#039;;
    jQuery(&#039;#$_eval[&#039;$_name&#039;.&#039;_date&#039;]&#039;).val(v);
  }
});</script>', 'metatemplate' => '', 'id' => 20, 'date_modified' => '2020-11-05 13:58', 'modified_by' => 2, 'js_viewtemplate' => '    <span id="{{id}}_{{f.id}}"></span>
    <input type="hidden" value="{{value}}" id="_date_{{id}}_{{f.id}}" name="{{name}}">
    <script>
      var vo = jQuery("#_date_{{id}}_{{f.id}}").val();
      var v = "";
      if (vo == ""){
        v = global.lang.LBL_EMPTY;
      } else {
        v = moment(vo, "YYYY-MM-DD HH:mm").format(global.config.js_datetime_format);
      }
      jQuery("#{{id}}_{{f.id}}").html(v);
    </script>', 'js_edittemplate' => '  <input type="text" id="{{id}}" value="" autocomplete="off" size=16>
  <input type="hidden" value="{{value}}" id="_date_{{id}}" name="{{name}}">
  <script>
  jQuery(document).ready(function(){ 
    jQuery.datetimepicker.setLocale(global.config.js_datetime_locale);
    jQuery("#{{id}}").datetimepicker({
      timepicker:true,
      format: global.config.php_datetime_format,
      step: 15,
      onChangeDateTime: function(dp,input) {
        var v = moment(input.val(), global.config.js_datetime_format).format("YYYY-MM-DD HH:mm");
        if (v == "Invalid date") v = "";
        jQuery("#_date_{{id}}").val(v);
      }
    });
    var v = moment(jQuery("#_date_{{id}}").val(), "YYYY-MM-DD HH:mm").format(global.config.js_datetime_format);
    if (v == "Invalid date") v = "";
    jQuery("#{{id}}").val(v);
  });
  </script>
', 'search_ops' => '', 'search_ops_1' => '6', 'search_ops_2' => '8', 'search_ops_3' => '9', 'search_ops_4' => '10', 'search_ops_5' => '11');

?><?php $_datatypes [] = array('typename' => 'TextArea', 'viewtemplate' => '<span style="white-space:pre-line">
$($_ismobile)[
  $($_get.view == 2)[
    $($_value)[...][]
  ][$_value]
][$_value]
</span>
<input type=hidden name="$_name" value="$_htmlentities(")[$_value]" id="$_name">', 'edittemplate' => '<textarea cols=50 rows=$($f.size)[$f.size][6] name="$_name" id="$_name">$_value</textarea>', 'metatemplate' => '<label class=view>Rows:</label> <input type=text size=5 name=size value="$r.size"><br>', 'id' => 2, 'date_modified' => '2021-01-21 03:48', 'modified_by' => 2, 'js_viewtemplate' => '<span class="textarea_view">{{value}}</span> <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}"><script>generateCopyButton("{{id}}")</script>', 'js_edittemplate' => '<textarea cols="50" rows="{{f.size}}" name="{{name}}" id="{{id}}">{{value}}</textarea> ', 'search_ops' => '', 'search_ops_1' => '2', 'search_ops_2' => '3', 'search_ops_3' => '4', 'search_ops_4' => '10', 'search_ops_5' => '11', 'search_ops_label' => '');
$_datatypes [] = array('typename' => 'FileUpload', 'viewtemplate' => '$_eval[ $_name . &#039;_size&#039;](=size_field)
$[$$size_field](=size) 
$($size > 0)[ 
<a href=admin.php?action=download&id=$record_id&field=$_name&table=$table> $_lang.LBL_DOWNLOAD </a>
]', 'edittemplate' => '$($_value)[ <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=hidden name=$_name id=$_name value> $_lang.BTN_DELETE&#039;)" value="$_lang.BTN_REMOVE"> <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=file name=$_name id=$_name>&#039;)" value="$_lang.BTN_MODIFY"> ][<input type=file name="$_name" id="$_name">]', 'metatemplate' => '', 'id' => 5, 'assigned_user_id' => '', 'date_modified' => '2021-01-14 14:15', 'modified_by' => 2, 'js_viewtemplate' => '{{#xif " this.value != &#039;&#039; "}}<a href="admin.php?action=download&id={{record_id}}&field={{f.name}}&table={{table}}"> {{lang.LBL_DOWNLOAD}} </a>{{/xif}}', 'js_edittemplate' => '{{#xif " this.value != &#039;&#039; "}} <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=hidden name={{name}} id={{id}} value> {{lang.BTN_DELETE}}&#039;)" value="{{lang.BTN_REMOVE}}"> <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=file name={{name}} id={{id}}>&#039;)" value="{{lang.BTN_MODIFY}}"><input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}" disabled="disabled"> {{else}} <input type=file name="{{name}}" id="{{id}}"> {{/xif}}
<script>
setTimeout(function(){ document.querySelectorAll(&#039;a.button.remove.{{f.name}}&#039;).forEach(function(e){ e.remove(); });});
</script>', 'search_ops' => '', 'search_ops_1' => '10', 'search_ops_2' => '11');
$_datatypes [] = array('typename' => 'ImageUpload', 'viewtemplate' => '$_eval[ &#039;$_name&#039; . &#039;_size&#039;](=size_field)
$[$$size_field](=size) 
$_eval[ &#039;$_name&#039; . &#039;_thumb&#039;](=thumb_field) 
$_entities(id==$_fields.parent)[$table](=_table)  

$($_value)[
<a href=admin.php?action=download&image=1&id=$_record_id&field=$_name&table=$_table> <img src=admin.php?action=download&image=1&id=$_record_id&field=$thumb_field&table=$_table height=50px> </a> 
]', 'edittemplate' => '$($_value)[ <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=hidden name=$_name id=$_name value> $_lang.BTN_DELETE&#039;)" value="$_lang.BTN_REMOVE"> <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=file name=$_name id=$_name>&#039;)" value="$_lang.BTN_MODIFY"> ][<input type=file name="$_name" id="$_name">]', 'metatemplate' => '', 'id' => 6, 'date_modified' => '2021-01-13 17:10', 'modified_by' => 2, 'js_viewtemplate' => '{{#xif " this.value != &#039;&#039; "}}<a href="admin.php?action=download&image=1&id={{record_id}}&field={{db_name}}&table={{table}}"> <img src="admin.php?action=download&image=1&id={{record_id}}&field={{db_name}}_thumb&table={{table}}" height=32px> </a>{{/xif}}', 'js_edittemplate' => '{{#xif " this.value != &#039;&#039; "}} <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=hidden name={{name}} id={{id}} value> {{lang.BTN_DELETE}}&#039;)" value="{{lang.BTN_REMOVE}}"> <input type="button" onclick="jQuery(this).parent().html(&#039;<input type=file name={{name}} id={{id}}>&#039;)" value="{{lang.BTN_MODIFY}}"> <img src="admin.php?action=download&image=1&id={{record_id}}&field={{db_name}}_thumb&table={{table}}" style="height:32px;vertical-align: middle;"><input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}" disabled="disabled"> {{else}} <input type=file name="{{name}}" id="{{id}}"> {{/xif}}
<script>
setTimeout(function(){ document.querySelectorAll(&#039;a.button.remove.{{f.name}}&#039;).forEach(function(e){ e.remove(); });});
</script>', 'search_ops' => '', 'search_ops_1' => '10', 'search_ops_2' => '11', 'search_ops_label' => '');
$_datatypes [] = array('typename' => 'Code', 'js_viewtemplate' => '{{#xif " this.view == &#039;2&#039; "}}...{{else}}<span style="white-space:pre-wrap;background-color:rgb(39, 40, 34);display:block;padding:8px;border-radius:5px;margin-bottom:5px;color:white;font-family:Monaco, Menlo, Ubuntu Mono, Consolas, source-code-pro, monospace">{{value}}</span>{{/xif}} <input type="hidden" name="{{name}}" id="{{id}}" value="{{value}}">', 'js_edittemplate' => '<div id="{{id}}" style="width:100%; max-height: 80%; height: 450px">{{value}}</div>
<textarea name="{{name}}" style="display: none;" />

<script src="include/ace/ace.js" type="text/javascript" charset="utf-8"></script>

<script>
    var editor_{{id}} = ace.edit("{{id}}");
    editor_{{id}}.setTheme("ace/theme/monokai");
    editor_{{id}}.setOption("wrap", true);
    editor_{{id}}.session.setMode("ace/mode/html");
    var textarea_{{id}} = $(&#039;textarea[name="{{name}}"]&#039;);
    editor_{{id}}.getSession().on("change", function () {
        textarea_{{id}}.val(editor_{{id}}.getSession().getValue());
    });
    setTimeout(function (){ 
      textarea_{{id}}.val(editor_{{id}}.getSession().getValue());
    }, 10);
</script> ', 'search_ops' => '', 'date_entered' => '2021-01-21 02:07', 'created_by' => 2, 'date_modified' => '2021-01-21 03:51', 'modified_by' => 2, 'search_ops_1' => '2', 'search_ops_2' => '10', 'search_ops_3' => '11', 'id' => 28, 'search_ops_label' => '');
?><?php

$_datatypes [] = array('typename' => 'Bean Function', 'id' => 29, 'date_modified' => '2021-06-03 13:11', 'modified_by' => 2, 'js_viewtemplate' => '<span id="_bean_function_{{id}}">{{value}}</span><input type=hidden value="{{value}}" id="{{id}}"><script> getBeanFunctionData(\'{{table}}\', \'{{f.name}}\', \'{{record_id}}\', \'{{id}}\', \'{{value}}\'); </script>', 'js_edittemplate' => '{{#xif " this.record_id != &#039;&#039; "}}<span id="_bean_function_{{id}}">{{value}}</span><input type=hidden value="{{value}}" id="{{id}}"><script> getBeanFunctionData(\'{{table}}\', \'{{f.name}}\', \'{{record_id}}\', \'{{id}}\', \'{{value}}\'); </script>{{/xif}}');

?>
