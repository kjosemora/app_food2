<?php
/**
 * Modelo de Usuario
 */

class Usuario {
    private $conn;
    private $table = 'usuarios';

    public $id;
    public $nombre;
    public $email;
    public $password_hash;
    public $rol;
    public $activo;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Autenticar usuario
     */
    public function login($email, $password) {
        // Normalizar email
        $email_search = strtolower(trim($email));

        $query = "SELECT id, nombre, email, password_hash, rol, activo 
                  FROM " . $this->table . " 
                  WHERE LOWER(email) = :email AND activo = 1 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email_search);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            
            if (password_verify($password, $row['password_hash'])) {
                $this->id = $row['id'];
                $this->nombre = $row['nombre'];
                $this->email = $row['email'];
                $this->rol = $row['rol'];
                return true;
            }
        }
        return false;
    }

    /**
     * Obtener todos los usuarios
     */
    public function getAll($rol = null) {
        $query = "SELECT id, nombre, email, rol, activo, fecha_creacion 
                  FROM " . $this->table;
        
        if ($rol) {
            $query .= " WHERE rol = :rol";
        }
        
        $query .= " ORDER BY nombre ASC";

        $stmt = $this->conn->prepare($query);
        
        if ($rol) {
            $stmt->bindParam(':rol', $rol);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Obtener usuario por ID
     */
    public function getById($id) {
        $query = "SELECT id, nombre, email, rol, activo, fecha_creacion 
                  FROM " . $this->table . " 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Crear usuario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (nombre, email, password_hash, rol, activo) 
                  VALUES (:nombre, :email, :password_hash, :rol, :activo)";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $password_hashed = password_hash($this->password_hash, PASSWORD_DEFAULT);

        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password_hash', $password_hashed);
        $stmt->bindParam(':rol', $this->rol);
        $stmt->bindParam(':activo', $this->activo);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    /**
     * Actualizar usuario
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET nombre = :nombre, email = :email, rol = :rol, activo = :activo";
        
        if (!empty($this->password_hash)) {
            $query .= ", password_hash = :password_hash";
        }
        
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':rol', $this->rol);
        $stmt->bindParam(':activo', $this->activo);
        $stmt->bindParam(':id', $this->id);

        if (!empty($this->password_hash)) {
            $password_hashed = password_hash($this->password_hash, PASSWORD_DEFAULT);
            $stmt->bindParam(':password_hash', $password_hashed);
        }

        return $stmt->execute();
    }

    /**
     * Eliminar usuario (desactivar)
     */
    public function delete() {
        $query = "UPDATE " . $this->table . " SET activo = 0 WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Verificar si email existe
     */
    public function emailExists($email, $exclude_id = null) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        
        if ($exclude_id) {
            $query .= " AND id != :exclude_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        
        if ($exclude_id) {
            $stmt->bindParam(':exclude_id', $exclude_id);
        }
        
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>