<?php
require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Core/Database.php';
require_once __DIR__ . '/../../Services/MobileMoneyService.php';
require_once __DIR__ . '/../../Services/MailService.php';

class PaymentController extends Controller {
    
    public function initiate() {
        require_auth();
        RoleMiddleware::requireRole(['etudiant']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/mon-bilan-financier');
            return;
        }
        
        // ---- Validation et nettoyage des inputs ----
        $montant = filter_input(INPUT_POST, 'montant', FILTER_VALIDATE_FLOAT);
        $provider = $_POST['provider'] ?? null;
        $phone = preg_replace('/[^0-9+]/', '', $_POST['phone'] ?? '');

        // Valider le montant (doit être positif, max raisonnable 100 000 USD)
        if (!$montant || $montant <= 0 || $montant > 100000) {
            $_SESSION['error'] = 'Montant de paiement invalide.';
            redirect('/mon-bilan-financier');
            return;
        }

        // Valider le provider (liste blanche)
        $allowedProviders = ['Orange Money', 'M-Pesa', 'Airtel Money'];
        if (!in_array($provider, $allowedProviders)) {
            $_SESSION['error'] = 'Opérateur de paiement invalide.';
            redirect('/mon-bilan-financier');
            return;
        }

        // Valider le numéro de téléphone (format africain : 8 à 15 chiffres)
        if (!preg_match('/^\+?[0-9]{8,15}$/', $phone)) {
            $_SESSION['error'] = 'Numéro de téléphone invalide.';
            redirect('/mon-bilan-financier');
            return;
        }
        
        $db = (new Database())->connect();
        $userEmail = $_SESSION['user']['email'] ?? '';
        
        // Récupérer l'ID de l'étudiant
        $stmtEt = $db->prepare("SELECT id FROM etudiants WHERE email = ?");
        $stmtEt->execute([$userEmail]);
        $etudiant = $stmtEt->fetch(PDO::FETCH_ASSOC);
        $etudiant_id = $etudiant['id'] ?? null;
        
        if (!$etudiant_id) {
            $_SESSION['error'] = "Profil étudiant introuvable.";
            redirect('/mon-bilan-financier');
            return;
        }
        
        // 1. Initier la demande de paiement Mobile Money
        $response = MobileMoneyService::initiatePayment($phone, $montant, $provider);
        
        if ($response['success']) {
            // 2. Enregistrer la transaction en attente de validation par le réseau / opérateur
            $stmt = $db->prepare("
                INSERT INTO transactions_en_ligne (reference, etudiant_id, montant, moyen_paiement, numero_telephone, statut)
                VALUES (?, ?, ?, ?, ?, 'En attente')
            ");
            $stmt->execute([$response['reference'], $etudiant_id, $montant, $provider, $phone]);
            
            // 3. Simuler la réception instantanée du message de confirmation de l'opérateur
            $this->processPaymentValidation($response['reference']);
            
            $_SESSION['success'] = "Paiement en ligne de " . format_currency($montant) . " via $provider reçu et validé par le système ! Le reçu vous a été envoyé par e-mail.";
        } else {
            $_SESSION['error'] = 'Échec du traitement du paiement Mobile Money.';
        }
        
        redirect('/mon-bilan-financier');
    }
    
    /**
     * Webhook / Route API appelée automatiquement par l'opérateur Mobile Money
     * Protégé par vérification de signature HMAC-SHA256
     */
    public function callback() {
        $input = file_get_contents('php://input');

        // ---- Vérification de la signature HMAC (protection contre les webhooks forgés) ----
        $webhookSecret = getenv('WEBHOOK_SECRET');
        if (!empty($webhookSecret)) {
            // L'opérateur doit envoyer l'en-tête X-Webhook-Signature: sha256=<hmac>
            $signatureHeader = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
            if (empty($signatureHeader)) {
                error_log('Webhook rejeté : signature manquante depuis ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                http_response_code(401);
                echo json_encode(['error' => 'Signature manquante']);
                return;
            }
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $input, $webhookSecret);
            if (!hash_equals($expectedSignature, $signatureHeader)) {
                error_log('Webhook rejeté : signature invalide depuis ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
                http_response_code(403);
                echo json_encode(['error' => 'Signature invalide']);
                return;
            }
        }

        $payload = json_decode($input, true) ?? [];
        
        $reference = $payload['reference'] ?? $payload['transaction_id'] ?? null;
        $status = strtolower($payload['status'] ?? $payload['code'] ?? '');
        
        if (!$reference || !preg_match('/^[A-Za-z0-9_\-]{4,64}$/', $reference)) {
            http_response_code(400);
            echo json_encode(['error' => 'Référence transaction manquante ou invalide']);
            return;
        }
        
        // Si le message de l'opérateur est valide
        if (in_array($status, ['success', 'validé', 'valide', '00', 'completed', 'paid'])) {
            $validated = $this->processPaymentValidation($reference);
            if ($validated) {
                echo json_encode(['success' => true, 'message' => 'Paiement validé']);
                return;
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Traitement échoué ou statut invalide']);
    }
    
    /**
     * Valide automatiquement le paiement à la réception du message opérateur
     */
    public function processPaymentValidation($reference) {
        $db = (new Database())->connect();
        
        $stmt = $db->prepare("SELECT * FROM transactions_en_ligne WHERE reference = ? AND statut = 'En attente'");
        $stmt->execute([$reference]);
        $txn = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($txn) {
            // Insérer dans la table officielle des paiements validés
            $insertStmt = $db->prepare("
                INSERT INTO paiements (etudiant_id, montant, date_paiement, statut)
                VALUES (?, ?, NOW(), 'Validé')
            ");
            $insertStmt->execute([$txn['etudiant_id'], $txn['montant']]);
            $paiement_id = $db->lastInsertId();
            
            // INTÉGRATION COMPTABILITÉ AVANCÉE (Module Banana)
            $provider = $txn['moyen_paiement'];
            $compte_debit = '5712'; // Caisse par défaut
            if (stripos($provider, 'orange') !== false) $compte_debit = '5811';
            elseif (stripos($provider, 'mpesa') !== false || stripos($provider, 'm-pesa') !== false) $compte_debit = '5812';
            elseif (stripos($provider, 'airtel') !== false) $compte_debit = '5813';
            
            $compte_credit = '7011'; // Minerval par défaut
            $desc = "Paiement en ligne " . $provider;
            
            recordAccountingTransaction(
                date('Y-m-d'), 
                $reference, 
                $desc, 
                $compte_debit, 
                $compte_credit, 
                $txn['montant'], 
                'USD', 
                $txn['etudiant_id']
            );
            
            // Mettre à jour le statut de la transaction en ligne
            $updateStmt = $db->prepare("
                UPDATE transactions_en_ligne 
                SET statut = 'Validé', paiement_id = ? 
                WHERE id = ?
            ");
            $updateStmt->execute([$paiement_id, $txn['id']]);
            
            // Débloquer automatiquement les relevés de notes
            $db->prepare("UPDATE etudiants SET releve_bloque = 0 WHERE id = ?")->execute([$txn['etudiant_id']]);
            
            // Envoi de l'email de confirmation
            $userStmt = $db->prepare("SELECT email, prenom FROM etudiants WHERE id = ?");
            $userStmt->execute([$txn['etudiant_id']]);
            $etudiant = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($etudiant && $etudiant['email']) {
                $body = "<h3>Reçu de Paiement Confirmé</h3>
                         <p>Bonjour {$etudiant['prenom']},</p>
                         <p>Le système a bien validé votre paiement de <strong>" . format_currency($txn['montant']) . "</strong> effectué via {$txn['moyen_paiement']}.</p>
                         <p><strong>Référence de transaction :</strong> {$reference}</p>
                         <p>Vos relevés de notes et documents académiques sont débloqués.</p>
                         <br><p>Cordialement,<br>Le Service Financier SGAU</p>";
                MailService::send($etudiant['email'], "SGAU - Confirmation & Reçu de paiement", $body);
            }
            return true;
        }
        
        return false;
    }
}
