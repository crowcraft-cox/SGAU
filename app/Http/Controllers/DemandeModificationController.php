<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/DemandeModificationRepository.php';
require_once __DIR__ . '/../../Repositories/EnseignantRepository.php';
require_once __DIR__ . '/../../Repositories/CoursRepository.php';
require_once __DIR__ . '/../../Repositories/EtudiantRepository.php';
require_once __DIR__ . '/../../Repositories/NoteRepository.php';

class DemandeModificationController extends Controller {
    private $repository;
    private $enseignantRepository;
    private $coursRepository;
    private $etudiantRepository;
    private $noteRepository;

    public function __construct() {
        $this->repository = new DemandeModificationRepository();
        $this->enseignantRepository = new EnseignantRepository();
        $this->coursRepository = new CoursRepository();
        $this->etudiantRepository = new EtudiantRepository();
        $this->noteRepository = new NoteRepository();
    }

    /**
     * Liste des demandes (Pour SGA / Admin ou Enseignant)
     */
    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);

        $user = auth();
        $statut = $_GET['statut'] ?? null;
        $currentEnseignant = null;

        if ($user['role'] === 'enseignant') {
            $currentEnseignant = $this->enseignantRepository->findByEmail($user['email']);
            if (!$currentEnseignant) {
                $all = $this->enseignantRepository->getAll();
                foreach ($all as $ens) {
                    if (strcasecmp($ens['email'], $user['email']) === 0 || strcasecmp($ens['nom'], $user['name'] ?? '') === 0) {
                        $currentEnseignant = $ens;
                        break;
                    }
                }
            }
            $demandes = $currentEnseignant ? $this->repository->getByEnseignantId($currentEnseignant['id'], $statut) : [];
        } else {
            $demandes = $this->repository->getAll($statut);
        }

        $pendingCount = $this->repository->getPendingCount();

        $this->render('Demandes.index', [
            'demandes' => $demandes,
            'statut' => $statut,
            'pendingCount' => $pendingCount,
            'userRole' => $user['role'],
            'currentEnseignant' => $currentEnseignant,
            'pageTitle' => 'Demandes de Modification de Cotes (SGA)'
        ]);
    }

    /**
     * Soumettre une nouvelle demande de modification
     */
    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);

        $user = auth();
        $coursId = $_POST['cours_id'] ?? null;
        $etudiantId = $_POST['etudiant_id'] ?? null;
        $motif = trim($_POST['motif'] ?? '');

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
               || (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false);

        if (empty($coursId) || empty($etudiantId) || empty($motif)) {
            $errorMsg = 'Veuillez renseigner le cours, l\'étudiant et le motif de la modification.';
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            $_SESSION['error'] = $errorMsg;
            redirect($coursId ? '/notes/cours/' . $coursId : '/notes');
            return;
        }

        // Trouver l'enseignant de manière robuste
        $enseignantId = null;
        if ($user['role'] === 'enseignant') {
            $ens = $this->enseignantRepository->findByEmail($user['email']);
            if ($ens) {
                $enseignantId = $ens['id'];
            } else {
                // Chercher dans tous les enseignants par email ou nom
                $all = $this->enseignantRepository->getAll();
                foreach ($all as $ensItem) {
                    if (strcasecmp($ensItem['email'], $user['email']) === 0 || strcasecmp($ensItem['nom'], $user['name'] ?? '') === 0) {
                        $enseignantId = $ensItem['id'];
                        break;
                    }
                }
            }
        }

        // Si non trouvé par l'utilisateur, chercher via le cours assigné
        if (!$enseignantId && !empty($coursId)) {
            $cours = $this->coursRepository->findById($coursId);
            if ($cours && !empty($cours['enseignant_id'])) {
                $enseignantId = $cours['enseignant_id'];
            }
        }

        // Fallback pour administrateur ou cas d'urgence
        if (!$enseignantId) {
            $cours = $this->coursRepository->findById($coursId);
            $enseignantId = $cours['enseignant_id'] ?? 1;
        }

        // Vérifier si une demande est déjà en cours
        $existingPending = $this->repository->findPendingForStudentAndCourse($coursId, $etudiantId);
        if ($existingPending) {
            $warnMsg = 'Une demande de modification est déjà en attente de traitement par le SGA pour cet étudiant.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $warnMsg]);
                exit;
            }
            $_SESSION['error'] = $warnMsg;
            redirect('/notes/cours/' . $coursId);
            return;
        }

        // Snapshot des notes actuelles
        $currentNote = $this->noteRepository->getByEtudiantAndCours($etudiantId, $coursId);
        $anciennesCotes = $currentNote ? json_encode($currentNote) : null;

        $demandeId = $this->repository->create([
            'enseignant_id' => $enseignantId,
            'cours_id' => $coursId,
            'etudiant_id' => $etudiantId,
            'note_id' => $currentNote['id'] ?? null,
            'motif' => $motif,
            'anciennes_cotes' => $anciennesCotes
        ]);

        $successMsg = 'Votre demande de modification a été transmise au Secrétariat Général Académique (SGA). Vous serez notifié dès son traitement.';

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $successMsg]);
            exit;
        }

        $_SESSION['success'] = $successMsg;
        redirect('/notes/cours/' . $coursId);
    }

    /**
     * Approuver une demande (SGA / Admin)
     */
    public function approuver($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        $user = auth();
        $reponse = trim($_POST['reponse_sga'] ?? '');

        $this->repository->approuver($id, $user['id'], $reponse);
        $_SESSION['success'] = 'Demande approuvée avec succès ! La dérogation a été accordée à l\'enseignant.';

        redirect('/demandes-modification-notes');
    }

    /**
     * Rejeter une demande (SGA / Admin)
     */
    public function rejeter($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);

        $user = auth();
        $reponse = trim($_POST['reponse_sga'] ?? '');

        $this->repository->rejeter($id, $user['id'], $reponse);
        $_SESSION['success'] = 'La demande a été rejetée.';

        redirect('/demandes-modification-notes');
    }
}
