<?php

require_once __DIR__ . '/../Core/Database.php';

class NoteRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
        $this->ensureSchema();
    }

    private function ensureSchema() {
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
            error_log('NoteRepository ensureSchema error: ' . $e->getMessage());
        }
    }

    public function getAll() {
        $stmt = $this->db->query('
            SELECT n.*, 
                   e.matricule AS etudiant_matricule, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, e.photo AS etudiant_photo, 
                   c.nom AS cours_nom, c.code AS cours_code, c.credit AS cours_credit
            FROM notes n 
            LEFT JOIN etudiants e ON n.etudiant_id = e.id 
            LEFT JOIN cours c ON n.cours_id = c.id 
            ORDER BY e.nom ASC
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByEtudiantId($etudiantId) {
        $stmt = $this->db->prepare('
            SELECT n.*, 
                   e.matricule AS etudiant_matricule, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, e.photo AS etudiant_photo, 
                   c.nom AS cours_nom, c.code AS cours_code, c.credit AS cours_credit
            FROM notes n 
            LEFT JOIN etudiants e ON n.etudiant_id = e.id 
            LEFT JOIN cours c ON n.cours_id = c.id 
            WHERE n.etudiant_id = ? 
            ORDER BY n.semestre ASC, c.nom ASC
        ');
        $stmt->execute([$etudiantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('
            SELECT n.*, 
                   e.matricule AS etudiant_matricule, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, e.photo AS etudiant_photo, 
                   c.nom AS cours_nom, c.code AS cours_code, c.credit AS cours_credit
            FROM notes n 
            LEFT JOIN etudiants e ON n.etudiant_id = e.id 
            LEFT JOIN cours c ON n.cours_id = c.id 
            WHERE n.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les étudiants enrôlés dans un cours avec leurs notes, statuts de verrouillage et demandes SGA
     */
    public function getEnrolledStudentsWithNotes($coursId) {
        $stmt = $this->db->prepare('
            SELECT e.id AS etudiant_id, e.matricule AS etudiant_matricule, e.nom AS etudiant_nom, e.prenom AS etudiant_prenom, e.photo AS etudiant_photo,
                   o.nom AS orientation_nom, f.nom AS filiere_nom,
                   n.id AS note_id,
                   n.cours_id,
                   COALESCE(n.semestre, c.semestre, 1) AS semestre,
                   n.interro,
                   n.tp,
                   n.td,
                   n.mi_session,
                   n.examen,
                   n.total,
                   n.note,
                   COALESCE(n.modifications_count, 0) AS modifications_count,
                   COALESCE(n.derogation_accordee, 0) AS derogation_accordee,
                   (SELECT d.id FROM demandes_modification_notes d WHERE d.cours_id = ce.cours_id AND d.etudiant_id = ce.etudiant_id AND d.statut = "en_attente" LIMIT 1) AS demande_en_attente_id,
                   c.nom AS cours_nom, c.code AS cours_code, c.credit AS cours_credit
            FROM cours_etudiants ce
            INNER JOIN etudiants e ON ce.etudiant_id = e.id
            INNER JOIN cours c ON ce.cours_id = c.id
            LEFT JOIN orientations o ON e.orientation_id = o.id
            LEFT JOIN filieres f ON o.filiere_id = f.id
            LEFT JOIN notes n ON n.cours_id = ce.cours_id AND n.etudiant_id = ce.etudiant_id
            WHERE ce.cours_id = ?
            ORDER BY e.nom ASC, e.prenom ASC
        ');
        $stmt->execute([$coursId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère la note d\'un étudiant pour un cours donné
     */
    public function getByEtudiantAndCours($etudiantId, $coursId) {
        $stmt = $this->db->prepare('SELECT * FROM notes WHERE etudiant_id = ? AND cours_id = ?');
        $stmt->execute([$etudiantId, $coursId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Sauvegarde ou met à jour les notes en masse pour un cours avec règle de verrouillage
     * @param int $coursId
     * @param array $notesData
     * @param bool $isEnseignant
     */
    public function saveBulkNotesForCours($coursId, array $notesData, $isEnseignant = false) {
        $this->db->beginTransaction();
        try {
            foreach ($notesData as $etudiantId => $data) {
                $interro = isset($data['interro']) && $data['interro'] !== '' ? floatval($data['interro']) : null;
                $tp = isset($data['tp']) && $data['tp'] !== '' ? floatval($data['tp']) : null;
                $td = isset($data['td']) && $data['td'] !== '' ? floatval($data['td']) : null;
                $mi_session = isset($data['mi_session']) && $data['mi_session'] !== '' ? floatval($data['mi_session']) : null;
                $examen = isset($data['examen']) && $data['examen'] !== '' ? floatval($data['examen']) : null;
                $semestre = isset($data['semestre']) && !empty($data['semestre']) ? intval($data['semestre']) : 1;

                // Calcul du total sur 200 points
                $sum = ($interro ?? 0) + ($tp ?? 0) + ($td ?? 0) + ($mi_session ?? 0) + ($examen ?? 0);
                $hasAnyGrade = ($interro !== null || $tp !== null || $td !== null || $mi_session !== null || $examen !== null);
                
                $total = $hasAnyGrade ? round($sum, 2) : null;
                $noteSur20 = $hasAnyGrade ? round($sum / 10, 2) : null;

                // Vérifier si une note existe déjà
                $existing = $this->getByEtudiantAndCours($etudiantId, $coursId);

                if ($existing) {
                    $modCount = (int)($existing['modifications_count'] ?? 0);
                    $derogation = (int)($existing['derogation_accordee'] ?? 0);

                    // Vérification du verrou pour enseignant
                    if ($isEnseignant) {
                        if ($modCount >= 1 && $derogation === 0) {
                            // Verrouillé : pas le droit de modifier sans dérogation
                            continue;
                        }
                    }

                    // Déterminer les nouveaux compteurs
                    $newModCount = $modCount;
                    $newDerogation = 0; // Une fois modifiée, la dérogation est consommée

                    // Si c'est un enseignant :
                    if ($isEnseignant) {
                        if ($modCount === 0) {
                            // 1ère modification autorisée
                            $newModCount = 1;
                        } elseif ($derogation === 1) {
                            // Modification par dérogation SGA
                            $newModCount = $modCount + 1;
                            $newDerogation = 0;
                        }
                    }

                    $stmt = $this->db->prepare('
                        UPDATE notes 
                        SET interro = ?, tp = ?, td = ?, mi_session = ?, examen = ?, total = ?, note = ?, semestre = ?, 
                            modifications_count = ?, derogation_accordee = ?
                        WHERE id = ?
                    ');
                    $stmt->execute([
                        $interro, $tp, $td, $mi_session, $examen, $total, $noteSur20, $semestre,
                        $newModCount, $newDerogation, $existing['id']
                    ]);
                } else {
                    // Première saisie initiale
                    $stmt = $this->db->prepare('
                        INSERT INTO notes (etudiant_id, cours_id, interro, tp, td, mi_session, examen, total, note, semestre, modifications_count, derogation_accordee) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)
                    ');
                    $stmt->execute([
                        $etudiantId, $coursId, $interro, $tp, $td, $mi_session, $examen, $total, $noteSur20, $semestre
                    ]);
                }
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function create(array $data) {
        $interro = isset($data['interro']) && $data['interro'] !== '' ? floatval($data['interro']) : null;
        $tp = isset($data['tp']) && $data['tp'] !== '' ? floatval($data['tp']) : null;
        $td = isset($data['td']) && $data['td'] !== '' ? floatval($data['td']) : null;
        $mi_session = isset($data['mi_session']) && $data['mi_session'] !== '' ? floatval($data['mi_session']) : null;
        $examen = isset($data['examen']) && $data['examen'] !== '' ? floatval($data['examen']) : null;

        $hasAnyGrade = ($interro !== null || $tp !== null || $td !== null || $mi_session !== null || $examen !== null);
        $total = $hasAnyGrade ? round(($interro ?? 0) + ($tp ?? 0) + ($td ?? 0) + ($mi_session ?? 0) + ($examen ?? 0), 2) : (isset($data['total']) ? floatval($data['total']) : null);
        $note = $hasAnyGrade ? round($total / 10, 2) : (isset($data['note']) ? floatval($data['note']) : null);

        $stmt = $this->db->prepare('
            INSERT INTO notes (etudiant_id, cours_id, note, interro, tp, td, mi_session, examen, total, semestre, modifications_count, derogation_accordee) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 0)
        ');
        $stmt->execute([
            $data['etudiant_id'],
            $data['cours_id'],
            $note,
            $interro,
            $tp,
            $td,
            $mi_session,
            $examen,
            $total,
            $data['semestre'] ?? 1,
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, array $data) {
        $interro = isset($data['interro']) && $data['interro'] !== '' ? floatval($data['interro']) : null;
        $tp = isset($data['tp']) && $data['tp'] !== '' ? floatval($data['tp']) : null;
        $td = isset($data['td']) && $data['td'] !== '' ? floatval($data['td']) : null;
        $mi_session = isset($data['mi_session']) && $data['mi_session'] !== '' ? floatval($data['mi_session']) : null;
        $examen = isset($data['examen']) && $data['examen'] !== '' ? floatval($data['examen']) : null;

        $hasAnyGrade = ($interro !== null || $tp !== null || $td !== null || $mi_session !== null || $examen !== null);
        $total = $hasAnyGrade ? round(($interro ?? 0) + ($tp ?? 0) + ($td ?? 0) + ($mi_session ?? 0) + ($examen ?? 0), 2) : (isset($data['total']) ? floatval($data['total']) : null);
        $note = $hasAnyGrade ? round($total / 10, 2) : (isset($data['note']) ? floatval($data['note']) : null);

        $stmt = $this->db->prepare('
            UPDATE notes 
            SET etudiant_id = ?, cours_id = ?, note = ?, interro = ?, tp = ?, td = ?, mi_session = ?, examen = ?, total = ?, semestre = ? 
            WHERE id = ?
        ');
        return $stmt->execute([
            $data['etudiant_id'],
            $data['cours_id'],
            $note,
            $interro,
            $tp,
            $td,
            $mi_session,
            $examen,
            $total,
            $data['semestre'] ?? 1,
            $id,
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare('DELETE FROM notes WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
