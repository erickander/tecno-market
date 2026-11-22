/* ============================================================
   TECNO-MARKET | Cliente HTTP y utilidades compartidas
   ============================================================ */

(function () {
    'use strict';

    // Ruta relativa: funciona en XAMPP (/tecno_market/) y en Docker (/)
    var API_BASE = new URL('../api/v1/', window.location.href).href;

    var CLAVE_CARRITO = 'tecno_carrito';

    function formatearPrecio(valor) {
        return Number(valor).toLocaleString('es-EC', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function escaparHtml(texto) {
        return String(texto === null || texto === undefined ? '' : texto)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function imagenProducto(nombreArchivo) {
        var valor = nombreArchivo === null || nombreArchivo === undefined
            ? ''
            : String(nombreArchivo).trim();

        // Si es una URL externa, se usa tal cual
        if (/^https?:\/\//i.test(valor)) {
            return valor;
        }

        // Si es una ruta ya armada (por ejemplo img/...) se respeta
        if (/^(img\/|\.\.?\/)/i.test(valor)) {
            return valor;
        }

        var archivo = valor !== '' ? valor : 'placeholder.svg';

        return 'img/productos/' + archivo;
    }

    async function pedir(ruta, opciones) {
        opciones = opciones || {};

        var url = /^https?:/i.test(ruta) ? ruta : API_BASE + String(ruta).replace(/^\//, '');

        var config = {
            method: opciones.method || 'GET',
            headers: Object.assign({ Accept: 'application/json' }, opciones.headers || {})
        };

        if (opciones.body !== undefined && opciones.body !== null) {
            if (typeof opciones.body === 'string') {
                config.body = opciones.body;
            } else {
                config.body = JSON.stringify(opciones.body);
                config.headers['Content-Type'] = 'application/json';
            }
        }

        var token = localStorage.getItem('tecno_token');
        if (token) {
            config.headers.Authorization = 'Bearer ' + token;
        }

        var respuesta;

        try {
            respuesta = await fetch(url, config);
        } catch (e) {
            throw new Error('No se pudo conectar con la API. Verifica que Apache y MySQL esten activos.');
        }

        var texto = await respuesta.text();
        var json = null;

        if (texto) {
            try {
                json = JSON.parse(texto);
            } catch (e) {
                json = null;
            }
        }

        if (!respuesta.ok) {
            var error = new Error(
                (json && json.error && json.error.mensaje) || 'Error ' + respuesta.status
            );
            error.codigo = respuesta.status;
            error.detalles = (json && json.error && json.error.errores) || null;
            throw error;
        }

        if (respuesta.status === 204) {
            return null;
        }

        return json && Object.prototype.hasOwnProperty.call(json, 'data') ? json.data : json;
    }

    var API = {
        base: API_BASE,
        get: function (ruta) { return pedir(ruta); },
        post: function (ruta, cuerpo) { return pedir(ruta, { method: 'POST', body: cuerpo }); },
        put: function (ruta, cuerpo) { return pedir(ruta, { method: 'PUT', body: cuerpo }); },
        patch: function (ruta, cuerpo) { return pedir(ruta, { method: 'PATCH', body: cuerpo }); },
        del: function (ruta) { return pedir(ruta, { method: 'DELETE' }); }
    };

    /* ---------------- Carrito (localStorage) ---------------- */

    function leerCarrito() {
        try {
            var datos = JSON.parse(localStorage.getItem(CLAVE_CARRITO) || '[]');
            return Array.isArray(datos) ? datos : [];
        } catch (e) {
            return [];
        }
    }

    function guardarCarrito(items) {
        localStorage.setItem(CLAVE_CARRITO, JSON.stringify(items));
        actualizarContador();
    }

    var Carrito = {
        items: leerCarrito,

        agregar: function (producto, cantidad) {
            cantidad = Math.max(1, parseInt(cantidad, 10) || 1);
            var items = leerCarrito();
            var existente = items.find(function (i) {
                return i.id_producto === producto.id_producto;
            });
            var disponible = Number(producto.stock);

            var enCarrito = existente ? existente.cantidad : 0;

            if (cantidad + enCarrito > disponible) {
                throw new Error('Solo hay ' + disponible + ' unidades disponibles de ' + producto.nombre);
            }

            if (existente) {
                existente.cantidad += cantidad;
            } else {
                items.push({
                    id_producto: producto.id_producto,
                    nombre: producto.nombre,
                    precio: Number(producto.precio),
                    imagen: producto.imagen,
                    stock: disponible,
                    cantidad: cantidad
                });
            }

            guardarCarrito(items);
        },

        quitar: function (idProducto) {
            guardarCarrito(leerCarrito().filter(function (i) {
                return i.id_producto !== idProducto;
            }));
        },

        cambiarCantidad: function (idProducto, cantidad) {
            cantidad = parseInt(cantidad, 10) || 0;
            var items = leerCarrito();
            var item = items.find(function (i) {
                return i.id_producto === idProducto;
            });

            if (!item) {
                return;
            }

            if (cantidad <= 0) {
                this.quitar(idProducto);
                return;
            }

            if (cantidad > item.stock) {
                throw new Error('Solo hay ' + item.stock + ' unidades disponibles');
            }

            item.cantidad = cantidad;
            guardarCarrito(items);
        },

        total: function () {
            return leerCarrito().reduce(function (suma, item) {
                return suma + item.precio * item.cantidad;
            }, 0);
        },

        contar: function () {
            return leerCarrito().reduce(function (suma, item) {
                return suma + item.cantidad;
            }, 0);
        },

        vaciar: function () {
            localStorage.removeItem(CLAVE_CARRITO);
            actualizarContador();
        }
    };

    function actualizarContador() {
        var contador = document.getElementById('contadorCarrito');

        if (contador) {
            contador.textContent = String(Carrito.contar());
        }
    }

    /* ---------------- Notificaciones ---------------- */

    function toast(mensaje, tipo, duracion) {
        tipo = tipo || 'info';
        duracion = duracion || 4200;

        var contenedor = document.querySelector('.toasts');

        if (!contenedor) {
            contenedor = document.createElement('div');
            contenedor.className = 'toasts';
            document.body.appendChild(contenedor);
        }

        var elemento = document.createElement('div');
        elemento.className = 'toast toast--' + tipo;
        elemento.innerHTML = '<span class="toast__punto"></span><span>' + escaparHtml(mensaje) + '</span>';

        contenedor.appendChild(elemento);

        setTimeout(function () {
            elemento.style.opacity = '0';
            elemento.style.transform = 'translateY(6px)';
            elemento.style.transition = '160ms ease';
            setTimeout(function () { elemento.remove(); }, 180);
        }, duracion);
    }

    document.addEventListener('DOMContentLoaded', actualizarContador);

    window.API = API;
    window.Carrito = Carrito;
    window.formatearPrecio = formatearPrecio;
    window.escaparHtml = escaparHtml;
    window.imagenProducto = imagenProducto;
    window.toast = toast;
    window.actualizarContador = actualizarContador;
})();
