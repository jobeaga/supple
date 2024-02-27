function abcjs_render(canvas_id, m, l, k, notes, words){
    if (words === undefined) words = '';
    ABCJS.renderAbc(canvas_id, "M:"+ m + "\nL: "+ l +"\nK: "+ k + "\n" + notes +"\nw: "+ words  , {
        staffwidth: $('#'+canvas_id).width(),
        responsive: "resize",
    });
    $('#'+canvas_id).css('overflow', '').css('height', '');
}

function abcjs_editor(canvas_id, element_m, element_l, element_k, element_notes, element_words){
    var ch = function(){
        abcjs_update(canvas_id, element_m, element_l, element_k, element_notes, element_words);
    };
    $('#'+element_m).change(ch);
    $('#'+element_m).keyup(ch);
    $('#'+element_l).change(ch);
    $('#'+element_l).keyup(ch);
    $('#'+element_k).change(ch);
    $('#'+element_k).keyup(ch);
    $('#'+element_notes).change(ch);
    $('#'+element_notes).keyup(ch);
    $('#'+element_words).change(ch);
    $('#'+element_words).keyup(ch);
    ch();
}

function abcjs_update(canvas_id, element_m, element_l, element_k, element_notes, element_words){
    var m = $('#'+element_m).val();
    var l = $('#'+element_l).val();
    var k = $('#'+element_k).val();
    var words = $('#'+element_words).val();
    var notes = $('#'+element_notes).val();
    abcjs_render(canvas_id, m, l, k, notes, words);
}

