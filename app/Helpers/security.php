<?php

/**
 * Génère un jeton CSRF et le stocke en session s'il n'existe pas
 */
function csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Génère un champ HTML caché pour le jeton CSRF
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Valide le jeton CSRF envoyé dans la requête
 */
function validate_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        
        // 1. Essayer de récupérer le token depuis les en-têtes HTTP (X-CSRF-TOKEN)
        if (empty($token)) {
            $headers = [];
            if (function_exists('getallheaders')) {
                $headers = getallheaders();
            } else {
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                    }
                }
            }
            
            // Chercher dans les variations courantes
            $token = $headers['X-CSRF-TOKEN'] ?? $headers['X-Csrf-Token'] ?? $headers['x-csrf-token'] ?? '';
        }
        
        // 2. Essayer de récupérer le token depuis le corps JSON de la requête
        if (empty($token)) {
            $input = file_get_contents('php://input');
            $jsonData = json_decode($input, true);
            if (is_array($jsonData) && isset($jsonData['csrf_token'])) {
                $token = $jsonData['csrf_token'];
            }
        }
        
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            // Log de sécurité
            error_log('CSRF Token Validation Failed from ' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            
            // Déterminer s'il s'agit d'une requête AJAX/JSON
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
                      || (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false)
                      || (isset($_SERVER['CONTENT_TYPE']) && strpos(strtolower($_SERVER['CONTENT_TYPE']), 'application/json') !== false);
            
            http_response_code(403);
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Jeton CSRF invalide ou manquant. Veuillez rafraîchir la page.']);
                exit;
            }
            
            die('Erreur de sécurité : Jeton CSRF invalide ou manquant. Veuillez rafraîchir la page.');
        }
    }
    return true;
}

