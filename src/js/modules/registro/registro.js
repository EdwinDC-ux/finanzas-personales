'use strict';

// Variables
let html = '', formData = '', requestData = '', modalId = '', modalData = '';

$(async () => {
    // ========== EVENTOS ========== //
    const BODY = $('body');

    // Inicio de sesión
    BODY.on('click','#btn_registrar', function () {validarDatosRegistro($(this));});
});

/**
 * @function    validarDatosRegistro
 * @description Valida los datos de inicio de sesión de un usuario.
 * @param {*} $btn Elemento Jquery del botón que disparó el evento 
*/
async function validarDatosRegistro ($btn) {
    let nameFormData = $btn.data().formulario;

    // Validación de datos obligatorios
    if(!fnWasValidate(nameFormData)) {
        fnAlertError('Faltan campos obligatorios por llenar');
        return;
    }
    /**
     * todo: Validar que la contraseña cumpla con parámetros
     * todo: Validar que contraseña y confirmación sean iguales
     * todo: Encriptar contraseña para enviar el dato.
     * todo: Convertir en mayusculas los campos excepto usuarios y contraseñas
     * todo: Validar con base de datos que el usuario no exista y registrar al usuario
    */
}