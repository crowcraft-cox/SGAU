<?php

require_once APP_ROOT . '/app/Core/Controller.php';
require_once APP_ROOT . '/app/Repositories/MessageRepository.php';
require_once APP_ROOT . '/app/Helpers/auth.php';
require_once APP_ROOT . '/app/Http/Middleware/RoleMiddleware.php';

class ChatController extends Controller
{
    private $messageRepository;

    public function __construct()
    {
        $this->messageRepository = new MessageRepository();
    }

    /**
     * Liste des conversations (page principale du Chat)
     */
    public function index()
    {
        if (!check_auth()) {
            redirect('/login');
        }

        $userId = (int)$_SESSION['user']['id'];
        $conversations = $this->messageRepository->getConversations($userId);
        $unreadCount = $this->messageRepository->countUnreadMessages($userId);
        $availableUsers = $this->messageRepository->getAvailableUsers($userId);
        
        // Marquer l'utilisateur comme en ligne
        $this->messageRepository->updateUserOnlineStatus($userId, true);

        return $this->render('Chat/index', [
            'conversations'  => $conversations,
            'availableUsers' => $availableUsers,
            'unreadCount'    => $unreadCount,
            'receiver'       => null,
            'messages'       => [],
            'currentUserId'  => $userId,
            'pageTitle'      => 'Messagerie & Chat'
        ]);
    }

    /**
     * Affiche une conversation avec un utilisateur spécifique
     */
    public function conversation($receiverId)
    {
        if (!check_auth()) {
            if ($this->isAjax()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Non authentifié']);
                exit;
            }
            redirect('/login');
        }

        $userId     = (int)$_SESSION['user']['id'];
        $receiverId = (int)$receiverId;

        if ($receiverId <= 0 || $receiverId === $userId) {
            if ($this->isAjax()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Destinataire invalide']);
                exit;
            }
            redirect('/chat');
            return;
        }

        // Récupérer le destinataire
        $db   = (new Database())->connect();
        $stmt = $db->prepare("SELECT id, name, email, role, is_online FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$receiverId]);
        $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$receiver) {
            if ($this->isAjax()) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Utilisateur introuvable']);
                exit;
            }
            redirect('/chat');
            return;
        }

        // Marquer les messages comme lus
        $this->messageRepository->markConversationAsRead($userId, $receiverId);

        // Récupérer les messages entre les deux utilisateurs
        $messages = $this->messageRepository->getMessages($userId, $receiverId);

        // Formatage des URLs de fichiers pour chaque message
        foreach ($messages as &$msg) {
            if (!empty($msg['file_path'])) {
                $msg['file_url'] = base_url('/storage/uploads/chat/' . basename($msg['file_path']));
            }
        }
        unset($msg);

        // Si requête AJAX (clic dynamique sur un contact)
        if ($this->isAjax() || isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'success'       => true,
                'receiver'      => $receiver,
                'messages'      => $messages,
                'currentUserId' => $userId
            ]);
            exit;
        }

        // Sinon rendu de la page complète avec conversation pré-sélectionnée
        $conversations  = $this->messageRepository->getConversations($userId);
        $availableUsers = $this->messageRepository->getAvailableUsers($userId);
        $unreadCount    = $this->messageRepository->countUnreadMessages($userId);

        $this->messageRepository->updateUserOnlineStatus($userId, true);

        return $this->render('Chat/index', [
            'conversations'  => $conversations,
            'availableUsers' => $availableUsers,
            'unreadCount'    => $unreadCount,
            'receiver'       => $receiver,
            'messages'       => $messages,
            'currentUserId'  => $userId,
            'pageTitle'      => 'Chat — ' . $receiver['name']
        ]);
    }

    /**
     * Envoie un message
     */
    public function sendMessage()
    {
        if (!check_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            exit;
        }

        $senderId = (int)$_SESSION['user']['id'];
        $receiverId = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : null;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        $filePath = null;
        $fileName = null;

        if (!$receiverId || ($message === '' && (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK))) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Destinataire et contenu ou fichier requis']);
            exit;
        }

        // Gérer l'upload de fichier
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $originalName = basename($_FILES['file']['name']);
            $cleanName    = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);
            $storedName   = time() . '_' . bin2hex(random_bytes(4)) . '_' . $cleanName;
            
            $uploadDir = APP_ROOT . '/public/storage/uploads/chat/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            
            $destination = $uploadDir . $storedName;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => "Échec lors de l'enregistrement de la pièce jointe"]);
                exit;
            }
            
            $filePath = $destination;
            $fileName = $originalName;
        }

        try {
            $messageId = $this->messageRepository->create([
                'sender_id'   => $senderId,
                'receiver_id' => $receiverId,
                'message'     => $message,
                'file_path'   => $filePath,
                'file_name'   => $fileName
            ]);

            $newMessage = $this->messageRepository->findById($messageId);
            if ($newMessage && !empty($newMessage['file_path'])) {
                $newMessage['file_url'] = base_url('/storage/uploads/chat/' . basename($newMessage['file_path']));
            }

            // Marquer aussi l'activité de l'expéditeur
            $this->messageRepository->updateUserOnlineStatus($senderId, true);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => $newMessage,
                'id'      => $messageId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Récupère les messages par AJAX (polling ou changement de conversation)
     */
    public function getMessagesAjax()
    {
        if (!check_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];
        $otherUserId = isset($_GET['other_user_id']) ? (int)$_GET['other_user_id'] : (isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : null);

        if (!$otherUserId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Identifiant utilisateur manquant']);
            exit;
        }

        // Marquer comme lus
        $this->messageRepository->markConversationAsRead($userId, $otherUserId);

        // Récupérer les messages
        $messages = $this->messageRepository->getMessages($userId, $otherUserId);
        foreach ($messages as &$msg) {
            if (!empty($msg['file_path'])) {
                $msg['file_url'] = base_url('/storage/uploads/chat/' . basename($msg['file_path']));
            }
        }
        unset($msg);

        // Informations du destinataire
        $db   = (new Database())->connect();
        $stmt = $db->prepare("SELECT id, name, email, role, is_online FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$otherUserId]);
        $receiver = $stmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'receiver'      => $receiver,
            'messages'      => $messages,
            'currentUserId' => $userId
        ]);
        exit;
    }

    /**
     * Obtient les conversations par AJAX
     */
    public function getConversationsAjax()
    {
        if (!check_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];
        $conversations = $this->messageRepository->getConversations($userId);

        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'conversations' => $conversations
        ]);
        exit;
    }

    /**
     * Supprime un message
     */
    public function deleteMessage()
    {
        if (!check_auth()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Non authentifié']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
            exit;
        }

        $messageId = isset($_POST['message_id']) ? (int)$_POST['message_id'] : null;

        if (!$messageId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ID du message manquant']);
            exit;
        }

        $message = $this->messageRepository->findById($messageId);

        if (!$message) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Message introuvable']);
            exit;
        }

        if ($message['sender_id'] != $_SESSION['user']['id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Action non autorisée']);
            exit;
        }

        try {
            $this->messageRepository->delete($messageId);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Détecte si la requête est AJAX / Fetch
     */
    private function isAjax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false);
    }
}
