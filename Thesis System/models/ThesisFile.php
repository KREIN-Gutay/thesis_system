<?php
require_once 'Model.php';

class ThesisFile extends Model {
    
    public function createFile($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO thesis_files (thesis_id, file_name, file_path, file_size, mime_type) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['thesis_id'],
            $data['file_name'],
            $data['file_path'],
            $data['file_size'],
            $data['mime_type']
        ]);
    }
    
    public function getFilesByThesis($thesisId) {
        $stmt = $this->pdo->prepare("SELECT * FROM thesis_files WHERE thesis_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$thesisId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getFileById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM thesis_files WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>