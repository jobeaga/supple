

function colorpicker_init(element_id){

    var input = document.getElementById(element_id);
    var picker = document.getElementById(element_id + '_picker');
    var preview = document.getElementById(element_id + '_preview');

    if (!input || !picker || !preview) return;

    // Evitar inicializar dos veces.
    if (input.dataset.colorpickerInitialized == 'true') return;
    input.dataset.colorpickerInitialized = 'true';

    // Actualizar desde el input de texto.
    function updateFromText(){

        var value = input.value.trim();

        if (value == ''){
            input.setCustomValidity('');
            preview.style.backgroundColor = 'transparent';
            return;
        }

        if (!/^[0-9a-fA-F]{6}$/.test(value)){
            input.setCustomValidity('Ingresá un color hexadecimal válido.');
            return;
        }

        input.setCustomValidity('');

        var color = '#' + value;

        picker.value = color;
        preview.style.backgroundColor = color;

    }

    // Actualizar desde el selector nativo.
    picker.addEventListener('input', function(){

        var color = picker.value.toLowerCase();

        input.value = color.substring(1);
        input.setCustomValidity('');

        preview.style.backgroundColor = color;

    });

    // Actualizar al escribir.
    input.addEventListener('input', updateFromText);

    // Normalizar al terminar de editar.
    input.addEventListener('change', function(){

        updateFromText();

        if (/^[0-9a-fA-F]{6}$/.test(input.value)){
            input.value = input.value.toLowerCase();
        }

    });

    // Inicializar.
    updateFromText();

}
