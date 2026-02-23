<?php require_once __DIR__ . '../templates/header.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Transporte Gutiérrez</title>

    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }

        .container {
            padding: 20px;
            margin-top: 20px;
        }

        /* Tarjetas de resumen */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid #F40009;
        }

        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }

        .card-title {
            font-size: 14px;
            color: #6c757d;
            margin: 0 0 10px 0;
            font-weight: 500;
        }

        .card-value {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: #2c3e50;
        }

        /* Filtros y controles */
        .controls-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .filters-row {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #555;
            font-weight: 500;
        }

        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid #ddd;
            background-color: #f8f9fa;
            font-size: 14px;
        }

        .export-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .export-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            border-radius: 6px;
            border: none;
            background-color: #F40009;
            color: white;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .export-btn:hover {
            background-color: #D10000;
        }

        .export-btn.secondary {
            background-color: #2c3e50;
        }

        .export-btn.secondary:hover {
            background-color: #1a252f;
        }

        /* Sección de gráficos */
        .charts-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: #2c3e50;
        }

        .chart-wrapper {
            height: 300px;
            position: relative;
        }

        /* Diseño responsive */
        @media (max-width: 768px) {
            .charts-section {
                grid-template-columns: 1fr;
            }

            .filters-row {
                flex-direction: column;
                gap: 15px;
            }

            .filter-group {
                min-width: 100%;
            }

            .export-buttons {
                flex-direction: column;
            }

            .export-btn {
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sección de filtros -->
        <div class="controls-section">
            <h2 style="margin-top: 0; margin-bottom: 20px; color: #2c3e50;">Filtros del Reporte</h2>

            <div class="filters-row">
                <div class="filter-group">
                    <label for="date-range">Rango de fechas</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="start-date" class="date-input" value="<?= date('Y-m-d', strtotime('-7 days')) ?>">
                        <span style="align-self: center;">a</span>
                        <input type="date" id="end-date" class="date-input" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="filter-group">
                    <label for="truck-filter">Camión</label>
                    <select id="truck-filter">
                        <option value="">Todos los camiones</option>
                        <!-- Opciones se llenarán con JS -->
                    </select>
                </div>
            </div>

            <div class="export-buttons">
                <button class="export-btn">
                    <i class="fas fa-file-excel"></i> Exportar a Excel
                </button>
                <button class="export-btn secondary">
                    <i class="fas fa-file-pdf"></i> Exportar a PDF
                </button>
                <button class="export-btn" id="apply-filters" style="background-color: #3498db;">
                    <i class="fas fa-filter"></i> Aplicar Filtros
                </button>
            </div>
        </div>

        <!-- Tarjetas de resumen simplificadas -->
        <div class="summary-grid">
            <div class="summary-card">
                <h3 class="card-title">Paquetes Transportados</h3>
                <p class="card-value" id="total-packages">0</p>
            </div>

            <div class="summary-card">
                <h3 class="card-title">Ganancias Totales</h3>
                <p class="card-value" id="total-earnings">Bs. 0.00</p>
            </div>

            <div class="summary-card">
                <h3 class="card-title">Viajes Realizados</h3>
                <p class="card-value" id="total-trips">0</p>
            </div>

            <div class="summary-card">
                <h3 class="card-title">Productos Vendidos</h3>
                <p class="card-value" id="total-products">0</p>
            </div>
        </div>

        <!-- Sección de gráficos en pares -->
        <div class="charts-section">
            <!-- Gráfico 1: Paquetes vendidos últimos 7 días -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Paquetes Vendidos (Últimos 7 días)</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="packagesChart"></canvas>
                </div>
            </div>

            <!-- Gráfico 2: Ganancias últimos 7 días -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Ganancias (Últimos 7 días)</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="earningsChart"></canvas>
                </div>
            </div>

            <!-- Gráfico 3: Viajes realizados últimos 7 días -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Viajes Realizados (Últimos 7 días)</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="tripsChart"></canvas>
                </div>
            </div>

            <!-- Gráfico 4: Productos más vendidos -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3 class="chart-title">Productos Más Vendidos</h3>
                </div>
                <div class="chart-wrapper">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Variables globales para los gráficos
        let packagesChart, earningsChart, tripsChart, topProductsChart;
        let chartInitialized = false;

        $(document).ready(function() {
            // Cargar camiones disponibles
            loadTrucks();

            // Cargar datos iniciales
            loadData();

            // Configurar evento para filtros
            $('#apply-filters').click(function() {
                loadData();
            });
        });

        function loadTrucks() {
            $.ajax({
                url: 'action.php?action=obtener_camiones',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    const select = $('#truck-filter');
                    select.empty();
                    select.append('<option value="">Todos los camiones</option>');

                    $.each(data, function(index, camion) {
                        select.append(`<option value="${camion}">${camion}</option>`);
                    });
                },
                error: function() {
                    console.error('Error al cargar camiones');
                }
            });
        }

        function loadData() {
            const fechaInicio = $('#start-date').val();
            const fechaFin = $('#end-date').val();
            const camion = $('#truck-filter').val();

            console.log("Enviando filtros:", {
                fechaInicio,
                fechaFin,
                camion
            }); // Debug

            // Cargar datos de resumen
            $.ajax({
                url: 'action.php?action=obtener_datos_resumen',
                type: 'GET',
                data: {
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    camion: camion
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        updateSummaryCards(response.data);
                    }
                },
                error: function() {
                    console.error('Error al cargar datos de resumen');
                }
            });

            // Cargar datos para gráficos
            $.ajax({
                url: 'action.php?action=obtener_datos_graficos',
                type: 'GET',
                data: {
                    fechaInicio: fechaInicio,
                    fechaFin: fechaFin,
                    camion: camion
                },
                dataType: 'json',
                success: function(response) {
                    console.log("Datos recibidos:", response); // Debug
                    if (response.success) {
                        updateCharts(response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error al cargar datos para gráficos:', error);
                    console.log("Detalles:", xhr.responseText);
                }
            });
        }

        function updateSummaryCards(data) {
            $('#total-packages').text(data.totalPaquetes.toLocaleString());
            $('#total-earnings').text('Bs. ' + data.totalGanancias.toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#total-trips').text(data.totalViajes.toLocaleString());
            $('#total-products').text(data.totalProductos.toLocaleString());
        }

        function updateCharts(data) {
            console.log("Actualizando gráficos con:", data); // Debug

            // Actualizar gráfico de paquetes
            packagesChart = updateChart(packagesChart, 'packagesChart', 'bar',
                data.paquetes.labels, data.paquetes.data,
                '#F40009', 'Cantidad de Paquetes');

            // Actualizar gráfico de ganancias
            earningsChart = updateChart(earningsChart, 'earningsChart', 'line',
                data.ganancias.labels, data.ganancias.data,
                '#4CAF50', 'Ganancias (Bs)', true);

            // Actualizar gráfico de viajes
            tripsChart = updateChart(tripsChart, 'tripsChart', 'bar',
                data.viajes.labels, data.viajes.data,
                '#3498db', 'Cantidad de Viajes');

            // Actualizar gráfico de productos
            updateTopProductsChart(data.productos);
        }

        function updateChart(chartVar, canvasId, type, labels, data, color, yAxisTitle, isLine = false) {
            const ctx = document.getElementById(canvasId).getContext('2d');

            // Destruir el gráfico anterior si existe
            if (chartVar) {
                chartVar.destroy();
            }

            // Configuración común
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: yAxisTitle
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            };

            // Configuración específica para gráfico de línea
            if (isLine) {
                chartVar = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            borderColor: color,
                            backgroundColor: color.replace(')', ', 0.1)').replace('rgb', 'rgba'),
                            borderWidth: 2,
                            fill: true,
                            tension: 0.3
                        }]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Bs. ' + context.raw.toLocaleString('es-ES', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        }
                    }
                });
            }
            // Configuración para gráficos de barras
            else {
                chartVar = new Chart(ctx, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: color,
                            borderColor: color,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        ...commonOptions,
                        plugins: {
                            ...commonOptions.plugins,
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.raw.toLocaleString() +
                                            (yAxisTitle.includes('Viajes') ? ' viajes' : ' paquetes');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            return chartVar;
        }

        function updateTopProductsChart(productos) {
            console.log("Datos para productos:", productos); // Debug

            const ctx = document.getElementById('topProductsChart').getContext('2d');

            // Limpiar el canvas completamente
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);

            // Destruir el gráfico anterior si existe
            if (topProductsChart) {
                topProductsChart.destroy();
                topProductsChart = null;
            }

            // Verificar si hay datos
            if (!productos || productos.length === 0) {
                ctx.font = '16px Arial';
                ctx.fillStyle = '#666';
                ctx.textAlign = 'center';
                ctx.fillText('No hay datos disponibles', ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            // Preparar datos
            const labels = productos.map(p => p.NOMBRE);
            const data = productos.map(p => p.cantidad);
            const backgroundColors = [
                '#F40009', '#4CAF50', '#FFC107', '#9C27B0', '#2196F3'
            ].slice(0, productos.length);

            // Configuración mejorada del gráfico
            const config = {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: backgroundColors.map(c => c.replace(')', ', 0.8)').replace('rgb', 'rgba')),
                        borderWidth: 1,
                        hoverBackgroundColor: backgroundColors.map(c => c.replace(')', ', 0.7)').replace('rgb', 'rgba')),
                        hoverBorderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad Vendida',
                                font: {
                                    weight: 'bold'
                                }
                            },
                            grid: {
                                color: 'rgba(0,0,0,0.05)'
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.raw.toLocaleString()} unidades`;
                                }
                            }
                        }
                    }
                }
            };

            // Crear nuevo gráfico
            topProductsChart = new Chart(ctx, config);

            // Forzar redibujado
            setTimeout(() => {
                topProductsChart.update();
            }, 100);
        }
    </script>
</body>

</html>