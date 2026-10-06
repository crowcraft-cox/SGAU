<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/CoursRepository.php';
require_once __DIR__ . '/../../Repositories/DomaineRepository.php';
require_once __DIR__ . '/../../Repositories/EnseignantRepository.php';
require_once __DIR__ . '/../../Repositories/CoursEtudiantRepository.php';

class CoursController extends Controller {
    private $repository;
    private $domaineRepository;
    private $enseignantRepository;
    private $coursEtudiantRepository;

    public function __construct() {
        $this->repository = new CoursRepository();
        $this->domaineRepository = new DomaineRepository();
        $this->enseignantRepository = new EnseignantRepository();
        $this->coursEtudiantRepository = new CoursEtudiantRepository();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant', 'etudiant']);
        
        $user = auth();
        $currentEnseignant = null;
        
        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
            if ($currentEnseignant) {
                $cours = $this->repository->getByEnseignantId($currentEnseignant['id']);
            } else {
                $cours = [];
            }
        } else {
            $cours = $this->repository->getAll();
        }

        $this->render('Cours.index', [
            'cours' => $cours, 
            'currentEnseignant' => $currentEnseignant,
            'pageTitle' => 'Gestion des Cours'
        ]);
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        
        $user = auth();
        $currentEnseignant = null;
        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
        }

        $domaines = $this->domaineRepository->getAll();
        $enseignants = $this->enseignantRepository->getAll();
        
        $this->render('Cours.create', [
            'domaines' => $domaines, 
            'enseignants' => $enseignants, 
            'currentEnseignant' => $currentEnseignant,
            'pageTitle' => 'Ajouter un cours'
        ]);
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        
        $user = auth();
        $enseignantId = $_POST['enseignant_id'] ?? null;
        
        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
            if ($currentEnseignant) {
                $enseignantId = $currentEnseignant['id'];
            }
        }

        $data = [
            'code' => trim($_POST['code'] ?? ''),
            'nom' => trim($_POST['nom'] ?? ''),
            'orientation_id' => !empty($_POST['orientation_id']) ? $_POST['orientation_id'] : null,
            'credit' => !empty($_POST['credit']) ? $_POST['credit'] : 1,
            'enseignant_id' => $enseignantId,
            'description' => $_POST['description'] ?? null,
        ];

        $newId = $this->repository->create($data);
        $_SESSION['success'] = 'Cours créé avec succès ! Vous pouvez maintenant enrôler les étudiants.';
        
        // Redirection directe vers la page d'enrôlement du cours créé
        redirect('/cours/' . $newId . '/enrollement');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        
        $cours = $this->repository->findById($id);
        if (!$cours) {
            abort(404, 'Cours introuvable');
        }

        $user = auth();
        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
            if ($currentEnseignant && $cours['enseignant_id'] != $currentEnseignant['id']) {
                abort(403, 'Vous n\'avez pas accès à ce cours');
            }
        }

        $domaines = $this->domaineRepository->getAll();
        $enseignants = $this->enseignantRepository->getAll();
        $this->render('Cours.edit', [
            'cours' => $cours, 
            'domaines' => $domaines, 
            'enseignants' => $enseignants, 
            'pageTitle' => 'Modifier un cours'
        ]);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        
        $cours = $this->repository->findById($id);
        if (!$cours) {
            abort(404, 'Cours introuvable');
        }

        $user = auth();
        $enseignantId = $_POST['enseignant_id'] ?? $cours['enseignant_id'];
        
        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
            if ($currentEnseignant) {
                $enseignantId = $currentEnseignant['id'];
            }
        }

        $data = [
            'code' => trim($_POST['code'] ?? ''),
            'nom' => trim($_POST['nom'] ?? ''),
            'orientation_id' => !empty($_POST['orientation_id']) ? $_POST['orientation_id'] : null,
            'credit' => !empty($_POST['credit']) ? $_POST['credit'] : 1,
            'enseignant_id' => $enseignantId,
            'description' => $_POST['description'] ?? null,
        ];
        
        $this->repository->update($id, $data);
        $_SESSION['success'] = 'Cours mis à jour avec succès.';
        redirect('/cours');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        
        $cours = $this->repository->findById($id);
        if (!$cours) {
            abort(404, 'Cours introuvable');
        }

        $this->repository->delete($id);
        $_SESSION['success'] = 'Cours supprimé avec succès.';
        redirect('/cours');
    }

    /**
     * Interface d'enrôlement des étudiants dans un cours
     */
    public function enrollement($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);

        $cours = $this->repository->findById($id);
        if (!$cours) {
            abort(404, 'Cours introuvable');
        }

        $user = auth();
        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
            if ($currentEnseignant && $cours['enseignant_id'] != $currentEnseignant['id']) {
                abort(403, 'Vous n\'avez pas accès aux enrôlements de ce cours');
            }
        }

        $search = $_GET['search'] ?? '';
        $etudiants = $this->coursEtudiantRepository->getAllStudentsWithEnrollmentStatus($id, $search);
        $totalEnroles = $this->coursEtudiantRepository->countEnrolled($id);

        $this->render('Cours.enrollement', [
            'cours' => $cours,
            'etudiants' => $etudiants,
            'totalEnroles' => $totalEnroles,
            'search' => $search,
            'pageTitle' => 'Enrôlement des étudiants - ' . htmlspecialchars($cours['nom'], ENT_QUOTES, 'UTF-8')
        ]);
    }

    /**
     * Enregistrer l'enrôlement (unitaire ou par lot)
     */
    public function storeEnrollement($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);

        $cours = $this->repository->findById($id);
        if (!$cours) {
            abort(404, 'Cours introuvable');
        }

        if (!empty($_POST['etudiant_ids']) && is_array($_POST['etudiant_ids'])) {
            $count = $this->coursEtudiantRepository->enrollBulk($id, $_POST['etudiant_ids']);
            $_SESSION['success'] = "$count étudiant(s) enrôlé(s) avec succès dans le cours.";
        } elseif (!empty($_POST['etudiant_id'])) {
            $this->coursEtudiantRepository->enroll($id, $_POST['etudiant_id']);
            $_SESSION['success'] = "Étudiant enrôlé avec succès.";
        } else {
            $_SESSION['error'] = "Veuillez sélectionner au moins un étudiant.";
        }

        redirect('/cours/' . $id . '/enrollement');
    }

    /**
     * Désenrôler un étudiant
     */
    public function deleteEnrollement($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);

        $cours = $this->repository->findById($id);
        if (!$cours) {
            abort(404, 'Cours introuvable');
        }

        $etudiantId = $_POST['etudiant_id'] ?? null;
        if ($etudiantId) {
            $this->coursEtudiantRepository->unenroll($id, $etudiantId);
            $_SESSION['success'] = "Étudiant retiré du cours.";
        }

        redirect('/cours/' . $id . '/enrollement');
    }
}
