<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Core/Database.php';

class AccountingController extends Controller {

    /**
     * Affiche l'interface du journal comptable interactif
     */
    public function journal() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        $db = (new Database())->connect();
        $stmt = $db->query("SELECT * FROM exercices ORDER BY date_debut DESC");
        $exercices = $stmt->fetchAll();

        $this->render('Finance.journal', [
            'pageTitle' => 'Journal Comptable',
            'exercices' => $exercices
        ]);
    }

    /**
     * Affiche l'interface du plan comptable interactif
     */
    public function planComptable() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        $db = (new Database())->connect();
        $stmt = $db->query("
            SELECT numero, libelle, type_compte, is_groupe, parent_numero 
            FROM comptes 
            ORDER BY numero ASC
        ");
        $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->render('Finance.plan_comptable', [
            'pageTitle' => 'Plan Comptable (Syscohada)',
            'comptes' => $comptes
        ]);
    }

    /**
     * API: Retourne la liste des comptes pour l'autocomplétion (sans les groupes)
     */
    public function getAccounts() {
        require_auth();
        header('Content-Type: application/json');
        try {
            $db = (new Database())->connect();
            $stmt = $db->query("SELECT numero, libelle, type_compte, is_groupe FROM comptes ORDER BY numero ASC");
            $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $dropdown = [];
            foreach ($comptes as $c) {
                if ($c['is_groupe']) continue;
                $dropdown[] = ['id' => $c['numero'], 'name' => $c['numero'] . ' - ' . $c['libelle']];
            }
            
            echo json_encode(['success' => true, 'data' => $dropdown]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Retourne TOUS les comptes (pour la configuration dans Paramètres)
     */
    public function getAllAccounts() {
        require_auth();
        header('Content-Type: application/json');
        try {
            $db = (new Database())->connect();
            $stmt = $db->query("SELECT numero, libelle, type_compte, is_groupe, parent_numero FROM comptes ORDER BY numero ASC");
            $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $comptes]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Ajoute un nouveau compte
     */
    public function addAccount() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['numero']) || empty($input['libelle'])) {
            echo json_encode(['success' => false, 'message' => 'Numéro et intitulé obligatoires.']);
            return;
        }
        try {
            $db = (new Database())->connect();
            $stmt = $db->prepare("INSERT INTO comptes (numero, libelle, type_compte, parent_numero, is_groupe) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $input['numero'],
                $input['libelle'],
                $input['type_compte'] ?? 'Actif',
                $input['parent_numero'] ?: null,
                $input['is_groupe'] ?? 0
            ]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Supprime un compte
     */
    public function deleteAccount() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input['numero'])) {
            echo json_encode(['success' => false, 'message' => 'Numéro obligatoire.']);
            return;
        }
        try {
            $db = (new Database())->connect();
            $stmt = $db->prepare("DELETE FROM comptes WHERE numero = ? AND is_groupe = 0");
            $stmt->execute([$input['numero']]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Retourne les transactions pour l'exercice actif
     */
    public function getTransactions() {
        require_auth();
        header('Content-Type: application/json');
        try {
            $db = (new Database())->connect();
            $exercice_id = $_GET['exercice_id'] ?? 1; // Par défaut
            
            $stmt = $db->prepare("SELECT * FROM transactions WHERE exercice_id = ? ORDER BY date_transaction ASC, id ASC");
            $stmt->execute([$exercice_id]);
            $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $data = [];
            foreach ($transactions as $t) {
                $data[] = [
                    $t['id'],
                    $t['date_transaction'],
                    $t['num_doc'],
                    $t['description'],
                    $t['compte_debit'],
                    $t['compte_credit'],
                    $t['montant'],
                    $t['devise'],
                    $t['taux_change']
                ];
            }
            
            // Add some empty rows at the end for easy entry
            for($i=0; $i<10; $i++){
                $data[] = [null, '', '', '', '', '', '', 'FC', 1];
            }

            echo json_encode(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * API: Sauvegarde en lot des transactions depuis la grille
     */
    public function saveTransactions() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid method']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['data']) || !isset($input['exercice_id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid payload']);
            return;
        }

        $exercice_id = (int)$input['exercice_id'];
        $data = $input['data'];

        $db = (new Database())->connect();
        try {
            $db->beginTransaction();

            $insertStmt = $db->prepare("
                INSERT INTO transactions (exercice_id, date_transaction, num_doc, description, compte_debit, compte_credit, montant, devise, taux_change, montant_base, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $updateStmt = $db->prepare("
                UPDATE transactions 
                SET date_transaction=?, num_doc=?, description=?, compte_debit=?, compte_credit=?, montant=?, devise=?, taux_change=?, montant_base=?
                WHERE id=? AND exercice_id=?
            ");

            $processedIds = [];

            foreach ($data as $row) {
                // row: [id, date, doc, desc, debit, credit, montant, devise, taux]
                $id = $row[0] ?: null;
                $date = $row[1] ?: null;
                $doc = $row[2] ?: '';
                $desc = $row[3] ?: '';
                $debit = $row[4] ?: null;
                $credit = $row[5] ?: null;
                $montant = floatval($row[6] ?: 0);
                $devise = $row[7] ?: 'FC';
                $taux = floatval($row[8] ?: 1);

                // Skip completely empty rows
                if (empty($date) && empty($doc) && empty($desc) && empty($debit) && empty($credit) && $montant == 0) {
                    continue;
                }
                
                if (empty($date) || empty($desc)) {
                    throw new \Exception("La date et la description sont obligatoires pour les lignes saisies.");
                }

                $montant_base = $montant * $taux;
                if ($devise === 'USD') {
                     // Normally we should check settings, but for simple MVP let's assume rate is FC per 1 foreign currency.
                }

                if ($id) {
                    $updateStmt->execute([$date, $doc, $desc, $debit, $credit, $montant, $devise, $taux, $montant_base, $id, $exercice_id]);
                    $processedIds[] = $id;
                } else {
                    $insertStmt->execute([$exercice_id, $date, $doc, $desc, $debit, $credit, $montant, $devise, $taux, $montant_base, $_SESSION['user_id']]);
                    $processedIds[] = $db->lastInsertId();
                }
            }

            // Optional: delete rows that were removed from the grid
            // This requires sending the list of deleted IDs from the frontend, or deleting all IDs not in $processedIds
            // For safety, we only update/insert. Deletions should be explicit.

            $db->commit();
            echo json_encode(['success' => true, 'message' => 'Transactions saved']);
        } catch (\Exception $e) {
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
