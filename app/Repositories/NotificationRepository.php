<?php

require_once __DIR__ . '/../Core/Database.php';

class NotificationRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll($limit = 50, $offset = 0) {
        $stmt = $this->db->prepare('SELECT n.*, u.name AS user_name FROM notifications n LEFT JOIN users u ON n.user_id = u.id ORDER BY n.created_at DESC LIMIT ? OFFSET ?');
        $stmt->bindValue(1, (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByUserId($userId, $limit = 50) {
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?');
        $stmt->bindValue(1, (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(2, (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount($userId) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND lue = 0');
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM notifications WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO notifications (user_id, titre, message, lien, lue) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$data['user_id'], $data['titre'], $data['message'], $data['lien'] ?? null, $data['lue'] ?? 0]);
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE notifications SET titre = ?, message = ?, lien = ?, lue = ? WHERE id = ?');
        return $stmt->execute([$data['titre'], $data['message'], $data['lien'] ?? null, $data['lue'], $id]);
    }

    public function markAsRead($id) {
        $stmt = $this->db->prepare('UPDATE notifications SET lue = 1 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function markAllAsRead($userId) {
        $stmt = $this->db->prepare('UPDATE notifications SET lue = 1 WHERE user_id = ?');
        return $stmt->execute([$userId]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM notifications WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
