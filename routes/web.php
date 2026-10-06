<?php

require_once APP_ROOT . '/app/Http/Controllers/AuthController.php';
require_once APP_ROOT . '/app/Http/Controllers/DashboardController.php';
require_once APP_ROOT . '/app/Http/Controllers/EtudiantController.php';
require_once APP_ROOT . '/app/Http/Controllers/EnseignantController.php';
require_once APP_ROOT . '/app/Http/Controllers/DomaineController.php';
require_once APP_ROOT . '/app/Http/Controllers/CoursController.php';
require_once APP_ROOT . '/app/Http/Controllers/NoteController.php';
require_once APP_ROOT . '/app/Http/Controllers/NotificationController.php';
require_once APP_ROOT . '/app/Http/Controllers/CreditController.php';
require_once APP_ROOT . '/app/Http/Controllers/PaiementController.php';
require_once APP_ROOT . '/app/Http/Controllers/ReleveController.php';
require_once APP_ROOT . '/app/Http/Controllers/CoteController.php';
require_once APP_ROOT . '/app/Http/Controllers/FinanceController.php';
require_once APP_ROOT . '/app/Http/Controllers/ParametreController.php';
require_once APP_ROOT . '/app/Http/Controllers/ValidationController.php';
require_once APP_ROOT . '/app/Http/Controllers/ChatController.php';
require_once APP_ROOT . '/app/Http/Controllers/PaymentController.php';
require_once APP_ROOT . '/app/Http/Controllers/DemandeModificationController.php';

$router->get('/', function () {
    redirect('/login');
});

$router->get('/login', [new AuthController(), 'showLogin']);
$router->post('/login', [new AuthController(), 'login']);
$router->get('/logout', [new AuthController(), 'logout']);

$router->get('/dashboard', [new DashboardController(), 'index']);

$router->get('/etudiants', [new EtudiantController(), 'index']);
$router->get('/dossiers-etudiants', [new EtudiantController(), 'dossierMaintenance']);
$router->get('/etudiants/create', [new EtudiantController(), 'create']);
$router->post('/etudiants/store', [new EtudiantController(), 'store']);
$router->get('/etudiants/template', [new EtudiantController(), 'downloadTemplate']);
$router->post('/etudiants/import', [new EtudiantController(), 'import']);
$router->get('/etudiants/{id}/edit', [new EtudiantController(), 'edit']);
$router->post('/etudiants/{id}/update', [new EtudiantController(), 'update']);
$router->post('/etudiants/{id}/delete', [new EtudiantController(), 'delete']);

$router->get('/enseignants', [new EnseignantController(), 'index']);
$router->get('/enseignants/create', [new EnseignantController(), 'create']);
$router->post('/enseignants/store', [new EnseignantController(), 'store']);
$router->get('/enseignants/{id}/edit', [new EnseignantController(), 'edit']);
$router->post('/enseignants/{id}/update', [new EnseignantController(), 'update']);
$router->post('/enseignants/{id}/delete', [new EnseignantController(), 'delete']);

$router->get('/domaines', [new DomaineController(), 'index']);
$router->get('/domaines/create', [new DomaineController(), 'create']);
$router->post('/domaines/store', [new DomaineController(), 'store']);
$router->get('/domaines/{id}/edit', [new DomaineController(), 'edit']);
$router->post('/domaines/{id}/update', [new DomaineController(), 'update']);
$router->post('/domaines/{id}/delete', [new DomaineController(), 'delete']);

$router->get('/cours', [new CoursController(), 'index']);
$router->get('/cours/create', [new CoursController(), 'create']);
$router->post('/cours/store', [new CoursController(), 'store']);
$router->get('/cours/{id}/edit', [new CoursController(), 'edit']);
$router->post('/cours/{id}/update', [new CoursController(), 'update']);
$router->post('/cours/{id}/delete', [new CoursController(), 'delete']);
$router->get('/cours/{id}/enrollement', [new CoursController(), 'enrollement']);
$router->post('/cours/{id}/enrollement', [new CoursController(), 'storeEnrollement']);
$router->post('/cours/{id}/desenrollement', [new CoursController(), 'deleteEnrollement']);

$router->get('/notes', [new NoteController(), 'index']);
$router->get('/notes/cours/{id}', [new NoteController(), 'coursNotes']);
$router->post('/notes/cours/{id}/bulk-save', [new NoteController(), 'bulkStoreCoursNotes']);
$router->get('/notes/cours/{id}/print', [new NoteController(), 'printFiche']);
$router->get('/notes/create', [new NoteController(), 'create']);
$router->post('/notes/store', [new NoteController(), 'store']);
$router->get('/notes/{id}/edit', [new NoteController(), 'edit']);
$router->post('/notes/{id}/update', [new NoteController(), 'update']);
$router->post('/notes/{id}/delete', [new NoteController(), 'delete']);

// Routes Demandes de Modification de Cotes (Dérogation SGA)
$router->get('/demandes-modification-notes', [new DemandeModificationController(), 'index']);
$router->post('/demandes-modification-notes/store', [new DemandeModificationController(), 'store']);
$router->post('/demandes-modification-notes/{id}/approuver', [new DemandeModificationController(), 'approuver']);
$router->post('/demandes-modification-notes/{id}/rejeter', [new DemandeModificationController(), 'rejeter']);

$router->get('/notifications', [new NotificationController(), 'index']);
$router->get('/notifications/create', [new NotificationController(), 'create']);
$router->post('/notifications/store', [new NotificationController(), 'store']);
$router->get('/notifications/{id}/edit', [new NotificationController(), 'edit']);
$router->post('/notifications/{id}/update', [new NotificationController(), 'update']);
$router->post('/notifications/{id}/delete', [new NotificationController(), 'delete']);
$router->post('/notifications/{id}/mark-as-read', [new NotificationController(), 'markAsRead']);
$router->post('/notifications/mark-all-read', [new NotificationController(), 'markAllAsRead']);

$router->get('/credits', [new CreditController(), 'index']);
$router->get('/credits/create', [new CreditController(), 'create']);
$router->post('/credits/store', [new CreditController(), 'store']);
$router->post('/credits/update-bulk', [new CreditController(), 'updateBulk']);
$router->get('/credits/{id}/edit', [new CreditController(), 'edit']);
$router->post('/credits/{id}/update', [new CreditController(), 'update']);
$router->post('/credits/{id}/delete', [new CreditController(), 'delete']);

$router->get('/paiements', [new PaiementController(), 'index']);
$router->get('/paiements/create', [new PaiementController(), 'create']);
$router->post('/paiements/store', [new PaiementController(), 'store']);
$router->get('/paiements/{id}/edit', [new PaiementController(), 'edit']);
$router->post('/paiements/{id}/update', [new PaiementController(), 'update']);
$router->post('/paiements/{id}/delete', [new PaiementController(), 'delete']);

$router->get('/releves', [new ReleveController(), 'index']);
$router->get('/releves/create', [new ReleveController(), 'create']);
$router->post('/releves/store', [new ReleveController(), 'store']);
$router->get('/releves/{id}', [new ReleveController(), 'show']);
$router->get('/releves/{id}/edit', [new ReleveController(), 'edit']);
$router->post('/releves/{id}/update', [new ReleveController(), 'update']);
$router->post('/releves/{id}/delete', [new ReleveController(), 'delete']);

$router->get('/cotes', [new CoteController(), 'index']);
$router->get('/cotes/create', [new CoteController(), 'create']);
$router->post('/cotes/store', [new CoteController(), 'store']);
$router->get('/cotes/{id}/edit', [new CoteController(), 'edit']);
$router->post('/cotes/{id}/update', [new CoteController(), 'update']);
$router->post('/cotes/{id}/delete', [new CoteController(), 'delete']);

$router->get('/finance', [new FinanceController(), 'index']);
$router->get('/finance/create', [new FinanceController(), 'create']);
$router->post('/finance/store', [new FinanceController(), 'store']);
$router->post('/finance/{id}/update', [new FinanceController(), 'update']);
$router->post('/finance/{id}/delete', [new FinanceController(), 'delete']);
$router->get('/finance/{id}/edit', [new FinanceController(), 'edit']);
$router->get('/finance/{id}/solde', [new FinanceController(), 'solde']);
$router->get('/api/finance/solde/{id}', [new FinanceController(), 'apiGetSolde']);
$router->get('/mon-bilan-financier', [new FinanceController(), 'monBilan']);
$router->post('/finance/{id}/bloquer-releve', [new FinanceController(), 'bloquerReleve']);
$router->post('/finance/{id}/debloquer-releve', [new FinanceController(), 'debloquerReleve']);
$router->post('/finance/update-settings', [new FinanceController(), 'updateSettings']);
$router->get('/parametres-financiers', [new FinanceController(), 'settings']);


$router->post('/payment/initiate', [new PaymentController(), 'initiate']);
$router->post('/payment/callback', [new PaymentController(), 'callback']);

$router->get('/parametres', [new ParametreController(), 'index']);
$router->get('/parametres/users', [new ParametreController(), 'users']);
$router->get('/parametres/roles', [new ParametreController(), 'roles']);
$router->post('/parametres/update-role', [new ParametreController(), 'updateRole']);
$router->post('/parametres/update-settings', [new ParametreController(), 'updateSettings']);
$router->post('/parametres/users/store', [new ParametreController(), 'storeUser']);
$router->post('/parametres/users/update', [new ParametreController(), 'updateUser']);
$router->post('/parametres/users/delete/{id}', [new ParametreController(), 'deleteUser']);

$router->get('/validations', [new ValidationController(), 'index']);
$router->get('/validations/{id}', [new ValidationController(), 'show']);
$router->get('/validations/{id}/semestre/{semestre}', [new ValidationController(), 'bySemester']);

// Routes Chat
$router->get('/chat', [new ChatController(), 'index']);
$router->get('/chat/conversation/{receiverId}', [new ChatController(), 'conversation']);
$router->post('/chat/send-message', [new ChatController(), 'sendMessage']);

/**
 * Routes API pour les Notifications
 */
$router->get('/api/notifications/unread-count', function() {
    $controller = new NotificationController();
    $controller->apiGetUnreadCount();
});
$router->get('/chat/get-messages', [new ChatController(), 'getMessagesAjax']);
$router->get('/chat/get-conversations', [new ChatController(), 'getConversationsAjax']);
$router->post('/chat/delete-message', [new ChatController(), 'deleteMessage']);
