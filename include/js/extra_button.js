
function extra_button_click(button, record_id){
    // console.log(button);
    var fields = document.querySelectorAll('#record'+record_id+' .field');
    var cont = document.createElement('div');
    var p = getCoords(button);
    cont.className = 'extra_container';
    cont.innerHTML = '<input type=button value=x class="extra_button_close" onclick="remove_extra_button(this, \'' + record_id + '\')">';
    cont.style.top = p.top+'px';
    cont.style.right = (window.innerWidth - p.left)+'px';
    cont.id = 'extra_container'+record_id;
    // console.log(cont);
    document.body.append(cont);    

    fields.forEach(function (f){
        if (f.offsetParent === null){
            // console.log(f.innerHTML);
            cont.appendChild(f);
            // alert('no-visible ' + f.id);
        }
    });
    
}

function remove_extra_button(close_button, record_id){
    var p = close_button.parentNode;
    var r = document.getElementById('record'+record_id);
    var pb = r.querySelector('.post.buttons');
    r.removeChild(pb);
    p.querySelectorAll('.field').forEach(function (f){
        r.appendChild(f);
    });
    r.appendChild(pb);
    p.remove();
}

function getCoords(elem) { // crossbrowser version
    var box = elem.getBoundingClientRect();

    var body = document.body;
    var docEl = document.documentElement;

    var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
    var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;

    var clientTop = docEl.clientTop || body.clientTop || 0;
    var clientLeft = docEl.clientLeft || body.clientLeft || 0;

    var top  = box.top +  scrollTop - clientTop;
    var left = box.left + scrollLeft - clientLeft;

    return { top: Math.round(top), left: Math.round(left) };
}