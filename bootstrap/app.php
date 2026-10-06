<?php

define('APP_ROOT', realpath(__DIR__ . '/../'));

// Calculer BASE_PATH dynamiquement de manière robuste
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = rtrim(str_replace('/public', '', $scriptDir), '/');

// Si on est dans un environnement CLI, on ne peut pas vraiment détecter le basePath via SCRIPT_NAME
if (php_sapi_name() === 'cli') {
    $basePath = '/university-system';
}

define('BASE_PATH', $basePath);

require_once APP_ROOT . '/app/Helpers/functions.php';
require_once APP_ROOT . '/app/Helpers/auth.php';
require_once APP_ROOT . '/app/Helpers/security.php';
require_once APP_ROOT . '/app/Http/Middleware/RoleMiddleware.php';
require_once APP_ROOT . '/app/Core/Database.php';
require_once APP_ROOT . '/app/Core/Controller.php';
require_once APP_ROOT . '/app/Core/Router.php';
require_once APP_ROOT . '/app/Core/Application.php';

// Initialize env and application
Application::loadEnv(APP_ROOT . '/.env');
$app = new Application();
if (session_status() === PHP_SESSION_NONE) {
    // Sécurisation des sessions
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Correction à chaud des rôles en session (migration entier -> string)
if (isset($_SESSION['user']['role'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 0 || $role === '0' || $role === 1 || $role === '1') {
        $_SESSION['user']['role'] = 'admin';
    } elseif ($role === 2 || $role === '2') {
        $_SESSION['user']['role'] = 'enseignant';
    } elseif ($role === 3 || $role === '3') {
        $_SESSION['user']['role'] = 'etudiant';
    } elseif ($role === 4 || $role === '4') {
        $_SESSION['user']['role'] = 'finance';
    }
}
