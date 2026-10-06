<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/EnseignantRepository.php';
require_once __DIR__ . '/../../Repositories/UserRepository.php';

class EnseignantController extends Controller {
    private $repository;
    private $userRepository;
    private $uploadDir = __DIR__ . '/../../..' . '/public/storage/uploads/enseignants/';

    public function __construct() {
        $this->repository = new EnseignantRepository();
        $this->userRepository = new UserRepository();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $enseignants = $this->repository->getAll();
        $this->render('Enseignants.index', ['enseignants' => $enseignants, 'pageTitle' => 'Enseignants']);
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $this->render('Enseignants.create', ['pageTitle' => 'Ajouter un enseignant']);
    }

    private function handlePhotoUpload() {
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['photo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        // Vérifications
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.');
        }

        if ($file['size'] > $maxFileSize) {
            throw new Exception('Le fichier dépasse la taille maximale de 5MB.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erreur lors du téléchargement du fichier.');
        }

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        // Générer un nom unique pour le fichier
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'enseignant_' . time() . '_' . uniqid() . '.' . $ext;
        $filepath = $this->uploadDir . $filename;

        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Erreur lors du déplacement du fichier.');
        }

        // Retourner le chemin web accessible
        // Le BASE_PATH sera ajouté automatiquement par le système en public/index.php
        return '/storage/uploads/enseignants/' . $filename;
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        // Validation des données
        $matricule = $_POST['matricule'] ?? null;
        $email = $_POST['email'] ?? null;
        $nom = $_POST['nom'] ?? null;

        // Vérifier si le matricule existe déjà
        if ($matricule && $this->repository->findByMatricule($matricule)) {
            $_SESSION['error'] = 'Le matricule "' . htmlspecialchars($matricule, ENT_QUOTES, 'UTF-8') . '" existe déjà.';
            redirect('/enseignants/create');
            return;
        }

        // Vérifier si l'email existe déjà
        if ($email && $this->repository->findByEmail($email)) {
            $_SESSION['error'] = 'L\'email "' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '" existe déjà.';
            redirect('/enseignants/create');
            return;
        }

        // Vérifier les champs obligatoires
        if (!$nom || !$matricule || !$email) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
            redirect('/enseignants/create');
            return;
        }

        try {
            $photoPath = $this->handlePhotoUpload();
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            redirect('/enseignants/create');
            return;
        }

        $data = [
            'matricule' => $matricule,
            'nom' => $nom,
            'prenom' => $_POST['prenom'] ?? null,
            'email' => $email,
            'telephone' => $_POST['telephone'] ?? null,
            'departement' => $_POST['departement'] ?? null,
            'photo' => $photoPath,
        ];

        $this->repository->create($data);

        // Création automatique du compte utilisateur
        $userData = [
            'name' => ($data['prenom'] . ' ' . $data['nom']),
            'email' => $data['email'],
            'password' => $_POST['password'] ?: $data['matricule'], // Utilise le mot de passe du formulaire ou le matricule par défaut
            'role' => 'enseignant'
        ];
        $this->userRepository->create($userData);

        $_SESSION['success'] = 'Enseignant ajouté avec succès et compte utilisateur créé.';
        redirect('/enseignants');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $enseignant = $this->repository->findById($id);

        if (!$enseignant) {
            abort(404, 'Enseignant introuvable');
        }

        $this->render('Enseignants.edit', ['enseignant' => $enseignant, 'pageTitle' => 'Modifier un enseignant']);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        // Récupérer l'enseignant actuel
        $currentEnseignant = $this->repository->findById($id);
        if (!$currentEnseignant) {
            abort(404, 'Enseignant introuvable');
        }

        // Validation des données
        $matricule = $_POST['matricule'] ?? null;
        $email = $_POST['email'] ?? null;
        $nom = $_POST['nom'] ?? null;

        // Vérifier si le matricule a changé et si la nouvelle valeur existe déjà
        if ($matricule && $matricule !== $currentEnseignant['matricule']) {
            if ($this->repository->findByMatricule($matricule)) {
                $_SESSION['error'] = 'Le matricule "' . htmlspecialchars($matricule, ENT_QUOTES, 'UTF-8') . '" existe déjà.';
                redirect('/enseignants/' . $id . '/edit');
                return;
            }
        }

        // Vérifier si l'email a changé et si la nouvelle valeur existe déjà
        if ($email && $email !== $currentEnseignant['email']) {
            if ($this->repository->findByEmail($email)) {
                $_SESSION['error'] = 'L\'email "' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '" existe déjà.';
                redirect('/enseignants/' . $id . '/edit');
                return;
            }
        }

        // Vérifier les champs obligatoires
        if (!$nom || !$matricule || !$email) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
            redirect('/enseignants/' . $id . '/edit');
            return;
        }

        try {
            $photoPath = $this->handlePhotoUpload();
            
            // Si une nouvelle photo est fournie, supprimer l'ancienne
            if ($photoPath && !empty($currentEnseignant['photo'])) {
                $oldPath = __DIR__ . '/../../..' . str_replace('/university-system', '', $currentEnseignant['photo']);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            redirect('/enseignants/' . $id . '/edit');
            return;
        }

        $data = [
            'matricule' => $matricule,
            'nom' => $nom,
            'prenom' => $_POST['prenom'] ?? null,
            'email' => $email,
            'telephone' => $_POST['telephone'] ?? null,
            'departement' => $_POST['departement'] ?? null,
            'photo' => $photoPath ?? $currentEnseignant['photo'],
        ];

        $this->repository->update($id, $data);
        $_SESSION['success'] = 'Enseignant mis à jour avec succès.';
        redirect('/enseignants');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        
        // Récupérer l'enseignant pour supprimer sa photo
        $enseignant = $this->repository->findById($id);
        if ($enseignant && !empty($enseignant['photo'])) {
            $photoPath = __DIR__ . '/../../..' . str_replace('/university-system', '', $enseignant['photo']);
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }
        
        $this->repository->delete($id);
        redirect('/enseignants');
    }
}
