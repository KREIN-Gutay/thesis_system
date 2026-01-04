<?php
require_once 'Model.php';

class Department extends Model {
    
    public function getAllDepartments() {
        return $this->findAll('departments');
    }
    
    public function getDepartmentById($id) {
        return $this->findById('departments', $id);
    }
    
    public function createDepartment($data) {
        $stmt = $this->pdo->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
        return $stmt->execute([$data['name'], $data['description']]);
    }
    
    public function updateDepartment($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE departments SET name = ?, description = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$data['name'], $data['description'], $id]);
    }
    
    public function deleteDepartment($id) {
        return $this->deleteById('departments', $id);
    }
}
?>