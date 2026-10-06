<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/CreditRepository.php';
require_once __DIR__ . '/../../Repositories/CoursRepository.php';
require_once __DIR__ . '/../../Repositories/EtudiantRepository.php';

class CreditController extends Controller {
    private $creditRepository;
    private $coursRepository;
    private $etudiantRepository;

    public function __construct() {
        $this->creditRepository = new CreditRepository();
        $this->coursRepository = new CoursRepository();
        $this->etudiantRepository = new EtudiantRepository();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant', 'etudiant']);
        
        $user = auth();
        $coursData = [];
        
        if ($user['role'] === 'etudiant') {
            $etudiant = $this->etudiantRepository->findByEmail($user['email']);
            if ($etudiant) {
                // Pour un étudiant, on affiche les cours de sa filière et de son niveau
                $allCours = $this->coursRepository->getAll();
                $coursData = array_filter($allCours, function($c) use ($etudiant) {
                    return $c['filiere_id'] == $etudiant['filiere_id']; // Filtre par filière
                });
            }
        } else {
            $coursData = $this->coursRepository->getAll();
        }
        
        // Transformer les cours en format de crédits
        $credits = [];
        foreach ($coursData as $cours) {
            $credits[] = [
                'id' => $cours['id'],
                'cours_nom' => $cours['nom'],
                'code' => $cours['code'],
                'credit' => $cours['credit'] ?? 0,
                'filiere_nom' => $cours['filiere_nom'] ?? 'N/A',
                'niveau' => $cours['niveau'] ?? 'L1',
                'semestre' => $cours['semestre'] ?? 'S1',
            ];
        }
        
        $this->render('Credits.index', ['credits' => $credits, 'pageTitle' => 'Gestion des Crédits']);
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $etudiants = $this->etudiantRepository->getAll();
        $this->render('Credits.create', ['etudiants' => $etudiants, 'pageTitle' => 'Ajouter un crédit']);
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $data = [
            'etudiant_id' => $_POST['etudiant_id'] ?? null,
            'credits_acquis' => $_POST['credits_acquis'] ?? null,
            'credits_requis' => $_POST['credits_requis'] ?? null,
        ];
        $this->creditRepository->create($data);
        redirect('/credits');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $credit = $this->creditRepository->findById($id);
        if (!$credit) {
            abort(404, 'Crédit introuvable');
        }
        $etudiants = $this->etudiantRepository->getAll();
        $this->render('Credits.edit', ['credit' => $credit, 'etudiants' => $etudiants, 'pageTitle' => 'Modifier un crédit']);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $data = [
            'etudiant_id' => $_POST['etudiant_id'] ?? null,
            'credits_acquis' => $_POST['credits_acquis'] ?? null,
            'credits_requis' => $_POST['credits_requis'] ?? null,
        ];
        $this->creditRepository->update($id, $data);
        redirect('/credits');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $this->creditRepository->delete($id);
        redirect('/credits');
    }

    public function updateBulk() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $courseIds = $_POST['credits'] ?? [];
        $semesters = $_POST['semestre'] ?? [];
        
        // Mettre à jour les informations de crédit pour chaque cours
        foreach ($courseIds as $courseId => $creditValue) {
            $semestre = $semesters[$courseId] ?? 'S1';
            $this->coursRepository->updateCreditAndSemestre($courseId, $creditValue, $semestre);
        }
        
        // Rediriger avec un message de succès
        $_SESSION['success'] = 'Crédits et semestres mis à jour avec succès';
        redirect('/credits');
    }
}
