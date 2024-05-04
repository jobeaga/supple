<?php $_entities = array (
  0 => 
  array (
    'name' => 'Usuarios',
    'table' => 'users',
    'template' => '',
    'id' => 3,
    'adminonly' => '1',
    'show' => '1',
    'order' => '22',
    'superentity' => '',
    'view1' => '1',
    'view2' => '1',
    'view3' => '1',
    'listview_order' => 'user',
    'view4' => '1',
    'view7' => '0',
    'name_es' => 'Usuarios',
    'name_en' => 'Users',
    'date_modified' => '2019-05-21 16:27',
    'modified_by' => 2,
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
    'modified_by' => 2,
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

<a id="dashboard_add_button" href="admin.php?entity=36&amp;view=1&amp;1=0" onclick="return nav_viewbutton(&#039;1&#039;, &#039;36&#039;)" class="button button-primary" style="float:right;margin-top: 6px;height: 30px;margin-bottom: 0;">+</a>
<a id="dashboard_edit_button" href="admin.php?entity=36&amp;view=2" onclick="return loadView(&#039;36&#039;, &#039;2&#039;)" class="button button-primary" style="float:right;margin-top: 6px;height: 30px;margin-bottom: 0;"><img src="templates/admin/images/edit_white.png"></a>

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
    'order' => '10',
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
    'date_modified' => '2018-05-12 23:16',
    'modified_by' => 2,
  ),
  6 => 
  array (
    'name' => 'Vistas',
    'table' => '_viewdefs',
    'order' => '6',
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
  ),
  7 => 
  array (
    'name' => '',
    'table' => '_fields',
    'order' => '12',
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
    'date_modified' => '2022-04-23 20:21',
    'modified_by' => 2,
    'view8' => '0',
    'default_filter' => '',
  ),
  9 => 
  array (
    'name' => 'Relaciones',
    'table' => '_relationships',
    'order' => '13',
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
    'date_modified' => '2018-05-12 23:16',
    'modified_by' => 2,
  ),
  10 => 
  array (
    'name' => '',
    'table' => '_relviews',
    'order' => '15',
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
    'date_modified' => '2019-05-15 20:35',
    'modified_by' => 2,
  ),
  11 => 
  array (
    'name' => 'Roles',
    'table' => '_roles',
    'order' => '52',
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
    'date_modified' => '2018-05-12 23:16',
    'modified_by' => 2,
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
    'modified_by' => 2,
  ),
  14 => 
  array (
    'name' => 'Extender Vistas',
    'order' => '8',
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
    'date_modified' => '2018-05-12 23:16',
    'modified_by' => 2,
  ),
  15 => 
  array (
    'name' => 'Grupos de Pesta&ntilde;as',
    'order' => '53',
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
    'date_modified' => '2018-05-12 23:16',
    'modified_by' => 2,
  ),
  19 => 
  array (
    'name' => '',
    'order' => '9',
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
    'date_modified' => '2020-11-07 00:50',
    'modified_by' => 2,
  ),
  18 => 
  array (
    'name' => 'SuppleUnit',
    'order' => '55',
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
    'date_modified' => '2019-05-20 02:58',
    'modified_by' => 2,
  ),
  22 => 
  array (
    'name' => 'Idiomas',
    'order' => '17',
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
    'date_modified' => '2019-05-20 02:24',
    'modified_by' => 2,
  ),
  23 => 
  array (
    'name' => 'Parametros de Tipos de Datos',
    'order' => '11',
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
    'date_modified' => '2018-05-12 23:16',
    'modified_by' => 2,
  ),
  24 => 
  array (
    'name' => 'Tablas',
    'order' => '54',
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
    'date_modified' => '2019-05-20 02:58',
    'modified_by' => 2,
  ),
  25 => 
  array (
    'name' => 'Campos de Tablas',
    'order' => '56',
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
    'date_modified' => '2018-05-12 23:16',
    'modified_by' => 2,
  ),
  26 => 
  array (
    'name' => 'Botones de Vistas',
    'name_es' => 'Botones de Vistas',
    'name_en' => 'View Buttons',
    'order' => '7',
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
    'created_by' => 2,
    'date_modified' => '2018-05-12 23:16',
    'modified_by' => 2,
    'id' => 28,
  ),
  28 => 
  array (
    'name' => '',
    'name_es' => 'Operaciones de Busqueda',
    'name_en' => 'Searh Operations',
    'order' => '23',
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
    'created_by' => 2,
    'date_modified' => '2020-04-14 21:32',
    'modified_by' => 2,
    'id' => 30,
  ),
  29 => 
  array (
    'name' => '',
    'name_es' => 'Reportes',
    'name_en' => 'Reports',
    'order' => '24',
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
    'created_by' => 2,
    'date_modified' => '2022-04-23 20:22',
    'modified_by' => 2,
    'id' => 31,
    'view8' => '0',
    'default_filter' => '',
  ),
  30 => 
  array (
    'name' => '',
    'name_es' => 'Columnas del Reporte',
    'name_en' => 'Report Columns',
    'order' => 25,
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
    'created_by' => 2,
    'date_modified' => '2020-04-22 20:02',
    'modified_by' => 2,
    'id' => 32,
  ),
  31 => 
  array (
    'name' => '',
    'name_es' => 'Filtros del Reporte',
    'name_en' => 'Report Filters',
    'order' => 26,
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
    'created_by' => 2,
    'date_modified' => '2020-04-26 18:17',
    'modified_by' => 2,
    'id' => 33,
  ),
  32 => 
  array (
    'name' => '',
    'name_es' => 'Listas Desplegables',
    'name_en' => 'Dropdown Lists',
    'order' => 27,
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
    'created_by' => 2,
    'date_modified' => '2020-05-17 04:44',
    'modified_by' => 2,
    'id' => 34,
  ),
  33 => 
  array (
    'name' => '',
    'name_es' => 'Opciones de Listas Desplegables',
    'name_en' => 'Dropdown Lists Options',
    'order' => '28',
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
    'created_by' => 2,
    'date_modified' => '2020-05-18 02:32',
    'modified_by' => 2,
    'id' => 35,
  ),
  34 => 
  array (
    'name' => '',
    'name_es' => 'Dashboard',
    'name_en' => 'Dashboard',
    'order' => '40',
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
    'created_by' => 2,
    'date_modified' => '2021-06-01 06:15',
    'modified_by' => 2,
    'id' => 36,
  ),
  35 => 
  array (
    'name' => '',
    'name_es' => 'Plantillas',
    'name_en' => 'Templates',
    'order' => 43,
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
    'created_by' => 2,
    'date_modified' => '2021-07-11 01:33',
    'modified_by' => 2,
    'id' => 39,
  ),
  36 => 
  array (
    'name' => '',
    'name_es' => 'Agrupar campos',
    'name_en' => 'Field Groups',
    'order' => 44,
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
    'created_by' => 2,
    'date_modified' => '2022-03-10 14:17',
    'modified_by' => 2,
    'id' => 40,
  ),
  37 => 
  array (
    'name' => '',
    'name_es' => 'Paquetes',
    'name_en' => 'Packages',
    'order' => '47',
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
    'created_by' => 2,
    'date_modified' => '2022-04-29 15:54',
    'modified_by' => 2,
    'id' => 41,
  ),
  38 => 
  array (
    'name' => '',
    'name_es' => 'Archivos del Paquete',
    'name_en' => 'Package Files',
    'order' => '48',
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
    'created_by' => 2,
    'date_modified' => '2022-05-03 03:00',
    'modified_by' => 2,
    'id' => 42,
  ),
  39 => 
  array (
    'name' => '',
    'name_es' => 'Datos del paquete',
    'name_en' => 'Package data',
    'order' => '49',
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
    'created_by' => 2,
    'date_modified' => '2022-05-03 03:00',
    'modified_by' => 2,
    'id' => 43,
  ),
); ?>
