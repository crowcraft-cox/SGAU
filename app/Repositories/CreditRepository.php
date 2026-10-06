<?php

require_once __DIR__ . '/../Core/Database.php';

class CreditRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query('SELECT c.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom FROM credits c LEFT JOIN etudiants e ON c.etudiant_id = e.id ORDER BY c.id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEtudiantId($etudiantId) {
        $stmt = $this->db->prepare('SELECT c.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom FROM credits c LEFT JOIN etudiants e ON c.etudiant_id = e.id WHERE c.etudiant_id = ?');
        $stmt->execute([$etudiantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM credits WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO credits (etudiant_id, credits_acquis, credits_requis) VALUES (?, ?, ?)');
        $stmt->execute([$data['etudiant_id'], $data['credits_acquis'], $data['credits_requis']]);
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE credits SET etudiant_id = ?, credits_acquis = ?, credits_requis = ? WHERE id = ?');
        $stmt->execute([$data['etudiant_id'], $data['credits_acquis'], $data['credits_requis'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM credits WHERE id = ?');
        $stmt->execute([$id]);
    }
}
