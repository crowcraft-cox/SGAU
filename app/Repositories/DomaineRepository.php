<?php

require_once __DIR__ . '/../Core/Database.php';

class DomaineRepository {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    public function getAll() {
        $stmt = $this->db->query('SELECT * FROM domaines ORDER BY nom ASC');
        $domaines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch filières and orientations for each domaine
        foreach ($domaines as &$domaine) {
            $domaine['filieres'] = $this->getFilieresByDomaine($domaine['id']);
        }

        return $domaines;
    }

    public function getFilieresByDomaine($domaineId) {
        $stmt = $this->db->prepare('SELECT * FROM filieres WHERE domaine_id = ? ORDER BY nom ASC');
        $stmt->execute([$domaineId]);
        $filieres = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($filieres as &$filiere) {
            $filiere['orientations'] = $this->getOrientationsByFiliere($filiere['id']);
        }

        return $filieres;
    }

    public function getOrientationsByFiliere($filiereId) {
        $stmt = $this->db->prepare('SELECT * FROM orientations WHERE filiere_id = ? ORDER BY nom ASC');
        $stmt->execute([$filiereId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare('SELECT * FROM domaines WHERE id = ?');
        $stmt->execute([$id]);
        $domaine = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($domaine) {
            $domaine['filieres'] = $this->getFilieresByDomaine($domaine['id']);
        }
        return $domaine;
    }

    public function create(array $data) {
        try {
            $this->db->beginTransaction();

            // Insert Domaine
            $stmt = $this->db->prepare('INSERT INTO domaines (code, nom, doyen, description) VALUES (?, ?, ?, ?)');
            $stmt->execute([$data['code'], $data['nom'], $data['doyen'] ?? null, $data['description']]);
            $domaineId = $this->db->lastInsertId();

            // Insert Filières and Orientations
            if (!empty($data['filieres']) && is_array($data['filieres'])) {
                foreach ($data['filieres'] as $filiereData) {
                    if (empty($filiereData['nom'])) continue;

                    $stmtF = $this->db->prepare('INSERT INTO filieres (domaine_id, nom) VALUES (?, ?)');
                    $stmtF->execute([$domaineId, $filiereData['nom']]);
                    $filiereId = $this->db->lastInsertId();

                    if (!empty($filiereData['orientations']) && is_array($filiereData['orientations'])) {
                        foreach ($filiereData['orientations'] as $orientationNom) {
                            if (empty($orientationNom)) continue;
                            $stmtO = $this->db->prepare('INSERT INTO orientations (filiere_id, nom) VALUES (?, ?)');
                            $stmtO->execute([$filiereId, $orientationNom]);
                        }
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, array $data) {
        try {
            $this->db->beginTransaction();

            // Update Domaine
            $stmt = $this->db->prepare('UPDATE domaines SET code = ?, nom = ?, doyen = ?, description = ? WHERE id = ?');
            $stmt->execute([$data['code'], $data['nom'], $data['doyen'] ?? null, $data['description'], $id]);

            // Synchronisation des filières et orientations (Approche douce pour éviter les FK errors)
            if (!empty($data['filieres']) && is_array($data['filieres'])) {
                foreach ($data['filieres'] as $filiereData) {
                    if (empty($filiereData['nom'])) continue;

                    // Vérifier si la filière existe déjà pour ce domaine
                    $stmtF = $this->db->prepare('SELECT id FROM filieres WHERE domaine_id = ? AND nom = ?');
                    $stmtF->execute([$id, $filiereData['nom']]);
                    $filiere = $stmtF->fetch(PDO::FETCH_ASSOC);

                    if ($filiere) {
                        $filiereId = $filiere['id'];
                    } else {
                        // Créer la filière
                        $stmtInsF = $this->db->prepare('INSERT INTO filieres (domaine_id, nom) VALUES (?, ?)');
                        $stmtInsF->execute([$id, $filiereData['nom']]);
                        $filiereId = $this->db->lastInsertId();
                    }

                    // Gérer les orientations
                    if (!empty($filiereData['orientations']) && is_array($filiereData['orientations'])) {
                        foreach ($filiereData['orientations'] as $orientationNom) {
                            if (empty($orientationNom)) continue;

                            // Vérifier si l'orientation existe déjà
                            $stmtO = $this->db->prepare('SELECT id FROM orientations WHERE filiere_id = ? AND nom = ?');
                            $stmtO->execute([$filiereId, $orientationNom]);
                            
                            if (!$stmtO->fetch()) {
                                // Créer l'orientation
                                $stmtInsO = $this->db->prepare('INSERT INTO orientations (filiere_id, nom) VALUES (?, ?)');
                                $stmtInsO->execute([$filiereId, $orientationNom]);
                            }
                        }
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        try {
            $this->db->beginTransaction();

            // 1. Récupérer les filières du domaine
            $stmtF = $this->db->prepare('SELECT id FROM filieres WHERE domaine_id = ?');
            $stmtF->execute([$id]);
            $filiereIds = $stmtF->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($filiereIds)) {
                $placeholdersF = implode(',', array_fill(0, count($filiereIds), '?'));

                // 2. Récupérer les orientations associées
                $stmtO = $this->db->prepare("SELECT id FROM orientations WHERE filiere_id IN ($placeholdersF)");
                $stmtO->execute($filiereIds);
                $orientationIds = $stmtO->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($orientationIds)) {
                    $placeholdersO = implode(',', array_fill(0, count($orientationIds), '?'));

                    // Vérifier si des cours sont associés aux orientations
                    $stmtCheckCours = $this->db->prepare("SELECT COUNT(*) FROM cours WHERE orientation_id IN ($placeholdersO)");
                    $stmtCheckCours->execute($orientationIds);
                    if ($stmtCheckCours->fetchColumn() > 0) {
                        throw new Exception("Impossible de supprimer ce domaine car des cours sont rattachés à ses filières/orientations.");
                    }

                    // Vérifier si des étudiants sont associés aux orientations
                    try {
                        $stmtCheckEtud = $this->db->prepare("SELECT COUNT(*) FROM etudiants WHERE orientation_id IN ($placeholdersO)");
                        $stmtCheckEtud->execute($orientationIds);
                        if ($stmtCheckEtud->fetchColumn() > 0) {
                            throw new Exception("Impossible de supprimer ce domaine car des étudiants y sont inscrits.");
                        }
                    } catch (PDOException $pe) {
                        // Ignorer si la colonne n'existe pas dans la table etudiants
                    }

                    // Supprimer les orientations
                    $stmtDelO = $this->db->prepare("DELETE FROM orientations WHERE id IN ($placeholdersO)");
                    $stmtDelO->execute($orientationIds);
                }

                // Supprimer les filières
                $stmtDelF = $this->db->prepare("DELETE FROM filieres WHERE id IN ($placeholdersF)");
                $stmtDelF->execute($filiereIds);
            }

            // 3. Supprimer le domaine
            $stmt = $this->db->prepare('DELETE FROM domaines WHERE id = ?');
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            if ($e instanceof PDOException) {
                if ($e->getCode() == '23000' || strpos($e->getMessage(), '1451') !== false) {
                    throw new Exception("Impossible de supprimer ce domaine car il contient des données liées (cours ou étudiants).");
                }
            }
            throw $e;
        }
    }

    public function getAllOrientationsWithHierarchy() {
        $stmt = $this->db->query('
            SELECT o.id as orientation_id, o.nom as orientation_nom, 
                   f.id as filiere_id, f.nom as filiere_nom,
                   d.id as domaine_id, d.nom as domaine_nom
            FROM orientations o
            JOIN filieres f ON o.filiere_id = f.id
            JOIN domaines d ON f.domaine_id = d.id
            ORDER BY d.nom, f.nom, o.nom
        ');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
