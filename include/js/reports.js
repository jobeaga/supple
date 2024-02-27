
var visited_entity;
var visited_rels;

// COLUMNS EDIT VIEW
function init_columns_editview(){
    // Event listener for Entity root, and relationships
    $('#report_id').change(function (){
        report_id_change('entity_id', 'relationships_chain', 'report_id');
    });
    report_id_change('entity_id', 'relationships_chain', 'report_id');
    // Event listener that completes the relationships_chain
    $('#entity_id').change(function (){
        saveOptionAttribute('entity_id', 'relationships_chain', 'chain');
        translateChain('relationships_chain');
    });
}
// FILTERS EDIT VIEW
function init_filters_editview(){
    // Event listener for Entity root, and relationships
    $('#report_id').change(function (){
        report_id_change('entity_id', 'relationships_chain', 'report_id');
    });
    report_id_change('entity_id', 'relationships_chain', 'report_id');
    // Event listener that completes the relationships_chain
    $('#entity_id').change(function (){
        saveOptionAttribute('entity_id', 'relationships_chain', 'chain');
        translateChain('relationships_chain');
    });
    // Event listener for OP
    $('#op').change(function (){
        report_filter_op_type_change('op', 'type', 'param1', 'param2', 'field_id');
    });
    // Event listener for TYPE
    $('#type').change(function (){
        report_filter_op_type_change('op', 'type', 'param1', 'param2', 'field_id');
    });
    // Event listener for FIELD
    $('#field_id').change(function (){
        report_filter_op_type_change('op', 'type', 'param1', 'param2', 'field_id');
    });
    $('#entity_id').change(function (){
        report_filter_op_type_change('op', 'type', 'param1', 'param2', 'field_id');
    });
    report_filter_op_type_change('op', 'type', 'param1', 'param2', 'field_id');
    // REMOVE OPS with M params
    $('#op option').each(function (i,e){
        var op_id = $(e).val();
        if(op_id != '' && metadata._search_ops[op_id].params == 'M') $(e).remove();
    });
}


function report_id_change(entity_field, chain_field, report_field){
    // TODO: Case of pre-selected ENTITY
    var selected_entity = $('#'+entity_field).val();
    var selected_chain = $('#'+chain_field).val();
    var report_id = $('#'+report_field).val();
    if (report_id == undefined || report_id == null) return;
    if (report_id == ''){
        // Empty entity selector:
        $('#'+entity_field).html('');
    } else {
        getRecord('_reports', report_id, function(data){
            if (data.entity_id != undefined && data.entity_id != ''){
                visited_entity = {};
                visited_rels = {};
                $('#'+entity_field).html(build_entity_selector(data.entity_id, selected_entity, selected_chain));
                $('#'+entity_field).change();
            } else {
                // Empty entity selector:
                $('#'+entity_field).html('');
            }
        });
    }
}

function build_entity_selector(root_entity, selected_entity, selected_chain, pre_chain, pre_label, rel_name, level){
    var html = '';

    if (level == undefined) level = 0;

    // ADD ENTITY SELECTOR
    html += '<option value="'+ root_entity +'" chain="';
    if (pre_chain != undefined) html += pre_chain;
    html += '" ';
    if (selected_entity == root_entity && selected_chain == pre_chain) html += 'selected="selected"';
    html += '>';
    if (pre_label != undefined) html += pre_label;
    if (rel_name != undefined) html += rel_name + ':';
    html += metadata._entities[root_entity].name;
    html += '</option>';

    // SUB_ENTITIES by RELATIONSHIPS
    var p = '';
    var pl = '';
    var rs = [];
    if (pre_chain != undefined) p = pre_chain + '::';
    if (pre_label != undefined) pl = pre_label;
    $.each(metadata._relationships, function (rid, rel){
        if (visited_rels[rid] == undefined || level < visited_rels[rid]){
            if (rel.id_a == root_entity){
                rs.push({entity_id: rel.id_b, rel_id: rid, rel_name: rel.name});
            }
            if (rel.id_b == root_entity){
                rs.push({entity_id: rel.id_a, rel_id: rid, rel_name: rel.name});
            }
        }
    });

    $.each(rs, function (i, r){
        visited_rels[r.rel_id] = level;
        html += build_entity_selector(r.entity_id, selected_entity, selected_chain, p + r.rel_id, pl + ' > ', r.rel_name, level + 1);
    });

    return html;

}

function translateChain(chain_field){
    var chain = $('#'+chain_field).val();
    var html = '';

    $(chain.split('::')).each(function (i,e){ 
        if (e != ''){
            if (html != '') html += '->';
            html += metadata._relationships[e].name; 
        }
    });
    
    html = '<span class="chain">' + html + '</span>';
    // APPLY
    $('#'+chain_field).parent().find('span.chain').remove();
    $('#'+chain_field).parent().append(html);
}

function report_filter_op_type_change(op_field, type_field, param1_field, param2_field, field_field){
    var op_id = $('#'+op_field).val();
    var type = $('#'+type_field).val();
    var param1 = $('#'+param1_field).val();
    var param2 = $('#'+param2_field).val();
    var field_id = $('#'+field_field).val();
    var op;
    var field;

    if (field_id != ''){
        field = metadata._fields[field_id]
    }

    if (op_id == ''){
        $('#'+type_field).parent().parent().hide();
        $('#'+param1_field).parent().parent().hide();
        $('#'+param2_field).parent().parent().hide();
        $('#'+type_field).val('V');
        $('#'+param1_field).val('');
        $('#'+param2_field).val('');
    } else {
        op = metadata._search_ops[op_id];
        // TYPE
        var p1_html = '';
        var p2_html = '';
        if (type == 'V'){
            // VALUE SELECTOR
            if (op.params == 'N'){
                // TYPE: 18 (Number)
                p1_html = renderBasicSingleFieldFor(18, param1_field, param1_field, 1, param1, {});
                p2_html = renderBasicSingleFieldFor(18, param2_field, param2_field, 1, param2, {});
            } else if (field_id != '' && op.params != 'T'){
                // TYPE: El mismo que el campo seleccionado
                p1_html = renderBasicSingleFieldFor(field.type, param1_field, param1_field, 1, param1, field);
                p2_html = renderBasicSingleFieldFor(field.type, param2_field, param2_field, 1, param2, field);
            } else {
                // TYPE: 1 (TextField)
                p1_html = renderBasicSingleFieldFor(1, param1_field, param1_field, 1, param1, {});
                p2_html = renderBasicSingleFieldFor(1, param2_field, param2_field, 1, param2, {});
            }
        } else if (type == 'F'){
            // TYPE: 25 (Field)
            p1_html = renderBasicSingleFieldFor(25, param1_field, param1_field, 1, param1, {field_name_field:'', fixed_entity:'', entity_field:'entity_id'});
            p2_html = renderBasicSingleFieldFor(25, param2_field, param2_field, 1, param2, {field_name_field:'', fixed_entity:'', entity_field:'entity_id'});
        } else if (type == 'D'){
            p1_html = createDateIntervalSelector(param1_field, param1);
            p2_html = createDateIntervalSelector(param2_field, param2);
        }
        $('#'+param1_field).parent().html(p1_html);
        $('#'+param2_field).parent().html(p2_html);
        // OP
        if (op.params == 0){
            $('#'+type_field).parent().parent().hide();
            $('#'+param1_field).parent().parent().hide();
            $('#'+param2_field).parent().parent().hide();
            $('#'+type_field).val('V');
            $('#'+param1_field).val('');
            $('#'+param2_field).val('');
        } else if (op.params == 1 || op.params == 'T' || op.params == 'N'){
            $('#'+type_field).parent().parent().show();
            $('#'+param1_field).parent().parent().show();
            $('#'+param2_field).parent().parent().hide();
            $('#'+param2_field).val('');
        } else if (op.params == 2){
            $('#'+type_field).parent().parent().show();
            $('#'+param1_field).parent().parent().show();
            $('#'+param2_field).parent().parent().show();
        }
    }
}

function createDateIntervalSelector(param_field, param_value){
    var html = '';
    var param_interval = '';
    var param_number = 0;
    param_interval = param_value.slice(0,1);
    param_number = parseInt(param_value.slice(1));

    // TYPE: 14 (Dropdown) // TYPE: 18 (Number) // TYPE: 9 (Hidden)
    html = global.lang.LBL_TODAY + ' + ';
    html += renderBasicSingleFieldFor(18, param_field + '_number', param_field + '_number', 1, param_number, {size:5});
    html += renderBasicSingleFieldFor(14, param_field + '_interval', param_field + '_interval', 1, param_interval, {options:'H:Hours;d:Days;m:Months', sep1:59, sep2:58});
    html += renderBasicSingleFieldFor(9, param_field, param_field, 1, param_value, {});
    while_selector('#'+param_field, function (){
        var listener = function(){
            $('#'+param_field).val( $('#'+param_field + '_interval').val() + $('#'+param_field + '_number').val());
        };
        $('#'+param_field + '_interval').change(listener);
        $('#'+param_field + '_number').change(listener);
    });
    return html;
}
