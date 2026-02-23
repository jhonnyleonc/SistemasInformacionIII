<?php
require_once __DIR__ . '/../config/database.php';

class ProductoModel
{
    private $db;

    public function __construct()
    {
        $conexion = new Database();
        $this->db = $conexion->getConnection();
    }

    public function registrarProducto($codigo, $nombre, $comision)
    {
        try {
            $sql = "INSERT INTO PRODUCTOS (CODIGO, NOMBRE, Comision) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$codigo, $nombre, $comision]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function buscarProductosSelect($termino)
    {
        try {
            $sql = "SELECT CODIGO, NOMBRE FROM PRODUCTOS 
                WHERE CODIGO LIKE :termino OR NOMBRE LIKE :termino
                ORDER BY NOMBRE ASC";
            $stmt = $this->db->prepare($sql);
            $busqueda = '%' . $termino . '%';
            $stmt->bindParam(':termino', $busqueda);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerTodos()
    {
        try {
            $sql = "SELECT CODIGO, NOMBRE, Comision FROM PRODUCTOS ORDER BY CODIGO";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function buscarProductosTabla($termino)
    {
        try {
            $termino = "%$termino%";
            $sql = "SELECT * FROM PRODUCTOS 
                WHERE CODIGO LIKE ? OR NOMBRE LIKE ? OR Comision LIKE ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$termino, $termino, $termino]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function obtenerProductoPorCodigo($codigo)
    {
        try {
            $sql = "SELECT * FROM PRODUCTOS WHERE CODIGO = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$codigo]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarProducto($codigo, $nombre, $comision)
    {
        try {
            $sql = "UPDATE PRODUCTOS SET NOMBRE = ?, Comision = ? WHERE CODIGO = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$nombre, $comision, $codigo]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function eliminarProducto($codigo)
    {
        try {
            $sql = "DELETE FROM PRODUCTOS WHERE CODIGO = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$codigo]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
