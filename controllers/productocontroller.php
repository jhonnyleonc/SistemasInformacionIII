<?php
require_once __DIR__ . '/../models/productomodel.php';

class ProductoController
{
    private $model;

    public function __construct()
    {
        $this->model = new ProductoModel();
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo   = trim($_POST['codigo'] ?? '');
            $nombre   = trim($_POST['nombre'] ?? '');
            $comision = floatval($_POST['comision'] ?? 0);

            if (!empty($codigo) && !empty($nombre) && $comision > 0) {
                $registrado = $this->model->registrarProducto($codigo, $nombre, $comision);
                if ($registrado) {
                    header("Location: index.php?success=1");
                    exit;
                } else {
                    header("Location: index.php?error=codigo"); // Código duplicado o error DB
                    exit;
                }
            } else {
                header("Location: index.php?error=campos");
                exit;
            }
        }
    }

    public function obtenerTodosSelect()
    {
        $busqueda = $_GET['term'] ?? '';

        $productos = $this->model->buscarProductosSelect($busqueda);

        $resultado = array_map(function ($p) {
            return [
                'id' => $p['CODIGO'],
                'text' => $p['CODIGO'] . ' | ' . $p['NOMBRE']
            ];
        }, $productos);

        header('Content-Type: application/json');
        echo json_encode($resultado);
    }

    public function listar()
    {
        $productos = $this->model->obtenerTodos();
        header('Content-Type: application/json');
        echo json_encode($productos);
    }

    public function buscarProductosInfo()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
            $termino = trim($_GET['q']);
            $resultados = $this->model->buscarProductosTabla($termino);
            echo json_encode($resultados);
        }
    }

    public function obtenerProducto()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['codigo'])) {
            $codigo = $_GET['codigo'];

            $producto = $this->model->obtenerProductoPorCodigo($codigo);

            if ($producto) {
                header('Content-Type: application/json');
                echo json_encode($producto);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
            }
        }
    }

    public function actualizar()
    {
        session_start(); // Asegúrate de iniciar la sesión aquí
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');
            $nombre = trim($_POST['nombre'] ?? '');
            $comision = floatval($_POST['comision'] ?? 0);

            if (!empty($codigo) && !empty($nombre) && $comision >= 0) {
                $actualizado = $this->model->actualizarProducto($codigo, $nombre, $comision);
                if ($actualizado) {
                    $_SESSION['producto_ok'] = true;
                } else {
                    $_SESSION['producto_error'] = true;
                }
                header("Location: index.php");
                exit;
            }
        }
    }

    public function eliminar()
    {
        try {
            if (isset($_POST['codigo'])) {
                $codigo = $_POST['codigo'];
                $eliminado = $this->model->eliminarProducto($codigo);

                header('Content-Type: application/json');
                echo json_encode(['success' => $eliminado]);
            } else {
                throw new Exception("Código no recibido en POST");
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
