'use strict';

// Variables
let html = '', formData = '', requestData = '', modalId = '', modalData = '';

$(async () => {
    // ========== EVENTOS ========== //
    const BODY = $('body');

    // Inicio de sesión
    BODY.on('click','#btn_ingresar', function () {validarDatosInicio($(this));});
});

/**
 * @function    validarDatosInicio
 * @description Valida los datos de inicio de sesión de un usuario.
 * @param {*} $btn Elemento Jquery del botón que disparó el evento 
*/
async function validarDatosInicio ($btn) {
    let nameFormData = $btn.data().formulario;

    // Validación de datos obligatorios
    if(!fnWasValidate(nameFormData)) {
        fnAlertError('Faltan campos obligatorios por llenar');
        return;
    }

    // todo: Encriptar contraseña para enviar el dato
    // todo: Conectar a base de datos para una validación más profunda de datos (e iniciar sesión).
}