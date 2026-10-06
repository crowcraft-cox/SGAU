<?php

require_once __DIR__ . '/../Core/Database.php';

class DemandeModificationRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->ensureSchema();
    }

    /**
     * S'assure que la table demandes_modification_notes et les colonnes dans notes existent
     */
    public function ensureSchema() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS demandes_modification_notes (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    enseignant_id INT NOT NULL,
                    cours_id INT NOT NULL,
                    etudiant_id INT NOT NULL,
                    note_id INT NULL,
                    motif TEXT NOT NULL,
                    anciennes_cotes TEXT NULL,
                    statut ENUM('en_attente', 'approuvee', 'rejetee') NOT NULL DEFAULT 'en_attente',
                    reponse_sga TEXT NULL,
                    traite_par INT NULL,
                    traite_le DATETIME NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_enseignant (enseignant_id),
                    INDEX idx_cours (cours_id),
                    INDEX idx_etudiant (etudiant_id),
                    INDEX idx_statut (statut)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            $stmtCol1 = $this->db->query("SHOW COLUMNS FROM notes LIKE 'modifications_count'");
            if ($stmtCol1 && $stmtCol1->rowCount() === 0) {
                $this->db->exec("ALTER TABLE notes ADD COLUMN modifications_count INT NOT NULL DEFAULT 0 AFTER total");
            }

            $stmtCol2 = $this->db->query("SHOW COLUMNS FROM notes LIKE 'derogation_accordee'");
            if ($stmtCol2 && $stmtCol2->rowCount() === 0) {
                $this->db->exec("ALTER TABLE notes ADD COLUMN derogation_accordee TINYINT(1) NOT NULL DEFAULT 0 AFTER modifications_count");
            }
        } catch (Exception $e) {
            error_log('DemandeModification schema check: ' . $e->getMessage());
        }
    }

    /**
     * Récupère toutes les demandes avec filtre optionnel par statut
     */
    public function getAll($statut = null) {
        $sql = '
            SELECT d.*, 
                   e.nom AS enseignant_nom, e.prenom AS enseignant_prenom, e.email AS enseignant_email,
                   c.nom AS cours_nom, c.code AS cours_code,
                   et.nom AS etudiant_nom, et.prenom AS etudiant_prenom, et.matricule AS etudiant_matricule, et.photo AS etudiant_photo,
                   u.name AS traite_par_nom
            FROM demandes_modification_notes d
            LEFT JOIN enseignants e ON d.enseignant_id = e.id
            LEFT JOIN cours c ON d.cours_id = c.id
            LEFT JOIN etudiants et ON d.etudiant_id = et.id
            LEFT JOIN users u ON d.traite_par = u.id
        ';

        $params = [];
        if ($statut !== null && in_array($statut, ['en_attente', 'approuvee', 'rejetee'])) {
            $sql .= ' WHERE d.statut = ? ';
            $params[] = $statut;
        }

        $sql .= ' ORDER BY d.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les demandes soumises par un enseignant
     */
    public function getByEnseignantId($enseignantId, $statut = null) {
        $sql = '
            SELECT d.*, 
                   c.nom AS cours_nom, c.code AS cours_code,
                   et.nom AS etudiant_nom, et.prenom AS etudiant_prenom, et.matricule AS etudiant_matricule, et.photo AS etudiant_photo,
                   u.name AS traite_par_nom
            FROM demandes_modification_notes d
            LEFT JOIN cours c ON d.cours_id = c.id
            LEFT JOIN etudiants et ON d.etudiant_id = et.id
            LEFT JOIN users u ON d.traite_par = u.id
            WHERE d.enseignant_id = ?
        ';

        $params = [$enseignantId];
        if ($statut !== null && in_array($statut, ['en_attente', 'approuvee', 'rejetee'])) {
            $sql .= ' AND d.statut = ? ';
            $params[] = $statut;
        }

        $sql .= ' ORDER BY d.created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une demande par son identifiant
     */
    public function findById($id) {
        $stmt = $this->db->prepare('
            SELECT d.*, 
                   e.nom AS enseignant_nom, e.prenom AS enseignant_prenom, e.email AS enseignant_email,
                   c.nom AS cours_nom, c.code AS cours_code,
                   et.nom AS etudiant_nom, et.prenom AS etudiant_prenom, et.matricule AS etudiant_matricule,
                   u.name AS traite_par_nom
            FROM demandes_modification_notes d
            LEFT JOIN enseignants e ON d.enseignant_id = e.id
            LEFT JOIN cours c ON d.cours_id = c.id
            LEFT JOIN etudiants et ON d.etudiant_id = et.id
            LEFT JOIN users u ON d.traite_par = u.id
            WHERE d.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie s'il existe une demande en attente pour cet étudiant et ce cours
     */
    public function findPendingForStudentAndCourse($coursId, $etudiantId) {
        $stmt = $this->db->prepare('
            SELECT * FROM demandes_modification_notes 
            WHERE cours_id = ? AND etudiant_id = ? AND statut = "en_attente" 
            LIMIT 1
        ');
        $stmt->execute([$coursId, $etudiantId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Nombre de demandes en attente (pour badge SGA)
     */
    public function getPendingCount() {
        $stmt = $this->db->query('SELECT COUNT(*) FROM demandes_modification_notes WHERE statut = "en_attente"');
        return (int) $stmt->fetchColumn();
    }

    /**
     * Enregistrer une nouvelle demande de modification
     */
    public function create(array $data) {
        $stmt = $this->db->prepare('
            INSERT INTO demandes_modification_notes (enseignant_id, cours_id, etudiant_id, note_id, motif, anciennes_cotes, statut) 
            VALUES (?, ?, ?, ?, ?, ?, "en_attente")
        ');
        $stmt->execute([
            $data['enseignant_id'],
            $data['cours_id'],
            $data['etudiant_id'],
            $data['note_id'] ?? null,
            $data['motif'],
            $data['anciennes_cotes'] ?? null
        ]);

        $demandeId = $this->db->lastInsertId();

        // Notifier tous les administrateurs / SGA
        $this->notifySgaNewRequest($data['enseignant_id'], $data['cours_id'], $data['etudiant_id']);

        return $demandeId;
    }

    /**
     * Approuver une demande de dérogation
     */
    public function approuver($id, $traiteParUserId, $reponse = null) {
        $demande = $this->findById($id);
        if (!$demande) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            // 1. Mettre à jour le statut de la demande
            $stmt = $this->db->prepare('
                UPDATE demandes_modification_notes 
                SET statut = "approuvee", reponse_sga = ?, traite_par = ?, traite_le = NOW() 
                WHERE id = ?
            ');
            $stmt->execute([$reponse, $traiteParUserId, $id]);

            // 2. Accorder la dérogation sur la note correspondante
            $stmtNote = $this->db->prepare('
                UPDATE notes 
                SET derogation_accordee = 1 
                WHERE cours_id = ? AND etudiant_id = ?
            ');
            $stmtNote->execute([$demande['cours_id'], $demande['etudiant_id']]);

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }

        // 3. Notifier l'enseignant que sa demande a été approuvée
        $this->notifyTeacherResolution($demande, 'approuvee', $reponse);

        return true;
    }

    /**
     * Rejeter une demande de dérogation
     */
    public function rejeter($id, $traiteParUserId, $reponse = null) {
        $demande = $this->findById($id);
        if (!$demande) {
            return false;
        }

        $stmt = $this->db->prepare('
            UPDATE demandes_modification_notes 
            SET statut = "rejetee", reponse_sga = ?, traite_par = ?, traite_le = NOW() 
            WHERE id = ?
        ');
        $result = $stmt->execute([$reponse, $traiteParUserId, $id]);

        // Notifier l'enseignant du rejet
        $this->notifyTeacherResolution($demande, 'rejetee', $reponse);

        return $result;
    }

    /**
     * Notifie les administrateurs SGA d'une nouvelle demande
     */
    private function notifySgaNewRequest($enseignantId, $coursId, $etudiantId) {
        try {
            // Récupérer les infos
            $stmt = $this->db->prepare('
                SELECT e.nom AS ens_nom, e.prenom AS ens_prenom,
                       c.nom AS cours_nom, c.code AS cours_code,
                       et.nom AS etud_nom, et.prenom AS etud_prenom, et.matricule AS etud_mat
                FROM enseignants e, cours c, etudiants et
                WHERE e.id = ? AND c.id = ? AND et.id = ?
            ');
            $stmt->execute([$enseignantId, $coursId, $etudiantId]);
            $info = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$info) return;

            // Trouver les admins
            $stmtAdmins = $this->db->query('SELECT id FROM users WHERE role = "admin"');
            $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);

            $titre = "Demande de modification de cote (SGA)";
            $teacher = trim($info['ens_nom'] . ' ' . $info['ens_prenom']);
            $student = trim($info['etud_nom'] . ' ' . $info['etud_prenom']) . ' (' . $info['etud_mat'] . ')';
            $message = "L'enseignant $teacher a soumis une demande de modification de cotes pour l'étudiant $student dans le cours « {$info['cours_nom']} ({$info['cours_code']}) ». Rendez-vous dans la section SGA pour examiner la demande.";
            $lien = '/demandes-modification-notes';

            $stmtNotif = $this->db->prepare('INSERT INTO notifications (user_id, titre, message, lien, lue, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
            foreach ($admins as $adminId) {
                $stmtNotif->execute([$adminId, $titre, $message, $lien]);
            }
        } catch (Exception $e) {
            error_log('Erreur notification SGA: ' . $e->getMessage());
        }
    }

    /**
     * Notifie l'enseignant de la décision du SGA
     */
    private function notifyTeacherResolution($demande, $decision, $reponse = null) {
        try {
            // Trouver le user_id de l'enseignant via son email
            $stmtUser = $this->db->prepare('SELECT id FROM users WHERE email = ?');
            $stmtUser->execute([$demande['enseignant_email']]);
            $userId = $stmtUser->fetchColumn();

            if (!$userId) return;

            $studentName = trim($demande['etudiant_nom'] . ' ' . $demande['etudiant_prenom']);
            $coursName = $demande['cours_nom'] . ' (' . $demande['cours_code'] . ')';

            if ($decision === 'approuvee') {
                $titre = "Dérogation accordée : Modification autorisée";
                $message = "Excellente nouvelle ! Le Secrétariat Général Académique a approuvé votre demande de modification de cotes pour l'étudiant $studentName dans le cours « $coursName ». Vous pouvez dès maintenant éditer ses notes dans la Fiche de Cotes.";
                if (!empty($reponse)) {
                    $message .= " Remarque du SGA : " . $reponse;
                }
                $lien = '/notes/cours/' . ($demande['cours_id'] ?? '');
            } else {
                $titre = "Demande de dérogation rejetée (SGA)";
                $message = "Votre demande de modification de cotes pour l'étudiant $studentName dans le cours « $coursName » n'a pas été acceptée par le Secrétariat Général Académique.";
                if (!empty($reponse)) {
                    $message .= " Motif : " . $reponse;
                }
                $lien = '/demandes-modification-notes';
            }

            $stmtNotif = $this->db->prepare('INSERT INTO notifications (user_id, titre, message, lien, lue, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
            $stmtNotif->execute([$userId, $titre, $message, $lien]);
        } catch (Exception $e) {
            error_log('Erreur notification enseignant: ' . $e->getMessage());
        }
    }
}
