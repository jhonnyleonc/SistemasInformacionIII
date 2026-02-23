<?php
require_once __DIR__ . '/../config/database.php';

class ViajeModel
{
    private $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->getConnection();
    }

    public function registrarViaje($camion, $fecha, $carga, $producto, $cantidad)
    {
        try {
            // 1. Obtener la comisión del producto
            $sqlComision = "SELECT Comision FROM PRODUCTOS WHERE CODIGO = ?";
            $stmtComision = $this->db->prepare($sqlComision);
            $stmtComision->execute([$producto]);
            $comision = $stmtComision->fetchColumn();

            // Si no se encuentra el producto, no continuar
            if ($comision === false) {
                return false;
            }

            // 2. Calcular el total
            $total = floatval($comision) * intval($cantidad);

            // 3. Insertar el viaje con total
            $sql = "INSERT INTO DETALLE_VENTA (Camion, Fecha, Carga, Producto, Cantidad, Total)
                VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$camion, $fecha, $carga, $producto, $cantidad, $total]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerViajesPorCamionYFecha($camion, $fecha)
    {
        try {
            $sql = "SELECT dv.Nro, dv.Camion, dv.Fecha, dv.Carga, dv.Producto, 
                       p.NOMBRE AS NombreProducto, dv.Cantidad, dv.Total
                FROM DETALLE_VENTA dv
                JOIN PRODUCTOS p ON p.CODIGO = dv.Producto
                WHERE dv.Camion = ? AND dv.Fecha = ?
                ORDER BY dv.Nro DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$camion, $fecha]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerViajePorId($nro)
    {
        try {
            $sql = "SELECT dv.Nro, dv.Camion, dv.Fecha, dv.Carga, dv.Producto, dv.Cantidad, dv.Total,
                       p.NOMBRE AS NombreProducto
                FROM DETALLE_VENTA dv
                JOIN PRODUCTOS p ON p.CODIGO = dv.Producto
                WHERE dv.Nro = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$nro]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function actualizarViaje($nro, $camion, $fecha, $carga, $producto, $cantidad)
    {
        try {
            // Obtener la comisión actual del producto
            $sqlComision = "SELECT Comision FROM PRODUCTOS WHERE CODIGO = ?";
            $stmtComision = $this->db->prepare($sqlComision);
            $stmtComision->execute([$producto]);
            $comision = $stmtComision->fetchColumn();

            if ($comision === false) {
                return false;
            }

            $total = floatval($comision) * intval($cantidad);

            $sql = "UPDATE DETALLE_VENTA 
                SET Camion=?, Fecha=?, Carga=?, Producto=?, Cantidad=?, Total=? 
                WHERE Nro=?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$camion, $fecha, $carga, $producto, $cantidad, $total, $nro]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarViaje($nro)
    {
        try {
            $sql = "DELETE FROM DETALLE_VENTA WHERE Nro = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$nro]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerResumenPorCamionYFecha($camion, $fecha)
    {
        try {
            // Consulta para obtener el resumen por tipo de carga
            $sql = "SELECT Carga, 
                   SUM(Cantidad) AS TotalCantidad,
                   SUM(Total) AS TotalBs
            FROM DETALLE_VENTA
            WHERE Camion = ? AND Fecha = ?
            GROUP BY Carga
            ORDER BY Carga ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$camion, $fecha]);
            $resumenCargas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Consulta para obtener el total general
            $sqlTotal = "SELECT 
                    SUM(Cantidad) AS TotalGeneralPaquetes,
                    SUM(Total) AS TotalGeneralBs
                 FROM DETALLE_VENTA
                 WHERE Camion = ? AND Fecha = ?";
            $stmtTotal = $this->db->prepare($sqlTotal);
            $stmtTotal->execute([$camion, $fecha]);
            $totalGeneral = $stmtTotal->fetch(PDO::FETCH_ASSOC);

            return [
                'cargas' => $resumenCargas,
                'total' => $totalGeneral,
                'camion' => $camion,
                'fecha' => $fecha
            ];
        } catch (PDOException $e) {
            return [];
        }
    }
}
