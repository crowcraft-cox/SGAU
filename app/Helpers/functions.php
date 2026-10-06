<?php

function config($key) {
    static $config = null;

    if ($config === null) {
        $config = require APP_ROOT . '/config/database.php';
    }

    return $config[$key] ?? null;
}

function base_url($path = '') {
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    if ($path === '' || $path === '/') {
        return BASE_PATH;
    }

    $normalized = '/' . ltrim($path, '/');
    return BASE_PATH . $normalized;
}

function redirect($path) {
    if (!preg_match('#^https?://#i', $path) && strpos($path, '/') === 0) {
        $path = base_url($path);
    }

    header('Location: ' . $path);
    exit;
}

function abort($code = 404, $message = 'Page introuvable') {
    http_response_code($code);
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>' . $code . '</title></head><body>';
    echo '<h1>' . $code . '</h1>';
    echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</body></html>';
    exit;
}

function timeAgo($date) {
    $timestamp = strtotime($date);
    $now = time();
    $seconds = $now - $timestamp;
    
    $intervals = [
        'année' => 31536000,
        'mois' => 2592000,
        'jour' => 86400,
        'heure' => 3600,
        'minute' => 60
    ];
    
    foreach ($intervals as $name => $value) {
        if ($seconds >= $value) {
            $interval = (int)($seconds / $value);
            return $interval . substr($name, 0, 1);
        }
    }
    
    return 'à l\'instant';
}
function settings($key, $default = null) {
    static $settings = null;
    if ($settings === null) {
        try {
            $db = (new Database())->connect();
            $settings = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        } catch (Exception $e) {
            $settings = [];
        }
    }
    return $settings[$key] ?? $default;
}

function format_currency($amount) {
    $amount = (float) $amount;
    $taux = (float) settings('taux_change_usd_fc', 2850);
    $usd = number_format($amount, 2, '.', ',');
    $fc = number_format($amount * $taux, 0, '.', ' ');
    return "\${$usd} ({$fc} FC)";
}

/**
 * Enregistre automatiquement une transaction comptable dans le journal (transactions)
 */
function recordAccountingTransaction($date, $num_doc, $description, $compte_debit, $compte_credit, $montant, $devise, $etudiant_id = null, $user_id = null) {
    try {
        $db = (new Database())->connect();
        
        // Trouver l'exercice ouvert correspondant à la date
        $stmt = $db->prepare("SELECT id FROM exercices WHERE statut = 'Ouvert' AND date_debut <= ? AND date_fin >= ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$date, $date]);
        $exercice = $stmt->fetchColumn();
        
        if (!$exercice) {
            // S'il n'y a pas d'exercice correspondant, prendre le dernier ouvert
            $stmt = $db->query("SELECT id FROM exercices WHERE statut = 'Ouvert' ORDER BY id DESC LIMIT 1");
            $exercice = $stmt->fetchColumn();
        }

        if (!$exercice) return false;

        $taux = ($devise === 'USD') ? (float)settings('taux_change_usd_fc', 2850) : 1;
        $montant_base = $montant * $taux;

        $stmt = $db->prepare("
            INSERT INTO transactions (exercice_id, date_transaction, num_doc, description, compte_debit, compte_credit, montant, devise, taux_change, montant_base, etudiant_id, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $exercice, $date, $num_doc, $description, $compte_debit, $compte_credit, $montant, $devise, $taux, $montant_base, $etudiant_id, $user_id
        ]);

        return true;
    } catch (\Exception $e) {
        error_log("Erreur comptable : " . $e->getMessage());
        return false;
    }
}
