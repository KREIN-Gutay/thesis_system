<?php
class Model {
    protected $pdo;
    
    public function __construct($database) {
        $this->pdo = $database;
    }
    
    // Generic method to fetch all records from a table
    public function findAll($table) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$table}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Generic method to find a record by ID
    public function findById($table, $id) {
        $stmt = $this->pdo->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Generic method to delete a record by ID
    public function deleteById($table, $id) {
        $stmt = $this->pdo->prepare("DELETE FROM {$table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>