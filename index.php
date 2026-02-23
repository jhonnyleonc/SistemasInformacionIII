<?php
session_start();
require_once __DIR__ . '../templates/header.php';
require_once __DIR__ . '../controllers/productocontroller.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cargas - Transporte Gutiérrez</title>
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            display: flex;
            padding: 20px;
            gap: 30px;
        }

        /* Estilos para el formulario - más angosto */
        .form-container {
            flex: 0 0 400px;
            /* Ancho fijo más angosto */
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            color: #F40009;
            border-bottom: 2px solid #F40009;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .producto-container {
            display: flex;
            gap: 5px;
        }

        .producto-container select {
            flex: 1;
        }

        .add-product-btn {
            background-color: #F40009;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            font-weight: bold;
        }

        .add-product-btn:hover {
            background-color: #D10000;
        }

        .submit-btn {
            background-color: #F40009;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            margin-top: 10px;
        }

        .submit-btn:hover {
            background-color: #D10000;
        }

        /* Área de información más amplia */
        .info-container {
            flex: 1;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .info-title {
            color: #F40009;
            border-bottom: 2px solid #F40009;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        /* Modal emergente */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 25px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            position: relative;
        }

        /* Estilos para el resumen */
        .summary-container {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            max-width: 100%;
            /* añade esto si no estaba */
            overflow-x: hidden;
            /* evita desbordes horizontales */
        }

        .summary-title {
            color: #F40009;
            margin-top: 0;
        }

        /* Estilos para la tabla */
        .viajes-table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }

        .viajes-table thead {
            background-color: #eee;
        }

        .viajes-table th,
        .viajes-table td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        .viajes-table th {
            font-weight: bold;
            color: #333;
        }

        .viajes-table tbody tr:hover {
            background-color: #f5f5f5;
        }

        /* Manteniendo la coherencia con el diseño general */
        .viajes-table th {
            color: #333;
        }

        .viajes-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Contenedor que permite scroll solo a la tabla */
        .scroll-table-container {
            flex: 1;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 5px;
            max-height: 350px;
        }

        /* Estructura de 2 filas con columnas flexibles */
        .resumen-table-layout {
            display: grid;
            grid-template-rows: auto auto;
            grid-auto-flow: column;
            column-gap: 20px;
            row-gap: 5px;
            margin-top: 10px;
            font-size: 14px;
        }

        .resumen-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
        }

        .resumen-table-layout p {
            margin: 0;
            background-color: #fff;
            padding: 6px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            white-space: nowrap;
            text-align: center;
        }

        /* Estilos específicos para el modal de productos */
        .modal-producto {
            width: 800px;
            /* Aumenté un poco el ancho total */
            max-width: 95%;
        }

        .modal-producto-container {
            display: flex;
            gap: 20px;
            /* Reduje el gap entre formulario y tabla */
            margin-top: 15px;
        }

        .modal-producto-form {
            flex: 0 0 280px;
            /* Ancho fijo más angosto para el formulario */
        }

        .modal-producto-list {
            flex: 1;
            min-width: 0;
            /* Esto ayuda a evitar desbordamientos */
        }

        /* Ajustes para los inputs */
        .form-group input {
            width: 100%;
            box-sizing: border-box;
            max-width: 100%;
        }

        .modal-buttons {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .cancel-btn {
            background-color: #6c757d;
        }

        .cancel-btn:hover {
            background-color: #5a6268;
        }

        /* Estilos para la tabla de productos */
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
            margin-top: 10px;
            table-layout: fixed;
            /* Esto ayuda a controlar el ancho de las columnas */
        }

        .productos-table thead {
            background-color: #eee;
        }

        .productos-table th,
        .productos-table td {
            padding: 10px 8px;
            /* Reduje el padding horizontal */
            text-align: center;
            border-bottom: 1px solid #ddd;
            word-wrap: break-word;
            /* Para manejar texto largo */
        }

        .productos-table th {
            font-weight: bold;
            color: #333;
        }

        /* Ajustes específicos de ancho de columnas */
        .productos-table th:nth-child(1),
        .productos-table td:nth-child(1) {
            width: 15%;
            /* Código */
        }

        .productos-table th:nth-child(2),
        .productos-table td:nth-child(2) {
            width: 40%;
            /* Producto */
        }

        .productos-table th:nth-child(3),
        .productos-table td:nth-child(3) {
            width: 20%;
            /* Comisión */
        }

        .productos-table th:nth-child(4),
        .productos-table td:nth-child(4) {
            width: 20%;
            /* Acciones */
        }

        .productos-table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .productos-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        /* Estilos para el botón de editar */
        .edit-btn {
            background-color: #17a2b8;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
        }

        .edit-btn:hover {
            background-color: #138496;
        }

        /* Estilos para el buscador */
        .search-container {
            margin-bottom: 10px;
        }

        .search-input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .table-container {
            max-height: 180px;
            /* Altura máxima para 4 filas aprox */
            overflow-y: auto;
            overflow-x: hidden;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Scroll personalizado para la tabla */
        .table-container::-webkit-scrollbar {
            width: 6px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 10px;
        }

        /*estilos para el resumen de cargas*/
        .resumen-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 100%;
            /* evita que se salga */
        }

        .resumen-header,
        .resumen-cargas,
        .resumen-total {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .resumen-header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 5px;
        }

        .resumen-cargas {
            flex-direction: column;
            gap: 5px;
        }

        .resumen-row {
            display: flex;
            gap: 30px;
            justify-content: space-between;
        }

        .resumen-item {
            margin: 0;
            padding: 5px 0;
            min-width: 250px;
            flex: 1;
        }

        .resumen-total {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 5px;
            font-weight: bold;
        }

        .resumen-total .resumen-item {
            width: 100%;
            flex: none;
            margin: 0 auto;
            /* centrado */
            text-align: center;
            background-color: #fff;
            border-radius: 4px;
            border: 1px solid #ddd;
            padding: 8px 12px;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Formulario de registro de viajes más angosto -->
        <div class="form-container">
            <h2 class="form-title">Registro de Carga</h2>
            <form action="action.php" method="post" id="formNuevoProducto">
                <input type="hidden" name="accion" id="accionViaje" value="guardar_viaje">
                <div class="form-group">
                    <label for="camion">Camión:</label>
                    <input type="text" id="camion" name="camion" maxlength="5" required value="<?= $_SESSION['camion'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" id="fecha" name="fecha" required value="<?= $_SESSION['fecha'] ?? '' ?>">
                </div>

                <div class="form-group">
                    <label for="carga">Tipo de Carga:</label>
                    <input type="text" id="carga" name="carga" required value="<?= $_SESSION['carga'] ?? '' ?>" style="text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label for="producto">Producto:</label>
                    <div class="producto-container">
                        <select id="producto" name="producto" required style="width: 100%;">
                            <option value="">Seleccione producto...</option>
                        </select>
                        <button type="button" class="add-product-btn" id="btnAddProduct">+</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cantidad">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" min="1" required>
                </div>

                <input type="hidden" name="nro" id="nroViaje">

                <div id="botones-editar" style="display: none; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="submit-btn" id="cancelarEdicion">Cancelar</button>
                    <button type="submit" class="submit-btn" id="confirmarCambio">Confirmar cambio</button>
                </div>

                <!-- Botón original -->
                <button type="submit" class="submit-btn" id="btnRegistrarViaje">Registrar carga</button>
            </form>
        </div>

        <div class="info-container">
            <h2 class="info-title">Información de Cargas</h2>

            <div class="summary-container">
                <h3 class="summary-title">Resumen del día</h3>
                <!-- <p><strong>Camión:</strong> <?= $_SESSION['camion'] ?? '-' ?></p>
                <p><strong>Fecha:</strong> <?= $_SESSION['fecha'] ?? '-' ?></p> -->
                <div id="resumenCarga" class="resumen-table-layout"></div>
            </div>


            <!-- NUEVO: Contenedor desplazable -->
            <div class="scroll-table-container">
                <table class="viajes-table" id="tablaViajes">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Carga</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Se cargará por AJAX -->
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Modal emergente para agregar producto -->
    <div id="modalProducto" class="modal-overlay" style="display: none;">
        <div class="modal-content modal-producto">
            <h2 class="form-title">Nuevo Producto</h2>

            <div class="modal-producto-container">
                <!-- Formulario de registro -->
                <form id="formNuevoProducto" action="action.php" method="post" class="modal-producto-form">
                    <input type="hidden" name="accion" value="guardar_producto">

                    <div class="form-group">
                        <label for="codigo">Código del Producto:</label>
                        <input type="text" id="codigo" name="codigo" maxlength="5" required>
                    </div>

                    <div class="form-group">
                        <label for="nombre">Nombre del Producto:</label>
                        <input type="text" id="nombre" name="nombre" maxlength="100" required>
                    </div>

                    <div class="form-group">
                        <label for="comision">Comisión (Bs):</label>
                        <input type="number" id="comision" name="comision" step="0.1" min="0" max="100" required>
                    </div>

                    <div class="modal-buttons">
                        <button type="button" class="submit-btn cancel-btn" onclick="cerrarModal()">Cancelar</button>
                        <button type="submit" class="submit-btn">Guardar</button>
                    </div>
                </form>

                <!-- Lista de productos ya registrados -->
                <div class="modal-producto-list">
                    <div class="search-container">
                        <input type="text" id="buscarProducto" placeholder="Buscar producto..." class="search-input">
                    </div>

                    <div class="table-container">
                        <table class="productos-table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Producto</th>
                                    <th>Comisión</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        //cargar productos en la tabla del modal
        function cargarProductosEnTabla() {
            $.ajax({
                url: 'action.php?action=listar_productos',
                type: 'GET',
                dataType: 'json',
                success: function(productos) {
                    const tbody = document.querySelector('.productos-table tbody');
                    tbody.innerHTML = '';

                    productos.forEach(producto => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                    <td>${producto.CODIGO}</td>
                    <td>${producto.NOMBRE}</td>
                    <td>${producto.Comision.toFixed(2)} Bs</td>
                    <td>
                        <button class="btn-editar">✏️</button>
                        <button class="btn-eliminar">🗑️</button>
                    </td>
                `;
                        tbody.appendChild(tr);
                    });
                },
                error: function() {
                    alert('No se pudieron cargar los productos.');
                }
            });
        }

        // Filtrar productos en la tabla del modal
        document.getElementById('buscarProducto').addEventListener('input', function() {
            const filtro = this.value.toLowerCase();
            const filas = document.querySelectorAll('.productos-table tbody tr');

            filas.forEach(fila => {
                const codigo = fila.children[0].textContent.toLowerCase();
                const nombre = fila.children[1].textContent.toLowerCase();
                const comision = fila.children[2].textContent.toLowerCase();

                if (codigo.includes(filtro) || nombre.includes(filtro) || comision.includes(filtro)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });

        // Editar producto
        $(document).on('click', '.btn-editar', function() {
            const tr = $(this).closest('tr');
            const codigo = tr.find('td').eq(0).text().trim();

            // Obtener datos del producto
            $.get('action.php?action=obtener_producto&codigo=' + codigo, function(producto) {
                if (producto) {
                    // Llenar el formulario
                    $('#codigo').val(producto.CODIGO).prop('readonly', true);
                    $('#nombre').val(producto.NOMBRE);
                    $('#comision').val(producto.Comision);

                    // Cambiar acción del formulario
                    $('input[name="accion"]').val('actualizar_producto');
                    $('.submit-btn[type="submit"]').text('Confirmar');

                    // Mostrar el modal
                    $('#modalProducto').css('display', 'flex');
                } else {
                    Swal.fire('Error', 'No se pudo obtener la información del producto.', 'error');
                }
            }, 'json');
        });

        // Abrir modal al hacer click en el botón +
        document.getElementById('btnAddProduct').addEventListener('click', function() {
            document.getElementById('modalProducto').style.display = 'flex';
            cargarProductosEnTabla(); // Cargar productos al abrir
        });

        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('modalProducto').style.display = 'none';
        }

        //permite buscar productos en el select del formulario
        $('#producto').select2({
            placeholder: 'Seleccione producto...',
            allowClear: true,
            ajax: {
                url: 'action.php?action=obtener_productos',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '' // Asegura que si está vacío, igual mande algo
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        function cargarTablaViajes() {
            $.ajax({
                url: 'action.php?action=listar_viajes',
                method: 'GET',
                success: function(html) {
                    $('#tablaViajes tbody').html(html);
                }
            });
        }

        function cargarResumen() {
            $.ajax({
                url: 'action.php?action=obtener_resumen',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    $('#resumenCarga').html(response.html);
                }
            });
        }

        // Cargar tabla al iniciar si hay sesión activa
        window.addEventListener('DOMContentLoaded', () => {
            cargarTablaViajes();
            cargarResumen();
        });

        $('#confirmarCambio').on('click', function() {
            $('#accionViaje').val('actualizar_viaje');
        });
    </script>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Producto registrado',
                text: 'El producto fue agregado correctamente.',
                confirmButtonColor: '#3085d6'
            });
        </script>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'codigo'): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Código ya registrado',
                text: 'El código del producto ya existe. Intenta con otro.',
                confirmButtonColor: '#d33'
            });
        </script>
    <?php elseif (isset($_GET['error']) && $_GET['error'] === 'campos'): ?>
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Debes completar todos los campos para registrar el producto.',
                confirmButtonColor: '#f39c12'
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_SESSION['producto_ok'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Producto actualizado',
                text: 'Los cambios se guardaron correctamente.',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                document.getElementById('modalProducto').style.display = 'none';
                cargarProductosEnTabla(); // Refresca la tabla
            });
        </script>
        <?php unset($_SESSION['producto_ok']); ?>
    <?php elseif (isset($_SESSION['producto_error'])): ?>
        <script>
            Swal.fire('Error', 'No se pudo actualizar el producto.', 'error');
        </script>
        <?php unset($_SESSION['producto_error']); ?>
    <?php endif; ?>

    <script>
        // Eliminar producto
        $(document).on('click', '.btn-eliminar', function() {
            const tr = $(this).closest('tr');
            const codigo = tr.find('td').eq(0).text().trim(); // Asegúrate que es el valor exacto que espera el servidor

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el producto permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'action.php?action=eliminar_producto',
                        method: 'POST',
                        data: {
                            codigo: codigo
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Producto eliminado',
                                    text: 'El producto fue eliminado correctamente.',
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    cargarProductosEnTabla();
                                });
                            } else {
                                Swal.fire('Error', response.error || 'No se pudo eliminar el producto.', 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error(xhr.responseText);
                            Swal.fire('Error', 'Ocurrió un error en el servidor.', 'error');
                        }
                    });
                }
            });
        });
    </script>

    <?php if (isset($_SESSION['viaje_ok']) || isset($_SESSION['viaje_error']) || isset($_SESSION['viaje_campos'])): ?>
        <script>
            <?php if (isset($_SESSION['viaje_ok'])): ?>
                Swal.fire({
                    icon: 'success',
                    title: '¡Viaje registrado!',
                    text: 'El viaje fue registrado exitosamente.',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    const producto = document.getElementById('producto');
                    const cantidad = document.getElementById('cantidad');

                    if ($(producto).hasClass("select2-hidden-accessible")) {
                        $(producto).val(null).trigger('change');
                    } else {
                        producto.value = '';
                    }

                    cantidad.value = '';
                });
            <?php elseif (isset($_SESSION['viaje_error'])): ?>
                Swal.fire('Error', 'Hubo un problema al registrar el viaje.', 'error');
            <?php elseif (isset($_SESSION['viaje_campos'])): ?>
                Swal.fire('Campos incompletos', 'Por favor completa todos los campos.', 'warning');
            <?php endif; ?>
        </script>
        <?php
        // Limpiar sesión para que no se repita el mensaje al recargar
        unset($_SESSION['viaje_ok'], $_SESSION['viaje_error'], $_SESSION['viaje_campos']);
        ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['viaje_actualizado'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: '¡Viaje actualizado!',
                text: 'Los datos del viaje se modificaron correctamente.',
                confirmButtonColor: '#3085d6'
            }).then(() => {
                // Restaurar estado del formulario
                $('#nroViaje').val('');
                $('#accionViaje').val('guardar_viaje');
                $('#btnRegistrarViaje').show();
                $('#botones-editar').hide();

                // Limpiar campos producto y cantidad
                const producto = document.getElementById('producto');
                const cantidad = document.getElementById('cantidad');
                if ($(producto).hasClass("select2-hidden-accessible")) {
                    $(producto).val(null).trigger('change');
                } else {
                    producto.value = '';
                }
                cantidad.value = '';
            });
        </script>
        <?php unset($_SESSION['viaje_actualizado']); ?>
    <?php endif; ?>

    <script>
        $(document).on('click', '.btn-editar-viaje', function() {
            const id = $(this).data('id');
            $.ajax({
                url: 'action.php?action=obtener_viaje&id=' + id,
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data) {
                        $('#nroViaje').val(data.Nro);
                        $('#camion').val(data.Camion);
                        $('#fecha').val(data.Fecha);
                        $('#carga').val(data.Carga);
                        $('#cantidad').val(data.Cantidad);

                        // Cargar select2 con el producto y seleccionarlo
                        const productoSelect = $('#producto');
                        const option = new Option(
                            data.Producto + ' | ' + data.NombreProducto, // texto
                            data.Producto, // valor
                            true,
                            true
                        );
                        $('#producto').append(option).trigger('change');
                        productoSelect.append(option).trigger('change');

                        // Cambiar botones
                        $('#btnRegistrarViaje').hide();
                        $('#botones-editar').show();
                    }
                }
            });
        });

        // Cancelar edición
        $('#cancelarEdicion').on('click', function() {
            $('#nroViaje').val('');
            $('#formNuevoProducto')[0].reset();
            $('#producto').val(null).trigger('change');
            $('#btnRegistrarViaje').show();
            $('#botones-editar').hide();
        });
    </script>

    <script>
        $(document).on('click', '.btn-eliminar-viaje', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el viaje permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Llamar al controlador por AJAX
                    $.ajax({
                        url: 'action.php?action=eliminar_viaje',
                        method: 'POST',
                        data: {
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Eliminado!',
                                    text: 'El viaje fue eliminado correctamente.',
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    cargarTablaViajes(); // Recargar tabla
                                    cargarResumen(); // Recargar resumen
                                });
                            } else {
                                Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Ocurrió un error en el servidor.', 'error');
                        }
                    });
                }
            });
        });
    </script>
</body>