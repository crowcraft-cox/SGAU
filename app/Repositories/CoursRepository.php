<?php

require_once __DIR__ . '/../Core/Database.php';

class CoursRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query('
            SELECT c.*, e.nom AS enseignant_nom, e.prenom AS enseignant_prenom, 
                   o.nom AS orientation_nom, o.filiere_id, 
                   f.nom AS filiere_nom, f.domaine_id, 
                   d.nom AS domaine_nom,
                   (SELECT COUNT(*) FROM cours_etudiants ce WHERE ce.cours_id = c.id) AS total_enroles,
                   (SELECT COUNT(*) FROM notes n WHERE n.cours_id = c.id AND (n.interro > 0 OR n.tp > 0 OR n.td > 0 OR n.mi_session > 0 OR n.examen > 0 OR n.note > 0)) AS total_notes
            FROM cours c 
            LEFT JOIN enseignants e ON c.enseignant_id = e.id 
            LEFT JOIN orientations o ON c.orientation_id = o.id 
            LEFT JOIN filieres f ON o.filiere_id = f.id 
            LEFT JOIN domaines d ON f.domaine_id = d.id 
            ORDER BY c.id DESC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEnseignantId($enseignantId) {
        $stmt = $this->db->prepare('
            SELECT c.*, e.nom AS enseignant_nom, e.prenom AS enseignant_prenom, 
                   o.nom AS orientation_nom, o.filiere_id, 
                   f.nom AS filiere_nom, f.domaine_id, 
                   d.nom AS domaine_nom,
                   (SELECT COUNT(*) FROM cours_etudiants ce WHERE ce.cours_id = c.id) AS total_enroles,
                   (SELECT COUNT(*) FROM notes n WHERE n.cours_id = c.id AND (n.interro > 0 OR n.tp > 0 OR n.td > 0 OR n.mi_session > 0 OR n.examen > 0 OR n.note > 0)) AS total_notes
            FROM cours c 
            LEFT JOIN enseignants e ON c.enseignant_id = e.id 
            LEFT JOIN orientations o ON c.orientation_id = o.id 
            LEFT JOIN filieres f ON o.filiere_id = f.id 
            LEFT JOIN domaines d ON f.domaine_id = d.id 
            WHERE c.enseignant_id = ?
            ORDER BY c.id DESC
        ');
        $stmt->execute([$enseignantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('
            SELECT c.*, e.nom AS enseignant_nom, e.prenom AS enseignant_prenom, 
                   o.nom AS orientation_nom, o.filiere_id, 
                   f.nom AS filiere_nom, f.domaine_id, 
                   d.nom AS domaine_nom,
                   (SELECT COUNT(*) FROM cours_etudiants ce WHERE ce.cours_id = c.id) AS total_enroles
            FROM cours c 
            LEFT JOIN enseignants e ON c.enseignant_id = e.id 
            LEFT JOIN orientations o ON c.orientation_id = o.id 
            LEFT JOIN filieres f ON o.filiere_id = f.id 
            LEFT JOIN domaines d ON f.domaine_id = d.id 
            WHERE c.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO cours (code, nom, orientation_id, credit, enseignant_id, description) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['code'], $data['nom'], $data['orientation_id'], $data['credit'], $data['enseignant_id'], $data['description']]);
        return $this->db->lastInsertId();
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE cours SET code = ?, nom = ?, orientation_id = ?, credit = ?, enseignant_id = ?, description = ? WHERE id = ?');
        return $stmt->execute([$data['code'], $data['nom'], $data['orientation_id'], $data['credit'], $data['enseignant_id'], $data['description'], $id]);
    }

    public function updateCreditAndSemestre($id, $credit, $semestre) {
        $stmt = $this->db->prepare('UPDATE cours SET credit = ?, semestre = ? WHERE id = ?');
        return $stmt->execute([$credit, $semestre, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM cours WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
