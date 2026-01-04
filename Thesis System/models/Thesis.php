<?php
require_once 'Model.php';

class Thesis extends Model {
    
    public function createThesis($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO theses (title, abstract, keywords, author_id, adviser_id, department_id, program_id, year, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['title'],
            $data['abstract'],
            $data['keywords'],
            $data['author_id'],
            $data['adviser_id'],
            $data['department_id'],
            $data['program_id'],
            $data['year'],
            $data['status']
        ]);
    }
    
    public function updateThesis($id, $data) {
        $stmt = $this->pdo->prepare("
            UPDATE theses SET 
                title = ?, abstract = ?, keywords = ?, adviser_id = ?, department_id = ?, 
                program_id = ?, year = ?, status = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['title'],
            $data['abstract'],
            $data['keywords'],
            $data['adviser_id'],
            $data['department_id'],
            $data['program_id'],
            $data['year'],
            $data['status'],
            $id
        ]);
    }
    
    public function submitThesis($id) {
        $stmt = $this->pdo->prepare("UPDATE theses SET status = 'submitted', submitted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function getThesisById($id) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, u.first_name as author_first_name, u.last_name as author_last_name,
                   adv.first_name as adviser_first_name, adv.last_name as adviser_last_name,
                   d.name as department_name, p.name as program_name
            FROM theses t
            LEFT JOIN users u ON t.author_id = u.id
            LEFT JOIN users adv ON t.adviser_id = adv.id
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN programs p ON t.program_id = p.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getThesesByAuthor($authorId) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, u.first_name as author_first_name, u.last_name as author_last_name,
                   adv.first_name as adviser_first_name, adv.last_name as adviser_last_name,
                   d.name as department_name, p.name as program_name
            FROM theses t
            LEFT JOIN users u ON t.author_id = u.id
            LEFT JOIN users adv ON t.adviser_id = adv.id
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN programs p ON t.program_id = p.id
            WHERE t.author_id = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$authorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllTheses() {
        $stmt = $this->pdo->prepare("
            SELECT t.*, u.first_name as author_first_name, u.last_name as author_last_name,
                   adv.first_name as adviser_first_name, adv.last_name as adviser_last_name,
                   d.name as department_name, p.name as program_name
            FROM theses t
            LEFT JOIN users u ON t.author_id = u.id
            LEFT JOIN users adv ON t.adviser_id = adv.id
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN programs p ON t.program_id = p.id
            ORDER BY t.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getThesesByStatus($status) {
        $stmt = $this->pdo->prepare("
            SELECT t.*, u.first_name as author_first_name, u.last_name as author_last_name,
                   adv.first_name as adviser_first_name, adv.last_name as adviser_last_name,
                   d.name as department_name, p.name as program_name
            FROM theses t
            LEFT JOIN users u ON t.author_id = u.id
            LEFT JOIN users adv ON t.adviser_id = adv.id
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN programs p ON t.program_id = p.id
            WHERE t.status = ?
            ORDER BY t.created_at DESC
        ");
        $stmt->execute([$status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function searchTheses($query) {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, u.first_name as author_first_name, u.last_name as author_last_name,
                   adv.first_name as adviser_first_name, adv.last_name as adviser_last_name,
                   d.name as department_name, p.name as program_name
            FROM theses t
            LEFT JOIN users u ON t.author_id = u.id
            LEFT JOIN users adv ON t.adviser_id = adv.id
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN programs p ON t.program_id = p.id
            WHERE t.title LIKE ? OR t.abstract LIKE ? OR t.keywords LIKE ?
            ORDER BY t.created_at DESC
        ");
        $searchTerm = '%' . $query . '%';
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function deleteThesis($id, $authorId) {
        // Only allow authors to delete their own theses
        $stmt = $this->pdo->prepare("DELETE FROM theses WHERE id = ? AND author_id = ?");
        return $stmt->execute([$id, $authorId]);
    }
    
    public function getStudentThesisCount($studentId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM theses WHERE author_id = ?");
        $stmt->execute([$studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getStudentThesisCountByStatus($studentId, $status) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM theses WHERE author_id = ? AND status = ?");
        $stmt->execute([$studentId, $status]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    public function getRecentStudentTheses($studentId, $limit = 5) {
        $stmt = $this->pdo->prepare(
            "SELECT t.*, u.first_name as author_first_name, u.last_name as author_last_name,
                   adv.first_name as adviser_first_name, adv.last_name as adviser_last_name,
                   d.name as department_name, p.name as program_name
            FROM theses t
            LEFT JOIN users u ON t.author_id = u.id
            LEFT JOIN users adv ON t.adviser_id = adv.id
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN programs p ON t.program_id = p.id
            WHERE t.author_id = ?
            ORDER BY t.created_at DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute([$studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>