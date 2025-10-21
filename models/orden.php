<?php
/**
 * Modelo Orden (básico)
 */

class Orden {
    private $conn;
    private $table = 'ordenes';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener estadísticas de ventas entre dos fechas. Si se pasa mesero_id, filtra por mesero.
     */
    public function getEstadisticas($desde, $hasta, $mesero_id = null) {
        $where = " WHERE fecha BETWEEN :desde AND :hasta";
        if ($mesero_id) {
            $where .= " AND mesero_id = :mesero_id";
        }

        $query = "SELECT 
                    COUNT(*) as total_ordenes,
                    SUM(total_usd) as total_usd,
                    SUM(total_bs) as total_bs,
                    AVG(total_usd) as promedio_usd
                  FROM " . $this->table . $where;

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':desde', $desde);
        $stmt->bindParam(':hasta', $hasta);
        if ($mesero_id) {
            $stmt->bindParam(':mesero_id', $mesero_id);
        }
        $stmt->execute();
        $row = $stmt->fetch();
        return $row ?: [];
    }

    /**
     * Obtener órdenes (opciones: mesero_id, limit/limite, offset)
     */
    public function getAll($options = []) {
        $params = [];
        $where = '';

        if (!empty($options['mesero_id'])) {
            $where = ' WHERE mesero_id = :mesero_id';
            $params[':mesero_id'] = $options['mesero_id'];
        }

        $limit = '';
        if (!empty($options['limit'])) {
            $limit = ' LIMIT ' . (int)$options['limit'];
        } elseif (!empty($options['limite'])) {
            $limit = ' LIMIT ' . (int)$options['limite'];
        }

        $query = "SELECT o.*, u.nombre as mesero_nombre, c.nombre as cliente_nombre 
                  FROM " . $this->table . " o 
                  LEFT JOIN usuarios u ON o.mesero_id = u.id 
                  LEFT JOIN clientes c ON o.cliente_id = c.id 
                  " . $where . " ORDER BY o.fecha DESC" . $limit;

        $stmt = $this->conn->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener órdenes para cocina (estado Pendiente o En Preparación)
     */
    public function getOrdenesParaCocina() {
        $query = "SELECT o.*, u.nombre as mesero_nombre 
                  FROM " . $this->table . " o 
                  LEFT JOIN usuarios u ON o.mesero_id = u.id 
                  WHERE o.estado IN ('Pendiente', 'En Preparación') 
                  ORDER BY o.fecha DESC LIMIT 50";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $ordenes = $stmt->fetchAll();

        // Opcional: agregar detalles si existen tablas relacionadas (detalles)
        foreach ($ordenes as &$o) {
            $o['detalles'] = $this->getDetalles($o['id']);
        }

        return $ordenes;
    }

    private function getDetalles($orden_id) {
        $query = "SELECT od.*, p.nombre as producto_nombre 
                  FROM orden_detalles od 
                  LEFT JOIN productos p ON od.producto_id = p.id 
                  WHERE od.orden_id = :orden_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':orden_id', $orden_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

?>
