<?php

class DatabaseConnection {
    private $host = 'localhost';
    private $db_name = 'employee-task-management-system';
    private $username = 'root';
    private $password = '';
    private $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    private $conn; 

    public function __construct() {
        try {
            $this->conn = new PDO("mysql:host={$this->host};dbname={$this->db_name}", $this->username, $this->password, $this->options);
            // new PDO("mysql:host=localhost;dbname=basic-php-crud, root, ''"); This is the same as the line above, shown for better understanding
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function getConnection() {
        return $this->conn;
    }

    public function select($query, $params = []) {
        $qry = $this->conn->prepare($query);
        $qry->execute($params); 
        return $qry->fetchAll(); 
    }

    public function create($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $this->conn->lastInsertId();
    }

    public function update($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount(); 
    }

    public function delete($query, $params = []) {
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->rowCount(); 
    }
}