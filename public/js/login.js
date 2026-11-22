/* ============================================================
   TECNO-MARKET | Inicio de sesion
   ============================================================ */

(function () {
    'use strict';

    function iniciar() {
        var formulario = document.getElementById('formLogin');
        var boton = document.getElementById('btnEntrar');
        var alerta = document.getElementById('alerta');
        var destino = new URLSearchParams(window.location.search).get('destino') || 'admin.html';

        // Si ya hay sesion, no tiene sentido mostrar el formulario
        if (localStorage.getItem('tecno_token')) {
            window.location.replace(destino);
            return;
        }

        function mostrarError(mensaje) {
            alerta.textContent = mensaje;
            alerta.classList.remove('oculto');
        }

        formulario.addEventListener('submit', async function (evento) {
            evento.preventDefault();

            alerta.classList.add('oculto');

            var usuario = document.getElementById('nombre_usuario').value.trim();
            var contrasena = document.getElementById('contrasena').value;

            if (usuario.length < 3) {
                mostrarError('Escribe tu nombre de usuario');
                return;
            }

            if (contrasena.length < 1) {
                mostrarError('Escribe tu contraseña');
                return;
            }

            boton.disabled = true;
            boton.textContent = 'Validando...';

            try {
                var data = await API.post('auth/login', {
                    nombre_usuario: usuario,
                    contrasena: contrasena
                });

                localStorage.setItem('tecno_token', data.token);
                localStorage.setItem('tecno_usuario', JSON.stringify(data.usuario));

                window.location.replace(destino);
            } catch (error) {
                mostrarError(error.message);
                boton.disabled = false;
                boton.textContent = 'Entrar';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('formLogin')) {
            iniciar();
        }
    });
})();
