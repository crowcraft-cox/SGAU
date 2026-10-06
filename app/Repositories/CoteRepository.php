<?php

require_once __DIR__ . '/../Core/Database.php';

class CoteRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query('SELECT * FROM cotes ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM cotes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO cotes (code, nom) VALUES (?, ?)');
        $stmt->execute([$data['code'], $data['nom']]);
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE cotes SET code = ?, nom = ? WHERE id = ?');
        $stmt->execute([$data['code'], $data['nom'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM cotes WHERE id = ?');
        $stmt->execute([$id]);
    }
}
