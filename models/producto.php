<?php
/**
 * Modelo de Producto
 */

class Producto {
    private $conn;
    private $table = 'productos';

    public $id;
    public $nombre;
    public $descripcion;
    public $precio_usd;
    public $precio_bs;
    public $categoria_id;
    public $imagen;
    public $disponible;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los productos
     */
    public function getAll($disponible_only = false) {
        $query = "SELECT p.*, c.nombre as categoria_nombre 
                  FROM " . $this->table . " p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id";
        
        if ($disponible_only) {
            $query .= " WHERE p.disponible = 1";
        }
        
        $query .= " ORDER BY c.nombre, p.nombre";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener producto por ID
     */
    public function getById($id) {
        $query = "SELECT p.*, c.nombre as categoria_nombre 
                  FROM " . $this->table . " p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                  WHERE p.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Obtener productos por categoría
     */
    public function getByCategoria($categoria_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE categoria_id = :categoria_id AND disponible = 1 
                  ORDER BY nombre";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':categoria_id', $categoria_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Crear producto
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (nombre, descripcion, precio_usd, precio_bs, categoria_id, imagen, disponible) 
                  VALUES (:nombre, :descripcion, :precio_usd, :precio_bs, :categoria_id, :imagen, :disponible)";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));

        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':precio_usd', $this->precio_usd);
        $stmt->bindParam(':precio_bs', $this->precio_bs);
        $stmt->bindParam(':categoria_id', $this->categoria_id);
        $stmt->bindParam(':imagen', $this->imagen);
        $stmt->bindParam(':disponible', $this->disponible);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Actualizar producto
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nombre = :nombre, 
                      descripcion = :descripcion, 
                      precio_usd = :precio_usd, 
                      precio_bs = :precio_bs, 
                      categoria_id = :categoria_id, 
                      disponible = :disponible";
        
        if ($this->imagen !== null) {
            $query .= ", imagen = :imagen";
        }
        
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));

        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':precio_usd', $this->precio_usd);
        $stmt->bindParam(':precio_bs', $this->precio_bs);
        $stmt->bindParam(':categoria_id', $this->categoria_id);
        $stmt->bindParam(':disponible', $this->disponible);
        $stmt->bindParam(':id', $this->id);

        if ($this->imagen !== null) {
            $stmt->bindParam(':imagen', $this->imagen);
        }

        return $stmt->execute();
    }

    /**
     * Eliminar producto (desactivar)
     */
    public function delete() {
        $query = "UPDATE " . $this->table . " SET disponible = 0 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Actualizar precios según tasa de cambio
     */
    public function updatePricesByExchangeRate($tasa_cambio) {
        $query = "UPDATE " . $this->table . " SET precio_bs = precio_usd * :tasa_cambio";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':tasa_cambio', $tasa_cambio);

        return $stmt->execute();
    }

    /**
     * Buscar productos
     */
    public function search($term) {
        $query = "SELECT p.*, c.nombre as categoria_nombre 
                  FROM " . $this->table . " p 
                  LEFT JOIN categorias c ON p.categoria_id = c.id 
                  WHERE p.nombre LIKE :term OR p.descripcion LIKE :term 
                  ORDER BY p.nombre";

        $stmt = $this->conn->prepare($query);
        $search_term = "%{$term}%";
        $stmt->bindParam(':term', $search_term);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
?>