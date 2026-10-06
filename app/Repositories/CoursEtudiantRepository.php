<?php

require_once __DIR__ . '/../Core/Database.php';

class CoursEtudiantRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    /**
     * Récupère la liste des étudiants enrôlés dans un cours donné
     */
    public function getEnrolledStudents($coursId) {
        $stmt = $this->db->prepare('
            SELECT e.*, ce.id AS enrollement_id, ce.created_at AS date_enrollement,
                   o.nom AS orientation_nom, f.nom AS filiere_nom, d.nom AS domaine_nom
            FROM cours_etudiants ce
            INNER JOIN etudiants e ON ce.etudiant_id = e.id
            LEFT JOIN orientations o ON e.orientation_id = o.id
            LEFT JOIN filieres f ON o.filiere_id = f.id
            LEFT JOIN domaines d ON f.domaine_id = d.id
            WHERE ce.cours_id = ?
            ORDER BY e.nom ASC, e.prenom ASC
        ');
        $stmt->execute([$coursId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les étudiants avec statut d'enrôlement pour un cours donné
     */
    public function getAllStudentsWithEnrollmentStatus($coursId, $searchTerm = '') {
        $sql = '
            SELECT e.*, 
                   o.nom AS orientation_nom, f.nom AS filiere_nom, d.nom AS domaine_nom,
                   (ce.id IS NOT NULL) AS is_enrolled,
                   ce.created_at AS date_enrollement
            FROM etudiants e
            LEFT JOIN cours_etudiants ce ON e.id = ce.etudiant_id AND ce.cours_id = ?
            LEFT JOIN orientations o ON e.orientation_id = o.id
            LEFT JOIN filieres f ON o.filiere_id = f.id
            LEFT JOIN domaines d ON f.domaine_id = d.id
        ';

        $params = [$coursId];

        if (!empty(trim($searchTerm))) {
            $sql .= ' WHERE e.nom LIKE ? OR e.prenom LIKE ? OR e.matricule LIKE ? OR e.email LIKE ? ';
            $term = '%' . trim($searchTerm) . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= ' ORDER BY is_enrolled DESC, e.nom ASC, e.prenom ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Enrôler un étudiant dans un cours et lui envoyer une notification
     */
    public function enroll($coursId, $etudiantId) {
        $stmt = $this->db->prepare('INSERT IGNORE INTO cours_etudiants (cours_id, etudiant_id) VALUES (?, ?)');
        $result = $stmt->execute([$coursId, $etudiantId]);

        if ($stmt->rowCount() > 0) {
            $this->sendEnrollmentNotification($coursId, $etudiantId);
        }

        return $result;
    }

    /**
     * Enrôler plusieurs étudiants en une transaction et leur envoyer des notifications
     */
    public function enrollBulk($coursId, array $etudiantIds) {
        if (empty($etudiantIds)) {
            return 0;
        }

        $this->db->beginTransaction();
        $enrolledIds = [];
        try {
            $stmt = $this->db->prepare('INSERT IGNORE INTO cours_etudiants (cours_id, etudiant_id) VALUES (?, ?)');
            $count = 0;
            foreach ($etudiantIds as $etudiantId) {
                if (!empty($etudiantId)) {
                    $stmt->execute([$coursId, $etudiantId]);
                    if ($stmt->rowCount() > 0) {
                        $count++;
                        $enrolledIds[] = $etudiantId;
                    }
                }
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        // Envoyer les notifications aux nouveaux étudiants enrôlés
        foreach ($enrolledIds as $eid) {
            try {
                $this->sendEnrollmentNotification($coursId, $eid);
            } catch (Exception $notifEx) {
                error_log("Erreur envoi notification enrôlement étudiant $eid: " . $notifEx->getMessage());
            }
        }

        return $count;
    }

    /**
     * Désenrôler un étudiant d'un cours
     */
    public function unenroll($coursId, $etudiantId) {
        $stmt = $this->db->prepare('DELETE FROM cours_etudiants WHERE cours_id = ? AND etudiant_id = ?');
        return $stmt->execute([$coursId, $etudiantId]);
    }

    /**
     * Compte le nombre d'étudiants enrôlés pour un cours
     */
    public function countEnrolled($coursId) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM cours_etudiants WHERE cours_id = ?');
        $stmt->execute([$coursId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Envoie une notification à l'étudiant lorsqu'il est enrôlé à un cours
     */
    private function sendEnrollmentNotification($coursId, $etudiantId) {
        // 1. Récupérer les informations du cours et de l'enseignant
        $stmtCours = $this->db->prepare('
            SELECT c.nom AS cours_nom, c.code AS cours_code, c.credit,
                   e.nom AS enseignant_nom, e.prenom AS enseignant_prenom
            FROM cours c
            LEFT JOIN enseignants e ON c.enseignant_id = e.id
            WHERE c.id = ?
        ');
        $stmtCours->execute([$coursId]);
        $cours = $stmtCours->fetch(PDO::FETCH_ASSOC);

        if (!$cours) {
            return;
        }

        // 2. Trouver l'utilisateur correspondant à l'étudiant
        $stmtUser = $this->db->prepare('
            SELECT u.id FROM users u
            INNER JOIN etudiants e ON u.email = e.email
            WHERE e.id = ?
        ');
        $stmtUser->execute([$etudiantId]);
        $userId = $stmtUser->fetchColumn();

        if (!$userId) {
            return;
        }

        $teacherName = trim(($cours['enseignant_nom'] ?? '') . ' ' . ($cours['enseignant_prenom'] ?? ''));
        $teacherText = !empty($teacherName) ? "dispensé par $teacherName" : "";
        $titre = "Enrôlement au cours : " . $cours['cours_nom'];
        $message = "Bonjour ! Vous venez d'être enrôlé(e) avec succès au cours « " . $cours['cours_nom'] . " (" . $cours['cours_code'] . ") » $teacherText (" . ($cours['credit'] ?? 1) . " crédits). Vous pouvez dès à présent consulter ce cours et vos notes associées.";
        $lien = '/notes';

        // 3. Insérer la notification avec lien
        $stmtNotif = $this->db->prepare('
            INSERT INTO notifications (user_id, titre, message, lien, lue, created_at)
            VALUES (?, ?, ?, ?, 0, NOW())
        ');
        $stmtNotif->execute([$userId, $titre, $message, $lien]);
    }
}
