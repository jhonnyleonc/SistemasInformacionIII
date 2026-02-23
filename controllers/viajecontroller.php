<?php
require_once __DIR__ . '../../models/viajemodel.php';

class ViajeController
{
    private $viajeModel;

    public function __construct()
    {
        $this->viajeModel = new ViajeModel();
    }

    public function registrar()
    {
        session_start(); // Asegúrate de iniciar la sesión aquí

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $camion   = trim($_POST['camion'] ?? '');
            $fecha    = trim($_POST['fecha'] ?? '');
            $carga    = strtoupper(trim($_POST['carga'] ?? ''));
            $producto = trim($_POST['producto'] ?? '');
            $cantidad = intval($_POST['cantidad'] ?? 0);

            // Validación básica
            if (!empty($camion) && !empty($fecha) && !empty($carga) && !empty($producto) && $cantidad > 0) {
                $registrado = $this->viajeModel->registrarViaje($camion, $fecha, $carga, $producto, $cantidad);
                if ($registrado) {
                    // Guardar en sesión para mantener valores
                    $_SESSION['viaje_ok'] = true;
                    $_SESSION['camion'] = $camion;
                    $_SESSION['fecha'] = $fecha;
                    $_SESSION['carga'] = $carga;

                    header("Location: index.php");
                    exit;
                } else {
                    $_SESSION['viaje_error'] = true;
                    header("Location: index.php");
                    exit;
                }
            } else {
                $_SESSION['viaje_campos'] = true;
                header("Location: index.php");
                exit;
            }
        }
    }

    public function listarPorCamionYFecha()
    {
        session_start();
        $camion = $_SESSION['camion'] ?? '';
        $fecha = $_SESSION['fecha'] ?? '';

        if (!empty($camion) && !empty($fecha)) {
            $viajes = $this->viajeModel->obtenerViajesPorCamionYFecha($camion, $fecha);
            foreach ($viajes as $v) {
                echo "<tr>
                    <td>{$v['Fecha']}</td>
                    <td>{$v['Carga']}</td>
                    <td>{$v['Producto']} - {$v['NombreProducto']}</td>
                    <td>{$v['Cantidad']}</td>
                    <td>Bs. {$v['Total']}</td>
                    <td>
                        <button class='btn-editar-viaje' data-id='{$v['Nro']}'>✏️</button>
                        <button class='btn-eliminar-viaje' data-id='{$v['Nro']}'>🗑️</button>
                    </td>
                  </tr>";
            }
        }
    }

    public function obtenerPorId()
    {
        if (isset($_GET['id'])) {
            $nro = intval($_GET['id']);
            $viaje = $this->viajeModel->obtenerViajePorId($nro);
            header('Content-Type: application/json');
            echo json_encode($viaje);
        }
    }

    public function actualizar()
    {
        session_start(); // Asegúrate de iniciar la sesión aquí
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nro      = intval($_POST['nro'] ?? 0);
            $camion   = trim($_POST['camion'] ?? '');
            $fecha    = trim($_POST['fecha'] ?? '');
            $carga    = strtoupper(trim($_POST['carga'] ?? ''));
            $producto = trim($_POST['producto'] ?? '');
            $cantidad = intval($_POST['cantidad'] ?? 0);

            if ($nro > 0 && $camion && $fecha && $carga && $producto && $cantidad > 0) {
                $actualizado = $this->viajeModel->actualizarViaje($nro, $camion, $fecha, $carga, $producto, $cantidad);
                if ($actualizado) {
                    $_SESSION['viaje_actualizado'] = true;
                    $_SESSION['camion'] = $camion;
                    $_SESSION['fecha'] = $fecha;
                    $_SESSION['carga'] = $carga;
                } else {
                    $_SESSION['viaje_error'] = true;
                }
            } else {
                $_SESSION['viaje_campos'] = true;
            }
            header("Location: index.php");
            exit;
        }
    }

    public function eliminar()
    {
        if (isset($_POST['id'])) {
            $nro = intval($_POST['id']);
            $eliminado = $this->viajeModel->eliminarViaje($nro);

            header('Content-Type: application/json');
            echo json_encode(['success' => $eliminado]);
        }
    }

    public function obtenerResumen()
    {
        session_start();
        $camion = $_SESSION['camion'] ?? '';
        $fecha = $_SESSION['fecha'] ?? '';

        if (!empty($camion) && !empty($fecha)) {
            $datos = $this->viajeModel->obtenerResumenPorCamionYFecha($camion, $fecha);

            // Encabezado (siempre visible)
            $html = "<div class='resumen-grid'>";
            $html .= "<div class='resumen-header'>";
            $html .= "<p class='resumen-item'><strong>Camion:</strong> {$datos['camion']}</p>";
            $html .= "<p class='resumen-item'><strong>Fecha:</strong> {$datos['fecha']}</p>";
            $html .= "</div>";

            // Cargas organizadas en columnas
            if (!empty($datos['cargas'])) {
                $html .= "<div class='resumen-cargas'>";

                // Dividimos las cargas en pares para mostrarlas en dos columnas
                $chunks = array_chunk($datos['cargas'], 2);

                foreach ($chunks as $pair) {
                    $html .= "<div class='resumen-row'>";
                    foreach ($pair as $carga) {
                        $html .= "<p class='resumen-item'>";
                        $html .= "<strong>Carga {$carga['Carga']}:</strong> ";
                        $html .= "{$carga['TotalCantidad']} paquetes - ";
                        $html .= "Total: Bs. " . number_format($carga['TotalBs'], 2);
                        $html .= "</p>";
                    }
                    // Si hay un elemento impar, añadimos un espacio vacío para mantener el diseño
                    if (count($pair) == 1) {
                        $html .= "<p class='resumen-item'></p>";
                    }
                    $html .= "</div>"; // cierra resumen-row
                }
                $html .= "</div>"; // cierra resumen-cargas
            }

            // Total general (siempre al final)
            $html .= "<div class='resumen-total'>";
            $html .= "<p class='resumen-item'><strong>Total General:</strong> ";
            $html .= "{$datos['total']['TotalGeneralPaquetes']} paquetes - ";
            $html .= "Total: Bs. " . number_format($datos['total']['TotalGeneralBs'], 2);
            $html .= "</p>";
            $html .= "</div>";

            $html .= "</div>"; // cierra resumen-grid

            header('Content-Type: application/json');
            echo json_encode(['html' => $html]);
        }
    }

    
}
