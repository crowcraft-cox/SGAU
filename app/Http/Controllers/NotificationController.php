<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../../Repositories/NotificationRepository.php';

class NotificationController extends Controller {
    private $repository;

    public function __construct() {
        $this->repository = new NotificationRepository();
    }

    public function index() {
        require_auth();
        $userId = $_SESSION['user']['id'];
        
        // Si c'est un admin, il peut voir toutes les notifications via un paramètre
        if (isset($_GET['all']) && $_SESSION['user']['role'] === 'admin') {
            $notifications = $this->repository->getAll();
        } else {
            $notifications = $this->repository->getByUserId($userId);
        }

        $this->render('Notifications.index', [
            'notifications' => $notifications, 
            'pageTitle' => 'Mes Notifications'
        ]);
    }

    public function markAsRead($id) {
        require_auth();
        $notification = $this->repository->findById($id);
        
        if ($notification && $notification['user_id'] == $_SESSION['user']['id']) {
            $this->repository->markAsRead($id);
        }

        if (isset($_GET['ajax'])) {
            echo json_encode(['success' => true]);
            return;
        }

        redirect('/notifications');
    }

    public function markAllAsRead() {
        require_auth();
        $this->repository->markAllAsRead($_SESSION['user']['id']);
        
        if (isset($_GET['ajax'])) {
            echo json_encode(['success' => true]);
            return;
        }

        redirect('/notifications');
    }

    /**
     * API: Récupère le nombre de notifications non lues
     */
    public function apiGetUnreadCount() {
        require_auth();
        $count = $this->repository->getUnreadCount($_SESSION['user']['id']);
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }

    public function create() {
        require_auth();
        // Seul l'admin peut créer des notifications manuelles pour d'autres
        if ($_SESSION['user']['role'] !== 'admin') {
            redirect('/notifications');
        }
        $this->render('Notifications.create', ['pageTitle' => 'Ajouter une notification']);
    }

    public function store() {
        require_auth();
        $data = [
            'user_id' => $_POST['user_id'] ?? $_SESSION['user']['id'],
            'titre' => $_POST['titre'] ?? null,
            'message' => $_POST['message'] ?? null,
            'lien' => !empty($_POST['lien']) ? trim($_POST['lien']) : null,
            'lue' => 0,
        ];
        $this->repository->create($data);
        redirect('/notifications');
    }

    public function edit($id) {
        require_auth();
        $notification = $this->repository->findById($id);
        if (!$notification || ($_SESSION['user']['role'] !== 'admin' && $notification['user_id'] != $_SESSION['user']['id'])) {
            abort(404, 'Notification introuvable');
        }
        $this->render('Notifications.edit', ['notification' => $notification, 'pageTitle' => 'Modifier une notification']);
    }

    public function update($id) {
        require_auth();
        $notification = $this->repository->findById($id);
        if (!$notification || ($_SESSION['user']['role'] !== 'admin' && $notification['user_id'] != $_SESSION['user']['id'])) {
            abort(403);
        }

        $data = [
            'titre' => $_POST['titre'] ?? $notification['titre'],
            'message' => $_POST['message'] ?? $notification['message'],
            'lien' => isset($_POST['lien']) ? (trim($_POST['lien']) ?: null) : ($notification['lien'] ?? null),
            'lue' => isset($_POST['lue']) ? 1 : 0,
        ];
        $this->repository->update($id, $data);
        redirect('/notifications');
    }

    public function delete($id) {
        require_auth();
        $notification = $this->repository->findById($id);
        if ($notification && ($_SESSION['user']['role'] === 'admin' || $notification['user_id'] == $_SESSION['user']['id'])) {
            $this->repository->delete($id);
        }
        redirect('/notifications');
    }
}
