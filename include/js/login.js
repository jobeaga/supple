
function getAuthKey(){
    var r = '';
    if (isAuthKeySet()){
        r = localStorage.getItem('auth_key');
    } else {
        r = uuidv4();
        localStorage.setItem('auth_key', r);
    }
    return r;
}

function isAuthKeySet(){
    return (localStorage.getItem('auth_key') != null);
}

function loginFormInit(login_form){

    var lf = document.getElementById(login_form);
    var auth_key = '';

    // try the async login
    if (isAuthKeySet()){
        // GET
        auth_key = getAuthKey();
        var url = script_name + '?action=login';
        var post = 'auth_key='+auth_key;
        // SEND THE AUTH_KEY
        basicAjaxPost(url, post, function(response){
            // IF LOGIN IS SUCCESSFULL, RELOAD
            if (parseInt(response) != -1){
                document.getElementById('_screen_lock').style.display = 'block';
                window.location.reload();
            } else {
                document.getElementById('_screen_lock').style.display = 'none';
                document.getElementById('main').style.display = '';
            }
        });
        
    } else {
        // CREATE!
        auth_key = getAuthKey();
        document.getElementById('_screen_lock').style.display = 'none';
        document.getElementById('main').style.display = '';
    }

    // save the key
    var hid = document.createElement('INPUT');
    hid.type = 'hidden';
    hid.name = 'auth_key';
    hid.value = auth_key;
    lf.appendChild(hid);

}

function basicAjaxPost(url, post, callback, error){
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function (){
        if (xhr.status == 200 && xhr.readyState == 4){
            callback(xhr.responseText);
        } else {
            // TODO: ERROR CASES
        }
    }
    xhr.send(post);
}

function uuidv4() {
    return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
      (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
    );
  }