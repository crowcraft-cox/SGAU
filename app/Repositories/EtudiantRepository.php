<?php

require_once __DIR__ . '/../Core/Database.php';

class EtudiantRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query('
            SELECT e.*, o.nom AS orientation_nom, o.filiere_id, f.nom AS filiere_nom, f.domaine_id, d.nom AS domaine_nom 
            FROM etudiants e 
            LEFT JOIN orientations o ON e.orientation_id = o.id 
            LEFT JOIN filieres f ON o.filiere_id = f.id 
            LEFT JOIN domaines d ON f.domaine_id = d.id 
            ORDER BY e.id DESC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('
            SELECT e.*, o.nom AS orientation_nom, o.filiere_id, f.nom AS filiere_nom, f.domaine_id, d.nom AS domaine_nom, d.doyen AS doyen_nom 
            FROM etudiants e 
            LEFT JOIN orientations o ON e.orientation_id = o.id 
            LEFT JOIN filieres f ON o.filiere_id = f.id 
            LEFT JOIN domaines d ON f.domaine_id = d.id 
            WHERE e.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByMatricule($matricule) {
        $stmt = $this->db->prepare('SELECT id FROM etudiants WHERE matricule = ?');
        $stmt->execute([$matricule]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByMatriculeExcept($matricule, $excludeId) {
        $stmt = $this->db->prepare('SELECT id FROM etudiants WHERE matricule = ? AND id != ?');
        $stmt->execute([$matricule, $excludeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email) {
        $stmt = $this->db->prepare('
            SELECT e.*, o.nom AS orientation_nom, o.filiere_id, f.nom AS filiere_nom, f.domaine_id, d.nom AS domaine_nom 
            FROM etudiants e 
            LEFT JOIN orientations o ON e.orientation_id = o.id 
            LEFT JOIN filieres f ON o.filiere_id = f.id 
            LEFT JOIN domaines d ON f.domaine_id = d.id 
            WHERE e.email = ?
        ');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmailExcept($email, $excludeId) {
        $stmt = $this->db->prepare('SELECT id FROM etudiants WHERE email = ? AND id != ?');
        $stmt->execute([$email, $excludeId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO etudiants (nom, prenom, lieu_naissance, date_naissance, email, matricule, orientation_id, niveau, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['lieu_naissance'] ?? null,
            $data['date_naissance'] ?? null,
            $data['email'],
            $data['matricule'],
            $data['orientation_id'],
            $data['niveau'],
            $data['photo'] ?? null,
        ]);
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE etudiants SET nom = ?, prenom = ?, lieu_naissance = ?, date_naissance = ?, email = ?, matricule = ?, orientation_id = ?, niveau = ?, photo = ? WHERE id = ?');
        $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['lieu_naissance'] ?? null,
            $data['date_naissance'] ?? null,
            $data['email'],
            $data['matricule'],
            $data['orientation_id'],
            $data['niveau'],
            $data['photo'] ?? null,
            $id,
        ]);
    }

    public function delete($id) {
        // Supprimer les dépendances en cascade (dans l'ordre des clés étrangères)
        
        // 1. Supprimer les notes
        $stmtNotes = $this->db->prepare('DELETE FROM notes WHERE etudiant_id = ?');
        $stmtNotes->execute([$id]);
        
        // 2. Supprimer les crédits
        $stmtCredits = $this->db->prepare('DELETE FROM credits WHERE etudiant_id = ?');
        $stmtCredits->execute([$id]);
        
        // 3. Supprimer les paiements
        $stmtPaiements = $this->db->prepare('DELETE FROM paiements WHERE etudiant_id = ?');
        $stmtPaiements->execute([$id]);
        
        // 4. Supprimer les relevés
        $stmtReleves = $this->db->prepare('DELETE FROM releves WHERE etudiant_id = ?');
        $stmtReleves->execute([$id]);
        
        // 5. Finalement, supprimer l'étudiant
        $stmt = $this->db->prepare('DELETE FROM etudiants WHERE id = ?');
        $stmt->execute([$id]);
    }
}
