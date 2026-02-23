<?php
require_once __DIR__ . '/../config/database.php';

class ReporteModel
{
    private $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->getConnection();
    }

    /**
     * Obtiene los códigos de camiones únicos registrados
     */
    public function obtenerCamiones()
    {
        try {
            $sql = "SELECT DISTINCT Camion FROM DETALLE_VENTA ORDER BY Camion";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Obtiene los datos para las tarjetas de resumen
     */
    public function obtenerDatosResumen($fechaInicio, $fechaFin, $camion = null)
    {
        try {
            // Construir la consulta con filtros
            $where = ["Fecha BETWEEN ? AND ?"];
            $params = [$fechaInicio, $fechaFin];

            if ($camion) {
                $where[] = "Camion = ?";
                $params[] = $camion;
            }

            $whereClause = implode(' AND ', $where);

            // Consulta para obtener los datos
            $sql = "SELECT 
                    SUM(Cantidad) as totalPaquetes,
                    SUM(Total) as totalGanancias,
                    COUNT(DISTINCT Nro) as totalViajes,
                    COUNT(DISTINCT Producto) as totalProductos
                FROM DETALLE_VENTA
                WHERE $whereClause";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [
                'totalPaquetes' => 0,
                'totalGanancias' => 0,
                'totalViajes' => 0,
                'totalProductos' => 0
            ];
        }
    }

    /**
     * Obtiene los datos para los gráficos
     */
    public function obtenerDatosGraficos($fechaInicio, $fechaFin, $camion = null)
    {
        try {
            // 1. Procesar los gráficos semanales (7 días)
            $rangoFechasSemanales = $this->ajustarRango7Dias($fechaInicio, $fechaFin);
            $fechaInicioSemanal = $rangoFechasSemanales['inicio'];
            $fechaFinSemanal = $rangoFechasSemanales['fin'];

            // Construir condiciones para gráficos semanales
            $whereSemanal = ["Fecha BETWEEN ? AND ?"];
            $paramsSemanal = [$fechaInicioSemanal, $fechaFinSemanal];

            if ($camion) {
                $whereSemanal[] = "Camion = ?";
                $paramsSemanal[] = $camion;
            }

            $whereClauseSemanal = implode(' AND ', $whereSemanal);

            // 2. Procesar el gráfico de productos (rango completo)
            $whereProductos = ["Fecha BETWEEN ? AND ?"];
            $paramsProductos = [$fechaInicio, $fechaFin];

            if ($camion) {
                $whereProductos[] = "Camion = ?";
                $paramsProductos[] = $camion;
            }

            $whereClauseProductos = implode(' AND ', $whereProductos);

            // 1. Datos para gráfico de paquetes por día (semanal)
            $sqlPaquetes = "SELECT 
                        Fecha,
                        SUM(Cantidad) as cantidad
                    FROM DETALLE_VENTA
                    WHERE $whereClauseSemanal
                    GROUP BY Fecha
                    ORDER BY Fecha";

            $stmtPaquetes = $this->db->prepare($sqlPaquetes);
            $stmtPaquetes->execute($paramsSemanal);
            $paquetesData = $stmtPaquetes->fetchAll(PDO::FETCH_ASSOC);

            // 2. Datos para gráfico de ganancias por día (semanal)
            $sqlGanancias = "SELECT 
                        Fecha,
                        SUM(Total) as ganancias
                    FROM DETALLE_VENTA
                    WHERE $whereClauseSemanal
                    GROUP BY Fecha
                    ORDER BY Fecha";

            $stmtGanancias = $this->db->prepare($sqlGanancias);
            $stmtGanancias->execute($paramsSemanal);
            $gananciasData = $stmtGanancias->fetchAll(PDO::FETCH_ASSOC);

            // 3. Datos para gráfico de viajes por día (semanal)
            $sqlViajes = "SELECT 
                    Fecha,
                    COUNT(*) as viajes
                FROM DETALLE_VENTA
                WHERE $whereClauseSemanal
                GROUP BY Fecha
                ORDER BY Fecha";

            $stmtViajes = $this->db->prepare($sqlViajes);
            $stmtViajes->execute($paramsSemanal);
            $viajesData = $stmtViajes->fetchAll(PDO::FETCH_ASSOC);

            // 4. Datos para gráfico de productos más vendidos (rango completo)
            $sqlProductos = "SELECT 
                        p.CODIGO,
                        p.NOMBRE,
                        SUM(dv.Cantidad) as cantidad
                    FROM DETALLE_VENTA dv
                    JOIN PRODUCTOS p ON p.CODIGO = dv.Producto
                    WHERE $whereClauseProductos
                    GROUP BY p.CODIGO, p.NOMBRE
                    ORDER BY cantidad DESC
                    LIMIT 5";

            $stmtProductos = $this->db->prepare($sqlProductos);
            $stmtProductos->execute($paramsProductos);
            $productosData = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

            // Procesar los datos para los gráficos semanales
            $labelsSemanales = $this->generarLabelsFechas($fechaInicioSemanal, $fechaFinSemanal);

            return [
                'paquetes' => $this->formatearDatosGrafico($labelsSemanales, $paquetesData, 'cantidad'),
                'ganancias' => $this->formatearDatosGrafico($labelsSemanales, $gananciasData, 'ganancias'),
                'viajes' => $this->formatearDatosGrafico($labelsSemanales, $viajesData, 'viajes'),
                'productos' => $productosData
            ];
        } catch (PDOException $e) {
            return [
                'paquetes' => ['labels' => [], 'data' => []],
                'ganancias' => ['labels' => [], 'data' => []],
                'viajes' => ['labels' => [], 'data' => []],
                'productos' => []
            ];
        }
    }

    /**
     * Ajusta el rango de fechas para asegurar 7 días
     */
    private function ajustarRango7Dias($fechaInicio, $fechaFin)
    {
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);

        // Si el rango es menor a 7 días, extendemos hacia atrás
        $diferencia = $inicio->diff($fin)->days;

        if ($diferencia < 6) {
            $inicio->modify('-' . (6 - $diferencia) . ' days');
        }
        // Si el rango es mayor a 7 días, mostramos solo los últimos 7 días
        elseif ($diferencia > 6) {
            $inicio = clone $fin;
            $inicio->modify('-6 days');
        }

        return [
            'inicio' => $inicio->format('Y-m-d'),
            'fin' => $fin->format('Y-m-d')
        ];
    }

    /**
     * Genera labels de fechas para los gráficos
     */
    private function generarLabelsFechas($fechaInicio, $fechaFin)
    {
        $labels = [];
        $inicio = new DateTime($fechaInicio);
        $fin = new DateTime($fechaFin);

        while ($inicio <= $fin) {
            $labels[] = $inicio->format('d M'); // Formato: "01 Ago"
            $inicio->modify('+1 day');
        }

        return $labels;
    }

    /**
     * Formatea los datos para los gráficos
     */
    private function formatearDatosGrafico($labels, $dbData, $campoValor)
    {
        $data = array_fill(0, count($labels), 0);

        // Mapeamos los datos de la BD a las posiciones correctas
        foreach ($dbData as $item) {
            $fecha = new DateTime($item['Fecha']);
            $index = array_search($fecha->format('d M'), $labels);

            if ($index !== false) {
                $data[$index] = (float)$item[$campoValor];
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    
}
