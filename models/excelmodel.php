<?php
require_once __DIR__ . '/../config/database.php';

class ExcelModel
{
    private $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->getConnection();
    }

    /**
     * Obtiene los datos para la tabla de resumen con filtros opcionales
     */
    public function obtenerDatosTabla($fecha_desde = null, $fecha_hasta = null, $camion = null)
    {
        try {
            // Consulta base con JOIN para obtener el nombre del producto y su comisión
            $sql = "SELECT 
                        dv.Fecha,
                        dv.Camion,
                        dv.Carga,
                        p.NOMBRE as Producto,
                        dv.Cantidad,
                        p.Comision,
                        (dv.Cantidad * p.Comision) as Total
                    FROM DETALLE_VENTA dv
                    INNER JOIN PRODUCTOS p ON dv.Producto = p.CODIGO
                    WHERE 1=1";

            $params = [];

            // Aplicar filtros si están presentes
            if (!empty($fecha_desde)) {
                $sql .= " AND dv.Fecha >= :fecha_desde";
                $params[':fecha_desde'] = $fecha_desde;
            }

            if (!empty($fecha_hasta)) {
                $sql .= " AND dv.Fecha <= :fecha_hasta";
                $params[':fecha_hasta'] = $fecha_hasta;
            }

            if (!empty($camion)) {
                $sql .= " AND dv.Camion = :camion";
                $params[':camion'] = $camion;
            }

            // Ordenar por fecha (más reciente primero) y camión
            $sql .= " ORDER BY dv.Fecha DESC, dv.Camion ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ExcelModel::obtenerDatosTabla: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene la lista de camiones únicos para filtros
     */
    public function obtenerCamiones()
    {
        try {
            $sql = "SELECT DISTINCT Camion FROM DETALLE_VENTA ORDER BY Camion ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error en ExcelModel::obtenerCamiones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene datos pivotados para Excel usando solo los productos de los datos filtrados
     */
    public function obtenerDatosPivotExcel($fecha_desde = null, $fecha_hasta = null, $camion = null)
    {
        try {
            // Primero obtenemos los datos base con los mismos filtros
            $datosBase = $this->obtenerDatosTabla($fecha_desde, $fecha_hasta, $camion);

            if (empty($datosBase)) {
                return [
                    'cabeceras' => [],
                    'datos' => [],
                    'totales_productos' => [],
                    'comisiones_productos' => [],
                    'total_general' => 0,
                    'total_comisiones' => 0
                ];
            }

            // Extraer los productos únicos que están en los datos filtrados y sus comisiones
            $productosUnicos = [];
            $comisionesProductos = [];
            foreach ($datosBase as $fila) {
                $producto = $fila['Producto'];
                if (!in_array($producto, $productosUnicos)) {
                    $productosUnicos[] = $producto;
                    $comisionesProductos[$producto] = $fila['Comision'];
                }
            }
            sort($productosUnicos); // Ordenar alfabéticamente

            // Estructura para agrupar por fecha, camión y carga
            $grupos = [];
            $totalesProductos = array_fill_keys($productosUnicos, 0);
            $totalGeneral = 0;
            $totalComisiones = 0;

            foreach ($datosBase as $fila) {
                $clave = $fila['Fecha'] . '|' . $fila['Camion'] . '|' . $fila['Carga'];

                if (!isset($grupos[$clave])) {
                    $grupos[$clave] = [
                        'Fecha' => $fila['Fecha'],
                        'Camion' => $fila['Camion'],
                        'Carga' => $fila['Carga'],
                        'productos' => array_fill_keys($productosUnicos, 0),
                        'total_fila' => 0,
                        'total_comision_fila' => 0
                    ];
                }

                // Sumar cantidad al producto correspondiente
                $grupos[$clave]['productos'][$fila['Producto']] += $fila['Cantidad'];
                $grupos[$clave]['total_fila'] += $fila['Cantidad'];
                $grupos[$clave]['total_comision_fila'] += $fila['Total'];

                // Acumular totales por producto
                $totalesProductos[$fila['Producto']] += $fila['Cantidad'];
                $totalGeneral += $fila['Cantidad'];
                $totalComisiones += $fila['Total'];
            }

            // Ordenar grupos por fecha (descendente) y camión (ascendente)
            uasort($grupos, function ($a, $b) {
                if ($a['Fecha'] == $b['Fecha']) {
                    return strcmp($a['Camion'], $b['Camion']);
                }
                return strcmp($b['Fecha'], $a['Fecha']);
            });

            // Preparar datos para Excel
            $cabeceras = ['Fecha', 'Camión', 'Carga'];
            $cabeceras = array_merge($cabeceras, $productosUnicos);
            $cabeceras[] = 'TOTAL (por carga)';

            $datosExcel = [];
            foreach ($grupos as $grupo) {
                $fila = [
                    $grupo['Fecha'],
                    $grupo['Camion'],
                    $grupo['Carga']
                ];

                // Agregar cantidades por producto (en el orden de productosUnicos)
                foreach ($productosUnicos as $producto) {
                    $fila[] = $grupo['productos'][$producto];
                }

                // Agregar total de la fila (cantidad total)
                $fila[] = $grupo['total_fila'];

                $datosExcel[] = $fila;
            }

            // Agregar fila de TOTAL (por producto)
            $filaTotalProducto = ['TOTAL (por producto)', '', ''];
            foreach ($productosUnicos as $producto) {
                $filaTotalProducto[] = $totalesProductos[$producto];
            }
            $filaTotalProducto[] = $totalGeneral; // Total general de cantidades
            $datosExcel[] = $filaTotalProducto;

            // Agregar fila de COMISIÓN
            $filaComisiones = ['COMISIÓN', '', ''];
            foreach ($productosUnicos as $producto) {
                $filaComisiones[] = $comisionesProductos[$producto];
            }
            $filaComisiones[] = ''; // Celda vacía para el TOTAL
            $datosExcel[] = $filaComisiones;

            // Agregar fila de TOTALES (bs)
            $filaTotalesBs = ['TOTALES (bs)', '', ''];
            foreach ($productosUnicos as $producto) {
                // Total en comisiones = cantidad total * comisión
                $filaTotalesBs[] = $totalesProductos[$producto] * $comisionesProductos[$producto];
            }
            $filaTotalesBs[] = $totalComisiones;
            $datosExcel[] = $filaTotalesBs;

            return [
                'cabeceras' => $cabeceras,
                'datos' => $datosExcel,
                'totales_productos' => $totalesProductos,
                'comisiones_productos' => $comisionesProductos,
                'total_general' => $totalGeneral,
                'total_comisiones' => $totalComisiones,
                'productos' => $productosUnicos
            ];
        } catch (PDOException $e) {
            error_log("Error en ExcelModel::obtenerDatosPivotExcel: " . $e->getMessage());
            return [
                'cabeceras' => [],
                'datos' => [],
                'totales_productos' => [],
                'comisiones_productos' => [],
                'total_general' => 0,
                'total_comisiones' => 0
            ];
        }
    }
}
