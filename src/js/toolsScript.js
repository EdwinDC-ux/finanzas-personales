/** ============================================================
 * @description Constantes del sistema
 * =============================================================
*/
const   PATH_ROOT = "/finanzas-personales/"
        MAIN_DEFAULT = `${PATH_ROOT}src/api/Main.php`,
        DATATABLE = { "LANGUAJE": `${PATH_ROOT}src/js/json/datatables-es-MX.json`, "URL_LANGUAJE": { url: `${PATH_ROOT}src/js/json/datatables-es-MX.json` } },
        LOADER = "<div id='loader' class='d-flex justify-content-center align-items-center'><div class='loader-circle'></div></div>";

/** ============================================================
 * @description Ejecuta una petición asincrona en método post.
 * @param {string} url 
 * @param {object} formData 
 * @param {string} type 
 * @returns
 * =============================================================
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

/** ============================================================
 * @description : Genera un FormData para enviar mediante AJAX
 * @param {object} object 
 * @returns
 * =============================================================
*/
function getFormData(object) {
    return Object.keys(object).reduce((formData, key) => {
        formData.append(key, object[key]);

        return formData;
    }, new FormData())
}

/** ============================================================
 * @function comprobarSesion
 * @description Validación de la sesión de un usuario.
 * =============================================================
*/
async function comprobarSesion () {
    const formData = {"Class": 'Sistema','Action': 'comprobarSesion'};

    await RunFetchPost(`${MAIN_DEFAULT}`, formData)
    .then(async sesion => {
        switch (sesion.ESTADO) {
            case 'Inactive':
                if (location.pathname != `${PATH_ROOT}login/`) {
                    location.pathname = `${PATH_ROOT}login/`;
                }

                await redireccionar('login');

                break;
            case 'Active':
                location.pathname == `${PATH_ROOT}login/` ? location.pathname = PATH_ROOT : false;

                await redireccionar('principal');

                break;
        }
    });
}

/** ============================================================
 * @function redireccionar
 * @description Realiza la redirección al elemento que es pasado como parámetro.
 * @param {string} redireccion elemento al cual se redirecciona
 * =============================================================
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