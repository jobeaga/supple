<?php $_relviews = array('1' => array('relview_name' => 'Lista de Checkboxes', 'relview_template' => '&lt;!-- Subpanel --&gt;
&lt;h2&gt; $_related_label &lt;/h2&gt;

$_entities(id == $_get.entity)[
  ${$table}(=_this_table) 
] 

$$_this_table(id == $_get.id)[
  $($_relationship_direction == 1)[
    ${$$_related_entity_a_field}(=_id_a)
  ][
    ${$$_related_entity_b_field}(=_id_b)
  ]
]

$_entities(id == $_related_entity)[
  ${$table}(=_related_table) 
] 

$_entities(id == $_related_entity)[$listview_order](=_listview_order)

&lt;table&gt;
&lt;tr&gt;
&lt;th&gt;&lt;/th&gt;
$_eval[1](=_first)
$_fields(parent==$_related_entity && view2==1){
  &lt;th align=left $($_first)[][class=&quot;extra&quot;]&gt;$_fields.label &lt;/th&gt;
  $_eval[0](=_first)
}(order) 
&lt;/tr&gt;

$_eval[1](=_n)

$_relationships_parameters(parent==$rr.id){&$name=$_parse[$value]}(=_params)

$$_related_table{  

$_eval[abs($_n-1)](=_n)
$[$id](=_record_id)
$_eval[1](=_first)

  $($_relationship_direction == 1)[
    ${$$_related_entity_b_field}(=_id_b)
  ][
    ${$$_related_entity_a_field}(=_id_a)
  ]

&lt;tr class=$($_n)[par][impar]&gt;
&lt;td&gt; &lt;input type=checkbox $$rel_table{$($id==$_get.id)[checked=checked]} onchange=&quot;if (this.checked) getUrlAjax(&#039;admin.php?action=save&table=$rel_table&#039;,&#039;&#039;,&#039;$_related_field_a=$_id_a&$_related_field_b=$_id_b$_params&#039;); else getUrlAjax(&#039;admin.php?action=delete&table=$rel_table&$_related_field_a=$_id_a&$_related_field_b=$_id_b&#039;,&#039;&#039;,&#039;&#039;)&quot;&gt; &lt;/td&gt;

$_fields:f(parent==$_related_entity && view2==1){
  &lt;td $($_first)[][class=&quot;extra&quot;]&gt;
    $_eval[&quot;$_related_table&quot; . &quot;.&quot; . &quot;$f.name&quot;](=_varname)
    $[$$_varname](=_value) 
    $[$f.name](=_name) 
    $_datatypes(id==$_fields.type)[
      $($_first)[ &lt;a href=admin.php?entity=$_related_entity&view=4&id=$_record_id&gt; ]
      $_parse[ $viewtemplate ]
      $($_first)[ &lt;/a&gt; ]
      $_eval[0](=_first) 
    ]
  &lt;/td&gt;
}(order) 


&lt;/tr&gt;

}($_entities.listview_order) 

&lt;/table&gt;
', 'id' => 2, 'relview_metatemplate' => '', 'relview_metascript' => 'jQuery(&#039;#label_a&#039;).show(); jQuery(&#039;#relationship_field_a&#039;).show(); jQuery(&#039;#relationship_field_b&#039;).show();', 'relview_name_es' => 'Lista de Checkboxes', 'relview_name_en' => 'CheckBox List', 'date_modified' => '2016-08-03 01:07', 'modified_by' => '2', 'assigned_user_id' => '')
, '2' => array('relview_name' => 'Dependientes Padre-Hijo', 'relview_template' => '&lt;!-- Subpanel --&gt;
$($_relationship_direction == 1)[
&lt;h2&gt; $_related_label &lt;/h2&gt;

$_entities(id == $_related_entity)[$table](=_related_table) 
$_entities(id == $_related_entity)[$view1](=_related_editable)
$_entities(id == $_related_entity)[$view3](=_related_deleteable)
$_entities(id == $_related_entity)[$view7](=_related_masscreate)
$_entities(id == $_related_entity)[$listview_order](=_listview_order)
$_entities(id == $_get.entity)[$table](=_parent_table)
$$_parent_table(id == $_get.id)[$$_relationships.entity_a_field](=_parent_value)


$_relationships_parameters(parent==$rr.id){&$name=$_parse[$value]}(=_rparams)

$($_related_editable)[&lt;input class=button type=button value=&quot;$_lang.BTN_ADD&quot; onclick=&quot;getUrl(&#039;admin.php?view=1&entity=$_related_entity&$_related_entity_b_field=$_parent_value$_rparams&_redirect_entity=$_get.entity&_redirect_view=$_get.view&_redirect_id=$_get.id&#039;)&quot;&gt;]

$($_related_masscreate)[&lt;input class=button type=button value=&quot;$_lang.BTN_MASS_CREATE&quot; onclick=&quot;getUrl(&#039;admin.php?view=7&entity=$_related_entity&$_related_entity_b_field=$_parent_value&_redirect_entity=$_get.entity&_redirect_view=$_get.view&_redirect_id=$_get.id&#039;)&quot;&gt;]

&lt;table style=&quot;margin-top:5px&quot;&gt;
&lt;tr&gt;
$_eval[1](=_first)
$_fields(parent==$_related_entity && view2==1){
  &lt;th align=left $($_first)[][class=&quot;extra&quot;]&gt;$_fields.label &lt;/th&gt;
  $_eval[0](=_first)
}(order) 
&lt;th width=&quot;5%&quot; class=&quot;control&quot;&gt;$($_related_deleteable)[Delete]&lt;/th&gt;
&lt;th width=&quot;5%&quot; class=&quot;control&quot;&gt;$($_related_editable)[Edit]&lt;/th&gt;
&lt;/tr&gt;

$_eval[1](=_n)
${$_related_table}(=_entitytable)

$$_related_table:e(${$_related_entity_b_field} == $_parent_value){

$_eval[abs($_n-1)](=_n)
$[$id](=_record_id) 
$_eval[1](=_first)

&lt;tr class=$($_n)[par][impar]&gt;
$_fields:f(parent==$_related_entity && view2==1){
  &lt;td $($_first)[][class=&quot;extra&quot;]&gt;
    $_eval[&#039;$_related_table&#039;.&#039;.&#039;.&#039;$f.name&#039;](=_fname)
    $[$$_fname](=_value) 
    $[$f.name](=_name) 
    $_datatypes(id==$_fields.type)[
      $($_first)[ &lt;a href=admin.php?entity=$_related_entity&view=4&id=$_record_id&_redirect_entity=$_get.entity&_redirect_view=$_get.view&_redirect_id=$_get.id&gt; ]
      $_parse[ $viewtemplate ]
      $($_first)[ $($_value)[][ $_lang.LBL_NO_NAME ] &lt;/a&gt; ]
      $_eval[0](=_first) 
    ]
  &lt;/td&gt;
}(order)

&lt;td align=center class=&quot;control&quot;&gt; $($_related_deleteable)[&lt;a href=&quot;admin.php?entity=$_related_entity&view=3&id=$_record_id&quot;&gt;delete&lt;/a&gt;] &lt;/td&gt;
&lt;td align=center class=&quot;control&quot;&gt;$($_related_editable)[&lt;a href=&quot;admin.php?entity=$_related_entity&view=1&id=$_record_id&quot;&gt;edit&lt;/a&gt;]&lt;/td&gt;

&lt;/tr&gt;
}($_listview_order)
&lt;/table&gt;
]', 'relview_metatemplate' => '', 'id' => 3, 'relview_metascript' => 'jQuery(&#039;#label_a&#039;).hide(); jQuery(&#039;#relationship_field_a&#039;).hide(); jQuery(&#039;#relationship_field_b&#039;).hide();', 'relview_name_es' => 'Dependientes Padre-Hijo', 'relview_name_en' => 'ParentChild Dependents', 'date_modified' => '2016-07-31 18:48', 'modified_by' => '2')
, '3' => array('relview_name' => 'Relaci&oacute;n Misma Entidad', 'relview_template' => '&lt;!-- Relationship $_related_label --&gt;
$($_relationship_direction == 1)[

&lt;!-- Subpanel --&gt;
&lt;h2&gt; $_related_label  &lt;/h2&gt;

$_entities(id == $_get.entity)[
  ${$table}(=_this_table) 
] 

$$_this_table(id == $_get.id)[
  ${$$_related_entity_a_field}(=_id_a)
]

$[$rr.rel_table](=_related_table)

$_relationships_parameters(parent==$rr.id){&$name=$_parse[$value]}(=_rparams)

&lt;!-- create new button --&gt;
&lt;input type=button class=button value=&quot;$_lang.BTN_ADD $_related_label&quot; onclick=&quot;getUrl(&#039;admin.php?entity=$rr.aux_entity&view=1&$_related_field_a=$_id_a$_rparams&#039;)&quot;&gt;

&lt;table&gt;
&lt;tr&gt;
$_eval[1](=_first)
$_fields(parent==$rr.aux_entity && view2==1){
  &lt;th align=left $($_first)[][class=&quot;extra&quot;]&gt;$_fields.label&lt;/th&gt;
  $_eval[0](=_first)
}(order) 
&lt;th class=&quot;control&quot;&gt; Delete &lt;/th&gt;&lt;/tr&gt;

$_eval[1](=_n)
$$_related_table($_related_field_a == &quot;$_id_a&quot; || $_related_field_b == &quot;$_id_a&quot;){

$_eval[abs($_n-1)](=_n)
$[$id](=_record_id)
$_eval[1](=_first)

&lt;tr class=$($_n)[par][impar]&gt;

$_fields:f(parent==$rr.aux_entity && view2==1){
  &lt;td $($_first)[][class=&quot;extra&quot;]&gt;
    $_eval[&quot;$_related_table&quot;.&quot;.$f.name&quot;](=_fname)
    $[$$_fname](=_value)
    $[$f.name](=_name) 
    $_datatypes(id==$_fields.type)[
      $($_first)[ &lt;a href=admin.php?entity=$rr.aux_entity&view=4&id=$_record_id&gt; ]
      $_parse[ $viewtemplate ]
      $($_first)[ &lt;/a&gt; ]
      $_eval[0](=_first) 
    ]
  &lt;/td&gt;
}(order) 

&lt;td align=center class=&quot;control&quot;&gt; &lt;a href=&quot;javascript:confirmar(&#039;admin.php?action=delete&id=$_record_id&table=$_related_table&redirect=admin.php?entity=$_get.entity%2526id=$_get.id%2526view=$_get.view&#039;,&#039;Desea borrar este registro?&#039;)&quot;&gt;delete&lt;/a&gt;&lt;/td&gt;
&lt;/tr&gt; 


}($_entities.listview_order) 

&lt;/table&gt;
] 
', 'relview_metatemplate' => '', 'id' => 4, 'relview_metascript' => 'jQuery(&#039;#label_a&#039;).show(); jQuery(&#039;#relationship_field_a&#039;).show(); jQuery(&#039;#relationship_field_b&#039;).show();', 'relview_name_es' => 'Relaci&oacute;n Misma Entidad', 'relview_name_en' => 'SameEntity Relationship', 'date_modified' => '2016-08-01 15:31', 'modified_by' => '2')
, '4' => array('relview_name' => 'None', 'relview_template' => '', 'relview_metatemplate' => '', 'id' => 5, 'relview_metascript' => 'jQuery(&#039;#label_a&#039;).show(); jQuery(&#039;#relationship_field_a&#039;).show(); jQuery(&#039;#relationship_field_b&#039;).show();', 'relview_name_es' => 'Ninguna', 'relview_name_en' => 'None')
)
; ?>