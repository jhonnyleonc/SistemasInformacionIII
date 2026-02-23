<?php
require_once 'controllers/productocontroller.php'; // Corrige el nombre del archivo si es necesario
require_once 'controllers/viajecontroller.php'; // Asegúrate de que este archivo exista
require_once 'controllers/reportecontroller.php'; // Asegúrate de que este archivo exista
require_once 'controllers/excelcontroller.php'; // Agregar esta línea

$controller = new ProductoController();
$viajeController = new ViajeController();
$reporteController = new ReporteController();
$excelController = new ExcelController(); // Agregar esta instancia

$action = $_POST['accion'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'guardar_producto':
        $controller->registrar(); // Este método procesará el registro
        break;

    case 'obtener_productos':
        $controller->obtenerTodosSelect();
        break;

    case 'listar_productos':
        $controller->listar(); // Devuelve JSON
        break;

    case 'buscar_productos':
        $controller->buscarProductosInfo();
        break;

    case 'obtener_producto':
        $controller->obtenerProducto();
        break;

    case 'actualizar_producto':
        $controller->actualizar();
        break;

    case 'eliminar_producto':
        $controller->eliminar();
        break;

    case 'guardar_viaje':
        $viajeController->registrar();
        break;

    case 'listar_viajes':
        $viajeController->listarPorCamionYFecha();
        break;

    case 'obtener_viaje':
        $viajeController->obtenerPorId();
        break;

    case 'actualizar_viaje':
        $viajeController->actualizar();
        break;

    case 'eliminar_viaje':
        $viajeController->eliminar();
        break;

    case 'obtener_resumen':
        $viajeController->obtenerResumen();
        break;

    case 'obtener_camiones':
        $reporteController->obtenerCamiones();
        break;

    case 'obtener_datos_resumen':
        $reporteController->obtenerDatosResumen();
        break;

    case 'obtener_datos_graficos':
        $reporteController->obtenerDatosGraficos();
        break;

    // AGREGAR ESTAS NUEVAS ACCIONES PARA EL EXCELCONTROLLER
    case 'cargar_datos':
        $excelController->cargarDatosTabla();
        break;

    case 'obtener_camiones_excel':
        $excelController->obtenerCamiones();
        break;

    case 'exportar_excel':
        $excelController->exportarExcel();
        break;

    default:
        echo "<div style='text-align:center; padding: 2rem;'>Acción no válida.</div>";
        break;
}
