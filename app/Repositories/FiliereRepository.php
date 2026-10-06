<?php

require_once __DIR__ . '/../Core/Database.php';

class FiliereRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query('SELECT * FROM filieres ORDER BY nom ASC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM filieres WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data) {
        $stmt = $this->db->prepare('INSERT INTO filieres (code, nom, description) VALUES (?, ?, ?)');
        $stmt->execute([$data['code'], $data['nom'], $data['description']]);
    }

    public function update($id, array $data) {
        $stmt = $this->db->prepare('UPDATE filieres SET code = ?, nom = ?, description = ? WHERE id = ?');
        $stmt->execute([$data['code'], $data['nom'], $data['description'], $id]);
    }

    public function delete($id) {
        try {
            $this->db->beginTransaction();

            // Récupérer les orientations associées
            $stmtO = $this->db->prepare('SELECT id FROM orientations WHERE filiere_id = ?');
            $stmtO->execute([$id]);
            $orientationIds = $stmtO->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($orientationIds)) {
                $placeholdersO = implode(',', array_fill(0, count($orientationIds), '?'));

                // Check cours
                $stmtCheckCours = $this->db->prepare("SELECT COUNT(*) FROM cours WHERE orientation_id IN ($placeholdersO)");
                $stmtCheckCours->execute($orientationIds);
                if ($stmtCheckCours->fetchColumn() > 0) {
                    throw new Exception("Impossible de supprimer cette filière car des cours sont rattachés à ses orientations.");
                }

                // Supprimer les orientations
                $stmtDelO = $this->db->prepare("DELETE FROM orientations WHERE id IN ($placeholdersO)");
                $stmtDelO->execute($orientationIds);
            }

            // Supprimer la filière
            $stmt = $this->db->prepare('DELETE FROM filieres WHERE id = ?');
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($e instanceof PDOException) {
                if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
                    throw new Exception("Impossible de supprimer cette filière car elle contient des données liées (cours ou étudiants).");
                }
            }
            throw $e;
        }
    }
}
