
var visited_entity;
var visited_rels;

// COLUMNS / FILTERS EDIT VIEW
function init_columns_editview() {
    _init_editview_common('entity_id', 'relationships_chain', 'report_id');
}

// FILTERS EDIT VIEW
function init_filters_editview() {
    var entity_field   = 'entity_id';
    var chain_field    = 'relationships_chain';
    var report_field   = 'report_id';
    var op_field       = 'op';
    var type_field     = 'type';
    var param1_field   = 'param1';
    var param2_field   = 'param2';
    var field_field    = 'field_id';

    _init_editview_common(entity_field, chain_field, report_field, function(){
        getEl(op_field, function(el){ el.addEventListener('change', function() { report_filter_op_type_change(op_field, type_field, param1_field, param2_field, field_field); }); });
        getEl(type_field, function(el){ el.addEventListener('change', function() { report_filter_op_type_change(op_field, type_field, param1_field, param2_field, field_field); }); });
        getEl(field_field, function(el){ el.addEventListener('change', function() { report_filter_op_type_change(op_field, type_field, param1_field, param2_field, field_field); }); });
        report_filter_op_type_change(op_field, type_field, param1_field, param2_field, field_field);

        var op_el = getEl(op_field);
        if (op_el) {
            var options = op_el.querySelectorAll('option');
            for (var i = 0; i < options.length; i++) {
                var opt = options[i];
                var op_id = opt.value;
                var op_meta = op_id !== '' && metadata._search_ops[op_id];
                if (op_id !== '' && op_meta && op_meta.params === 'M') {
                    opt.remove();
                }
            }
        }
    });
}

function _init_editview_common(entity_field, chain_field, report_field, afterInit) {
    var selected_entity = getEl(entity_field) ? getEl(entity_field).value : '';
    var selected_chain  = getEl(chain_field) ? getEl(chain_field).value : '';

    getEl(report_field, function(el) { el.addEventListener('change', function(){ report_id_change(entity_field, chain_field, report_field); }); });
    getEl(entity_field, function(el) { el.addEventListener('change', function(){ saveOptionAttribute(entity_field, chain_field, 'chain'); translateChain(chain_field); }); });

    report_id_change(entity_field, chain_field, report_field);

    if (typeof afterInit === 'function') afterInit();
}


function report_id_change(entity_field, chain_field, report_field){
    var selected_entity = getEl(entity_field) ? getEl(entity_field).value : '';
    var selected_chain  = getEl(chain_field) ? getEl(chain_field).value : '';
    var report_id       = getEl(report_field) ? getEl(report_field).value : '';

    if (report_id === undefined || report_id === null) return;

    if (report_id === ''){
        setHtml(entity_field, '');
        setHtml(chain_field, '');
    } else {
        getRecord('_reports', report_id, function(data){
            if (data.entity_id != undefined && data.entity_id != ''){
                visited_entity = {};
                visited_rels = {};
                setHtml(entity_field, build_entity_selector(data.entity_id, selected_entity, selected_chain));
                getEl(entity_field, function(el){ el.dispatchEvent(new Event('change')); });
            } else {
                setHtml(entity_field, '');
                setHtml(chain_field, '');
            }
        });
    }
}

function build_entity_selector(root_entity, selected_entity, selected_chain, pre_chain, pre_label, rel_name, level){
    var html = '';

    if (level === undefined) level = 0;

    html += '<option value="' + root_entity + '" chain="' + (pre_chain !== undefined ? pre_chain : '') + '" ';
    if (selected_entity === root_entity && selected_chain === pre_chain) html += 'selected="selected" ';
    html += '>';
    if (pre_label !== undefined) html += pre_label;
    if (rel_name !== undefined)  html += rel_name + ':';
    html += metadata._entities[root_entity].name;
    html += '</option>';

    var p  = pre_chain !== undefined ? pre_chain + '::' : '';
    var pl = pre_label !== undefined ? pre_label : '';
    var rs = [];

    var rel = metadata._relationships;
    for (var rid in rel) {
        if (rel.hasOwnProperty(rid)) {
            if (visited_rels[rid] === undefined || level < visited_rels[rid]) {
                if (rel[rid].id_a === root_entity) {
                    rs.push({entity_id: rel[rid].id_b, rel_id: rid, rel_name: rel[rid].name});
            }
                if (rel[rid].id_b === root_entity) {
                    rs.push({entity_id: rel[rid].id_a, rel_id: rid, rel_name: rel[rid].name});
            }
        }
        }
    }

    for (var i = 0; i < rs.length; i++) {
        visited_rels[rs[i].rel_id] = level;
        html += build_entity_selector(rs[i].entity_id, selected_entity, selected_chain, p + rs[i].rel_id, pl + ' > ', rs[i].rel_name, level + 1);
    }

    return html;
}

function translateChain(chain_field){
    var chain_el = getEl(chain_field);
    if (!chain_el) return;

    var chain = chain_el.value;
    var html  = '';

    var parts = chain.split('::');
    for (var i = 0; i < parts.length; i++) {
        if (parts[i] !== '') {
            if (html !== '') html += '->';
            html += metadata._relationships[parts[i]].name;
        }
    }

    var span = document.createElement('span');
    span.className = 'chain';
    span.innerHTML = html;

    var oldSpans = chain_el.parentElement.querySelectorAll('span.chain');
    for (var j = 0; j < oldSpans.length; j++) {
        oldSpans[j].remove();
    }
    chain_el.parentElement.appendChild(span);
}

function report_filter_op_type_change(op_field, type_field, param1_field, param2_field, field_field){
    var op_id    = getEl(op_field) ? getEl(op_field).value : '';
    var type     = getEl(type_field) ? getEl(type_field).value : '';
    var param1   = getEl(param1_field) ? getEl(param1_field).value : '';
    var param2   = getEl(param2_field) ? getEl(param2_field).value : '';
    var field_id = getEl(field_field) ? getEl(field_field).value : '';
    var op;
    var field;

    if (field_id !== '') {
        field = metadata._fields[field_id];
    }

    if (op_id === ''){
        _hide_row(param1_field); _hide_row(param2_field); _hide_row(type_field);
        _setVal(type_field, 'V');
        _setVal(param1_field, '');
        _setVal(param2_field, '');
    } else {
        op = metadata._search_ops[op_id];

        var p1_html = '';
        var p2_html = '';

        if (type === 'V'){
            if (op.params === 'N'){
                p1_html = renderBasicSingleFieldFor(18, param1_field, param1_field, 1, param1, {});
                p2_html = renderBasicSingleFieldFor(18, param2_field, param2_field, 1, param2, {});
            } else if (field_id !== '' && op.params !== 'T'){
                p1_html = renderBasicSingleFieldFor(field.type, param1_field, param1_field, 1, param1, field);
                p2_html = renderBasicSingleFieldFor(field.type, param2_field, param2_field, 1, param2, field);
            } else {
                // TYPE: 1 (TextField)
                p1_html = renderBasicSingleFieldFor(1, param1_field, param1_field, 1, param1, {});
                p2_html = renderBasicSingleFieldFor(1, param2_field, param2_field, 1, param2, {});
            }
        } else if (type === 'F'){
            var fieldOpts = {field_name_field:'', fixed_entity:'', entity_field:'entity_id'};
            p1_html = renderBasicSingleFieldFor(25, param1_field, param1_field, 1, param1, fieldOpts);
            p2_html = renderBasicSingleFieldFor(25, param2_field, param2_field, 1, param2, fieldOpts);
        } else if (type === 'D'){
            p1_html = createDateIntervalSelector(param1_field, param1);
            p2_html = createDateIntervalSelector(param2_field, param2);
        }

        var p1_row = _get_row(param1_field);
        if (p1_row) p1_row.innerHTML = p1_html;
        var p2_row = _get_row(param2_field);
        if (p2_row) p2_row.innerHTML = p2_html;

        if (op.params === 0){
            _hide_row(type_field); _hide_row(param1_field); _hide_row(param2_field);
            _setVal(type_field, 'V');
            _setVal(param1_field, '');
            _setVal(param2_field, '');
        } else if (op.params === 1 || op.params === 'T' || op.params === 'N'){
            _show_row(type_field); _show_row(param1_field); _hide_row(param2_field);
            _setVal(param2_field, '');
        } else if (op.params === 2){
            _show_row(type_field); _show_row(param1_field); _show_row(param2_field);
        }
    }
}

function _get_row(field_id){
    var el = getEl(field_id);
    return el ? el.closest('tr') : null;
}
function _hide_row(field_id){
    var row = _get_row(field_id);
    if (row) row.style.display = 'none';
}
function _show_row(field_id){
    var row = _get_row(field_id);
    if (row) row.style.display = '';
}
function _setVal(field_id, value){
    var el = getEl(field_id);
    if (el) el.value = value;
}

function createDateIntervalSelector(param_field, param_value){
    var html = '';
    var param_interval = '';
    var param_number = 0;

    param_interval = param_value.slice(0, 1);
    param_number   = parseInt(param_value.slice(1)) || 0;

    // TYPE: 14 (Dropdown) // TYPE: 18 (Number) // TYPE: 9 (Hidden)
    html = global.lang.LBL_TODAY + ' + ';
    html += renderBasicSingleFieldFor(18, param_field + '_number',   param_field + '_number',   1, param_number,   {size:5});
    html += renderBasicSingleFieldFor(14, param_field + '_interval', param_field + '_interval', 1, param_interval, {options:'H:Hours;d:Days;m:Months', sep1:59, sep2:58});
    html += renderBasicSingleFieldFor(9, param_field, param_field, 1, param_value, {});
    while_selector('#' + param_field, function (){
        var listener = function(){
            var iv = getEl(param_field + '_interval');
            var nv = getEl(param_field + '_number');
            var hf = getEl(param_field);
            if (iv && nv && hf) {
                hf.value = iv.value + nv.value;
            }
        };
        var iv = getEl(param_field + '_interval');
        if (iv) iv.addEventListener('change', listener);
        var nv2 = getEl(param_field + '_number');
        if (nv2) nv2.addEventListener('change', listener);
    });

    return html;
}