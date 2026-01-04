<?php
require_once 'Model.php';

class ActivityLog extends Model {
    
    public function logActivity($userId, $action, $description = '', $ipAddress = null, $userAgent = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $userId,
            $action,
            $description,
            $ipAddress ?: $_SERVER['REMOTE_ADDR'] ?? null,
            $userAgent ?: $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    public function getUserActivities($userId, $limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT al.*, u.username 
            FROM activity_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            WHERE al.user_id = ? 
            ORDER BY al.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAllActivities($limit = 100) {
        $stmt = $this->pdo->prepare("
            SELECT al.*, u.username 
            FROM activity_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            ORDER BY al.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getActivitiesByAction($action, $limit = 50) {
        $stmt = $this->pdo->prepare("
            SELECT al.*, u.username 
            FROM activity_logs al 
            LEFT JOIN users u ON al.user_id = u.id 
            WHERE al.action = ? 
            ORDER BY al.created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$action, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>