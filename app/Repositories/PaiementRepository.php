<?php

require_once __DIR__ . '/../Core/Database.php';

class PaiementRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query('SELECT p.*, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom FROM paiements p LEFT JOIN etudiants e ON p.etudiant_id = e.id ORDER BY p.id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM paiements WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO paiements (etudiant_id, montant, date_paiement, statut) VALUES (?, ?, ?, ?)');
        $stmt->execute([$data['etudiant_id'], $data['montant'], $data['date_paiement'], $data['statut']]);
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE paiements SET etudiant_id = ?, montant = ?, date_paiement = ?, statut = ? WHERE id = ?');
        $stmt->execute([$data['etudiant_id'], $data['montant'], $data['date_paiement'], $data['statut'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM paiements WHERE id = ?');
        $stmt->execute([$id]);
    }
}
