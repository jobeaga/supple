<?php $_entities = array (
  0 => 
  array (
    'name' => 'Usuarios',
    'table' => 'users',
    'template' => '',
    'id' => 3,
    'adminonly' => '1',
    'show' => '1',
    'order' => 15,
    'superentity' => '',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'listview_order' => 'user',
    'view4' => '1',
    'view7' => '0',
    'name_es' => 'Usuarios',
    'name_en' => 'Users',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  1 => 
  array (
    'name' => 'Entidades',
    'table' => '_entities',
    'adminonly' => '1',
    'template' => '',
    'id' => 1,
    'show' => '1',
    'order' => '2',
    'superentity' => '',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'listview_order' => 'order reverse',
    'view4' => '1',
    'view7' => '0',
    'name_es' => 'Entidades',
    'name_en' => 'Entities',
    'date_modified' => '2016-08-13 15:10',
    'modified_by' => '2',
  ),
  2 => 
  array (
    'name' => 'Mapeo de BD',
    'table' => '__mappings',
    'superentity' => '',
    'order' => '3',
    'adminonly' => '1',
    'show' => '1',
    'template' => '&lt;form method=&quot;post&quot; action=&quot;admin.php?action=mappings&redirect=admin.php?entity=$entity&quot;&gt;

&lt;table id=news&gt;
&lt;tr&gt;
&lt;th&gt;Table: &lt;/th&gt;
&lt;th&gt;BaseName: &lt;/th&gt;
&lt;th&gt;Type: &lt;/th&gt;
&lt;th&gt;Host: &lt;/th&gt;
&lt;th&gt;User: &lt;/th&gt;
&lt;th&gt;Pass: &lt;/th&gt;
&lt;/tr&gt;

$__mappings{ 

&lt;tr&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbtable[] value=&quot;$dbtable&quot; style=width:100px&gt;&lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbname[] value=&quot;$dbname&quot; style=width:100px&gt;&lt;/td&gt;
&lt;td&gt;&lt;select name=dbtype[] style=width:150px&gt; &lt;option value=&quot;&quot;&gt;--Erase--&lt;/option&gt; $__connectiontypes{&lt;option value=&quot;$value&quot;$(&#039;$value&#039;==&#039;$dbtype&#039;)[ SELECTED]&gt;$name&lt;/option&gt;} &lt;/select&gt;&lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbhost[] value=&quot;$dbhost&quot; style=width:120px&gt; &lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbuser[] value=&quot;$dbuser&quot; style=width:100px&gt; &lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbpass[] value=&quot;$dbpass&quot; style=width:100px&gt; &lt;/td&gt;
&lt;/tr&gt;
	
}
&lt;/table&gt;

&lt;table id=new style=&quot;display:none&quot;&gt;
&lt;tr&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbtable[] value=&quot;&quot; style=width:100px&gt;&lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbname[] value=&quot;&quot; style=width:100px&gt;&lt;/td&gt;
&lt;td&gt;&lt;select name=dbtype[] style=width:150px&gt; &lt;option value=&quot;&quot;&gt;--Erase--&lt;/option&gt;
$__connectiontypes{&lt;option value=&quot;$value&quot;&gt;$name&lt;/option&gt;}
&lt;/select&gt;&lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbhost[] value=&quot;&quot; style=width:120px&gt;&lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbuser[] value=&quot;&quot; style=width:100px&gt;&lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbpass[] value=&quot;&quot; style=width:100px&gt;&lt;/td&gt;
&lt;/tr&gt;
&lt;/table&gt;

&lt;p&gt;
&lt;input type=&quot;button&quot; class=button onclick=&quot;cloneContent(&#039;new&#039;, &#039;news&#039;)&quot; value=&quot;Add New&quot;&gt;
&lt;input type=submit class=&quot;button-primary button&quot; value=&quot;Set mappings&quot;&gt;
&lt;/p&gt;
&lt;/form&gt;

&lt;h2&gt; Migrate Table &lt;/h2&gt;
&lt;form method=&quot;post&quot; action=&quot;admin.php?action=migrate&redirect=admin.php?entity=$entity&quot;&gt;
&lt;table&gt;
&lt;tr&gt;
&lt;th&gt;Table: &lt;/th&gt;
&lt;th&gt;BaseName: &lt;/th&gt;
&lt;th&gt;Type: &lt;/th&gt;
&lt;th&gt;Host: &lt;/th&gt;
&lt;th&gt;User: &lt;/th&gt;
&lt;th&gt;Pass: &lt;/th&gt;
&lt;/tr&gt;
&lt;tr&gt;
&lt;td&gt;&lt;select name=dbtable style=width:100px&gt; $__tables{&lt;option value=&quot;$name&quot;&gt; $name &lt;/option&gt;} &lt;/select&gt;&lt;/td&gt;

&lt;td&gt;&lt;input type=&quot;text&quot; name=dbname value=&quot;&quot; style=width:100px&gt;&lt;/td&gt;
&lt;td&gt;&lt;select name=dbtype  style=width:150px&gt;
		$__connectiontypes{&lt;option value=&quot;$value&quot;&gt;$name&lt;/option&gt;}
	&lt;/select&gt;&lt;/td&gt;
	
	
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbhost value=&quot;&quot;  style=width:120px&gt; &lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbuser value=&quot;&quot;  style=width:100px&gt;  &lt;/td&gt;
&lt;td&gt;&lt;input type=&quot;text&quot; name=dbpass value=&quot;&quot;  style=width:100px&gt;&lt;/td&gt;
&lt;/tr&gt;&lt;/table&gt;
&lt;input type=submit class=&quot;button button-primary&quot; value=&quot;Migrate Table&quot; onclick=&quot;show_progress()&quot;&gt;


&lt;/p&gt;
&lt;/form&gt;',
    'id' => 4,
    'view1' => '0',
    'view2' => '0',
    'view3' => '0',
    'view4' => '0',
    'listview_order' => 'id',
    'view7' => '0',
    'name_es' => 'Mapeo de BD',
    'name_en' => 'DBMappings',
    'date_modified' => '2019-05-20 02:57',
    'modified_by' => 1,
  ),
  4 => 
  array (
    'name' => 'Inicio',
    'table' => 'home',
    'superentity' => '',
    'order' => '1',
    'adminonly' => '0',
    'show' => '1',
    'template' => '

<span id="dashboard_control_buttons">
  <a id="dashboard_add_button" href="admin.php?entity=36&amp;view=1&amp;1=0" onclick="return nav_viewbutton(&#039;1&#039;, &#039;36&#039;)" class="button button-primary"><svg width="20px" height="20px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1H6V6L1 6V10H6V15H10V10H15V6L10 6V1Z" fill="#FFFFFF"/></svg></a>
  <a id="dashboard_edit_button" href="admin.php?entity=36&amp;view=2" onclick="return loadView(&#039;36&#039;, &#039;2&#039;)" class="button button-primary"><svg width="20px" height="20px" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.29289 3.70711L1 11V15H5L12.2929 7.70711L8.29289 3.70711Z" fill="#FFFFFF"/><path d="M9.70711 2.29289L13.7071 6.29289L15.1716 4.82843C15.702 4.29799 16 3.57857 16 2.82843C16 1.26633 14.7337 0 13.1716 0C12.4214 0 11.702 0.297995 11.1716 0.828428L9.70711 2.29289Z" fill="#FFFFFF"/></svg></a>
</span>

<div id="dashboard_tabs"></div>
<span id="dashboard_buttons"></span>
<div id="dashboard_widgets"></div>
<div id="dashboard_panels"></div>

<script> dashboardInit(); </script>


',
    'id' => 6,
    'view1' => '0',
    'view2' => '0',
    'view3' => '0',
    'view4' => '0',
    'listview_order' => '',
    'view7' => '0',
    'name_es' => 'Inicio',
    'name_en' => 'Home',
  ),
  5 => 
  array (
    'name' => 'Tipos de Datos',
    'table' => '_datatypes',
    'order' => 9,
    'adminonly' => '1',
    'show' => '1',
    'template' => '',
    'id' => 7,
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'listview_order' => 'typename',
    'view4' => '1',
    'edittemplate' => '',
    'metatemplate' => '',
    'view7' => '0',
    'name_es' => 'Tipos de Datos',
    'name_en' => 'DataTypes',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  6 => 
  array (
    'name' => 'Vistas',
    'table' => '_viewdefs',
    'order' => 5,
    'adminonly' => '1',
    'show' => '1',
    'template' => '',
    'id' => 8,
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'listview_order' => 'id',
    'view4' => '1',
    'view7' => '0',
    'name_es' => 'Vistas',
    'name_en' => 'ViewDefs',
    'date_modified' => '2024-08-25 20:02',
  ),
  7 => 
  array (
    'name' => '',
    'table' => '_fields',
    'order' => 11,
    'adminonly' => '1',
    'show' => '0',
    'template' => '',
    'id' => 9,
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'listview_order' => 'order',
    'view4' => '1',
    'view7' => '0',
    'name_es' => 'Campos',
    'name_en' => 'Fields',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'view8' => '0',
    'default_filter' => '',
  ),
  9 => 
  array (
    'name' => 'Relaciones',
    'table' => '_relationships',
    'order' => 12,
    'adminonly' => '1',
    'show' => '1',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'template' => '',
    'id' => 11,
    'listview_order' => 'name',
    'view4' => '1',
    'view7' => '0',
    'name_es' => 'Relaciones',
    'name_en' => 'Relationships',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  10 => 
  array (
    'name' => '',
    'table' => '_relviews',
    'order' => 13,
    'adminonly' => '1',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'template' => '',
    'id' => 12,
    'listview_order' => 'relview_name',
    'view7' => '0',
    'name_es' => 'Vistas de Relaciones',
    'name_en' => 'RelViews',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  11 => 
  array (
    'name' => 'Roles',
    'table' => '_roles',
    'order' => 28,
    'adminonly' => '1',
    'show' => '1',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'template' => '',
    'id' => 13,
    'view7' => '0',
    'listview_order' => '',
    'name_es' => 'Roles',
    'name_en' => 'Roles',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  13 => 
  array (
    'name' => 'Configuracion',
    'table' => '__config',
    'order' => '4',
    'adminonly' => '1',
    'show' => '1',
    'view1' => '0',
    'view2' => '0',
    'view3' => '0',
    'view4' => '0',
    'template' => '&lt;!-- config --&gt;

&lt;form id=config method=post action=void.php&gt;

&lt;input type=submit class=&quot;button button-primary&quot; value=Save&gt;
&lt;input type=button class=&quot;button&quot; value=&quot;SMTP Test&quot; onclick=&quot;return nav_customviewbutton(&#039;main_body&#039;, &#039;7&#039;, &#039;15&#039;, &#039;2&#039;, &#039;&#039;)&quot;&gt;

&lt;div class=&quot;panel container&quot; id=&quot;configrecords&quot;&gt;
&lt;div class=&quot;view row&quot;&gt;&lt;div class=&quot;view three columns label&quot;&gt;Key&lt;/div&gt;&lt;div class=&quot;view nine columns label&quot;&gt; Value &lt;/div&gt;&lt;/div&gt;

$__config{
&lt;div class=&quot;view row&quot;&gt;
&lt;div class=&quot;view three columns&quot;&gt;$key&lt;/div&gt;&lt;div class=&quot;view nine columns&quot;&gt; &lt;input type=text name=&quot;$key&quot; id=&quot;$key&quot; value=&quot;$value&quot;&gt;
&lt;/div&gt;
&lt;/div&gt;
}
&lt;/div&gt;
  &lt;input type=hidden value=0 id=_count&gt;
  &lt;input type=button value=Add onclick=&quot;addConfigRecord()&quot; class=button&gt; 
&lt;input type=submit class=&quot;button button-primary&quot; value=Save&gt;

&lt;/form&gt;
<script> $(&#039;form#config&#039;).ajaxForm(); </script><script> $(&#039;form#config&#039;).submit(function (e){ save_config(this); e.preventDefault(); }); </script>

',
    'id' => 15,
    'listview_order' => 'id',
    'view7' => '0',
    'name_es' => 'Configuracion',
    'name_en' => 'Config',
    'date_modified' => '2019-05-20 02:57',
    'modified_by' => 1,
  ),
  14 => 
  array (
    'name' => 'Extender Vistas',
    'order' => 7,
    'table' => '_extend_views',
    'adminonly' => '1',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'listview_order' => 'id',
    'id' => 16,
    'template' => '',
    'view7' => '0',
    'name_es' => 'Extender Vistas',
    'name_en' => 'Extend Views',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  15 => 
  array (
    'name' => 'Grupos de Pesta&ntilde;as',
    'order' => 29,
    'table' => '_tab_groups',
    'adminonly' => '1',
    'show' => '1',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'listview_order' => 'order',
    'template' => '',
    'id' => 17,
    'view7' => '0',
    'name_es' => 'Grupos de Pesta&ntilde;as',
    'name_en' => 'Tab Groups',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  19 => 
  array (
    'name' => '',
    'order' => 8,
    'table' => '_custom_views',
    'adminonly' => '1',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'listview_order' => 'name',
    'template' => '',
    'id' => 19,
    'view7' => '0',
    'name_es' => 'Vistas Custom',
    'name_en' => 'Custom Views',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  18 => 
  array (
    'name' => 'SuppleUnit',
    'order' => 31,
    'table' => '__tests',
    'adminonly' => '1',
    'show' => '100',
    'view1' => '0',
    'view2' => '1',
    'view3' => '0',
    'view4' => '1',
    'listview_order' => '',
    'template' => '',
    'id' => 20,
    'view7' => '0',
    'name_es' => 'SuppleUnit',
    'name_en' => 'SuppleUnit',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  22 => 
  array (
    'name' => 'Idiomas',
    'order' => 14,
    'table' => '_languages',
    'adminonly' => '1',
    'show' => '1',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'listview_order' => 'name',
    'template' => '',
    'view7' => '0',
    'id' => 24,
    'name_es' => 'Idiomas',
    'name_en' => 'Languages',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  23 => 
  array (
    'name' => 'Parametros de Tipos de Datos',
    'order' => 10,
    'table' => '_datatypes_parameters',
    'adminonly' => '1',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'listview_order' => 'order',
    'template' => '',
    'view7' => '0',
    'id' => 25,
    'name_es' => 'Parametros de Tipos de Datos',
    'name_en' => 'DataTypes Parameters',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  24 => 
  array (
    'name' => 'Tablas',
    'order' => 30,
    'table' => '__tables',
    'adminonly' => '1',
    'show' => '1',
    'view1' => '0',
    'view2' => '1',
    'view3' => '0',
    'view4' => '1',
    'listview_order' => 'name',
    'template' => '',
    'view7' => '0',
    'id' => 26,
    'name_es' => 'Tablas',
    'name_en' => 'Tables',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  25 => 
  array (
    'name' => 'Campos de Tablas',
    'order' => 32,
    'table' => '__fields',
    'adminonly' => '1',
    'show' => '0',
    'view1' => '0',
    'view2' => '1',
    'view3' => '0',
    'view4' => '1',
    'listview_order' => 'name',
    'template' => '',
    'view7' => '0',
    'id' => 27,
    'name_es' => 'Campos de Tablas',
    'name_en' => 'Table Fields',
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
  ),
  26 => 
  array (
    'name' => 'Botones de Vistas',
    'name_es' => 'Botones de Vistas',
    'name_en' => 'View Buttons',
    'order' => 6,
    'table' => '_viewbuttons',
    'adminonly' => '1',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'listview_order' => '',
    'template' => '',
    'date_entered' => '2018-05-12 23:16',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 28,
  ),
  28 => 
  array (
    'name' => '',
    'name_es' => 'Operaciones de Busqueda',
    'name_en' => 'Searh Operations',
    'order' => 16,
    'table' => '_search_ops',
    'adminonly' => '1',
    'show' => '1',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '1',
    'listview_order' => 'order',
    'template' => '',
    'date_entered' => '2020-04-14 21:10',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 30,
  ),
  29 => 
  array (
    'name' => '',
    'name_es' => 'Reportes',
    'name_en' => 'Reports',
    'order' => 17,
    'table' => '_reports',
    'adminonly' => '0',
    'show' => '1',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'listview_order' => 'name',
    'template' => '',
    'date_entered' => '2020-04-22 19:49',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 31,
    'view8' => '0',
    'default_filter' => '',
  ),
  30 => 
  array (
    'name' => '',
    'name_es' => 'Columnas del Reporte',
    'name_en' => 'Report Columns',
    'order' => 18,
    'table' => '_report_columns',
    'adminonly' => '0',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'listview_order' => 'order',
    'template' => '',
    'date_entered' => '2020-04-22 20:02',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 32,
  ),
  31 => 
  array (
    'name' => '',
    'name_es' => 'Filtros del Reporte',
    'name_en' => 'Report Filters',
    'order' => 19,
    'table' => '_report_filters',
    'adminonly' => '0',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'listview_order' => '',
    'template' => '',
    'date_entered' => '2020-04-26 18:17',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 33,
  ),
  32 => 
  array (
    'name' => '',
    'name_es' => 'Listas Desplegables',
    'name_en' => 'Dropdown Lists',
    'order' => 20,
    'table' => '_dropdown_lists',
    'adminonly' => '0',
    'show' => '1',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'listview_order' => 'name',
    'template' => '',
    'date_entered' => '2020-05-17 04:44',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 34,
  ),
  33 => 
  array (
    'name' => '',
    'name_es' => 'Opciones de Listas Desplegables',
    'name_en' => 'Dropdown Lists Options',
    'order' => 21,
    'table' => '_dropdown_options',
    'adminonly' => '0',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'listview_order' => 'order',
    'template' => '',
    'date_entered' => '2020-05-17 04:46',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 35,
  ),
  34 => 
  array (
    'name' => '',
    'name_es' => 'Dashboard',
    'name_en' => 'Dashboard',
    'order' => 22,
    'table' => '_dashboard',
    'adminonly' => '0',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'view8' => '0',
    'listview_order' => 'order',
    'template' => '',
    'date_entered' => '2021-06-01 03:52',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 36,
  ),
  35 => 
  array (
    'name' => '',
    'name_es' => 'Plantillas',
    'name_en' => 'Templates',
    'order' => 23,
    'table' => '_templates',
    'adminonly' => '1',
    'show' => '1',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'view8' => '0',
    'listview_order' => 'name',
    'default_filter' => '',
    'template' => '',
    'date_entered' => '2021-07-11 01:33',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 39,
  ),
  36 => 
  array (
    'name' => '',
    'name_es' => 'Agrupar campos',
    'name_en' => 'Field Groups',
    'order' => 24,
    'table' => '_field_groups',
    'adminonly' => '1',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'view8' => '0',
    'listview_order' => 'order',
    'default_filter' => '',
    'template' => '',
    'date_entered' => '2022-03-10 14:17',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 40,
  ),
  37 => 
  array (
    'name' => '',
    'name_es' => 'Paquetes',
    'name_en' => 'Packages',
    'order' => 25,
    'table' => '_packages',
    'adminonly' => '1',
    'show' => '1',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'view8' => '0',
    'listview_order' => 'date_entered',
    'default_filter' => '',
    'template' => '',
    'date_entered' => '2022-04-23 20:19',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 41,
  ),
  38 => 
  array (
    'name' => '',
    'name_es' => 'Archivos del Paquete',
    'name_en' => 'Package Files',
    'order' => 26,
    'table' => '_package_files',
    'adminonly' => '1',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'view8' => '0',
    'listview_order' => 'filename',
    'default_filter' => '',
    'template' => '',
    'date_entered' => '2022-04-29 15:47',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 42,
  ),
  39 => 
  array (
    'name' => '',
    'name_es' => 'Datos del paquete',
    'name_en' => 'Package data',
    'order' => 27,
    'table' => '_package_data',
    'adminonly' => '1',
    'show' => '0',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'view4' => '1',
    'view7' => '0',
    'view8' => '0',
    'listview_order' => '',
    'default_filter' => '',
    'template' => '',
    'date_entered' => '2022-04-29 15:50',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:02',
    'modified_by' => 1,
    'id' => 43,
  ),
); ?>