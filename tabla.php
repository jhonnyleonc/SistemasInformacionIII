<?php require_once __DIR__ . '../templates/header.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Viajes - Transporte Gutiérrez</title>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.5.1/css/dataTables.dateTime.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/datetime/1.5.1/js/dataTables.dateTime.min.js"></script>

    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #F40009;
            text-align: center;
            margin-bottom: 30px;
        }

        .filters-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .filter-group input,
        .filter-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }

        .btn {
            padding: 8px 15px;
            background-color: #F40009;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            background-color: #D10000;
        }

        .btn-reset {
            background-color: #6c757d;
        }

        .btn-reset:hover {
            background-color: #5a6268;
        }

        .btn-excel {
            background-color: #217346;
            /* Verde característico de Excel */
            margin-bottom: 15px;
            padding: 10px 20px;
            font-size: 16px;
        }

        .btn-excel:hover {
            background-color: #1a5c38;
        }

        .btn-excel i {
            font-weight: bold;
        }

        /* Estilos para DataTables */
        .dataTables_wrapper {
            margin-top: 20px;
        }

        table.dataTable {
            border-collapse: collapse !important;
            width: 100% !important;
        }

        table.dataTable thead th {
            background-color: #F40009;
            color: white;
            font-weight: bold;
        }

        table.dataTable tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table.dataTable tbody tr:hover {
            background-color: #f1f1f1;
        }

        .dt-buttons {
            margin-bottom: 10px;
        }

        .dt-button {
            background-color: #F40009 !important;
            color: white !important;
            border: none !important;
            border-radius: 4px !important;
            padding: 5px 10px !important;
            margin-right: 5px !important;
        }

        .dt-button:hover {
            background-color: #D10000 !important;
        }

        .export-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Resumen de Viajes</h1>

        <!-- Botón Exportar a Excel -->
        <div class="export-container">
            <button class="btn btn-excel" id="export-excel">
                <i>XLS</i> Exportar a Excel
            </button>
        </div>

        <!-- Filtros -->
        <div class="filters-container">
            <div class="filter-group">
                <label for="min">Fecha desde:</label>
                <input type="date" id="min" name="min">
            </div>

            <div class="filter-group">
                <label for="max">Fecha hasta:</label>
                <input type="date" id="max" name="max">
            </div>

            <div class="filter-group">
                <label for="camion-filter">Código de Camión:</label>
                <select id="camion-filter">
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="filter-buttons">
                <button class="btn" id="filter-btn">Filtrar</button>
                <button class="btn btn-reset" id="reset-btn">Restablecer</button>
            </div>
        </div>

        <!-- Tabla -->
        <table id="tablaViajes" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Camion</th>
                    <th>Carga</th>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Comisión</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable con servidor-side processing
            var table = $('#tablaViajes').DataTable({
                dom: 'Bfrtip',
                buttons: [{
                    extend: 'excelHtml5',
                    text: 'Exportar a Excel',
                    className: 'btn-excel',
                    title: 'Resumen_Viajes_Transporte_Gutierrez'
                }],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'action.php?action=cargar_datos',
                    type: 'POST',
                    data: function(d) {
                        // Agregar nuestros filtros personalizados a la petición
                        d.fecha_desde = $('#min').val();
                        d.fecha_hasta = $('#max').val();
                        d.camion = $('#camion-filter').val();
                    },
                    error: function(xhr, error, thrown) {
                        console.error('Error cargando datos:', error);
                        alert('Error al cargar los datos. Por favor, intente nuevamente.');
                    }
                },
                columns: [{
                        data: 'Fecha',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('es-ES');
                        }
                    },
                    {
                        data: 'Camion'
                    },
                    {
                        data: 'Carga',
                        render: function(data) {
                            return '<span style="padding: 4px 8px; border-radius: 4px; font-weight: bold; background-color: ' +
                                (data === 'A' ? '#d4edda' : '#fff3cd') + '; color: ' +
                                (data === 'A' ? '#155724' : '#856404') + ';">' + data + '</span>';
                        }
                    },
                    {
                        data: 'Producto'
                    },
                    {
                        data: 'Cantidad',
                        className: 'text-end',
                        render: function(data) {
                            return data.toLocaleString('es-ES');
                        }
                    },
                    {
                        data: 'Comision',
                        className: 'text-end',
                        render: function(data) {
                            return 'Bs. ' + parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'Total',
                        className: 'text-end',
                        render: function(data) {
                            return 'Bs. ' + parseFloat(data).toFixed(2);
                        }
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 25
            });

            // Cargar opciones de camiones dinámicamente
            function cargarCamiones() {
                $.ajax({
                    url: 'action.php?action=obtener_camiones_excel',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var select = $('#camion-filter');
                            select.empty().append('<option value="">Todos</option>');

                            response.camiones.forEach(function(camion) {
                                select.append('<option value="' + camion + '">' + camion + '</option>');
                            });
                        }
                    },
                    error: function() {
                        console.error('Error cargando camiones');
                    }
                });
            }

            // Cargar camiones al iniciar
            cargarCamiones();

            // Botón Exportar a Excel personalizado
            $('#export-excel').on('click', function() {
                var fecha_desde = $('#min').val();
                var fecha_hasta = $('#max').val();
                var camion = $('#camion-filter').val();

                // Construir URL con parámetros
                var url = 'action.php?action=exportar_excel';
                var params = [];

                if (fecha_desde) params.push('fecha_desde=' + fecha_desde);
                if (fecha_hasta) params.push('fecha_hasta=' + fecha_hasta);
                if (camion) params.push('camion=' + camion);

                if (params.length > 0) {
                    url += '&' + params.join('&');
                }

                // Redirigir para descargar el Excel
                window.location.href = url;
            });

            // Filtro personalizado
            $('#filter-btn').on('click', function() {
                // DataTables automáticamente enviará los filtros via AJAX
                table.ajax.reload();
            });

            // Restablecer filtros
            $('#reset-btn').on('click', function() {
                $('#min').val('');
                $('#max').val('');
                $('#camion-filter').val('');
                table.ajax.reload();
            });

            // También recargar cuando cambien los selects directamente
            $('#camion-filter').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
</body>

</html>