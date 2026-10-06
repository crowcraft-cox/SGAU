<?php

require_once __DIR__ . '/../bootstrap/app.php';
require_once APP_ROOT . '/app/Http/Controllers/DashboardController.php';

header('Content-Type: application/json');

try {
    $controller = new DashboardController();
    $controller->apiInscriptionsParFiliere();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur: ' . $e->getMessage()]);
}
?>
