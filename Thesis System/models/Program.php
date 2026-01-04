<?php
require_once 'Model.php';

class Program extends Model {
    
    public function getAllPrograms() {
        $stmt = $this->pdo->prepare("
            SELECT p.*, d.name as department_name 
            FROM programs p 
            LEFT JOIN departments d ON p.department_id = d.id
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProgramById($id) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, d.name as department_name 
            FROM programs p 
            LEFT JOIN departments d ON p.department_id = d.id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getProgramsByDepartment($departmentId) {
        $stmt = $this->pdo->prepare("SELECT * FROM programs WHERE department_id = ?");
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function createProgram($data) {
        $stmt = $this->pdo->prepare("INSERT INTO programs (department_id, name, description) VALUES (?, ?, ?)");
        return $stmt->execute([$data['department_id'], $data['name'], $data['description']]);
    }
    
    public function updateProgram($id, $data) {
        $stmt = $this->pdo->prepare("UPDATE programs SET department_id = ?, name = ?, description = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$data['department_id'], $data['name'], $data['description'], $id]);
    }
    
    public function deleteProgram($id) {
        return $this->deleteById('programs', $id);
    }
}
?>