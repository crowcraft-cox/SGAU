<?php

require_once APP_ROOT . '/app/Http/Controllers/ReleveController.php';
require_once APP_ROOT . '/app/Http/Controllers/DashboardController.php';
require_once APP_ROOT . '/app/Http/Controllers/FinanceController.php';
require_once APP_ROOT . '/app/Http/Controllers/ValidationController.php';
require_once APP_ROOT . '/app/Http/Controllers/NotificationController.php';

/**
 * Routes API pour les relevés
 */


// Récupérer les semestres disponibles pour un étudiant
$router->get('/api/etudiants/{id}/semestres', function($id) {
    require_auth();
    header('Content-Type: application/json');
    try {
        $controller = new ReleveController();
        $controller->apiGetSemestres($id);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
});

// Récupérer les données du relevé (notes et moyenne) pour un étudiant et un semestre
$router->get('/api/etudiants/{id}/releve', function($id) {
    require_auth();
    header('Content-Type: application/json');
    try {
        $controller = new ReleveController();
        $controller->apiGetEtudiantReleve($id);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
});

// Récupérer les inscriptions par filière
$router->get('/api/dashboard/inscriptions-par-filiere', function() {
    require_auth();
    if (!RoleMiddleware::isAdmin()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    header('Content-Type: application/json');
    try {
        $controller = new DashboardController();
        $controller->apiInscriptionsParFiliere();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
});

/**
 * Routes API pour les finances
 */

// Récupérer le solde d'un étudiant
$router->get('/api/finance/solde/{id}', function($id) {
    require_auth();
    if (!RoleMiddleware::isAdmin() && !RoleMiddleware::isFinance()) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    header('Content-Type: application/json');
    try {
        $controller = new FinanceController();
        $controller->apiGetSolde($id);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
});

// Autoriser un paiement
$router->post('/api/finance/autoriser', function() {
    $controller = new FinanceController();
    $controller->autoriser();
});

// Refuser un paiement
$router->post('/api/finance/refuser', function() {
    $controller = new FinanceController();
    $controller->refuser();
});

/**
 * Routes API pour les validations
 */

// Récupérer les validations d'un étudiant
$router->get('/api/etudiants/{id}/validations', function($id) {
    header('Content-Type: application/json');
    try {
        $controller = new ValidationController();
        $controller->apiGetValidations($id);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

/**
 * Routes API pour le Chat - Statut en ligne
 */

require_once APP_ROOT . '/app/Repositories/MessageRepository.php';

// Mettre à jour le statut en ligne de l'utilisateur
$router->post('/api/chat/update-online-status', function() {
    header('Content-Type: application/json');
    
    // Vérifier l'authentification
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    try {
        $userId = $_SESSION['user']['id'];
        $isOnline = isset($_POST['online']) ? (bool)$_POST['online'] : true;
        
        $messageRepo = new MessageRepository();
        $messageRepo->updateUserOnlineStatus($userId, $isOnline);
        
        echo json_encode(['success' => true, 'message' => 'Status updated']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
});

// Récupérer le nombre d'utilisateurs en ligne
$router->get('/api/chat/online-count', function() {
    header('Content-Type: application/json');
    
    // Vérifier l'authentification
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    try {
        $messageRepo = new MessageRepository();
        // Mettre à jour les utilisateurs inactifs
        $messageRepo->updateInactiveUsers(5);
        
        $onlineCount = $messageRepo->countOnlineUsers();
        
        echo json_encode(['success' => true, 'count' => $onlineCount]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
});

// Récupérer la liste des utilisateurs en ligne
$router->get('/api/chat/online-users', function() {
    header('Content-Type: application/json');
    
    // Vérifier l'authentification
    if (empty($_SESSION['user'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    try {
        $userId = $_SESSION['user']['id'];
        $messageRepo = new MessageRepository();
        $onlineUsers = $messageRepo->getOnlineUsers($userId);
        
        echo json_encode(['success' => true, 'users' => $onlineUsers]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
});

/**
 * Routes API pour les Notifications
 */
$router->get('/api/notifications/unread-count', function() {
    require_auth();
    $controller = new NotificationController();
    $controller->apiGetUnreadCount();
});

