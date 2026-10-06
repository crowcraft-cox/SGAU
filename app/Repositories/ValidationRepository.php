<?php

require_once __DIR__ . '/../Core/Database.php';

class ValidationRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Récupérer tous les étudiants avec leurs validations de cours
     */
    public function getAllStudentsWithValidations() {
        $stmt = $this->db->query("
            SELECT 
                e.id,
                e.matricule,
                e.nom,
                e.prenom,
                o.filiere_id,
                e.niveau,
                f.nom AS filiere_nom,
                COUNT(DISTINCT n.cours_id) AS total_cours,
                SUM(CASE WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) >= 10 THEN 1 ELSE 0 END) AS cours_valides,
                SUM(CASE WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) < 10 THEN 1 ELSE 0 END) AS cours_non_valides,
                SUM(CASE WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) >= 10 THEN COALESCE(c.credit, 0) ELSE 0 END) AS credits_acquis,
                SUM(COALESCE(c.credit, 0)) AS credits_requis
            FROM etudiants e
            LEFT JOIN orientations o ON e.orientation_id = o.id
            LEFT JOIN filieres f ON o.filiere_id = f.id
            LEFT JOIN notes n ON e.id = n.etudiant_id
            LEFT JOIN cours c ON n.cours_id = c.id
            GROUP BY e.id, e.matricule, e.nom, e.prenom, o.filiere_id, e.niveau, f.nom
            ORDER BY e.nom ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentWithValidations($etudiantId) {
        $stmt = $this->db->prepare("
            SELECT 
                e.id,
                e.matricule,
                e.nom,
                e.prenom,
                o.filiere_id,
                e.niveau,
                f.nom AS filiere_nom,
                COUNT(DISTINCT n.cours_id) AS total_cours,
                SUM(CASE WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) >= 10 THEN 1 ELSE 0 END) AS cours_valides,
                SUM(CASE WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) < 10 THEN 1 ELSE 0 END) AS cours_non_valides,
                SUM(CASE WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) >= 10 THEN COALESCE(c.credit, 0) ELSE 0 END) AS credits_acquis,
                SUM(COALESCE(c.credit, 0)) AS credits_requis
            FROM etudiants e
            LEFT JOIN orientations o ON e.orientation_id = o.id
            LEFT JOIN filieres f ON o.filiere_id = f.id
            LEFT JOIN notes n ON e.id = n.etudiant_id
            LEFT JOIN cours c ON n.cours_id = c.id
            WHERE e.id = ?
            GROUP BY e.id, e.matricule, e.nom, e.prenom, o.filiere_id, e.niveau, f.nom
        ");
        $stmt->execute([$etudiantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les validations d'un étudiant spécifique
     */
    public function getStudentValidations($etudiantId) {
        $stmt = $this->db->prepare("
            SELECT 
                e.id,
                e.matricule,
                e.nom,
                e.prenom,
                e.niveau,
                f.nom AS filiere_nom,
                n.id AS note_id,
                n.semestre,
                c.id AS cours_id,
                c.code,
                c.nom AS cours_nom,
                c.credit,
                COALESCE(n.interro, 0) AS interro,
                COALESCE(n.tp, 0) AS tp,
                COALESCE(n.examen, 0) AS examen,
                ROUND((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3, 2) AS moyenne,
                CASE 
                    WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) >= 10 THEN 'Validé'
                    ELSE 'Non Validé'
                END AS statut
            FROM etudiants e
            LEFT JOIN orientations o ON e.orientation_id = o.id
            LEFT JOIN filieres f ON o.filiere_id = f.id
            LEFT JOIN notes n ON e.id = n.etudiant_id
            LEFT JOIN cours c ON n.cours_id = c.id
            WHERE e.id = ?
            ORDER BY n.semestre DESC, c.nom ASC
        ");
        $stmt->execute([$etudiantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer les validations par semestre
     */
    public function getValidationsBySemester($etudiantId, $semestre) {
        $stmt = $this->db->prepare("
            SELECT 
                c.id,
                c.code,
                c.nom,
                c.credit,
                COALESCE(n.interro, 0) AS interro,
                COALESCE(n.tp, 0) AS tp,
                COALESCE(n.examen, 0) AS examen,
                ROUND((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3, 2) AS moyenne,
                CASE 
                    WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) >= 10 THEN 'Validé'
                    ELSE 'Non Validé'
                END AS statut
            FROM cours c
            LEFT JOIN notes n ON c.id = n.cours_id AND n.etudiant_id = ?
            WHERE n.semestre = ? OR n.semestre IS NULL
            ORDER BY c.nom ASC
        ");
        $stmt->execute([$etudiantId, $semestre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculer les statistiques de validation pour un étudiant
     */
    public function getValidationStats($etudiantId) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT n.cours_id) AS total_cours,
                SUM(CASE WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) >= 10 THEN 1 ELSE 0 END) AS cours_valides,
                SUM(CASE WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) < 10 THEN 1 ELSE 0 END) AS cours_non_valides,
                SUM(CASE WHEN ((COALESCE(n.interro, 0) + COALESCE(n.tp, 0) + COALESCE(n.examen, 0)) / 3) >= 10 THEN COALESCE(c.credit, 0) ELSE 0 END) AS credits_acquis,
                SUM(COALESCE(c.credit, 0)) AS credits_requis
            FROM notes n
            LEFT JOIN cours c ON n.cours_id = c.id
            WHERE n.etudiant_id = ?
        ");
        $stmt->execute([$etudiantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
