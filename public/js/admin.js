/* ============================================================
   TECNO-MARKET | Panel de administracion
   ============================================================ */

(function () {
    'use strict';

    var IMAGENES = [
        'placeholder.svg',
        'laptop-lenovo.svg',
        'laptop-hp.svg',
        'mouse-logitech.svg',
        'teclado-gamer.svg',
        'monitor-samsung.svg',
        'audifonos-bluetooth.svg',
        'impresora-epson.svg',
        'usb-kingston.svg',
        'ssd-kingston.svg',
        'webcam-logitech.svg',
        'tablet-samsung.svg',
        'parlante-bluetooth.svg',
        'router-tplink.svg',
        'silla-gamer.svg',
        'cable-hdmi.svg'
    ];

    var ESTADOS = ['pendiente', 'procesando', 'enviado', 'entregado', 'cancelado'];
    var productos = [];
    var pedidos = [];

    function iniciar() {
        pintarOpcionesImagen();

        document.getElementById('formProducto').addEventListener('submit', guardar);
        document.getElementById('cancelarEdicion').addEventListener('click', limpiarFormulario);
        document.getElementById('recargar').addEventListener('click', function () {
            cargarProductos();
            cargarPedidos();
            window.toast('Datos actualizados', 'info');
        });

        document.querySelectorAll('[data-pestana]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                cambiarPestana(boton.getAttribute('data-pestana'));
            });
        });

        document.getElementById('cuerpoProductos').addEventListener('click', function (evento) {
            var editar = evento.target.closest('[data-editar]');
            var eliminar = evento.target.closest('[data-eliminar]');

            if (editar) {
                cargarEnFormulario(Number(editar.getAttribute('data-editar')));
            }

            if (eliminar) {
                borrarProducto(Number(eliminar.getAttribute('data-eliminar')));
            }
        });

        document.getElementById('cuerpoPedidos').addEventListener('change', function (evento) {
            var selector = evento.target.closest('[data-estado]');

            if (selector) {
                cambiarEstado(Number(selector.getAttribute('data-estado')), selector.value);
            }
        });

        cargarProductos();
        cargarPedidos();
    }

    function pintarOpcionesImagen() {
        var select = document.getElementById('imagen');
        select.innerHTML = IMAGENES.map(function (archivo) {
            return '<option value="' + archivo + '">' + archivo + '</option>';
        }).join('');
    }

    function cambiarPestana(nombre) {
        document.querySelectorAll('[data-pestana]').forEach(function (boton) {
            boton.classList.toggle('activo', boton.getAttribute('data-pestana') === nombre);
        });

        document.getElementById('tabProductos').classList.toggle('oculto', nombre !== 'productos');
        document.getElementById('tabPedidos').classList.toggle('oculto', nombre !== 'pedidos');
    }

    /* ---------------- Productos ---------------- */

    async function cargarProductos() {
        var cuerpo = document.getElementById('cuerpoProductos');
        cuerpo.innerHTML = '<tr><td colspan="5">Cargando productos...</td></tr>';

        try {
            var data = await API.get('productos?limite=50&orden=id&incluir_inactivos=1');
            productos = data.items;
            pintarProductos();
        } catch (error) {
            cuerpo.innerHTML = '<tr><td colspan="5">' + window.escaparHtml(error.message) + '</td></tr>';
            window.toast(error.message, 'error');
        }
    }

    function pintarProductos() {
        var cuerpo = document.getElementById('cuerpoProductos');

        if (!productos.length) {
            cuerpo.innerHTML = '<tr><td colspan="5">No hay productos registrados.</td></tr>';
            return;
        }

        cuerpo.innerHTML = productos.map(function (producto) {
            return '' +
                '<tr>' +
                    '<td>' +
                        '<div class="celda-producto">' +
                            '<img src="' + window.imagenProducto(producto.imagen) + '" alt="">' +
                            '<div>' +
                                '<strong>' + window.escaparHtml(producto.nombre) + '</strong><br>' +
                                '<span class="texto-suave" style="font-size:.78rem">#' + producto.id_producto + '</span>' +
                            '</div>' +
                        '</div>' +
                    '</td>' +
                    '<td>$' + window.formatearPrecio(producto.precio) + '</td>' +
                    '<td>' + producto.stock + '</td>' +
                    '<td>' +
                        (producto.activo
                            ? '<span class="estado-pedido estado-entregado">Activo</span>'
                            : '<span class="estado-pedido estado-cancelado">Inactivo</span>') +
                    '</td>' +
                    '<td>' +
                        '<div class="acciones">' +
                            '<button class="boton boton--sm" data-editar="' + producto.id_producto + '">Editar</button>' +
                            '<button class="boton boton--sm boton--peligro" data-eliminar="' + producto.id_producto + '">Eliminar</button>' +
                        '</div>' +
                    '</td>' +
                '</tr>';
        }).join('');
    }

    function cargarEnFormulario(id) {
        var producto = productos.find(function (p) { return p.id_producto === id; });

        if (!producto) {
            return;
        }

        document.getElementById('idProducto').value = producto.id_producto;
        document.getElementById('nombre').value = producto.nombre;
        document.getElementById('precio').value = producto.precio;
        document.getElementById('stock').value = producto.stock;
        document.getElementById('imagen').value = producto.imagen || 'placeholder.svg';
        document.getElementById('descripcion').value = producto.descripcion || '';
        document.getElementById('activo').value = producto.activo ? '1' : '0';

        document.getElementById('tituloFormulario').textContent = 'Editar producto #' + producto.id_producto;
        document.getElementById('btnGuardar').textContent = 'Actualizar producto';

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function limpiarFormulario() {
        document.getElementById('formProducto').reset();
        document.getElementById('idProducto').value = '';
        document.getElementById('activo').value = '1';
        document.getElementById('tituloFormulario').textContent = 'Nuevo producto';
        document.getElementById('btnGuardar').textContent = 'Guardar producto';
    }

    async function guardar(evento) {
        evento.preventDefault();

        var id = document.getElementById('idProducto').value;
        var datos = {
            nombre: document.getElementById('nombre').value.trim(),
            precio: Number(document.getElementById('precio').value),
            stock: Number(document.getElementById('stock').value),
            imagen: document.getElementById('imagen').value,
            descripcion: document.getElementById('descripcion').value.trim(),
            activo: Number(document.getElementById('activo').value)
        };

        if (datos.nombre.length < 3) {
            window.toast('El nombre debe tener al menos 3 caracteres', 'error');
            return;
        }

        try {
            if (id) {
                await API.put('productos/' + id, datos);
                window.toast('Producto actualizado', 'ok');
            } else {
                await API.post('productos', datos);
                window.toast('Producto creado', 'ok');
            }

            limpiarFormulario();
            cargarProductos();
        } catch (error) {
            var mensaje = error.message;

            if (error.detalles) {
                mensaje += ': ' + Object.keys(error.detalles).join(', ');
            }

            window.toast(mensaje, 'error', 6000);
        }
    }

    async function borrarProducto(id) {
        var producto = productos.find(function (p) { return p.id_producto === id; });
        var nombre = producto ? producto.nombre : 'este producto';

        if (!window.confirm('¿Eliminar "' + nombre + '"?')) {
            return;
        }

        try {
            await API.del('productos/' + id);
            window.toast('Producto eliminado', 'ok');
            cargarProductos();
        } catch (error) {
            window.toast(error.message, 'error', 6000);
        }
    }

    /* ---------------- Pedidos ---------------- */

    async function cargarPedidos() {
        var cuerpo = document.getElementById('cuerpoPedidos');
        cuerpo.innerHTML = '<tr><td colspan="6">Cargando pedidos...</td></tr>';

        try {
            var data = await API.get('pedidos?limite=30');
            pedidos = data.items;
            pintarPedidos();
        } catch (error) {
            cuerpo.innerHTML = '<tr><td colspan="6">' + window.escaparHtml(error.message) + '</td></tr>';
        }
    }

    function pintarPedidos() {
        var cuerpo = document.getElementById('cuerpoPedidos');

        if (!pedidos.length) {
            cuerpo.innerHTML = '<tr><td colspan="6">No hay pedidos registrados.</td></tr>';
            return;
        }

        cuerpo.innerHTML = pedidos.map(function (pedido) {
            var fecha = new Date(String(pedido.fecha).replace(' ', 'T'));

            return '' +
                '<tr>' +
                    '<td><strong>#' + pedido.id_pedido + '</strong></td>' +
                    '<td>' + window.escaparHtml(pedido.cliente.nombre) + '<br>' +
                        '<span class="texto-suave" style="font-size:.78rem">' +
                        window.escaparHtml(pedido.cliente.correo) + '</span></td>' +
                    '<td>' + fecha.toLocaleString('es-EC') + '</td>' +
                    '<td>' + pedido.items.reduce(function (s, i) { return s + i.cantidad; }, 0) + '</td>' +
                    '<td>$' + window.formatearPrecio(pedido.total) + '</td>' +
                    '<td>' +
                        '<select data-estado="' + pedido.id_pedido + '" style="min-width:140px">' +
                        ESTADOS.map(function (estado) {
                            return '<option value="' + estado + '"' +
                                (estado === pedido.estado ? ' selected' : '') + '>' + estado + '</option>';
                        }).join('') +
                        '</select>' +
                        '<div class="mt-1"><span class="estado-pedido estado-' + pedido.estado + '">' +
                        pedido.estado + '</span></div>' +
                    '</td>' +
                '</tr>';
        }).join('');
    }

    async function cambiarEstado(id, estado) {
        try {
            await API.patch('pedidos/' + id + '/estado', { estado: estado });
            window.toast('Pedido #' + id + ' actualizado a ' + estado, 'ok');
            cargarPedidos();
        } catch (error) {
            window.toast(error.message, 'error');
            cargarPedidos();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('formProducto')) {
            iniciar();
        }
    });
})();
