<?php
/**
 * Modelo de Configuración
 */

class Configuracion {
    private $conn;
    private $table = 'configuracion';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener valor de configuración
     */
    public function getValor($clave) {
        $query = "SELECT valor FROM " . $this->table . " WHERE clave = :clave";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clave', $clave);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ? $result['valor'] : null;
    }

    /**
     * Actualizar valor de configuración
     */
    public function setValor($clave, $valor) {
        $query = "INSERT INTO " . $this->table . " (clave, valor) 
                  VALUES (:clave, :valor) 
                  ON DUPLICATE KEY UPDATE valor = :valor2";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':clave', $clave);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':valor2', $valor);

        return $stmt->execute();
    }

    /**
     * Obtener tasa de cambio
     */
    public function getTasaCambio() {
        return (float) $this->getValor('tasa_cambio');
    }

    /**
     * Actualizar tasa de cambio (alias)
     */
    public function setTasaCambio($nueva_tasa) {
        return $this->setValor('tasa_cambio', $nueva_tasa);
    }

    /**
     * Actualizar tasa de cambio
     */
    public function actualizarTasaCambio($nueva_tasa) {
        return $this->setValor('tasa_cambio', $nueva_tasa);
    }

    /**
     * Obtener todas las configuraciones
     */
    public function getAll() {
        $query = "SELECT * FROM " . $this->table . " ORDER BY clave";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>