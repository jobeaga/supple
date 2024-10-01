<?php $_extend_views = array(/*'0' => array('name' => '', 'type' => '2', 'view' => '4', 'position' => '1', 'template' => '&lt;h2&gt; $_entities(id==9)[$name] &lt;/h2&gt;

&lt;input type=button class=button value=&quot;$_lang.LBL_ADD_FIELDS&quot; onclick=&quot;loadView(&#039;9&#039;, &#039;1&#039;, &#039;&#039;, &#039;&#039;, 0, false, undefined, {parent:&#039;$_get.id&#039;}); this_status[&#039;main_body&#039;].return = {record_id:&#039;$_get.id&#039;, entity_id:&#039;1&#039;};&quot;&gt;
&lt;table id=meta_data class=&quot;listview&quot;&gt;
  &lt;tr&gt;&lt;th&gt; $_fields(parent==9&&name==&#039;order&#039;)[$label] &lt;/th&gt;&lt;th align=left&gt; $_fields(parent==9&&name==&#039;name&#039;)[$label] &lt;/th&gt;&lt;th align=left class=extra&gt; $_fields(parent==9&&name==&#039;label&#039;)[$label] &lt;/th&gt;&lt;th class=extra&gt; $_fields(parent==9&&name==&#039;type&#039;)[$label] &lt;/th&gt;&lt;th class=&quot;desktop&quot; width=&quot;4%&quot;&gt; Req &lt;/th&gt;

$_viewdefs{
&lt;th class=&quot;desktop&quot; width=&quot;4%&quot; title=&quot;$name&quot;&gt;$_eval[onlyupper(&#039;$name&#039;)]&lt;/th&gt;
}

<th>$_lang.LBL_GROUP</th>

&lt;th class=control width=&quot;1%&quot;&gt; $_lang.LBL_DELETE &lt;/th&gt;&lt;/tr&gt;

$_fields:e(parent==$_get.id){
  &lt;tr class=&quot;record&quot; id=&quot;record$_fields.id&quot;&gt;
    &lt;td align=center&gt; ${order}(=_name) ${$order}(=_value) ${_fields}(=_entitytable) $_datatypes:d(id==21)[ $_parse[ $d.viewtemplate ] ]&lt;/td&gt;
    &lt;td&gt; &lt;a href=&quot;admin.php?entity=9&id=$_fields.id&view=1&quot; onclick=&quot;return loadView(&#039;9&#039;, &#039;1&#039;, &#039;$_fields.id&#039;);&quot; &gt;
             $_fields.name
           &lt;/a&gt;
    &lt;/td&gt;
    &lt;td class=extra&gt;$_fields.label&lt;/td&gt;
    &lt;td align=center class=extra&gt;$_datatypes(id==$_fields.type)[$typename]&lt;/td&gt;
    &lt;td align=center class=desktop&gt;  &lt;input type=checkbox onchange=&quot;if (this.checked) {v=1} else {v=0};save_do(&#039;required=&#039;+v, &#039;_fields&#039;, &#039;$_fields.id&#039;)&quot; value=1 $($_fields.required==1)[CHECKED]&gt;&lt;/td&gt;
$_viewdefs{
&lt;td class=&quot;desktop&quot;&gt; $[_fields.view$_viewdefs.id](=checked) &lt;input type=checkbox onchange=&quot;if (this.checked) {v=1} else {v=0};save_do(&#039;view$_viewdefs.id=&#039;+v, &#039;_fields&#039;, &#039;$_fields.id&#039;)&quot; value=1 $($$checked==1)[CHECKED]&gt;  &lt;/td&gt;
}

<td><select onchange="save_do(&#039;field_group=&#039;+this.value, &#039;_fields&#039;, &#039;$_fields.id&#039;)" style="width: 86px;"><option value=""></option>$_field_groups:g(entity_id==$_get.id){<option value="$g.id" $($g.id==$_fields.field_group)[selected=selected]>$g.label</option>}(order)</select></td>

    &lt;td align=center class=&quot;control post buttons&quot;&gt; &lt;a href=&quot;javascript:return delete_do(&#039;$_fields.id&#039;, &#039;_fields&#039;, &#039;2&#039;, &#039;9&#039;)&quot; onclick=&quot;return delete_do(&#039;$_fields.id&#039;, &#039;_fields&#039;, &#039;2&#039;, &#039;9&#039;)&quot; class=&quot;button delete&quot;&gt;$_lang.LNK_DELETE&lt;/a&gt;&lt;/td&gt;
  &lt;/tr&gt; 
}(order) 
&lt;/table&gt;', 'parent' => '1', 'id' => 1, 'name_es' => 'Panel de Campos', 'name_en' => 'Fields Panel', 'date_modified' => '2021-06-05 00:36', 'modified_by' => 1, 'type_value' => '', 'view_name' => '', 'position_value' => '', 'parent_name' => '')
,*/ '1' => array('name' => 'Totales', 'type' => '2', 'view' => '2', 'position' => '1', 'template' => '&lt;!-- TOTALES --&gt;
&lt;br&gt;&lt;table width=50%&gt;
&lt;tr&gt;
  &lt;th&gt;Passed&lt;/th&gt;
  &lt;td&gt;$__tests{$passed}(+passed)&lt;/td&gt;
&lt;/tr&gt;&lt;tr&gt;
  &lt;th&gt;Failed&lt;/th&gt;
  &lt;td&gt;$__tests{$failed}(+failed)&lt;/td&gt;
&lt;/tr&gt;&lt;tr&gt;
  &lt;th&gt;Error Count&lt;/th&gt;
  &lt;td&gt;$__tests{$errorCount}(+errorCount)&lt;/td&gt;
&lt;/tr&gt;&lt;tr&gt;
  &lt;th&gt;Time&lt;/th&gt;
  &lt;td&gt;$__tests{$time}(+time)&lt;/td&gt;
&lt;/tr&gt;
&lt;/table&gt;', 'parent' => '20', 'id' => 2, 'name_es' => 'Totales', 'name_en' => 'Totales')
, '2' => array('name' => '', 'type' => '2', 'view' => '1', 'position' => '1', 'template' => '&lt;div class=&quot;panel container&quot; style=&quot;display:none&quot; id=&quot;parameters&quot;&gt;
$_datatypes_parameters:f{
&lt;div class=&quot;field parameter&quot; parent=&quot;$f.parent&quot;&gt; 
$[$f.name](=_name) 
$($_get.id)[$_fields(id==$_get.id)[$$f.name]][$f.default_value](=_value)
&lt;label&gt;$f.label&lt;/label&gt;&lt;span class=&quot;group&quot;&gt;$_datatypes(id==$type)[$_parse{$edittemplate}]&lt;/span&gt;
&lt;/div&gt;
}(parent)
&lt;/div&gt;
&lt;script&gt; 
jQuery(&#039;#type&#039;).change(populateDataTypeParameters);
populateDataTypeParameters(); 
&lt;/script&gt;', 'parent' => '9', 'id' => 3, 'name_es' => 'Par&aacute;metros de Tipos', 'name_en' => 'Type Parameters', 'date_modified' => '2018-07-29 23:12', 'modified_by' => 2)
, '3' => array('name' => 'Parameters Parameters', 'type' => '2', 'view' => '1', 'position' => '1', 'template' => '&lt;div class=&quot;panel container&quot; style=&quot;display:none&quot; id=&quot;parameters&quot;&gt;
$_datatypes_parameters:f{
&lt;div class=&quot;view row parameter&quot; parent=&quot;$f.parent&quot;&gt; 
&lt;div class=&quot;view three columns label&quot;&gt;$f.label&lt;/div&gt;
&lt;div class=&quot;view nine columns&quot;&gt;
$[$f.name](=_name) 
$($_get.id)[$_datatypes_parameters(id==$_get.id)[$$f.name]][$f.default_value](=_value)
$_datatypes(id==$type)[
  $_parse{$edittemplate}
]
&lt;/div&gt;
&lt;/div&gt;
}(parent)
&lt;/div&gt;
&lt;script&gt; 
jQuery(&#039;#type&#039;).change(populateDataTypeParameters);
populateDataTypeParameters(); 
&lt;/script&gt;', 'parent' => '25', 'id' => 4, 'name_es' => 'Par&aacute;metros de Par&aacute;metros', 'name_en' => 'Parameters Parameters')
, '4' => array('name' => 'Table Data', 'type' => '2', 'view' => '4', 'position' => '1', 'template' => '&lt;h2&gt;Raw Table Data&lt;/h2&gt;
First 50 rows
$__tables(id == $_get.id)[$name](=_table)

&lt;div style=&quot;width:100%; height:80%;overflow:scroll;&quot;&gt;
&lt;table&gt;
&lt;tr&gt;
$__fields(table == $_table){
&lt;th width=&quot;5%&quot;&gt;$name&lt;/th&gt;
}
&lt;/tr&gt;
$$_table{
&lt;tr&gt;
$__fields(table == $_table){
$_eval[&#039;$__fields.table&#039; . &#039;.&#039; . &#039;$__fields.name&#039;](=_fieldname)
&lt;td&gt;$$_fieldname&lt;/td&gt;
}
&lt;/tr&gt;
}(0,50)
&lt;/table&gt;
&lt;/div&gt;', 'parent' => '26', 'id' => 5, 'name_es' => 'Informaci&oacute;n de la Tabla', 'name_en' => 'Table Data')
, '5' => array('name' => 'Traducir', 'type' => '2', 'view' => '4', 'position' => '1', 'template' => '&lt;div class=&quot;panel container&quot;&gt;
  &lt;div class=&quot;impar view row&quot;&gt;
    &lt;div class=&quot;view three columns label&quot;&gt;$_lang.LBL_TRANSLATE&lt;/div&gt;
    &lt;div class=&quot;view nine columns&quot;&gt;&lt;select name=&quot;trans_entity&quot; id=&quot;trans_entity&quot;&gt;
      $_entities{&lt;option value=&quot;$id&quot; $($_get.trans_entity==$id)[selected=&quot;selected&quot;]&gt;$name&lt;/option&gt;}
    &lt;/select&gt; &lt;input type=&quot;button&quot; value=&quot;$_lang.LBL_TRANSLATE&quot; onclick=&quot;getUrl(&#039;admin.php?entity=$_get.entity&view=4&id=$_get.id&trans_entity=&#039; + jQuery(&#039;#trans_entity&#039;).val());&quot;&gt;&lt;/div&gt;
  &lt;/div&gt;
  $($_get.trans_entity)[
    $_languages(id==$_get.id)[$code](=_code)
    $_entities(id==$_get.trans_entity)[$table](=_entitytable)
    &lt;form action=&quot;admin.php?action=massive&table=$_entitytable&redirect=admin.php?entity=$_get.entity%26view=4%26id=$_get.id&quot; method=&quot;POST&quot; accept-charset=&quot;ISO-8859-1&quot;&gt;
    $_fields:f(parent==$_get.trans_entity && multilanguage==1){
      $_eval[&#039;$f.name&#039;.&#039;_&#039;.&#039;$_code&#039;.&#039;[]&#039;](=_name)
      $_eval[&#039;$f.name&#039;.&#039;_&#039;.&#039;$_code&#039;](=_fname)
      $[$_fields.getEditTemplate(1)](=_template)
      $$_entitytable:e{
        &lt;div class=&quot;impar view row&quot;&gt;&lt;div class=&quot;view three columns label&quot;&gt;
          $f.label
        &lt;/div&gt;&lt;div class=&quot;view nine columns&quot;&gt;
          $[$$_fname](=_value)
          $[40](=size)
          $_parse[$_template]
          &lt;input type=&quot;hidden&quot; name=&quot;id[]&quot; value=&quot;$e.id&quot;&gt;
        &lt;/div&gt;&lt;/div&gt;
      }
    }
    &lt;div class=&quot;impar view row&quot;&gt;&lt;div class=&quot;view three columns label&quot;&gt;&lt;/div&gt;
    &lt;div class=&quot;view nine columns&quot;&gt;&lt;input type=&quot;submit&quot; value=&quot;$_lang.BTN_SAVE&quot;&gt;&lt;/div&gt;&lt;/div&gt;
    &lt;/form&gt;
  ]
&lt;/div&gt;
  ', 'parent' => '24', 'name_es' => 'Traducir', 'name_en' => 'Translate', 'id' => 6)
, '6' => array('name' => 'Matriz de Permisos', 'name_es' => 'Matriz de Permisos', 'name_en' => 'Permission Matrix', 'type' => '2', 'view' => '4', 'position' => '1', 'template' => '&lt;br&gt;&lt;h2&gt;$name&lt;/h2&gt;
&lt;table&gt;
&lt;tr&gt;&lt;th&gt;Entity&lt;/th&gt; $__actions(domain == &#039;table&#039; && needACL){&lt;th style=&quot;text-align:center&quot;&gt;$actionName&lt;/th&gt;}&lt;/tr&gt;

$_entities:e{
&lt;tr&gt;&lt;td&gt;$e.name&lt;/td&gt; 
    $__actions:a(domain == &#039;table&#039; && needACL){&lt;td&gt;
        $_acl(acl_role==$_get.id && acl_action==$a.id && acl_entity==$e.id)[$acl_value](=_value)
        &lt;select&gt;
            &lt;option value=&quot;0&quot; $($_value==0)[selected=selected]&gt;$_lang.LBL_PERMISSION_NONE&lt;/option&gt;
            &lt;option value=&quot;1&quot; $($_value==1)[selected=selected]&gt;$_lang.LBL_PERMISSION_ASSIGN&lt;/option&gt;
            &lt;option value=&quot;2&quot; $($_value==2)[selected=selected]&gt;$_lang.LBL_PERMISSION_TEAM&lt;/option&gt;
            &lt;option value=&quot;3&quot; $($_value==3)[selected=selected]&gt;$_lang.LBL_PERMISSION_ALL&lt;/option&gt;
        &lt;/select&gt;
    &lt;/td&gt;} &lt;/tr&gt;
}(name)
&lt;/table&gt;', 'parent' => '13', 'assigned_user_id' => '2', 'date_entered' => '2016-08-03 01:18', 'created_by' => '2', 'date_modified' => '2016-08-05 01:57', 'modified_by' => '2', 'id' => 7)
, '7' => array('name' => '', 'name_es' => 'Editview JS', 'name_en' => 'Editview JS', 'type' => '1', 'view' => '1', 'position' => '1', 'template' => 'init_columns_editview();', 'parent' => '32', 'date_entered' => '2020-04-22 20:47', 'created_by' => 1, 'date_modified' => '2020-04-22 20:47', 'modified_by' => 1, 'id' => 8)
)
; ?><?php $_extend_views[] = array('name' => '', 'name_es' => 'Editview JS', 'name_en' => 'Editview JS', 'type' => '1', 'view' => '1', 'position' => '1', 'template' => 'init_filters_editview();', 'parent' => '33', 'date_entered' => '2020-04-26 18:34', 'created_by' => 1, 'date_modified' => '2020-04-26 18:34', 'modified_by' => 1, 'id' => 9); ?><?php $_extend_views[] = array('name' => '', 'name_es' => 'Detailview JS', 'name_en' => 'Detailview JS', 'type' => '1', 'view' => '4', 'position' => '1', 'template' => 'translateChain(&#039;relationships_chain&#039;);', 'parent' => '32', 'date_entered' => '2020-04-27 22:48', 'created_by' => 1, 'date_modified' => '2020-04-27 22:48', 'modified_by' => 1, 'id' => 10); ?><?php $_extend_views[] = array('name' => '', 'name_es' => 'Detailview JS', 'name_en' => 'Detailview JS', 'type' => '1', 'view' => '4', 'position' => '1', 'template' => 'translateChain(&#039;relationships_chain&#039;);', 'parent' => '33', 'date_entered' => '2020-04-27 22:50', 'created_by' => 1, 'date_modified' => '2020-04-27 22:50', 'modified_by' => 1, 'id' => 11); ?><?php $_extend_views[] = array('name' => '', 'type' => '0', 'view' => '1', 'position' => '1', 'template' => '&lt;script&gt; 
jQuery(&#039;#type&#039;).change(populateDataTypeParameters);
populateDataTypeParameters(); 
&lt;/script&gt;', 'parent' => '9', 'id' => 3, 'name_es' => 'Par&aacute;metros de Tipos', 'name_en' => 'Type Parameters', 'date_modified' => '2021-01-21 03:14', 'modified_by' => 1, 'view_name' => '', 'parent_name' => ''); ?><?php $_extend_views[] = array('name' => '', 'name_es' => 'Panel de Campos', 'name_en' => 'Fields Panel', 'type' => '0', 'view' => '4', 'position' => '1', 'template' => '&lt;script&gt;
    function renderFieldsPanel(entity_id){
        var html = &#039;&#039;;
        const fields_entity_id = &#039;9&#039;;
        const fields_relationship_id = &#039;8&#039;;
        const view_count = Object.keys(metadata._viewdefs).length;
        const field_count = Math.min(10, view_count+5);
        
        // TITLE:
        html += &#039;&lt;h2&gt;&#039;+metadata._entities[fields_entity_id].name+&#039;&lt;/h2&gt;&#039;;
        html += &#039;&lt;input type=button class=button value=&quot;&#039;+global.lang.LBL_ADD_FIELDS+&#039;&quot; onclick=&quot;&#039; + &quot;loadView(&#039;&quot;+fields_entity_id+&quot;&#039;, &#039;1&#039;, &#039;&#039;, &#039;&#039;, 0, false, undefined, {parent:&#039;&quot;+ entity_id + &quot;&#039;}); this_status[&#039;main_body&#039;].return = {record_id:&#039;&quot;+ entity_id +&quot;&#039;, entity_id:&#039;1&#039;}; &quot;+ &#039;&quot;&gt;&#039;;
        
        html += &#039;&lt;span id=&quot;relationship_&#039;+fields_relationship_id+&#039;&quot; class=&quot;relationship_panel listview&quot;&gt;&#039;;
        html += &#039;&lt;form id=&quot;meta_data&quot; class=&quot;listview form&quot; entity_id=&quot;&#039;+fields_entity_id+&#039;&quot; record_id=&quot;undefined&quot; action=&quot;void.php&quot; autocomplete=&quot;off&quot;&gt;&#039;;
        
        // HEADER START
        html += &#039;&lt;div class=&quot;head fieldcount&#039;+field_count+&#039;&quot;&gt;&#039;; 
        html += &#039;&lt;div class=&quot;label prev buttons&quot;&gt;Cantidad &lt;/div&gt;&#039;;
        html += &#039;&lt;div class=&quot;label label0&quot;&gt; &#039; + getFieldProp(fields_entity_id, &#039;order&#039;, &#039;label&#039;) + &#039; &lt;/div&gt;&#039;;
        html += &#039;&lt;div class=&quot;label label1&quot;&gt; &#039; + getFieldProp(fields_entity_id, &#039;name&#039;, &#039;label&#039;) + &#039; &lt;/div&gt;&#039;;
        html += &#039;&lt;div class=&quot;label label2&quot;&gt; &#039; + getFieldProp(fields_entity_id, &#039;label&#039;, &#039;label&#039;) + &#039; &lt;/div&gt;&#039;;
        html += &#039;&lt;div class=&quot;label label3&quot;&gt; &#039; + getFieldProp(fields_entity_id, &#039;type&#039;, &#039;label&#039;) + &#039; &lt;/div&gt;&#039;;
        html += &#039;&lt;div class=&quot;label label4 checkboxcolumn&quot;&gt; Req &lt;/div&gt;&#039;;
        var field_n = 5;
        for (const v in metadata._viewdefs){
            var vd = metadata._viewdefs[v];
            html += &#039;&lt;div class=&quot;label checkboxcolumn&quot; title=&quot;&#039; + vd.name + &#039;&quot;&gt;&#039;;
            html += onlyupper(vd.name);
            html += &#039;&lt;/div&gt;&#039;;
            field_n++;
        }
        html += &#039;&lt;div class=&quot;label label&#039; + field_n + &#039;&quot;&gt; &#039;+global.lang.LBL_GROUP+&#039; &lt;/div&gt;&#039;;
        html += &#039;&lt;div class=&quot;label post buttons&quot;&gt;Cantidad &lt;/div&gt;&#039;;
        html += &#039;&lt;/div&gt;&#039;; 
        // HEADER END
        
        // BODY: FIELDS in order
        getMetadata(&#039;_fields&#039;, 0, 1000, &#039;order&#039;, function (fields){
            
            var field_label;
            
            for (const e of fields){
                var field_id = e.id;
                field_label = &#039;&#039;;
                field_n = 0;
                
                html += &#039;&lt;div class=&quot;record fieldcount&#039;+field_count+&#039;&quot;  id=&quot;record&#039;+field_id+&#039;&quot; record_id=&quot;&#039;+field_id+&#039;&quot; ondrop=&quot;orderDragStop(event, this, &#039;+ &quot;&#039;&quot; + field_id + &quot;&#039;&quot; + &#039;)&quot;  ondragover=&quot;orderOnDrag(event, this, &#039;+ &quot;&#039;&quot; + field_id + &quot;&#039;&quot; + &#039;)&quot;&gt;&#039;;
                html += &#039;&lt;div class=&quot;prev buttons&quot;&gt;&lt;/div&gt;&#039;;
                
                html += renderFieldCell(renderBasicSingleField(&#039;162&#039;, 0, e.order, e.id, e.id, e, &#039;order&#039;), &#039;order&#039;, field_n);
                field_n++;
                
                html += renderFieldCell(&#039;&lt;a href=&quot;admin.php?entity=&#039;+fields_entity_id+&#039;&amp;id=&#039;+e.id+&#039;&amp;view=1&quot; onclick=&quot;&#039; + &quot;return loadView(&#039;&quot;+fields_entity_id+&quot;&#039;, &#039;1&#039;, &#039;&quot;+e.id+&quot;&#039;);&quot; + &#039;&quot; &gt;&#039; + e.name + &#039;&lt;/a&gt;&#039;, &#039;name&#039;, field_n);
                field_n++;
  
                html += renderFieldCell(e.label, &#039;label&#039;, field_n);
                field_n++;
                
                html += renderFieldCell(e.type_typename, &#039;type&#039;, field_n);
                field_n++;
                
                var req_content = &#039;&lt;input type=checkbox onchange=&quot;if (this.checked) {v=1} else {v=0};save_do(&#039; + &quot;required=&#039;+v, &#039;_fields&#039;, &#039;&quot;+ e.id+ &quot;&#039;)&quot; + &#039;&quot; value=1&#039;;
                if (e.required == 1) req_content += &#039; CHECKED&#039;;
                req_content += &#039;&gt;&#039;;
                html +=  renderFieldCell(req_content, &#039;required&#039;, field_n, &#039;checkboxcolumn&#039;);
                field_n++;
                
                // views:
                for (const v in metadata._viewdefs){
                    var vd = metadata._viewdefs[v];
                    var checked = e[&#039;view&#039; + vd.id];
                    
                    html += &#039;&lt;div class=&quot;field checkboxcolumn&quot;&gt;&lt;label&gt;&#039;+vd.name+&#039;: &lt;/label&gt;&lt;span class=&quot;group&quot; alt=&quot;&#039;+vd.name+&#039;&quot; title=&quot;&#039;+vd.name+&#039;&quot; onclick=&quot;labelClick(this)&quot; onmouseout=&quot;labelMoveOut(this)&quot;&gt;&#039;;
                    
                    html += &#039;&lt;input type=checkbox onchange=&quot;if (this.checked) {v=1} else {v=0};&#039;+&quot; save_do(&#039;view&quot; + vd.id +&quot;=&#039;+v, &#039;_fields&#039;, &#039;&quot;+e.id+&quot;&#039;) &quot;+&#039;&quot; value=1&#039;;
                    if (checked == 1) html += &#039; CHECKED&#039;;
                    html += &#039;&gt; &lt;/span&gt;&lt;/div&gt;&#039;;
                    field_n++;
                }
                
                // field_group
                var group_content = &#039;&lt;select onchange=&quot;&#039; + &quot;save_do(&#039;field_group=&#039;+this.value, &#039;_fields&#039;, &#039;&quot; + e.id + &quot;&#039;)&quot; + &#039;&quot; style=&quot;width: 86px;&quot;&gt;&lt;option value=&quot;&quot;&gt;&lt;/option&gt;&#039;;
                var fgs = sortData(searchInArray(metadata._field_groups, &#039;entity_id&#039;, entity_id), &#039;order&#039;, false)
                for (const g of fgs){
                    group_content += &#039;&lt;option value=&quot;&#039;+g.id+&#039;&quot; &#039;;
                    if (g.id == e.field_group) group_content += &#039;selected=selected&#039;;
                    group_content += &#039;&gt;&#039; + g.label + &#039;&lt;/option&gt;&#039;;
                }
                group_content += &#039;&lt;/select&gt;&#039;;
                html += renderFieldCell(group_content, &#039;field_group&#039;, field_n);
                field_n++;
                
                
                // edit/delete
                html += &#039;&lt;div class=&quot;post buttons&quot;&gt; &lt;a href=&quot;&#039; + &quot;javascript:return delete_do(&#039;&quot;+e.id+&quot;&#039;, &#039;_fields&#039;, &#039;2&#039;, &#039;9&#039;)&quot;+&#039;&quot; onclick=&quot;&#039; + &quot;return delete_do(&#039;&quot;+e.id+&quot;&#039;, &#039;_fields&#039;, &#039;2&#039;, &#039;9&#039;)&quot; + &#039;&quot; class=&quot;button delete&quot;&gt;&#039; + global.lang.LNK_DELETE + &#039;&lt;/a&gt;&lt;/div&gt;&#039;;
                
                html += &#039;&lt;/div&gt;&#039;;

            }
            
        }, &#039;parent=&#039;+entity_id)
        
        html += &#039;&lt;/form&gt;&#039;;
        // APPEND:
        var e = document.querySelector(&#039;.subpanels&#039;);
        var n = document.createElement(&#039;SPAN&#039;); 
        n.id = &#039;extend_views12&#039;;
        n.className = &#039;extend_view&#039;;
        n.innerHTML = html;
        e.parentNode.insertBefore(n, e);
        
    }
    
    function getFieldProp(parent, name, prop){
        return Object.values(searchInArray(searchInArray(metadata._fields, &#039;parent&#039;, parent), &#039;name&#039;, name))[0][prop];
    }
    
    function onlyupper(string){
        return string.replace(/[a-z]+/, &#039;&#039;).replace(/[a-z]+/, &#039;&#039;).replace(/[a-z]+/, &#039;&#039;);
    }
    
    function renderFieldCell(content, field_name, field_n, css_class){
        var html = &#039;&#039;;
        var field_label = getFieldProp(&#039;9&#039;, field_name, &#039;label&#039;);
        var css_extra_class = &#039;&#039;;
        if (css_class != undefined){
            css_extra_class = &#039; &#039; + css_class;
        }
        html += &#039;&lt;div class=&quot;field field&#039;+field_n+css_extra_class+&#039;&quot;&gt;&lt;label&gt;&#039;+field_label+&#039;: &lt;/label&gt;&lt;span class=&quot;group&quot; alt=&quot;&#039;+field_label+&#039;&quot; title=&quot;&#039;+field_label+&#039;&quot; onclick=&quot;labelClick(this)&quot; onmouseout=&quot;labelMoveOut(this)&quot;&gt;&#039;;
        html += content;
        html += &#039;&lt;/span&gt;&lt;/div&gt;&#039;;
        return html;
    }
    
    var e_id = document.querySelector(&#039;.record&#039;).attributes.record_id.value;
    renderFieldsPanel(e_id);
&lt;/script&gt;', 'parent' => '1', 'date_entered' => '2024-02-02 10:12', 'created_by' => 1, 'date_modified' => '2024-02-02 13:15', 'modified_by' => 1, 'type_value' => '', 'view_name' => '', 'position_value' => '', 'parent_name' => '', 'id' => 12); ?>