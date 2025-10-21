<?php
/**
 * Configuración de la base de datos
 */

class Database {
    private $host = "localhost";
    private $db_name = "pos_restaurant";
    private $username = "root";
    private $password = "";
    private $conn;

    /**
     * Obtener conexión a la base de datos
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            die("Error al conectar con la base de datos. Por favor, contacte al administrador.");
        }

        return $this->conn;
    }

    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }
}
?>