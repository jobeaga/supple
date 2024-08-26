<?php $_custom_views = array (
  0 => 
  array (
    'name' => '',
    'name_es' => 'Generar Paquete',
    'name_en' => 'Generate Package',
    'type' => '0',
    'view' => '2',
    'template' => '&lt;h1 id=genpackage_title&gt;&lt;/h1&gt;

&lt;form method=&quot;POST&quot; action=&quot;void.php&quot; id=&quot;genpackage_form&quot;&gt;
    &lt;label&gt;From date:&lt;/label&gt;&lt;span id=genpackage_from&gt;&lt;/span&gt;&lt;br&gt;
    &lt;label&gt;To date:&lt;/label&gt;&lt;span id=genpackage_to&gt;&lt;/span&gt;&lt;br&gt;
    &lt;input type=button onclick=&quot;genpackage_do()&quot; value=&quot;Fetch data&quot; class=&quot;button-primary&quot; style=&quot;margin-top:2em&quot;&gt;
&lt;/form&gt;

&lt;form method=&quot;POST&quot; action=&quot;admin.php?action=downloadpackage&quot; id=genpackage_contents name=genpackage_contents style=&quot;display:none&quot; enctype=&quot;multipart/form-data&quot; accept-charset=&quot;utf-8&quot;&gt;
    &lt;div id=genpackage_contents_buttons&gt;
        &lt;input type=button value=&quot;Select None&quot; onclick=&quot;select_none()&quot;&gt;
        &lt;input type=button value=&quot;Select All&quot; onclick=&quot;select_all()&quot;&gt;
        &lt;input type=button value=&quot;Select Core&quot; onclick=&quot;select_core()&quot;&gt;
        &lt;input type=button value=&quot;Select Custom&quot; onclick=&quot;select_custom()&quot;&gt;
        &lt;input type=submit value=&quot;Generate&quot; class=&quot;button-primary&quot;&gt;
        &lt;label style=&quot;display:inline-block&quot;&gt;&lt;input type=checkbox name=download value=1&gt;Download&lt;/label&gt;&lt;br&gt;
        Package name: &lt;input type=text name=&quot;package_name&quot; value=&quot;&quot; style=&quot;margin: 8px;&quot;&gt;
    &lt;/div&gt;
    &lt;div id=genpackage_contents_list class=&quot;listview form&quot;&gt;
        
    &lt;/div&gt; 
&lt;/form&gt;

&lt;script&gt;
    setDataTo(&#039;genpackage_title&#039;, &#039;_custom_views&#039;, &#039;name&#039;, this_status[&#039;main_body&#039;].pre_filled.custom_view_id);
    var inputfrom = renderBasicSingleFieldFor(&#039;19&#039;, &#039;from&#039;, &#039;from&#039;, 1, &#039;&#039;, {});
    var inputto = renderBasicSingleFieldFor(&#039;19&#039;, &#039;to&#039;, &#039;to&#039;, 1, new Date().toISOString().slice(0, 10), {});
    setHtml(&#039;genpackage_from&#039;, inputfrom);
    setHtml(&#039;genpackage_to&#039;, inputto);
    
    function genpackage_do(){
        var f = document.getElementsByName(&#039;from&#039;)[0].value;
        var t = document.getElementsByName(&#039;to&#039;)[0].value;
        if (f != undefined &amp;&amp; t != undefined &amp;&amp; f != &#039;&#039; &amp;&amp; t != &#039;&#039;){
            executeAction(&#039;genpackage&#039;, &#039;from=&#039;+f+&#039;&amp;to=&#039;+t, &#039;&#039;, function(gendata){
                if (gendata.error == &#039;&#039;){
                    var html = &#039;&#039;;
                    // SHOW and CLEAN genpackage_contents
                    document.getElementById(&#039;genpackage_contents&#039;).style.display = &#039;&#039;;
                    document.getElementById(&#039;genpackage_contents_list&#039;).innerHTML = &#039;&#039;;
                    // GENERATE NEW FORM with checkboxes
                    var css = &#039;&#039;;
                        // FOR FILES
                        gendata.files.forEach(function (e){ 
                            if (e.slice(0,6) == &#039;custom&#039;){
                                css = &#039;custom&#039;;
                            } else {
                                css = &#039;core&#039;;
                            }
                            html += &#039;&lt;div class=&quot;record&quot;&gt;&lt;div class=&quot;label checkboxes&quot;&gt;&lt;input type=&quot;checkbox&quot; checked=&quot;checked&quot; name=&quot;files[]&quot; value=&quot;&#039;+e+&#039;&quot; class=&quot;&#039; + css + &#039;&quot;&gt;&lt;/div&gt;&lt;div class=&quot;field field0&quot;&gt;&#039;+e+&#039;&lt;/div&gt;&lt;/div&gt;&#039;;    
                        });
                        
                        // FOR DATA
                        for (const table in gendata.data) {
                            if (table.slice(0,1) == &#039;_&#039;){
                                css = &#039;core&#039;;
                            } else {
                                css = &#039;custom&#039;;
                            }
                            html += &#039;&lt;div class=&quot;record&quot;&gt;&lt;div class=&quot;label checkboxes&quot;&gt;&lt;input type=&quot;checkbox&quot; checked=&quot;checked&quot; onclick=&quot;toggle_table(this)&quot; class=&quot;&#039; + css + &#039;&quot; table=&quot;&#039; + table + &#039;&quot;&gt;&lt;/div&gt;&lt;div class=&quot;label label0&quot;&gt;&#039;+table+&#039;&lt;/div&gt;&lt;/div&gt;&#039;;  
                            for (const record_id in gendata.data[table]) {
                                html += &#039;&lt;div class=&quot;record&quot;&gt;&lt;div class=&quot;label checkboxes&quot;&gt;&lt;input type=&quot;checkbox&quot; name=&quot;data_&#039; + table + &#039;[]&quot; style=&quot;margin-left:2em&quot; checked=&quot;checked&quot; class=&quot;&#039; + css + &#039; data&quot; table=&quot;&#039; + table + &#039;&quot; value=&quot;&#039; + record_id + &#039;&quot;&gt;&lt;/div&gt;&lt;div class=&quot;field field0&quot;&gt;&#039; + gendata.data[table][record_id] +&#039;&lt;/div&gt;&lt;div class=&quot;field field1&quot;&gt;&#039; + record_id +&#039;&lt;/div&gt;&lt;/div&gt;&#039;;
                            }
                        }

                    // PUT DATA:
                    document.getElementById(&#039;genpackage_contents_list&#039;).innerHTML = html;
                } else {
                    document.getElementById(&#039;genpackage_contents&#039;).style.display = &#039;none&#039;;
                }
            });
        } else {
            show_message(&#039;Empty dates&#039;);
        }
    }
    
    function toggle_table(element){
        var c = element.checked;
        var tablename = element.attributes.table.value;
        document.querySelectorAll(&#039;input.data[table=&quot;&#039; + tablename + &#039;&quot;]&#039;).forEach(function (e){ e.checked = c; });
    }
    
    function select_all(){
        document.querySelectorAll(&#039;input[type=&quot;checkbox&quot;]&#039;).forEach(function (e){ e.checked = true; });
    }
    
    function select_none(){
        document.querySelectorAll(&#039;input[type=&quot;checkbox&quot;]&#039;).forEach(function (e){ e.checked = false; });
    }
    
    function select_core(){
        document.querySelectorAll(&#039;input.core&#039;).forEach(function (e){ e.checked = true; });
    }
    
    function select_custom(){
        document.querySelectorAll(&#039;input.custom&#039;).forEach(function (e){ e.checked = true; });
    }
    
&lt;/script&gt;
',
    'parent' => '41',
    'date_entered' => '2022-04-23 20:46',
    'created_by' => 1,
    'date_modified' => '2022-04-29 16:10',
    'modified_by' => 1,
    'type_value' => '',
    'view_value' => '',
    'parent_name' => '',
    'id' => 2,
  ),
  1 => 
  array (
    'name' => '',
    'name_es' => 'Cambios a Core',
    'name_en' => 'Core Changes',
    'type' => '0',
    'view' => '2',
    'template' => '&lt;h1 id=corechanges_title&gt;&lt;/h1&gt;

&lt;form method=&quot;POST&quot; action=&quot;admin.php?action=applycorechanges&quot; id=corechanges_contents name=corechanges_contents style=&quot;display:none&quot; enctype=&quot;multipart/form-data&quot; accept-charset=&quot;utf-8&quot;&gt;
    
    &lt;input type=submit value=&quot;Apply&quot; class=&quot;button-primary&quot;&gt; &lt;input type=button value=&quot;Accept All&quot; onclick=&quot;setAll(&#039;to_core&#039;)&quot;&gt; &lt;input type=button value=&quot;Revert All&quot; onclick=&quot;setAll(&#039;revert&#039;)&quot;&gt;
    
    &lt;div id=corechanges_list class=&quot;listview form&quot;&gt;
    &lt;/div&gt;
&lt;/form&gt;

&lt;script&gt;
    setDataTo(&#039;corechanges_title&#039;, &#039;_custom_views&#039;, &#039;name&#039;, this_status[&#039;main_body&#039;].pre_filled.custom_view_id);
    executeAction(&#039;getcorechanges&#039;, &#039;&#039;, &#039;&#039;, function(data){
        console.log(data);
        var html = &#039;&#039;;
        var s = &#039;&#039;;
        for (const table in data.data) {
            html += &#039;&lt;div class=&quot;record&quot; style=&quot;background-color:grey&quot;&gt;&lt;div class=&quot;label checkboxes&quot;&gt;&lt;/div&gt;&lt;div class=&quot;label label0&quot;&gt;&#039;+table+&#039;&lt;/div&gt;&lt;div class=&quot;label label1&quot;&gt;ID&lt;/div&gt;&lt;div class=&quot;label label2&quot;&gt;stat&lt;/div&gt;&lt;/div&gt;&#039;;  
            var s1 = &#039;&#039;;
            var s2 = &#039;&#039;;
            for (const record_id in data.data[table]) {
                if (data.stat[table][record_id]){
                    s = &#039;&lt;span style=&quot;color:blue&quot;&gt;mod&lt;/span&gt;&#039;;
                    s1 = &#039;Revert&#039;;
                    s2 = &#039;Override core&#039;;
                } else {
                    s = &#039;&lt;span style=&quot;color:red&quot;&gt;new&lt;/span&gt;&#039;;
                    s1 = &#039;Remove&#039;;
                    s2 = &#039;Add to core&#039;;
                }
                html += &#039;&lt;div class=&quot;record&quot;&gt;&lt;div class=&quot;label checkboxes&quot;&gt;&lt;input type=hidden name=&quot;record_id[]&quot; value=&quot;&#039;+record_id+&#039;&quot;&gt;&lt;input type=hidden name=&quot;table[]&quot; value=&quot;&#039;+table+&#039;&quot;&gt;&lt;select name=&quot;action[]&quot; table=&quot;&#039; + table + &#039;&quot;&gt;&lt;option value=&quot;&quot;&gt;No-change&lt;/option&gt;&lt;option value=&quot;revert&quot;&gt;&#039;+s1+&#039;&lt;/option&gt;&lt;option value=&quot;to_core&quot;&gt;&#039;+s2+&#039;&lt;/option&gt;&lt;/select&gt;&lt;/div&gt;&lt;div class=&quot;field field0&quot;&gt;&#039; + data.data[table][record_id] +&#039;&lt;/div&gt;&lt;div class=&quot;field field1&quot;&gt;&#039; + record_id +&#039;&lt;/div&gt;&lt;div class=&quot;field field2&quot;&gt;&#039; + s +&#039;&lt;/div&gt;&lt;/div&gt;&#039;;
            }
        }
        document.getElementById(&#039;corechanges_contents&#039;).style.display = &#039;&#039;;
        document.getElementById(&#039;corechanges_list&#039;).innerHTML = html;
    });
    
    function setAll(to_status){
        document.querySelectorAll(&#039;#corechanges_list select&#039;).forEach(function (e){ e.value = to_status; });
    }
&lt;/script&gt;',
    'parent' => '41',
    'date_entered' => '2022-04-25 14:56',
    'created_by' => 1,
    'date_modified' => '2024-08-25 20:11',
    'modified_by' => 1,
    'type_value' => '',
    'view_value' => '',
    'parent_name' => '',
    'id' => 3,
  ),
  2 => 
  array (
    'name' => '',
    'name_es' => 'Descargar',
    'name_en' => 'Download',
    'type' => '0',
    'view' => '4',
    'template' => '&lt;script&gt;
    window.location.href = &#039;admin.php?action=downloadpackage&amp;package_id=&#039;+this_status[&#039;main_body&#039;].record_id;
    loadView(&#039;41&#039;, &#039;4&#039;, this_status[&#039;main_body&#039;].record_id, &#039;&#039;, 0, false, undefined, {});
&lt;/script&gt;',
    'parent' => '41',
    'date_entered' => '2022-04-29 16:31',
    'created_by' => 1,
    'date_modified' => '2022-04-30 02:22',
    'modified_by' => 1,
    'type_value' => '',
    'view_value' => '',
    'parent_name' => '',
    'id' => 4,
  ),
  3 => 
  array (
    'name' => '',
    'name_es' => 'Instalar paquete',
    'name_en' => 'Install Package',
    'type' => '0',
    'view' => '2',
    'template' => '&lt;h1 id=installpackage_title&gt;&lt;/h1&gt;
&lt;form class=&quot;record&quot; enctype=&quot;multipart/form-data&quot; accept-charset=&quot;utf-8&quot; method=&quot;POST&quot; action=&quot;admin.php?action=installpackage&quot;&gt;
    Package zip-file: &lt;input type=file name=&quot;package&quot;&gt;&lt;br&gt;
    &lt;input type=submit class=&quot;button-primary&quot; style=&quot;margin:8px&quot;&gt;
&lt;/form&gt;
&lt;script&gt;
    setDataTo(&#039;installpackage_title&#039;, &#039;_custom_views&#039;, &#039;name&#039;, this_status[&#039;main_body&#039;].pre_filled.custom_view_id);
&lt;/script&gt;
',
    'parent' => '41',
    'date_entered' => '2022-04-30 03:03',
    'created_by' => 1,
    'date_modified' => '2022-04-30 03:15',
    'modified_by' => 1,
    'type_value' => '',
    'view_value' => '',
    'parent_name' => '',
    'id' => 5,
  ),
  4 => 
  array (
    'name' => '',
    'name_es' => 'Actualizar cache de metadatos',
    'name_en' => 'Update Metadata Cache',
    'type' => '0',
    'view' => '2',
    'template' => '&lt;img class=&quot;loader&quot; src=&quot;templates/admin/images/loader.gif&quot;&gt;
&lt;script&gt;
    updateMetadataCache(0, function (){
        loadMetadata(function (){
            menugoto(&#039;41&#039;);
        });
    });
&lt;/script&gt;',
    'parent' => '41',
    'date_entered' => '2022-05-17 01:50',
    'created_by' => 1,
    'date_modified' => '2022-05-17 01:54',
    'modified_by' => 1,
    'type_value' => '',
    'view_value' => '',
    'parent_name' => '',
    'id' => 6,
  ),
  5 => 
  array (
    'name' => '',
    'name_es' => 'Probar SMTP',
    'name_en' => 'Test SMTP',
    'type' => '1',
    'view' => '2',
    'template' => '$_eval[ SuppleMail::smtp_test(); ] 

&lt;h2&gt;Message Sent to $_config.to_email&lt;/h2&gt;',
    'parent' => '15',
    'date_entered' => '2022-05-18 10:13',
    'created_by' => 1,
    'date_modified' => '2022-05-18 10:39',
    'modified_by' => 1,
    'type_value' => '',
    'view_value' => '',
    'parent_name' => '',
    'id' => 7,
  ),
); ?>