/* ============================================================
   TECNO-MARKET | Catalogo y detalle de producto
   ============================================================ */

(function () {
    'use strict';

    var estado = {
        pagina: 1,
        limite: 8,
        busqueda: '',
        orden: 'id',
        total: 0,
        paginas: 0
    };

    function imagen(nombre) {
        return window.imagenProducto(nombre);
    }

    function claseStock(stock) {
        if (Number(stock) <= 0) return 'etiqueta etiqueta--agotado';
        if (Number(stock) <= 5) return 'etiqueta etiqueta--bajo';
        return 'etiqueta etiqueta--ok';
    }

    function textoStock(stock) {
        var n = Number(stock);
        if (n <= 0) return 'Agotado';
        if (n === 1) return '1 disponible';
        return n + ' disponibles';
    }

    /* ---------------- Catalogo ---------------- */

    function iniciarCatalogo() {
        var grilla = document.getElementById('grilla');
        var paginacion = document.getElementById('paginacion');
        var resumen = document.getElementById('resumenCatalogo');
        var entrada = document.getElementById('busqueda');
        var orden = document.getElementById('orden');
        var temporizador = null;

        async function cargar() {
            grilla.innerHTML = '<div class="cargando" style="grid-column:1/-1">' +
                '<div class="spinner"></div><span>Consultando la API...</span></div>';
            paginacion.innerHTML = '';

            try {
                var data = await API.get(
                    'productos?pagina=' + estado.pagina +
                    '&limite=' + estado.limite +
                    '&orden=' + encodeURIComponent(estado.orden) +
                    '&q=' + encodeURIComponent(estado.busqueda)
                );

                estado.total = data.meta.total;
                estado.paginas = data.meta.paginas;

                resumen.textContent = estado.busqueda
                    ? data.meta.total + ' resultado(s) para "' + estado.busqueda + '"'
                    : data.meta.total + ' productos disponibles';

                if (!data.items.length) {
                    grilla.innerHTML = '<div class="estado" style="grid-column:1/-1">' +
                        '<strong>Sin resultados</strong>' +
                        'No encontramos productos con ese criterio. Prueba con otra búsqueda.</div>';
                    return;
                }

                grilla.innerHTML = data.items.map(tarjeta).join('');

                grilla.querySelectorAll('[data-agregar]').forEach(function (boton) {
                    boton.addEventListener('click', function () {
                        agregarDesdeCatalogo(boton);
                    });
                });

                pintarPaginacion();
            } catch (error) {
                grilla.innerHTML = '<div class="estado" style="grid-column:1/-1">' +
                    '<strong>No se pudo cargar el catálogo</strong>' +
                    window.escaparHtml(error.message) + '</div>';
                toast(error.message, 'error');
            }
        }

        function tarjeta(producto) {
            var agotado = Number(producto.stock) <= 0;

            return '' +
                '<article class="tarjeta">' +
                    '<a class="tarjeta__imagen" href="producto.html?id=' + producto.id_producto + '">' +
                        '<img src="' + imagen(producto.imagen) + '" alt="' + window.escaparHtml(producto.nombre) + '" loading="lazy" ' +
                            'onerror="this.onerror=null;this.src=\'img/productos/placeholder.svg\'">' +
                        '<span class="' + claseStock(producto.stock) + '">' + textoStock(producto.stock) + '</span>' +
                    '</a>' +
                    '<div class="tarjeta__cuerpo">' +
                        '<h3 class="tarjeta__nombre">' +
                            '<a href="producto.html?id=' + producto.id_producto + '">' +
                                window.escaparHtml(producto.nombre) +
                            '</a>' +
                        '</h3>' +
                        '<p class="tarjeta__desc">' + window.escaparHtml(producto.descripcion || '') + '</p>' +
                        '<div class="tarjeta__pie">' +
                            '<span class="precio">' + window.formatearPrecio(producto.precio) + '</span>' +
                            '<button class="boton boton--primario boton--sm" data-agregar="' + producto.id_producto + '"' +
                                (agotado ? ' disabled' : '') + '>' +
                                (agotado ? 'Agotado' : 'Agregar') +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</article>';
        }

        function agregarDesdeCatalogo(boton) {
            var id = Number(boton.getAttribute('data-agregar'));
            boton.disabled = true;

            API.get('productos/' + id)
                .then(function (producto) {
                    Carrito.agregar(producto, 1);
                    toast(producto.nombre + ' agregado al carrito', 'ok');
                })
                .catch(function (error) {
                    toast(error.message, 'error');
                })
                .finally(function () {
                    boton.disabled = false;
                });
        }

        function pintarPaginacion() {
            if (estado.paginas <= 1) {
                paginacion.innerHTML = '';
                return;
            }

            var botones = [];

            botones.push('<button class="boton boton--sm" data-pagina="' + (estado.pagina - 1) + '"' +
                (estado.pagina <= 1 ? ' disabled' : '') + '>Anterior</button>');

            for (var i = 1; i <= estado.paginas; i++) {
                botones.push('<button class="boton boton--sm' + (i === estado.pagina ? ' activo' : '') +
                    '" data-pagina="' + i + '">' + i + '</button>');
            }

            botones.push('<button class="boton boton--sm" data-pagina="' + (estado.pagina + 1) + '"' +
                (estado.pagina >= estado.paginas ? ' disabled' : '') + '>Siguiente</button>');

            paginacion.innerHTML = botones.join('');

            paginacion.querySelectorAll('[data-pagina]').forEach(function (boton) {
                boton.addEventListener('click', function () {
                    var destino = Number(boton.getAttribute('data-pagina'));

                    if (destino < 1 || destino > estado.paginas || destino === estado.pagina) {
                        return;
                    }

                    estado.pagina = destino;
                    cargar();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            });
        }

        entrada.addEventListener('input', function () {
            clearTimeout(temporizador);
            temporizador = setTimeout(function () {
                estado.busqueda = entrada.value.trim();
                estado.pagina = 1;
                cargar();
            }, 350);
        });

        orden.addEventListener('change', function () {
            estado.orden = orden.value;
            estado.pagina = 1;
            cargar();
        });

        cargar();
    }

    /* ---------------- Detalle ---------------- */

    function iniciarDetalle() {
        var contenedor = document.getElementById('detalleProducto');
        var migas = document.getElementById('migasProducto');
        var id = Number(new URLSearchParams(window.location.search).get('id'));

        if (!id) {
            contenedor.innerHTML = '<div class="estado"><strong>Producto no especificado</strong>' +
                'Vuelve al <a href="index.html">catálogo</a> y elige un producto.</div>';
            return;
        }

        API.get('productos/' + id)
            .then(function (producto) {
                document.title = producto.nombre + ' | TECNO-MARKET';
                if (migas) {
                    migas.innerHTML = '<a href="index.html">Catálogo</a> <span>/</span> <span>' +
                        window.escaparHtml(producto.nombre) + '</span>';
                }

                contenedor.innerHTML = '' +
                    '<div class="detalle__imagen">' +
                        '<img src="' + imagen(producto.imagen) + '" alt="' + window.escaparHtml(producto.nombre) + '" ' +
                            'onerror="this.onerror=null;this.src=\'img/productos/placeholder.svg\'">' +
                    '</div>' +
                    '<div class="detalle__info">' +
                        '<h1>' + window.escaparHtml(producto.nombre) + '</h1>' +
                        '<div class="detalle__meta">' +
                            '<span class="chip ' + claseStock(producto.stock) + '">' + textoStock(producto.stock) + '</span>' +
                            '<span class="chip">Código #' + producto.id_producto + '</span>' +
                            '<span class="chip">Envío en 24 h</span>' +
                        '</div>' +
                        '<div class="detalle__precio"><span class="precio">' +
                            window.formatearPrecio(producto.precio) + '</span></div>' +
                        '<p class="detalle__desc">' + window.escaparHtml(producto.descripcion || '') + '</p>' +
                        '<div class="compra">' +
                            '<input type="number" id="cantidad" min="1" max="' + producto.stock + '" value="1" ' +
                                (Number(producto.stock) <= 0 ? 'disabled' : '') + ' aria-label="Cantidad">' +
                            '<button class="boton boton--primario" id="agregarDetalle"' +
                                (Number(producto.stock) <= 0 ? ' disabled' : '') + '>' +
                                (Number(producto.stock) <= 0 ? 'Producto agotado' : 'Agregar al carrito') +
                            '</button>' +
                        '</div>' +
                        '<p class="aviso">El stock se descuenta en el servidor al confirmar el pedido. ' +
                            'Si otro cliente compra las últimas unidades, la API responderá 409.</p>' +
                    '</div>';

                var cantidad = document.getElementById('cantidad');
                var boton = document.getElementById('agregarDetalle');

                if (boton && !boton.disabled) {
                    boton.addEventListener('click', function () {
                        try {
                            Carrito.agregar(producto, parseInt(cantidad.value, 10) || 1);
                            toast(producto.nombre + ' agregado al carrito', 'ok');
                        } catch (error) {
                            toast(error.message, 'error');
                        }
                    });
                }
            })
            .catch(function (error) {
                contenedor.innerHTML = '<div class="estado"><strong>Producto no disponible</strong>' +
                    window.escaparHtml(error.message) +
                    '<div class="mt-2"><a class="boton" href="index.html">Volver al catálogo</a></div></div>';
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('grilla')) {
            iniciarCatalogo();
        }

        if (document.getElementById('detalleProducto')) {
            iniciarDetalle();
        }
    });
})();
