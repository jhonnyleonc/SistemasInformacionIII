<?php
class Database {
    private $servidor = "localhost"; //127.0.0.1
    private $base_de_datos = "transporte_gutierrez";
    private $usuario = "root";
    private $contrasenia = "";
    private $conexion;
    
    /**
     * Constructor que establece la conexión
     */
    public function __construct() {
        try {
            $this->conexion = new PDO(
                "mysql:host=" . $this->servidor . ";dbname=" . $this->base_de_datos,
                $this->usuario,
                $this->contrasenia
            );
            // Configurar PDO para que lance excepciones en caso de errores
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $ex) {
            echo "Error de conexión: " . $ex->getMessage();
            die();
        }
    }
    
    /**
     * Devuelve la conexión PDO activa
     */
    public function getConnection() {
        return $this->conexion;
    }
}
?>