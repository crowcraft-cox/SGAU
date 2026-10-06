<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/EtudiantRepository.php';
require_once __DIR__ . '/../../Repositories/DomaineRepository.php';
require_once __DIR__ . '/../../Repositories/UserRepository.php';

class EtudiantController extends Controller {
    private $repository;
    private $domaineRepository;
    private $userRepository;
    private $uploadDir = __DIR__ . '/../../..' . '/public/storage/uploads/etudiants/';

    public function __construct() {
        $this->repository = new EtudiantRepository();
        $this->domaineRepository = new DomaineRepository();
        $this->userRepository = new UserRepository();
        
        // Créer le répertoire d'upload s'il n'existe pas
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant', 'etudiant']);
        
        $user = auth();
        if ($user['role'] === 'etudiant') {
            $etudiant = $this->repository->findByEmail($user['email']);
            $etudiants = $etudiant ? [$etudiant] : [];
        } else {
            $etudiants = $this->repository->getAll();
        }
        
        $this->render('Etudiants.index', ['etudiants' => $etudiants, 'pageTitle' => 'Étudiants']);
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $domaines = $this->domaineRepository->getAll();
        $this->render('Etudiants.create', ['domaines' => $domaines, 'pageTitle' => 'Ajouter un étudiant']);
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        $matricule = $_POST['matricule'] ?? null;
        $email = $_POST['email'] ?? null;

        // Vérifier l'unicité du matricule
        if ($this->repository->findByMatricule($matricule)) {
            $_SESSION['error'] = "Le matricule '{$matricule}' existe déjà!";
            redirect('/etudiants/create');
            return;
        }

        // Vérifier l'unicité de l'email
        if ($this->repository->findByEmail($email)) {
            $_SESSION['error'] = "L'email '{$email}' est déjà utilisé!";
            redirect('/etudiants/create');
            return;
        }

        $photoUploadResult = $this->handlePhotoUpload();
        
        if (!empty($_FILES['photo']['name']) && $photoUploadResult === false) {
            $_SESSION['error'] = $_SESSION['photo_error'] ?? "Erreur lors de l'upload de la photo";
            unset($_SESSION['photo_error']);
            redirect('/etudiants/create');
            return;
        }

        $data = [
            'nom' => $_POST['nom'] ?? null,
            'prenom' => $_POST['prenom'] ?? null,
            'lieu_naissance' => $_POST['lieu_naissance'] ?? null,
            'date_naissance' => $_POST['date_naissance'] ?? null,
            'email' => $email,
            'matricule' => $matricule,
            'orientation_id' => $_POST['orientation_id'] ?? null,
            'niveau' => $_POST['niveau'] ?? null,
            'photo' => $photoUploadResult,
        ];

        $this->repository->create($data);

        // Création automatique du compte utilisateur
        // Générer un mot de passe aléatoire fort si aucun n'est fourni
        // (JAMAIS utiliser le matricule comme mot de passe par défaut : trop prévisible)
        $rawPassword = (!empty($_POST['password']) && strlen($_POST['password']) >= 8)
            ? $_POST['password']
            : $this->generateSecurePassword();

        $userData = [
            'name'     => trim($data['prenom'] . ' ' . $data['nom']),
            'email'    => $data['email'],
            'password' => $rawPassword,
            'role'     => 'etudiant'
        ];
        $this->userRepository->create($userData);

        $_SESSION['success'] = "Étudiant ajouté avec succès et compte utilisateur créé ! Mot de passe temporaire : " . htmlspecialchars($rawPassword, ENT_QUOTES, 'UTF-8') . " (à changer après la première connexion)";
        redirect('/etudiants');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $etudiant = $this->repository->findById($id);

        if (!$etudiant) {
            abort(404, 'Étudiant introuvable');
        }

        $domaines = $this->domaineRepository->getAll();
        $this->render('Etudiants.edit', ['etudiant' => $etudiant, 'domaines' => $domaines]);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        $etudiant = $this->repository->findById($id);
        $photoPath = $etudiant['photo'] ?? null;

        $matricule = $_POST['matricule'] ?? null;
        $email = $_POST['email'] ?? null;

        // Vérifier l'unicité du matricule (si changé)
        if ($matricule !== $etudiant['matricule']) {
            if ($this->repository->findByMatricule($matricule)) {
                $_SESSION['error'] = "Le matricule '{$matricule}' existe déjà!";
                redirect("/etudiants/{$id}/edit");
                return;
            }
        }

        // Vérifier l'unicité de l'email (si changé)
        if ($email !== $etudiant['email']) {
            if ($this->repository->findByEmail($email)) {
                $_SESSION['error'] = "L'email '{$email}' est déjà utilisé!";
                redirect("/etudiants/{$id}/edit");
                return;
            }
        }

        // Supprimer l'ancienne photo si une nouvelle est uploadée
        if (!empty($_FILES['photo']['name'])) {
            $photoUploadResult = $this->handlePhotoUpload();
            
            if ($photoUploadResult === false) {
                $_SESSION['error'] = $_SESSION['photo_error'] ?? "Erreur lors de l'upload de la photo";
                unset($_SESSION['photo_error']);
                redirect("/etudiants/{$id}/edit");
                return;
            }
            
            if ($photoPath) {
                $physicalPath = __DIR__ . '/../../..' . $photoPath;
                if (file_exists($physicalPath)) {
                    unlink($physicalPath);
                }
            }
            $photoPath = $photoUploadResult;
        }

        $data = [
            'nom' => $_POST['nom'],
            'prenom' => $_POST['prenom'],
            'lieu_naissance' => $_POST['lieu_naissance'] ?? null,
            'date_naissance' => $_POST['date_naissance'] ?? null,
            'email' => $_POST['email'],
            'matricule' => $_POST['matricule'],
            'orientation_id' => $_POST['orientation_id'],
            'niveau' => $_POST['niveau'],
            'photo' => $photoPath
        ];

        $this->repository->update($id, $data);
        $_SESSION['success'] = "Étudiant modifié avec succès!";
        redirect('/etudiants');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        
        // Supprimer la photo si elle existe
        $etudiant = $this->repository->findById($id);
        if ($etudiant && $etudiant['photo']) {
            // Convertir le chemin web en chemin physique
            $photoPath = __DIR__ . '/../../..' . $etudiant['photo'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }
        
        $this->repository->delete($id);
        redirect('/etudiants');
    }

    /**
     * Télécharge un modèle CSV pour l'importation
     */
    public function downloadTemplate() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=modele_import_etudiants.csv');
        
        $output = fopen('php://output', 'w');
        // Ajouter BOM pour Excel (Windows)
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        
        // Entêtes
        fputcsv($output, ['nom', 'prenom', 'email', 'matricule', 'orientation', 'niveau'], ';');
        
        // Exemple
        fputcsv($output, ['KABAMBA', 'Jean', 'jean.kabamba@example.com', '2023001', 'Orientation Générale', 'L1'], ';');
        
        fclose($output);
        exit;
    }

    /**
     * Importe des étudiants à partir d'un fichier CSV
     */
    public function import() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        // ---- Validation du fichier CSV ----
        $csvFile = $_FILES['csv_file'];

        // Vérifier les erreurs d'upload PHP
        if ($csvFile['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Erreur lors du téléversement du fichier.";
            redirect('/etudiants');
            return;
        }

        // Taille maximale : 5 MB
        if ($csvFile['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = "Le fichier CSV dépasse la taille maximale autorisée (5 MB).";
            redirect('/etudiants');
            return;
        }

        // Valider l'extension
        $csvExtension = strtolower(pathinfo($csvFile['name'], PATHINFO_EXTENSION));
        if (!in_array($csvExtension, ['csv', 'txt'], true)) {
            $_SESSION['error'] = "Seuls les fichiers CSV sont acceptés.";
            redirect('/etudiants');
            return;
        }

        // Valider le type MIME réel via finfo (pas le type déclaré par le navigateur)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $csvFile['tmp_name']);
        finfo_close($finfo);
        $allowedCsvMimes = ['text/plain', 'text/csv', 'application/csv', 'application/vnd.ms-excel'];
        if (!in_array($realMime, $allowedCsvMimes, true)) {
            $_SESSION['error'] = "Le fichier sélectionné n'est pas un CSV valide.";
            redirect('/etudiants');
            return;
        }

        $file = $csvFile['tmp_name'];
        $handle = fopen($file, "r");

        
        // Ignorer le BOM si présent
        $bom = fread($handle, 3);
        if ($bom !== (chr(0xEF) . chr(0xBB) . chr(0xBF))) {
            rewind($handle);
        }

        // Lire les entêtes
        $headers = fgetcsv($handle, 1000, ";");
        if (!$headers) {
            $_SESSION['error'] = "Le fichier CSV est vide ou mal formaté.";
            redirect('/etudiants');
            return;
        }

        // Préparer le mapping des orientations (Nom -> ID)
        $domaines = $this->domaineRepository->getAll();
        $orientationMap = [];
        foreach ($domaines as $domaine) {
            if (!empty($domaine['filieres'])) {
                foreach ($domaine['filieres'] as $filiere) {
                    if (!empty($filiere['orientations'])) {
                        foreach ($filiere['orientations'] as $ori) {
                            $orientationMap[strtolower(trim($ori['nom']))] = $ori['id'];
                        }
                    }
                }
            }
        }

        $count = 0;
        $errors = 0;
        $rowNum = 1;

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $rowNum++;
            if (count($data) < 5) continue;

            $nom = trim($data[0] ?? '');
            $prenom = trim($data[1] ?? '');
            $email = trim($data[2] ?? '');
            $matricule = trim($data[3] ?? '');
            $orientationName = trim($data[4] ?? '');
            $niveau = trim($data[5] ?? 'L1');

            if (empty($nom) || empty($email) || empty($matricule)) {
                $errors++;
                continue;
            }

            // Vérifier si l'étudiant existe déjà
            if ($this->repository->findByMatricule($matricule) || $this->repository->findByEmail($email)) {
                $errors++;
                continue;
            }

            // Trouver l'ID de l'orientation
            $orientationId = $orientationMap[strtolower($orientationName)] ?? null;
            
            // Si l'orientation n'existe pas, on peut soit sauter, soit la créer. Ici on saute pour sécurité.
            if (!$orientationId) {
                $errors++;
                continue;
            }

            $success = $this->repository->create([
                'nom' => $nom,
                'prenom' => $prenom,
                'lieu_naissance' => null,
                'date_naissance' => null,
                'email' => $email,
                'matricule' => $matricule,
                'orientation_id' => $orientationId,
                'niveau' => $niveau,
                'photo' => null
            ]);

            if ($success) {
                $count++;
            } else {
                $errors++;
            }
        }

        fclose($handle);

        if ($count > 0) {
            $_SESSION['success'] = "{$count} étudiants importés avec succès.";
        }
        
        if ($errors > 0) {
            $_SESSION['error'] = "{$errors} lignes n'ont pas pu être importées (erreurs ou doublons).";
        }

        redirect('/etudiants');
    }

    private function handlePhotoUpload() {
        if (empty($_FILES['photo']['name'])) {
            return null;
        }

        $file = $_FILES['photo'];
        
        // Vérifier les erreurs d'upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => "Le fichier dépasse la limite de taille autorisée par le serveur",
                UPLOAD_ERR_FORM_SIZE => "Le fichier dépasse la limite de taille du formulaire",
                UPLOAD_ERR_PARTIAL => "Le fichier n'a été partiellement téléchargé",
                UPLOAD_ERR_NO_FILE => "Aucun fichier n'a été sélectionné",
                UPLOAD_ERR_NO_TMP_DIR => "Le répertoire temporaire est manquant",
                UPLOAD_ERR_CANT_WRITE => "Impossible d'écrire le fichier sur le disque",
                UPLOAD_ERR_EXTENSION => "Une extension PHP a arrêté le téléchargement"
            ];
            $_SESSION['photo_error'] = $errorMessages[$file['error']] ?? "Erreur inconnue lors du téléchargement";
            return false;
        }
        
        // Valider la taille du fichier
        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            $_SESSION['photo_error'] = "La photo dépasse la taille maximale autorisée (5MB)";
            return false;
        }

        if ($file['size'] === 0) {
            $_SESSION['photo_error'] = "Le fichier est vide";
            return false;
        }

        // Valider l'extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $_SESSION['photo_error'] = "Format de fichier non autorisé. Formats acceptés: JPG, PNG, GIF";
            return false;
        }

        // Valider le type MIME (en utilisant finfo si disponible)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $_SESSION['photo_error'] = "Le fichier n'est pas une image valide";
            return false;
        }

        // Valider que c'est vraiment une image
        if (!getimagesize($file['tmp_name'])) {
            $_SESSION['photo_error'] = "Le fichier n'est pas une image valide";
            return false;
        }

        // Vérifier que le répertoire d'upload existe
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                $_SESSION['photo_error'] = "Impossible de créer le répertoire de stockage";
                return false;
            }
        }

        // Vérifier que le répertoire est accessible en écriture
        if (!is_writable($this->uploadDir)) {
            $_SESSION['photo_error'] = "Le répertoire de stockage n'est pas accessible en écriture";
            return false;
        }

        // Générer un nom de fichier unique
        $filename = 'etudiant_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // Déplacer le fichier
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $_SESSION['photo_error'] = "Impossible de déplacer le fichier uploadé. Vérifiez les permissions du serveur";
            return false;
        }

        // Retourner le chemin web pour la base de données
        return '/storage/uploads/etudiants/' . $filename;
    }

    public function dossierMaintenance() {
        require_auth();
        $this->render('Etudiants.dossier_maintenance', ['pageTitle' => 'Dossiers Étudiant']);
    }

    /**
     * Génère un mot de passe sécurisé aléatoire.
     * Format : au moins 1 majuscule, 1 minuscule, 1 chiffre, 1 symbole, longueur 12.
     * Ne jamais utiliser le matricule comme mot de passe par défaut.
     *
     * @return string
     */
    private function generateSecurePassword(): string {
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghjkmnpqrstuvwxyz';
        $digits  = '23456789';
        $symbols = '!@#$%&*';
        $all     = $upper . $lower . $digits . $symbols;

        $password  = $upper[random_int(0, strlen($upper) - 1)];
        $password .= $lower[random_int(0, strlen($lower) - 1)];
        $password .= $digits[random_int(0, strlen($digits) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        for ($i = 4; $i < 12; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        // Mélanger les caractères pour éviter un motif prévisible
        return str_shuffle($password);
    }
}
