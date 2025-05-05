/**
 * @description Constantes del sistema
*/
const   PATH_ROOT = "/finanzas-personales/"
        MAIN_DEFAULT = `${PATH_ROOT}src/api/Main.php`,
        DATATABLE = { "LANGUAJE": `${PATH_ROOT}src/js/json/datatables-es-MX.json`, "URL_LANGUAJE": { url: `${PATH_ROOT}src/js/json/datatables-es-MX.json` } },
        LOADER = "<div id='loader' class='d-flex justify-content-center align-items-center'><div class='loader-circle'></div></div>",
        EMAIL_PATTERN = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

/**
 * @description Ejecuta una petición asincrona en método post.
 * @param {string} url 
 * @param {object} formData 
 * @param {string} type 
 * @returns
*/
async function RunFetchPost(url = '', formData = {}, type = 'json') {
    return await new Promise((resolve, reject) => {
        if (url.trim() == '') {
            reject('Por favor envíe la url para realizar el fetch');
            return
        }

        fetch(`${url}?cache=V${Math.random()}`, { method: 'POST', body: getFormData(formData) })
            .then(response => {
                if (response.ok) {
                    if (type == 'json') {
                        resolve(response.json())
                    }
                    if (type == 'html' || type == 'text') {
                        resolve(response.text())
                    }
                    if (type == 'blob') {
                        resolve(response.blob())
                    }
                } else {
                    if (response.status == 401) {
                        alert(`Sesion Terminada - ${response.statusText}`);
                        window.location.href = PATH_ROOT;
                    }

                    reject(`No hemos podidio recuperar la respuesta. El código de respuesta del servidor es:${response.status} - ${response.statusText}`);
                }
            })
            .then(request => resolve(request))
            .catch(error => console.error(`ERROR: ${error}`));
    });
}

/**
 * @description : Genera un FormData para enviar mediante AJAX
 * @param {object} object 
 * @returns
*/
function getFormData(object) {
    return Object.keys(object).reduce((formData, key) => {
        formData.append(key, object[key]);

        return formData;
    }, new FormData())
}

/**
 * @function comprobarSesion
 * @description Validación de la sesión de un usuario.
*/
async function comprobarSesion () {
    const formData = {"Class": 'Sistema','Action': 'comprobarSesion'};

    await RunFetchPost(`${MAIN_DEFAULT}`, formData)
    .then(async sesion => {
        switch (sesion.ESTADO) {
            case 'Inactive':
                if (location.pathname != `${PATH_ROOT}login/` && location.pathname != `${PATH_ROOT}registro/` )
                    location.pathname = `${PATH_ROOT}login/`;

                location.pathname == `${PATH_ROOT}login/` ? await redireccionar('login') : await redireccionar('registro');

                break;
            case 'Active':
                location.pathname == `${PATH_ROOT}login/` ? location.pathname = PATH_ROOT : false;

                await redireccionar('principal');

                break;
        }
    });
}

/**
 * Realiza la redirección al elemento que es pasado como parámetro.
 * @param {string} redireccion elemento al cual se redirecciona
 */
async function redireccionar(redireccion) {
    let nuevoScript, numeroAleatorio;

    // Se obtiene el template de redirección
    await fetch(`${PATH_ROOT}src/html-required/${redireccion}.html`)
    .then(response=> response.text())
    .then(data=> $('#pagina_principal').html(data));

    // Nuevo script incrustado en el DOM
    nuevoScript = document.createElement('script');
    numeroAleatorio = Math.floor(Math.random() * 1000);
    nuevoScript.src = `${PATH_ROOT}src/js/modules/${redireccion}/${redireccion}.js?cache=${numeroAleatorio}`;
    nuevoScript.type = 'module';
    document.body.appendChild(nuevoScript);
}

/**
 * Valida el formulario utilizando bootstrap. Los campos deben tener el atributo required
 * @param {string} formId - id del formulario a validar 
 * @param {boolean} disable -  indica si elformulario se debe deshabilitar o no. Default false
 * @return {boolean} true si el formulario es válido, false en caso contrario
*/
function fnWasValidate(formId, disable = false) {
    const $form = $(`#${formId}`);
    let validated = true;

    //Agregar la clase was-validated si no está presente
    if (!$form.hasClass('was-validated')) $form.addClass('was-validated');

    //Validación de campos de texto, archivos, contraseñas y areas de texto requeridas
    $form.find("input:text, input:file, input:password, textarea").filter("[required]:visible").each(function () {
        if($(this).val().trim() === "") validated = false;
    });

    // Validación para campos numéricos requeridos
    $form.find("input[type='number']").filter("[required]").each(function () {
        if ($(this).val() < 0 || $(this).val() === null) validated = false;
    });

    // Validación para selects requeridos
    $form.find("select").filter("[required]").each(function () {
        if ($(this).val() === null) validated = false;
    });

    // Validación para campos de tipo email
    $form.find("input[type='email']").filter("[required]").each(function () {
        if ($(this).val().trim() === "" || !EMAIL_PATTERN.test($(this).val())) validated = false;
    });

    if (validated && disable) fnDisabledForm(formId);

    return validated;
}

/**
 * Deshabilita o habilita los campos de un formulario y opcionalmente limpia los valores.
 * @param {string} formId - ID del formulario a deshabilitar.
 * @param {boolean} disable - Indica si se deben deshabilitar (true) o habilitar (false) los campos. Por defecto true.
 * @param {boolean} clearForm - Indica si se deben limpiar los valores de los campos (solo aplica si disable es true). Por defecto false.
 */
function fnDisabledForm (formId, disable = true, clearForm = false) {
    let $form = $(`#${formId}`);

    // Selección de tipos de input que deben ser tratados como texto
    $form.find("input[type='text'], input[type='file'], input[type='password'], textarea").each(function () {
        $(this).prop('disabled', disable);
        if (disable && clearForm) $(this).val('');
    });

    // Selección de otros tipos de input que no son texto
    $form.find("input[type='number'], input[type='email'], input[type='date']").each(function () {
        $(this).prop('disabled', disable);
        if (disable && clearForm) $(this).val('');
    });

    // Selección de selects y botones con clase .btn
    $form.find("select, button.btn").each(function () {
        $(this).prop('disabled', disable);
    });
};

/**
 * Ventana modal para mensajes de error al usuario
 * @param {string} message - Mensaje a desplegarle al usuario.
 */
function fnAlertError (message = "Ocurrio un error #-0001") {
    $.alert({
        title: '',
        type: 'orange',
        icon: 'fas fa-exclamation-triangle',
        content: message,
        typeAnimated: true,
    });
}

/**
 * Ventana modal para mensajes de éxito al usuario
 * @param {string} message - Mensaje a desplegarle al usuario.
 */
function fnAlertSuccess (message = "Exitoso!") {
    $.alert({
        title: 'Listo',
        type: 'blue',
        icon: 'fas fa-check',
        content: message,
    });
}

/**
 * Ventana modal para mensajes que realizan una acción al aceptarlos
 * @param {string} message - Mensaje a desplegarle al usuario.
 * @param {function} func - Función a ejecutar al aceptar el mensaje
 */
function fnAlertFunction (message, func) {
    var alert = $.confirm({
        title: 'Listo!',
        type: 'dark',
        content: message,
        buttons: {
            aceptar: {
                btnClass: 'btn-blue',
                action: function(){
                    if (typeof func === 'function') {
                        func(); // Llamamos a la función que recibimos como argumento
                    }
                }
            },
        }
    });
    return alert;
}

/**
 * Ventana modal para mensajes al usuario que recarga la página al aceptarlos
 * @param {string} message - Mensaje a desplegarle al usuario.
 */
function fnAlertReload (message = "Datos Guardados!") {
    var alert =  $.confirm({
        title: 'Listo!',
        type: 'dark',
        content: message,
        buttons: {
            aceptar: function () {
                location.reload();
            },
        }
    });
    return alert;
}