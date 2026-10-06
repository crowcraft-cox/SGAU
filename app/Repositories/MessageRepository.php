<?php

require_once __DIR__ . '/../Core/Database.php';

class MessageRepository
{
    private $db;

    public function __construct()
    {
        $this->db = (new Database())->connect();
    }

    /**
     * Récupère les conversations d'un utilisateur
     */
    public function getConversations($userId)
    {
        $stmt = $this->db->prepare("
            SELECT 
                u.id,
                u.name,
                u.email,
                m.message as last_message,
                m.created_at as last_message_time,
                (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
            FROM users u
            JOIN (
                SELECT sender_id, receiver_id, MAX(id) as max_id
                FROM messages
                WHERE sender_id = ? OR receiver_id = ?
                GROUP BY 
                    CASE 
                        WHEN sender_id = ? THEN receiver_id 
                        ELSE sender_id 
                    END
            ) conv ON (u.id = conv.sender_id AND conv.receiver_id = ?) 
                      OR (u.id = conv.receiver_id AND conv.sender_id = ?)
            JOIN messages m ON m.id = conv.max_id
            WHERE u.id != ?
            ORDER BY m.created_at DESC
        ");
        $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les messages entre deux utilisateurs
     */
    public function getMessages($userId, $otherUserId, $limit = 50, $offset = 0)
    {
        $limit = max(1, (int)$limit);
        $offset = max(0, (int)$offset);

        $stmt = $this->db->prepare("
            SELECT 
                m.*,
                u_sender.name as sender_name,
                u_receiver.name as receiver_name
            FROM messages m
            LEFT JOIN users u_sender ON m.sender_id = u_sender.id
            LEFT JOIN users u_receiver ON m.receiver_id = u_receiver.id
            WHERE 
                (m.sender_id = ? AND m.receiver_id = ?) OR
                (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at DESC
            LIMIT {$limit} OFFSET {$offset}
        ");
        $stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Crée un nouveau message
     */
    public function create(array $data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO messages (sender_id, receiver_id, message, file_path, file_name, is_read)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['sender_id'] ?? null,
            $data['receiver_id'] ?? null,
            $data['message'] ?? '',
            $data['file_path'] ?? null,
            $data['file_name'] ?? null,
            0
        ]);
        return $this->db->lastInsertId();
    }

    /**
     * Marque un message comme lu
     */
    public function markAsRead($messageId)
    {
        $stmt = $this->db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$messageId]);
    }

    /**
     * Marque tous les messages d'une conversation comme lus
     */
    public function markConversationAsRead($userId, $otherUserId)
    {
        $stmt = $this->db->prepare("
            UPDATE messages 
            SET is_read = 1 
            WHERE receiver_id = ? AND sender_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId, $otherUserId]);
    }

    /**
     * Récupère un message par ID
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT 
                m.*,
                u_sender.name as sender_name,
                u_receiver.name as receiver_name
            FROM messages m
            LEFT JOIN users u_sender ON m.sender_id = u_sender.id
            LEFT JOIN users u_receiver ON m.receiver_id = u_receiver.id
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Supprime un message
     */
    public function delete($id)
    {
        // Supprimer le fichier s'il existe
        $message = $this->findById($id);
        if ($message && $message['file_path'] && file_exists($message['file_path'])) {
            unlink($message['file_path']);
        }

        $stmt = $this->db->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Compte les messages non lus
     */
    public function countUnreadMessages($userId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM messages 
            WHERE receiver_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * Récupère les utilisateurs avec lesquels on peut chatter
     */
    public function getAvailableUsers($userId)
    {
        $stmt = $this->db->prepare("
            SELECT id, name, email, role, is_online
            FROM users 
            WHERE id != ?
            ORDER BY is_online DESC, name ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Met à jour le statut en ligne d'un utilisateur
     */
    public function updateUserOnlineStatus($userId, $isOnline = true)
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET is_online = ?, last_activity = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([$isOnline ? 1 : 0, $userId]);
    }

    /**
     * Récupère les utilisateurs actuellement en ligne
     */
    public function getOnlineUsers($userId)
    {
        $stmt = $this->db->prepare("
            SELECT id, name, email, role, is_online
            FROM users 
            WHERE id != ? AND is_online = 1 AND (role IN ('admin', 'enseignant', 'etudiant', 'finance'))
            ORDER BY name ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Compte le nombre d'utilisateurs en ligne
     */
    public function countOnlineUsers()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users WHERE is_online = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * Marque les utilisateurs inactifs comme hors ligne (timeout après 5 minutes)
     */
    public function updateInactiveUsers($timeoutMinutes = 5)
    {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET is_online = 0 
            WHERE is_online = 1 AND last_activity < DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        return $stmt->execute([$timeoutMinutes]);
    }
}
