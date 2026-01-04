<?php
require_once 'Model.php';

class User extends Model
{

    public function findByUsername($username)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password, role, first_name, last_name, middle_name, student_id, program_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $data['password'],
            $data['role'],
            $data['first_name'],
            $data['last_name'],
            $data['middle_name'] ?? null,
            $data['student_id'] ?? null,
            $data['program_id'] ?? null
        ]);
    }

    public function updateLastLogin($userId)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    public function getAllUsers()
    {
        $stmt = $this->pdo->prepare("SELECT u.*, p.name as program_name FROM users u LEFT JOIN programs p ON u.program_id = p.id ORDER BY u.created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserWithDetails($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, p.name as program_name, d.name as department_name 
            FROM users u 
            LEFT JOIN programs p ON u.program_id = p.id 
            LEFT JOIN departments d ON p.department_id = d.id 
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function updateUser($userId, $data)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE id = ?");
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $userId
        ]);
    }

    public function updateProfilePicture($userId, $picturePath)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
        return $stmt->execute([$picturePath, $userId]);
    }

    public function updateSignature($userId, $signaturePath)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET signature = ? WHERE id = ?");
        return $stmt->execute([$signaturePath, $userId]);
    }

    public function updateProgram($userId, $programId)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET program_id = ? WHERE id = ?");
        return $stmt->execute([$programId, $userId]);
    }

    public function getAllPrograms()
    {
        $stmt = $this->pdo->prepare("SELECT id, name FROM programs ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserFull($userId, $data)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, username = ?, role = ?, program_id = ?, is_active = ? WHERE id = ?");
        return $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['username'],
            $data['role'],
            $data['program_id'] ?? null,
            $data['is_active'],
            $userId
        ]);
    }

    public function getUserById($userId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updatePassword($userId, $newPassword)
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $userId]);
    }
}
