<?php

// ---- Sécurité : masquer les erreurs en production ----
// Les erreurs sont loguées côté serveur, jamais affichées à l'utilisateur
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
ini_set('log_errors', '1');

// ---- En-têtes de sécurité PHP ----
// Supprimer la version PHP de l'en-tête Server
header_remove('X-Powered-By');

// ---- Restriction CORS ----
$allowedOrigins = [
    'http://localhost',
    'http://127.0.0.1',
    'http://localhost/university-system',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host   = $_SERVER['HTTP_HOST'] ?? '';
if (!empty($origin)) {
    if (in_array($origin, $allowedOrigins) || ($host && strpos($origin, $host) !== false)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    }
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-TOKEN, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../bootstrap/app.php';


/*
ob_start(function ($buffer) {
    return preg_replace_callback(
        '/\b(href|src|action)=([\"\'])(\/(?!\/)[^\"\']*)\2/i',
        function ($matches) {
            return $matches[1] . '=' . $matches[2] . BASE_PATH . $matches[3] . $matches[2];
        },
        $buffer
    );
});
*/

$requestUri = $_GET['url'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$baseUri = str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME'])));
if ($baseUri !== '/' && strpos($requestUri, $baseUri) === 0) {
    $requestUri = substr($requestUri, strlen($baseUri));
}
$requestUri = preg_replace('#/+#', '/', $requestUri);
$requestUri = rtrim($requestUri, '/') ?: '/';
$requestMethod = $_SERVER['REQUEST_METHOD'];

$router = new Router();
require_once APP_ROOT . '/routes/web.php';
require_once APP_ROOT . '/routes/api.php';

$router->resolve($requestUri, $requestMethod);
