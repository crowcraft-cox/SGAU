<?php

require_once __DIR__ . '/../Core/Database.php';

class UserRepository {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->connect();
    }

    /**
     * Récupère tous les utilisateurs
     */
    public function getAll() {
        $query = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par ID
     */
    public function getById($id) {
        $query = "SELECT id, name, email, role, created_at, updated_at FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère un utilisateur par email
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function create(array $data) {
        $query = "INSERT INTO users (name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())";
        $stmt = $this->db->prepare($query);
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        return $stmt->execute([
            $data['name'],
            $data['email'],
            $hashedPassword,
            $data['role']
        ]);
    }

    /**
     * Met à jour un utilisateur (complet)
     */
    public function update($id, array $data) {
        $fields = ["name = ?", "email = ?", "role = ?", "updated_at = NOW()"];
        $params = [$data['name'], $data['email'], $data['role']];

        if (!empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $params[] = $id;
        $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Supprime un utilisateur
     */
    public function delete($id) {
        $query = "DELETE FROM users WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }

    /**
     * Met à jour uniquement le rôle (compatibilité)
     */
    public function updateRole($userId, $newRole) {
        $validRoles = ['admin', 'enseignant', 'etudiant', 'finance'];
        if (!in_array($newRole, $validRoles)) {
            return false;
        }

        $query = "UPDATE users SET role = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$newRole, $userId]);
    }

    /**
     * Récupère les utilisateurs par rôle
     */
    public function getByRole($role) {
        $validRoles = ['admin', 'enseignant', 'etudiant', 'finance'];
        if (!in_array($role, $validRoles)) {
            return [];
        }
        
        $query = "SELECT id, name, email, role, created_at FROM users WHERE role = ? ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$role]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte les utilisateurs par rôle
     */
    public function countByRole($role) {
        $validRoles = ['admin', 'enseignant', 'etudiant', 'finance'];
        if (!in_array($role, $validRoles)) {
            return 0;
        }

        $query = "SELECT COUNT(*) as count FROM users WHERE role = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$role]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * Obtient les statistiques des utilisateurs
     */
    public function getStatistics() {
        $query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = [
            'admin' => 0,
            'enseignant' => 0,
            'etudiant' => 0,
            'finance' => 0,
            'total' => 0
        ];

        foreach ($results as $row) {
            $stats[$row['role']] = (int)$row['count'];
            $stats['total'] += (int)$row['count'];
        }

        return $stats;
    }
}
