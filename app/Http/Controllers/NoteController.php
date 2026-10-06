<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/NoteRepository.php';
require_once __DIR__ . '/../../Repositories/EtudiantRepository.php';
require_once __DIR__ . '/../../Repositories/CoursRepository.php';
require_once __DIR__ . '/../../Repositories/EnseignantRepository.php';
require_once __DIR__ . '/../../Repositories/CoursEtudiantRepository.php';

class NoteController extends Controller {
    private $repository;
    private $etudiantRepository;
    private $coursRepository;
    private $enseignantRepository;
    private $coursEtudiantRepository;

    public function __construct() {
        $this->repository = new NoteRepository();
        $this->etudiantRepository = new EtudiantRepository();
        $this->coursRepository = new CoursRepository();
        $this->enseignantRepository = new EnseignantRepository();
        $this->coursEtudiantRepository = new CoursEtudiantRepository();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant', 'etudiant']);
        
        $user = auth();

        if ($user['role'] === 'etudiant') {
            $etudiant = $this->etudiantRepository->findByEmail($user['email']);
            $notes = $etudiant ? $this->repository->getByEtudiantId($etudiant['id']) : [];
            $this->render('Notes.index', [
                'userRole' => 'etudiant',
                'etudiant' => $etudiant,
                'notes' => $notes, 
                'pageTitle' => 'Mes Notes'
            ]);
            return;
        }

        // Pour l'enseignant et l'admin : affichage sous forme de cours avec statistiques
        $currentEnseignant = null;
        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
            $cours = $currentEnseignant ? $this->coursRepository->getByEnseignantId($currentEnseignant['id']) : [];
        } else {
            $cours = $this->coursRepository->getAll();
        }

        $this->render('Notes.index', [
            'userRole' => $user['role'],
            'currentEnseignant' => $currentEnseignant,
            'cours' => $cours,
            'pageTitle' => 'Gestion des Notes & Fiches de Cotes'
        ]);
    }

    /**
     * Grille de cotation (Fiche de Cotes) pour un cours donné
     */
    public function coursNotes($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);

        $cours = $this->coursRepository->findById($id);
        if (!$cours) {
            abort(404, 'Cours introuvable');
        }

        $user = auth();
        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
            if ($currentEnseignant && $cours['enseignant_id'] != $currentEnseignant['id']) {
                abort(403, 'Vous n\'avez pas accès à la cotation de ce cours');
            }
        }

        $etudiantsNotes = $this->repository->getEnrolledStudentsWithNotes($id);

        // Récupérer les paramètres système (Année académique, SGA, Logo, etc.)
        $db = (new Database())->connect();
        $settings = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

        $this->render('Notes.cours_notes', [
            'cours' => $cours,
            'etudiantsNotes' => $etudiantsNotes,
            'settings' => $settings,
            'pageTitle' => 'Fiche de Cotes - ' . htmlspecialchars($cours['nom'], ENT_QUOTES, 'UTF-8')
        ]);
    }

    /**
     * Sauvegarde en masse des notes d'un cours
     */
    public function bulkStoreCoursNotes($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);

        $cours = $this->coursRepository->findById($id);
        if (!$cours) {
            abort(404, 'Cours introuvable');
        }

        $user = auth();
        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
            if ($currentEnseignant && $cours['enseignant_id'] != $currentEnseignant['id']) {
                abort(403, 'Accès non autorisé');
            }
        }

        $notesData = $_POST['notes'] ?? [];
        $isEnseignant = ($user['role'] === 'enseignant');

        if (is_array($notesData)) {
            $this->repository->saveBulkNotesForCours($id, $notesData, $isEnseignant);
            $_SESSION['success'] = 'Toutes les notes ont été enregistrées avec succès !';
        } else {
            $_SESSION['error'] = 'Aucune donnée de note reçue.';
        }

        // Si requête AJAX
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Notes enregistrées avec succès']);
            exit;
        }

        redirect('/notes/cours/' . $id);
    }

    /**
     * Version imprimable A4 officielle de la Fiche de Cotes
     */
    public function printFiche($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);

        $cours = $this->coursRepository->findById($id);
        if (!$cours) {
            abort(404, 'Cours introuvable');
        }

        $etudiantsNotes = $this->repository->getEnrolledStudentsWithNotes($id);

        $db = (new Database())->connect();
        $settings = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

        // Vue d'impression isolée
        require APP_ROOT . '/app/Views/Notes/fiche_print.php';
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        $etudiants = $this->etudiantRepository->getAll();
        $cours = $this->coursRepository->getAll();
        $this->render('Notes.create', ['etudiants' => $etudiants, 'cours' => $cours, 'pageTitle' => 'Ajouter une note']);
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        
        $data = [
            'etudiant_id' => $_POST['etudiant_id'] ?? null,
            'cours_id' => $_POST['cours_id'] ?? null,
            'interro' => $_POST['interro'] ?? null,
            'tp' => $_POST['tp'] ?? null,
            'td' => $_POST['td'] ?? null,
            'mi_session' => $_POST['mi_session'] ?? null,
            'examen' => $_POST['examen'] ?? null,
            'semestre' => $_POST['semestre'] ?? null,
        ];
        
        // Assurer que l'étudiant est également enrôlé dans le cours
        if (!empty($data['cours_id']) && !empty($data['etudiant_id'])) {
            $this->coursEtudiantRepository->enroll($data['cours_id'], $data['etudiant_id']);
        }

        $this->repository->create($data);
        $_SESSION['success'] = 'Note ajoutée avec succès.';
        redirect('/notes');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        $note = $this->repository->findById($id);
        if (!$note) {
            abort(404, 'Note introuvable');
        }
        $etudiants = $this->etudiantRepository->getAll();
        $cours = $this->coursRepository->getAll();
        $this->render('Notes.edit', ['note' => $note, 'etudiants' => $etudiants, 'cours' => $cours, 'pageTitle' => 'Modifier une note']);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        $data = [
            'etudiant_id' => $_POST['etudiant_id'] ?? null,
            'cours_id' => $_POST['cours_id'] ?? null,
            'interro' => $_POST['interro'] ?? null,
            'tp' => $_POST['tp'] ?? null,
            'td' => $_POST['td'] ?? null,
            'mi_session' => $_POST['mi_session'] ?? null,
            'examen' => $_POST['examen'] ?? null,
            'semestre' => $_POST['semestre'] ?? null,
        ];
        $this->repository->update($id, $data);
        $_SESSION['success'] = 'Note mise à jour avec succès.';
        redirect('/notes');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        $this->repository->delete($id);
        $_SESSION['success'] = 'Note supprimée avec succès.';
        redirect('/notes');
    }
}
