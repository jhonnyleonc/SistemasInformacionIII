<?php
require_once __DIR__ . '/../models/excelmodel.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ExcelController
{
    private $model;

    public function __construct()
    {
        $this->model = new ExcelModel();
    }

    /**
     * Procesa la petición AJAX para cargar datos de la tabla
     */
    public function cargarDatosTabla()
    {
        try {
            // Obtener parámetros de filtro
            $fecha_desde = isset($_POST['fecha_desde']) ? $_POST['fecha_desde'] : null;
            $fecha_hasta = isset($_POST['fecha_hasta']) ? $_POST['fecha_hasta'] : null;
            $camion = isset($_POST['camion']) ? trim($_POST['camion']) : null;

            // Validar formato de fechas si están presentes
            if (!empty($fecha_desde) && !$this->validarFecha($fecha_desde)) {
                throw new Exception("Formato de fecha desde inválido");
            }

            if (!empty($fecha_hasta) && !$this->validarFecha($fecha_hasta)) {
                throw new Exception("Formato de fecha hasta inválido");
            }

            // Obtener datos del modelo
            $datos = $this->model->obtenerDatosTabla($fecha_desde, $fecha_hasta, $camion);

            // Preparar respuesta para DataTables
            $response = [
                'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
                'recordsTotal' => count($datos),
                'recordsFiltered' => count($datos),
                'data' => $datos
            ];

            // Enviar respuesta JSON
            header('Content-Type: application/json');
            echo json_encode($response);
        } catch (Exception $e) {
            // Manejar errores
            $errorResponse = [
                'draw' => isset($_POST['draw']) ? intval($_POST['draw']) : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ];

            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode($errorResponse);
        }
    }

    /**
     * Obtiene la lista de camiones para filtros
     */
    public function obtenerCamiones()
    {
        try {
            $camiones = $this->model->obtenerCamiones();

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'camiones' => $camiones
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Valida el formato de fecha (YYYY-MM-DD)
     */
    private function validarFecha($fecha)
    {
        $patron = '/^\d{4}-\d{2}-\d{2}$/';
        if (preg_match($patron, $fecha)) {
            $partes = explode('-', $fecha);
            if (checkdate($partes[1], $partes[2], $partes[0])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Método principal que dirige las acciones del controlador
     */
    public function handleRequest()
    {
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        switch ($action) {
            case 'cargar_datos':
                $this->cargarDatosTabla();
                break;
            case 'obtener_camiones':
                $this->obtenerCamiones();
                break;
            default:
                header('Content-Type: application/json');
                http_response_code(404);
                echo json_encode(['error' => 'Acción no encontrada']);
                break;
        }
    }

    /**
     * Exporta los datos a Excel en formato pivot
     */
    public function exportarExcel()
    {
        try {
            // Obtener parámetros de filtro (los mismos que la vista)
            $fecha_desde = isset($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null;
            $fecha_hasta = isset($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null;
            $camion = isset($_GET['camion']) ? trim($_GET['camion']) : null;

            // Obtener datos pivotados del modelo
            $datosPivot = $this->model->obtenerDatosPivotExcel($fecha_desde, $fecha_hasta, $camion);

            if (empty($datosPivot['datos'])) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'No hay datos para exportar con los filtros aplicados'
                ]);
                return;
            }

            // Crear nuevo spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Título del reporte
            $sheet->setTitle('Resumen de Viajes');
            $sheet->setCellValue('A1', 'TRANSPORTE GUTIÉRREZ - EMBOL COCA-COLA');
            $sheet->setCellValue('A2', 'Resumen de Viajes - ' . date('d/m/Y H:i:s'));

            // Información de filtros aplicados
            $filtroInfo = 'Filtros aplicados: ';
            $filtros = [];
            if ($fecha_desde) $filtros[] = "Desde: " . $fecha_desde;
            if ($fecha_hasta) $filtros[] = "Hasta: " . $fecha_hasta;
            if ($camion) $filtros[] = "Camión: " . $camion;

            if (empty($filtros)) {
                $filtroInfo .= 'Todos los datos';
            } else {
                $filtroInfo .= implode(', ', $filtros);
            }

            $sheet->setCellValue('A3', $filtroInfo);

            // Cabeceras de la tabla (empezando desde fila 5)
            $columna = 'A';
            foreach ($datosPivot['cabeceras'] as $cabecera) {
                $sheet->setCellValue($columna . '5', $cabecera);
                $columna++;
            }

            // Datos de la tabla
            $fila = 6;
            foreach ($datosPivot['datos'] as $datosFila) {
                $columna = 'A';
                foreach ($datosFila as $valor) {
                    $sheet->setCellValue($columna . $fila, $valor);
                    $columna++;
                }
                $fila++;
            }

            // ESTILOS Y FORMATOS

            // Estilo para el título principal
            $ultimaColumna = $this->getColumnaLetra(count($datosPivot['cabeceras']));
            $sheet->mergeCells('A1:' . $ultimaColumna . '1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'F40009']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ]);

            // Estilo para información de fecha y filtros
            $sheet->mergeCells('A2:' . $ultimaColumna . '2');
            $sheet->getStyle('A2')->getFont()->setItalic(true);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A3:' . $ultimaColumna . '3');
            $sheet->getStyle('A3')->getFont()->setItalic(true);
            $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Estilo para cabeceras de tabla
            $headerRange = 'A5:' . $ultimaColumna . '5';
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F40009']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER
                ]
            ]);

            // Estilo para datos
            $dataRange = 'A6:' . $ultimaColumna . ($fila - 1);
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ]);

            // Identificar las filas especiales
            $ultimaFila = $fila - 1;
            $filaTotalesBs = $ultimaFila;
            $filaComisiones = $ultimaFila - 1;
            $filaTotalProducto = $ultimaFila - 2;

            // Estilo para fila de TOTAL (por producto)
            if (
                isset($datosPivot['datos'][$filaTotalProducto][0]) &&
                $datosPivot['datos'][$filaTotalProducto][0] === 'TOTAL (por producto)'
            ) {

                $totalProductoRange = 'A' . $filaTotalProducto . ':' . $ultimaColumna . $filaTotalProducto;
                $sheet->getStyle($totalProductoRange)->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D4EDDA']
                    ]
                ]);
            }

            // Estilo para fila de COMISIÓN
            if (
                isset($datosPivot['datos'][$filaComisiones][0]) &&
                $datosPivot['datos'][$filaComisiones][0] === 'COMISIÓN'
            ) {

                $comisionRange = 'A' . $filaComisiones . ':' . $ultimaColumna . $filaComisiones;
                $sheet->getStyle($comisionRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'italic' => true
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFF2CC']
                    ]
                ]);
            }

            // Estilo para fila de TOTALES (bs)
            if (
                isset($datosPivot['datos'][$filaTotalesBs][0]) &&
                $datosPivot['datos'][$filaTotalesBs][0] === 'TOTALES (bs)'
            ) {

                $totalRange = 'A' . $filaTotalesBs . ':' . $ultimaColumna . $filaTotalesBs;
                $sheet->getStyle($totalRange)->applyFromArray([
                    'font' => [
                        'bold' => true
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E6E6E6']
                    ]
                ]);

                // Formato de moneda para los totales en bolivianos
                $columnaTotal = 'D'; // Empieza en columna D (primera columna de productos)
                for ($i = 0; $i < count($datosPivot['productos']); $i++) {
                    $celda = $columnaTotal . $filaTotalesBs;
                    $sheet->getStyle($celda)->getNumberFormat()->setFormatCode('"Bs." #,##0.00');
                    $columnaTotal++;
                }
                // Formato para el total general de comisiones
                $celdaTotalGeneral = $ultimaColumna . $filaTotalesBs;
                $sheet->getStyle($celdaTotalGeneral)->getNumberFormat()->setFormatCode('"Bs." #,##0.00');
            }

            // Autoajustar columnas
            $columna = 'A';
            for ($i = 0; $i < count($datosPivot['cabeceras']); $i++) {
                $sheet->getColumnDimension($columna)->setAutoSize(true);
                $columna++;
            }

            // Congelar paneles (cabeceras fijas)
            $sheet->freezePane('A6');

            // Preparar headers para descarga
            $filename = 'Resumen_Viajes_' . date('Y-m-d_His') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            header('Cache-Control: max-age=1');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: cache, must-revalidate');
            header('Pragma: public');

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        } catch (Exception $e) {
            error_log("Error en ExcelController::exportarExcel: " . $e->getMessage());

            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al generar el archivo Excel: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Convierte número de columna a letra (1=A, 2=B, etc.)
     */
    private function getColumnaLetra($numero)
    {
        $letras = range('A', 'Z');
        return $letras[$numero - 1] ?? 'Z';
    }
}
