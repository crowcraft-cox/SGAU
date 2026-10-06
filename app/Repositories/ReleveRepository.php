<?php

require_once __DIR__ . '/../Core/Database.php';

class ReleveRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query('SELECT r.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, e.releve_bloque FROM releves r LEFT JOIN etudiants e ON r.etudiant_id = e.id ORDER BY r.id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEtudiantId($etudiantId) {
        $stmt = $this->db->prepare('SELECT r.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, e.releve_bloque FROM releves r LEFT JOIN etudiants e ON r.etudiant_id = e.id WHERE r.etudiant_id = ? ORDER BY r.semestre DESC');
        $stmt->execute([$etudiantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM releves WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO releves (etudiant_id, semestre, moyenne, statut) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['etudiant_id'], $data['semestre'], $data['moyenne'], $data['statut']]);
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE releves SET etudiant_id = ?, semestre = ?, moyenne = ?, statut = ? WHERE id = ?');
        $stmt->execute([$data['etudiant_id'], $data['semestre'], $data['moyenne'], $data['statut'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM releves WHERE id = ?');
        $stmt->execute([$id]);
    }
}
