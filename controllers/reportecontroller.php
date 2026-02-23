<?php
require_once __DIR__ . '../../models/reportemodel.php';

class ReporteController
{
    private $reporteModel;

    public function __construct()
    {
        $this->reporteModel = new ReporteModel();
    }

    /**
     * Obtiene los camiones para el filtro
     */
    public function obtenerCamiones()
    {
        try {
            $camiones = $this->reporteModel->obtenerCamiones();
            header('Content-Type: application/json');
            echo json_encode($camiones);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([]);
        }
    }

    /**
     * Obtiene los datos para las tarjetas de resumen
     */
    public function obtenerDatosResumen()
    {
        try {
            $fechaInicio = $_GET['fechaInicio'] ?? date('Y-m-d', strtotime('-7 days'));
            $fechaFin = $_GET['fechaFin'] ?? date('Y-m-d');
            $camion = $_GET['camion'] ?? null;

            $datos = $this->reporteModel->obtenerDatosResumen($fechaInicio, $fechaFin, $camion);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $datos
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener datos de resumen'
            ]);
        }
    }

    /**
     * Obtiene los datos para los gráficos
     */
    public function obtenerDatosGraficos()
    {
        try {
            $fechaInicio = $_GET['fechaInicio'] ?? date('Y-m-d', strtotime('-7 days'));
            $fechaFin = $_GET['fechaFin'] ?? date('Y-m-d');
            $camion = $_GET['camion'] ?? null;

            $datos = $this->reporteModel->obtenerDatosGraficos($fechaInicio, $fechaFin, $camion);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $datos
            ]);
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener datos para gráficos'
            ]);
        }
    }
}
