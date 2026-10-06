<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/UserRepository.php';

class ParametreController extends Controller {
    private $userRepo;

    public function __construct() {
        $this->userRepo = new UserRepository();
    }

    /**
     * Affiche la page principale des paramètres
     */
    public function index() {
        require_auth();
        
        // Seul l'administrateur peut accéder aux paramètres
        RoleMiddleware::requireRole(['admin'], '/dashboard');

        $user = $_SESSION['user'] ?? [];
        $users = $this->userRepo->getAll();
        $stats = $this->userRepo->getStatistics();
        
        $db = (new Database())->connect();
        $settingsRaw = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

        $roleDescriptions = $this->getRoleDescriptions();

        $this->render('Parametres.index', [
            'pageTitle' => 'Paramètres du Système',
            'user' => $user,
            'users' => $users,
            'stats' => $stats,
            'settings' => $settingsRaw,
            'roleDescriptions' => $roleDescriptions
        ]);
    }

    /**
     * Met à jour les paramètres système
     */
    public function updateSettings() {
        RoleMiddleware::requireRole(['admin']);
        
        // Gérer les données selon le format de la requête
        $data = [];
        if (!empty($_POST)) {
            $data = $_POST;
        } else {
            $jsonData = json_decode(file_get_contents('php://input'), true);
            if ($jsonData) {
                $data = $jsonData;
            }
        }

        if (empty($data) && empty($_FILES)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Données invalides']);
            return;
        }

        $db = (new Database())->connect();
        
        // Gérer l'upload du logo
        if (isset($_FILES['university_logo']) && $_FILES['university_logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = APP_ROOT . '/public/assets/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $logoFile = $_FILES['university_logo'];

            // Vérifier la taille (max 2 MB)
            if ($logoFile['size'] > 2 * 1024 * 1024) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Le logo dépasse la taille maximale autorisée (2 MB)']);
                return;
            }

            // Valider l'extension
            $fileInfo = pathinfo($logoFile['name']);
            $extension = strtolower($fileInfo['extension'] ?? '');
            $allowedExtensions = ['jpg', 'jpeg', 'png'];

            if (!in_array($extension, $allowedExtensions, true)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Format de logo non autorisé. Formats acceptés : JPG, PNG']);
                return;
            }

            // Valider le type MIME réel via finfo (pas le type fourni par le navigateur)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMime = finfo_file($finfo, $logoFile['tmp_name']);
            finfo_close($finfo);
            $allowedLogoMimes = ['image/jpeg', 'image/png'];

            if (!in_array($realMime, $allowedLogoMimes, true)) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Le fichier sélectionné n\'est pas une image valide']);
                return;
            }

            // Valider que c'est vraiment une image (défense en profondeur)
            if (!getimagesize($logoFile['tmp_name'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Le fichier n\'est pas une image valide']);
                return;
            }

            $newFilename = 'logo_' . time() . '.' . $extension;
            $destination = $uploadDir . $newFilename;

            if (move_uploaded_file($logoFile['tmp_name'], $destination)) {
                $data['university_logo'] = $newFilename;
            }
        }



        $stmtCheck = $db->prepare("SELECT setting_key FROM settings WHERE setting_key = ?");
        $stmtUpdate = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmtInsert = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
        
        foreach ($data as $key => $value) {
            if ($key === 'csrf_token') {
                continue;
            }
            $stmtCheck->execute([$key]);
            if ($stmtCheck->fetch()) {
                $stmtUpdate->execute([$value, $key]);
            } else {
                $stmtInsert->execute([$key, $value]);
            }
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    /**
     * Affiche la page de gestion des utilisateurs
     */
    public function users() {
        require_auth();
        RoleMiddleware::requireRole(['admin'], '/dashboard');

        $user = $_SESSION['user'] ?? [];
        $users = $this->userRepo->getAll();

        $this->render('Parametres.users', [
            'user' => $user,
            'users' => $users,
            'pageTitle' => 'Gestion des utilisateurs'
        ]);
    }

    /**
     * Affiche la page de gestion des rôles
     */
    public function roles() {
        require_auth();
        RoleMiddleware::requireRole(['admin'], '/dashboard');

        $user = $_SESSION['user'] ?? [];
        $stats = $this->userRepo->getStatistics();
        $roleDescriptions = $this->getRoleDescriptions();

        $this->render('Parametres.roles', [
            'user' => $user,
            'stats' => $stats,
            'roleDescriptions' => $roleDescriptions,
            'pageTitle' => 'Gestion des rôles'
        ]);
    }

    /**
     * Crée un nouvel utilisateur
     */
    public function storeUser() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $role = $data['role'] ?? '';

        // ---- Validation des champs obligatoires ----
        if (!$name || !$email || !$password || !$role) {
            http_response_code(400);
            echo json_encode(['error' => 'Tous les champs sont obligatoires']);
            return;
        }

        // ---- Validation de l'email ----
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Adresse email invalide']);
            return;
        }

        // ---- Validation du rôle (liste blanche stricte) ----
        $allowedRoles = ['admin', 'enseignant', 'etudiant', 'finance'];
        if (!in_array($role, $allowedRoles, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Rôle invalide']);
            return;
        }

        // ---- Politique de mot de passe fort ----
        // Minimum 8 caractères, au moins 1 majuscule, 1 minuscule, 1 chiffre
        if (strlen($password) < 8) {
            http_response_code(400);
            echo json_encode(['error' => 'Le mot de passe doit contenir au moins 8 caractères']);
            return;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Le mot de passe doit contenir au moins une lettre majuscule']);
            return;
        }
        if (!preg_match('/[a-z]/', $password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Le mot de passe doit contenir au moins une lettre minuscule']);
            return;
        }
        if (!preg_match('/[0-9]/', $password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Le mot de passe doit contenir au moins un chiffre']);
            return;
        }

        // Vérifier si l'email existe déjà
        if ($this->userRepo->findByEmail($email)) {
            http_response_code(400);
            echo json_encode(['error' => 'Cet email est déjà utilisé']);
            return;
        }

        $success = $this->userRepo->create([
            'name'     => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            'email'    => $email,
            'password' => $password,
            'role'     => $role
        ]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur créé avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la création']);
        }
    }

    /**
     * Met à jour un utilisateur (complet)
     */
    public function updateUser() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $role = $data['role'] ?? null;
        $password = $data['password'] ?? null; // Optionnel

        if (!$id || !$name || !$email || !$role) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }

        // Vérifier le changement de rôle du dernier admin
        if ($role !== 'admin') {
            $admins = $this->userRepo->getByRole('admin');
            if (count($admins) === 1 && $admins[0]['id'] == $id) {
                http_response_code(400);
                echo json_encode(['error' => 'Impossible de retirer le rôle du dernier administrateur']);
                return;
            }
        }

        $success = $this->userRepo->update($id, [
            'name' => $name,
            'email' => $email,
            'role' => $role,
            'password' => $password
        ]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * Supprime un utilisateur
     */
    public function deleteUser($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        header('Content-Type: application/json');

        // Empêcher de se supprimer soi-même
        if ($id == $_SESSION['user']['id']) {
            http_response_code(400);
            echo json_encode(['error' => 'Vous ne pouvez pas supprimer votre propre compte']);
            return;
        }

        // Empêcher de supprimer le dernier admin
        $userToDelete = $this->userRepo->getById($id);
        if ($userToDelete && $userToDelete['role'] === 'admin') {
            $admins = $this->userRepo->getByRole('admin');
            if (count($admins) === 1) {
                http_response_code(400);
                echo json_encode(['error' => 'Impossible de supprimer le dernier administrateur']);
                return;
            }
        }

        $success = $this->userRepo->delete($id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la suppression']);
        }
    }

    /**
     * Met à jour uniquement le rôle (compatibilité AJAX)
     */
    public function updateRole() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $userId = $data['userId'] ?? null;
        $newRole = $data['newRole'] ?? null;

        if (!$userId || !$newRole) {
            http_response_code(400);
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }

        // Empêcher la modification du dernier administrateur
        if ($newRole !== 'admin') {
            $admins = $this->userRepo->getByRole('admin');
            if (count($admins) === 1 && $admins[0]['id'] == $userId) {
                http_response_code(400);
                echo json_encode(['error' => 'Impossible de retirer le dernier administrateur']);
                return;
            }
        }

        $success = $this->userRepo->updateRole($userId, $newRole);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Rôle mis à jour avec succès']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la mise à jour']);
        }
    }

    /**
     * Retourne les descriptions des rôles et leurs permissions
     */
    private function getRoleDescriptions() {
        return [
            'admin' => [
                'name' => 'Administrateur',
                'description' => 'Gestion globale du système et des accès.',
                'permissions' => [
                    'Gérer tous les modules du système',
                    'Gérer les utilisateurs',
                    'Gérer les paramètres'
                ]
            ],
            'enseignant' => [
                'name' => 'Enseignant',
                'description' => 'Gestion pédagogique (cours et évaluations).',
                'permissions' => [
                    'Consulter ses cours',
                    'Consulter les étudiants inscrits',
                    'Encoder les notes',
                    'Modifier les notes'
                ],
                'restrictions' => [
                    'Modifier les paiements',
                    'Modifier les filières',
                    'Accéder aux paramètres'
                ]
            ],
            'etudiant' => [
                'name' => 'Étudiant',
                'description' => 'Accès au suivi académique personnel.',
                'permissions' => [
                    'Consulter son profil',
                    'Consulter ses notes',
                    'Consulter ses crédits',
                    'Consulter son relevé de cotes',
                    'Télécharger le relevé (si autorisé par la finance)'
                ]
            ],
            'finance' => [
                'name' => 'Service Financier',
                'description' => 'Gestion de la facturation et des soldes.',
                'permissions' => [
                    'Gérer les paiements',
                    'Consulter les soldes étudiants',
                    'Autoriser le téléchargement des relevés'
                ]
            ]
        ];
    }
}
