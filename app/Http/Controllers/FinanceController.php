<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Core/Database.php';

class FinanceController extends Controller {
    
    /**
     * Module Finance — en cours de développement
     * Affiche une page temporaire jusqu'à la finalisation du module
     */
    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);

        $this->render('Finance.coming_soon', [
            'pageTitle' => 'Finance (Recettes) — Bientôt disponible'
        ]);
    }

    /**
     * Affiche le formulaire pour enregistrer un paiement
     */
    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        $db = (new Database())->connect();
        
        // Récupérer la liste des étudiants
        $stmt = $db->query("SELECT id, nom, prenom, matricule FROM etudiants ORDER BY nom");
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->render('Finance.create', [
            'etudiants' => $etudiants,
            'pageTitle' => 'Enregistrer un Paiement'
        ]);
    }

    /**
     * Enregistre un paiement en base de données
     */
    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/finance/create');
            return;
        }
        
        $etudiant_id = $_POST['etudiant_id'] ?? null;
        $montant = $_POST['montant'] ?? null;
        $date_paiement = $_POST['date_paiement'] ?? null;
        
        // Validation
        if (!$etudiant_id || !$montant || !$date_paiement) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires';
            redirect('/finance/create');
            return;
        }
        
        if ($montant <= 0) {
            $_SESSION['error'] = 'Le montant doit être positif';
            redirect('/finance/create');
            return;
        }
        
        $db = (new Database())->connect();
        
        // Vérifier que l'étudiant existe
        $checkStmt = $db->prepare("SELECT id FROM etudiants WHERE id = ?");
        $checkStmt->execute([$etudiant_id]);
        
        if (!$checkStmt->fetch()) {
            $_SESSION['error'] = 'Étudiant introuvable';
            redirect('/finance/create');
            return;
        }
        
        // Insérer le paiement (automatiquement validé pour un agent de finance)
        $stmt = $db->prepare("
            INSERT INTO paiements (etudiant_id, montant, date_paiement, statut)
            VALUES (?, ?, ?, 'Validé')
        ");
        
        $success = $stmt->execute([$etudiant_id, $montant, $date_paiement]);
        
        if ($success) {
            // INTÉGRATION COMPTABILITÉ AVANCÉE (Module Banana)
            $paiement_id = $db->lastInsertId();
            recordAccountingTransaction(
                $date_paiement, 
                "CAISSE-" . $paiement_id, 
                "Paiement manuel à la caisse", 
                "5711", // Caisse Principale (USD)
                "7011", // Minerval
                $montant, 
                "USD", 
                $etudiant_id,
                $_SESSION['user_id']
            );

            // Récupérer les informations de l'étudiant
            $etudiantStmt = $db->prepare("SELECT id, nom, prenom, email FROM etudiants WHERE id = ?");
            $etudiantStmt->execute([$etudiant_id]);
            $etudiant = $etudiantStmt->fetch(PDO::FETCH_ASSOC);

            if ($etudiant && !empty($etudiant['email'])) {
                $usd = number_format((float)$montant, (fmod((float)$montant, 1) == 0 ? 0 : 2), '.', ' ');
                $fc = number_format((float)$montant * 2850, 0, '.', ' ');
                $formattedMsgAmount = "{$usd} $ ({$fc} FC)";
                $dateFormatted = date('d/m/Y', strtotime($date_paiement));

                $customMessage = "Votre règlement de {$formattedMsgAmount} du {$dateFormatted} a bien été enregistré. Merci !";

                // 1. Ajouter une notification interne
                $userStmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $userStmt->execute([$etudiant['email']]);
                $user = $userStmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $notifStmt = $db->prepare("
                        INSERT INTO notifications (user_id, titre, message, lue)
                        VALUES (?, ?, ?, 0)
                    ");
                    $notifStmt->execute([$user['id'], "Paiement Enregistré", $customMessage]);
                }

                // 2. Envoyer un email de confirmation
                require_once APP_ROOT . '/app/Services/MailService.php';
                $subject = "SGAU - Reçu de Paiement";
                $mailBody = "<h3>Confirmation de Paiement</h3>
                             <p>Bonjour <strong>{$etudiant['prenom']} {$etudiant['nom']}</strong>,</p>
                             <p style='font-size: 16px; color: #1e293b;'><strong>{$customMessage}</strong></p>
                             <br>
                             <p>Vous pouvez consulter le détail de tous vos règlements sur votre espace <a href='" . base_url('/mon-bilan-financier') . "'>Mon Bilan Financier</a>.</p>
                             <br><p>Cordialement,<br>Le Service Financier SGAU</p>";
                MailService::send($etudiant['email'], $subject, $mailBody);
            }

            $_SESSION['success'] = 'Paiement enregistré avec succès. L\'étudiant a reçu sa notification et son e-mail.';
            redirect('/finance');
        } else {
            $_SESSION['error'] = 'Erreur lors de l\'enregistrement du paiement';
            redirect('/finance/create');
        }
    }

    /**
     * Affiche le formulaire pour modifier un paiement
     */
    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        $db = (new Database())->connect();
        
        // Récupérer le paiement
        $stmt = $db->prepare("SELECT * FROM paiements WHERE id = ?");
        $stmt->execute([$id]);
        $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$paiement) {
            $_SESSION['error'] = 'Paiement introuvable';
            redirect('/finance');
            return;
        }
        
        // Récupérer la liste des étudiants
        $stmt = $db->query("SELECT id, nom, prenom, matricule FROM etudiants ORDER BY nom");
        $etudiants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->render('Finance.edit', [
            'paiement' => $paiement,
            'etudiants' => $etudiants,
            'pageTitle' => 'Modifier Paiement'
        ]);
    }

    /**
     * Met à jour un paiement
     */
    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/finance');
            return;
        }
        
        $etudiant_id = $_POST['etudiant_id'] ?? null;
        $montant = $_POST['montant'] ?? null;
        $date_paiement = $_POST['date_paiement'] ?? null;
        
        // Validation
        if (!$etudiant_id || !$montant || !$date_paiement) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires';
            redirect("/finance/$id/edit");
            return;
        }
        
        if ($montant <= 0) {
            $_SESSION['error'] = 'Le montant doit être positif';
            redirect("/finance/$id/edit");
            return;
        }
        
        $db = (new Database())->connect();
        
        // Vérifier que le paiement existe
        $checkStmt = $db->prepare("SELECT id FROM paiements WHERE id = ?");
        $checkStmt->execute([$id]);
        
        if (!$checkStmt->fetch()) {
            $_SESSION['error'] = 'Paiement introuvable';
            redirect('/finance');
            return;
        }
        
        // Mettre à jour le paiement
        $stmt = $db->prepare("
            UPDATE paiements 
            SET etudiant_id = ?, montant = ?, date_paiement = ?
            WHERE id = ?
        ");
        
        $success = $stmt->execute([$etudiant_id, $montant, $date_paiement, $id]);
        
        if ($success) {
            $_SESSION['success'] = 'Paiement modifié avec succès';
            redirect('/finance');
        } else {
            $_SESSION['error'] = 'Erreur lors de la modification du paiement';
            redirect("/finance/$id/edit");
        }
    }

    /**
     * Supprime un paiement
     */
    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        $db = (new Database())->connect();
        
        // Vérifier que le paiement existe
        $checkStmt = $db->prepare("SELECT id FROM paiements WHERE id = ?");
        $checkStmt->execute([$id]);
        
        if (!$checkStmt->fetch()) {
            $_SESSION['error'] = 'Paiement introuvable';
            redirect('/finance');
            return;
        }
        
        // Supprimer le paiement
        $stmt = $db->prepare("DELETE FROM paiements WHERE id = ?");
        $success = $stmt->execute([$id]);
        
        if ($success) {
            $_SESSION['success'] = 'Paiement supprimé avec succès';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression du paiement';
        }
        
        redirect('/finance');
    }

    /**
     * Affiche le solde d'un étudiant
     */
    public function solde($etudiant_id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance', 'etudiant']);
        
        $db = (new Database())->connect();
        
        // Récupérer les infos de l'étudiant
        $stmt = $db->prepare("SELECT * FROM etudiants WHERE id = ?");
        $stmt->execute([$etudiant_id]);
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$etudiant) {
            $_SESSION['error'] = 'Étudiant introuvable';
            redirect('/finance');
            return;
        }
        
        // Récupérer tous les paiements de l'étudiant
        $stmt = $db->prepare("
            SELECT * FROM paiements 
            WHERE etudiant_id = ? 
            ORDER BY date_paiement DESC
        ");
        $stmt->execute([$etudiant_id]);
        $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculer les totaux
        $totalAuthStmt = $db->prepare("
            SELECT SUM(montant) FROM paiements 
            WHERE etudiant_id = ? AND statut = 'Validé'
        ");
        $totalAuthStmt->execute([$etudiant_id]);
        $montantTotal = $totalAuthStmt->fetchColumn() ?: 0;
        
        $totalAttentStmt = $db->prepare("
            SELECT SUM(montant) FROM paiements 
            WHERE etudiant_id = ? AND statut = 'En attente'
        ");
        $totalAttentStmt->execute([$etudiant_id]);
        $enAttente = $totalAttentStmt->fetchColumn() ?: 0;
        
        $totalRefStmt = $db->prepare("
            SELECT SUM(montant) FROM paiements 
            WHERE etudiant_id = ? AND statut = 'Refusé'
        ");
        $totalRefStmt->execute([$etudiant_id]);
        $refuse = $totalRefStmt->fetchColumn() ?: 0;
        
        $this->render('Finance.solde', [
            'etudiant' => $etudiant,
            'paiements' => $paiements,
            'montantTotal' => $montantTotal,
            'enAttente' => $enAttente,
            'refuse' => $refuse,
            'pageTitle' => 'Solde - ' . $etudiant['prenom'] . ' ' . $etudiant['nom']
        ]);
    }

    /**
     * Autorise un paiement (API)
     */
    public function autoriser() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);

        $input = json_decode(file_get_contents('php://input'), true);
        $paiement_id = $input['paiement_id'] ?? null;

        if (!$paiement_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID du paiement requis']);
            return;
        }

        $db = (new Database())->connect();

        // Vérifier que le paiement existe et est en attente
        $stmt = $db->prepare("SELECT id, statut FROM paiements WHERE id = ?");
        $stmt->execute([$paiement_id]);
        $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paiement) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Paiement introuvable']);
            return;
        }

        if ($paiement['statut'] !== 'En attente') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ce paiement ne peut pas être validé']);
            return;
        }

        // Mettre à jour le statut
        $updateStmt = $db->prepare("UPDATE paiements SET statut = 'Validé' WHERE id = ?");
        $success = $updateStmt->execute([$paiement_id]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Paiement validé avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors de la validation']);
        }
    }

    /**
     * Refuse un paiement (API)
     */
    public function refuser() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);

        $input = json_decode(file_get_contents('php://input'), true);
        $paiement_id = $input['paiement_id'] ?? null;

        if (!$paiement_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID du paiement requis']);
            return;
        }

        $db = (new Database())->connect();

        // Vérifier que le paiement existe et est en attente
        $stmt = $db->prepare("SELECT id, statut FROM paiements WHERE id = ?");
        $stmt->execute([$paiement_id]);
        $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$paiement) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Paiement introuvable']);
            return;
        }

        if ($paiement['statut'] !== 'En attente') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Ce paiement ne peut pas être refusé']);
            return;
        }

        // Mettre à jour le statut
        $updateStmt = $db->prepare("UPDATE paiements SET statut = 'Refusé' WHERE id = ?");
        $success = $updateStmt->execute([$paiement_id]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Paiement refusé']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erreur lors du refus']);
        }
    }

    /**
     * API: Récupère le solde et les statistiques financières d'un étudiant
     */
    public function apiGetSolde($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance', 'etudiant']);
        
        $db = (new Database())->connect();
        
        try {
            // Totaux par statut
            $stmt = $db->prepare("
                SELECT 
                    SUM(CASE WHEN statut = 'Validé' THEN montant ELSE 0 END) as total_valide,
                    SUM(CASE WHEN statut = 'En attente' THEN montant ELSE 0 END) as total_en_attente,
                    SUM(CASE WHEN statut = 'Refusé' THEN montant ELSE 0 END) as total_refuse
                FROM paiements 
                WHERE etudiant_id = ?
            ");
            $stmt->execute([$id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'etudiant_id' => $id,
                    'total_valide' => (float)($stats['total_valide'] ?? 0),
                    'total_en_attente' => (float)($stats['total_en_attente'] ?? 0),
                    'total_refuse' => (float)($stats['total_refuse'] ?? 0),
                    'solde' => (float)($stats['total_valide'] ?? 0)
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Bloquer le relevé d'un étudiant
     */
    public function bloquerReleve($etudiant_id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        $db = (new Database())->connect();
        
        // Vérifier que l'étudiant existe
        $stmt = $db->prepare("SELECT id FROM etudiants WHERE id = ?");
        $stmt->execute([$etudiant_id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Étudiant introuvable';
            redirect('/finance');
            return;
        }
        
        // Mettre à jour le statut
        $updateStmt = $db->prepare("UPDATE etudiants SET releve_bloque = 1 WHERE id = ?");
        if ($updateStmt->execute([$etudiant_id])) {
            $_SESSION['success'] = 'Le relevé de l\'étudiant a été bloqué avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors du blocage du relevé.';
        }
        
        redirect("/finance/{$etudiant_id}/solde");
    }

    /**
     * Débloquer le relevé d'un étudiant
     */
    public function debloquerReleve($etudiant_id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        $db = (new Database())->connect();
        
        // Vérifier que l'étudiant existe
        $stmt = $db->prepare("SELECT id FROM etudiants WHERE id = ?");
        $stmt->execute([$etudiant_id]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Étudiant introuvable';
            redirect('/finance');
            return;
        }
        
        // Mettre à jour le statut
        $updateStmt = $db->prepare("UPDATE etudiants SET releve_bloque = 0 WHERE id = ?");
        if ($updateStmt->execute([$etudiant_id])) {
            $_SESSION['success'] = 'Le relevé de l\'étudiant a été débloqué avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors du déblocage du relevé.';
        }
        
        redirect("/finance/{$etudiant_id}/solde");
    }

    public function monBilan() {
        require_auth();
        RoleMiddleware::requireRole(['etudiant']);

        $user = $_SESSION['user'] ?? null;
        if (!$user) {
            redirect('/login');
        }

        require_once APP_ROOT . '/app/Repositories/EtudiantRepository.php';
        $etudiantRepo = new EtudiantRepository();
        $etudiant = $etudiantRepo->findByEmail($user['email']);

        if (!$etudiant) {
            $_SESSION['error'] = "Profil étudiant introuvable. Veuillez contacter l'administration.";
            redirect('/dashboard');
        }

        $db = (new Database())->connect();
        $stmt = $db->prepare("
            SELECT * FROM paiements 
            WHERE etudiant_id = ? 
            ORDER BY date_paiement DESC
        ");
        $stmt->execute([$etudiant['id']]);
        $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalValide = 0;
        $totalEnAttente = 0;
        $totalRefuse = 0;

        foreach ($paiements as $p) {
            if ($p['statut'] === 'Validé') $totalValide += $p['montant'];
            elseif ($p['statut'] === 'En attente') $totalEnAttente += $p['montant'];
            elseif ($p['statut'] === 'Refusé') $totalRefuse += $p['montant'];
        }

        $this->render('Finance.mon_bilan', [
            'etudiant' => $etudiant,
            'paiements' => $paiements,
            'totalValide' => $totalValide,
            'totalEnAttente' => $totalEnAttente,
            'totalRefuse' => $totalRefuse,
            'pageTitle' => 'Mon Bilan Financier'
        ]);
    }

    /**
     * Affiche la page des paramètres financiers
     */
    public function settings() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        
        $this->render('Finance.settings', [
            'pageTitle' => 'Paramètres Financiers'
        ]);
    }

    /**
     * Met à jour les paramètres financiers (taux de change, numéros Mobile Money)
     */
    public function updateSettings() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/parametres-financiers');
            return;
        }

        $taux = $_POST['taux_change_usd_fc'] ?? null;
        $orange = $_POST['mobile_money_orange'] ?? null;
        $mpesa = $_POST['mobile_money_mpesa'] ?? null;
        $airtel = $_POST['mobile_money_airtel'] ?? null;

        $db = (new Database())->connect();

        $settingsToSave = [
            'taux_change_usd_fc' => $taux,
            'mobile_money_orange' => $orange,
            'mobile_money_mpesa' => $mpesa,
            'mobile_money_airtel' => $airtel,
        ];

        $stmt = $db->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");

        foreach ($settingsToSave as $key => $val) {
            if ($val !== null) {
                $stmt->execute([$key, trim($val)]);
            }
        }

        $_SESSION['success'] = "Paramètres financiers mis à jour avec succès.";
        redirect('/parametres-financiers');
    }
}