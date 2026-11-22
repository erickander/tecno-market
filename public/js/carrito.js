/* ============================================================
   TECNO-MARKET | Carrito y confirmacion de pedido
   ============================================================ */

(function () {
    'use strict';

    function iniciar() {
        var contenedor = document.getElementById('itemsCarrito');
        var contadorItems = document.getElementById('contadorItems');
        var resumen = document.getElementById('resumenCarrito');
        var subtotal = document.getElementById('subtotal');
        var total = document.getElementById('total');
        var formulario = document.getElementById('formCheckout');
        var boton = document.getElementById('btnConfirmar');
        var exito = document.getElementById('pedidoExito');
        var panelCheckout = document.getElementById('panelCheckout');

        function render() {
            var items = Carrito.items();

            contadorItems.textContent = String(Carrito.contar());

            if (!items.length) {
                contenedor.innerHTML = '<div class="estado">' +
                    '<strong>Tu carrito está vacío</strong>' +
                    'Agrega productos desde el <a href="index.html">catálogo</a>.</div>';
                resumen.textContent = 'No hay productos en el carrito.';
                subtotal.textContent = '$0.00';
                total.textContent = '0.00';
                boton.disabled = true;
                return;
            }

            boton.disabled = false;
            resumen.textContent = items.length + ' producto(s) listo(s) para confirmar.';

            contenedor.innerHTML = items.map(function (item) {
                return '' +
                    '<div class="item" data-item="' + item.id_producto + '">' +
                        '<div class="item__imagen">' +
                            '<img src="' + window.imagenProducto(item.imagen) + '" alt="' +
                                window.escaparHtml(item.nombre) + '">' +
                        '</div>' +
                        '<div>' +
                            '<div class="item__nombre">' + window.escaparHtml(item.nombre) + '</div>' +
                            '<div class="item__precio">$' + window.formatearPrecio(item.precio) +
                                ' · ' + item.stock + ' disponibles</div>' +
                        '</div>' +
                        '<div class="item__acciones">' +
                            '<button class="boton boton--sm" data-menos="' + item.id_producto + '">−</button>' +
                            '<input class="item__cantidad" type="number" min="1" max="' + item.stock +
                                '" value="' + item.cantidad + '" data-cantidad="' + item.id_producto + '">' +
                            '<button class="boton boton--sm" data-mas="' + item.id_producto + '">+</button>' +
                            '<button class="boton boton--sm boton--peligro" data-quitar="' + item.id_producto + '">×</button>' +
                        '</div>' +
                    '</div>';
            }).join('');

            actualizarTotales();
        }

        function actualizarTotales() {
            subtotal.textContent = '$' + window.formatearPrecio(Carrito.total());
            total.textContent = window.formatearPrecio(Carrito.total());
        }

        contenedor.addEventListener('click', function (evento) {
            var botonMenos = evento.target.closest('[data-menos]');
            var botonMas = evento.target.closest('[data-mas]');
            var botonQuitar = evento.target.closest('[data-quitar]');

            try {
                if (botonMenos) {
                    var idMenos = Number(botonMenos.getAttribute('data-menos'));
                    var itemMenos = Carrito.items().find(function (i) { return i.id_producto === idMenos; });
                    if (itemMenos) {
                        Carrito.cambiarCantidad(idMenos, itemMenos.cantidad - 1);
                    }
                } else if (botonMas) {
                    var idMas = Number(botonMas.getAttribute('data-mas'));
                    var itemMas = Carrito.items().find(function (i) { return i.id_producto === idMas; });
                    if (itemMas) {
                        Carrito.cambiarCantidad(idMas, itemMas.cantidad + 1);
                    }
                } else if (botonQuitar) {
                    Carrito.quitar(Number(botonQuitar.getAttribute('data-quitar')));
                } else {
                    return;
                }

                render();
            } catch (error) {
                window.toast(error.message, 'error');
            }
        });

        contenedor.addEventListener('change', function (evento) {
            var entrada = evento.target.closest('[data-cantidad]');

            if (!entrada) {
                return;
            }

            try {
                Carrito.cambiarCantidad(Number(entrada.getAttribute('data-cantidad')), entrada.value);
                render();
            } catch (error) {
                window.toast(error.message, 'error');
                render();
            }
        });

        formulario.addEventListener('submit', async function (evento) {
            evento.preventDefault();

            var items = Carrito.items();

            if (!items.length) {
                window.toast('El carrito está vacío', 'error');
                return;
            }

            var cliente = {
                nombre: document.getElementById('nombre').value.trim(),
                correo: document.getElementById('correo').value.trim(),
                telefono: document.getElementById('telefono').value.trim(),
                direccion: document.getElementById('direccion').value.trim()
            };

            if (cliente.nombre.length < 3) {
                window.toast('Escribe tu nombre completo (mínimo 3 caracteres)', 'error');
                return;
            }

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cliente.correo)) {
                window.toast('Escribe un correo electrónico válido', 'error');
                return;
            }

            if (cliente.telefono.replace(/\D/g, '').length < 7) {
                window.toast('Escribe un teléfono válido (mínimo 7 dígitos)', 'error');
                return;
            }

            boton.disabled = true;
            boton.textContent = 'Procesando...';

            try {
                var pedido = await API.post('pedidos', {
                    cliente: cliente,
                    items: items.map(function (item) {
                        return { id_producto: item.id_producto, cantidad: item.cantidad };
                    })
                });

                Carrito.vaciar();

                document.getElementById('exitoNumero').textContent = 'Pedido #' + pedido.id_pedido;
                document.getElementById('exitoCliente').textContent = pedido.cliente.nombre;
                document.getElementById('exitoEstado').textContent = pedido.estado;
                document.getElementById('exitoTotal').textContent = window.formatearPrecio(pedido.total);

                panelCheckout.classList.add('oculto');
                exito.classList.remove('oculto');
                window.scrollTo({ top: 0, behavior: 'smooth' });
                window.toast('Pedido #' + pedido.id_pedido + ' registrado', 'ok');
            } catch (error) {
                window.toast(error.message, 'error', 6000);
            } finally {
                boton.disabled = false;
                boton.textContent = 'Confirmar pedido';
                render();
            }
        });

        render();
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('itemsCarrito')) {
            iniciar();
        }
    });
})();
