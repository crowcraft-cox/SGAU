<?php

require_once __DIR__ . '/../Core/Database.php';

class EnseignantRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query('SELECT * FROM enseignants ORDER BY id DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM enseignants WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByMatricule($matricule) {
        $stmt = $this->db->prepare('SELECT * FROM enseignants WHERE matricule = ?');
        $stmt->execute([$matricule]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM enseignants WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO enseignants (matricule, nom, prenom, email, telephone, departement, photo) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['matricule'],
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['telephone'],
            $data['departement'],
            $data['photo'] ?? null,
        ]);
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE enseignants SET matricule = ?, nom = ?, prenom = ?, email = ?, telephone = ?, departement = ?, photo = ? WHERE id = ?');
        $stmt->execute([
            $data['matricule'],
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['telephone'],
            $data['departement'],
            $data['photo'] ?? null,
            $id,
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM enseignants WHERE id = ?');
        $stmt->execute([$id]);
    }
}
