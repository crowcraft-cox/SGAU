<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/PaiementRepository.php';
require_once __DIR__ . '/../../Repositories/EtudiantRepository.php';

class PaiementController extends Controller {
    private $repository;
    private $etudiantRepository;

    public function __construct() {
        $this->repository = new PaiementRepository();
        $this->etudiantRepository = new EtudiantRepository();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        $paiements = $this->repository->getAll();
        $this->render('Finance.index', ['paiements' => $paiements, 'pageTitle' => 'Finance']);
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        $etudiants = $this->etudiantRepository->getAll();
        $this->render('Finance.create', ['etudiants' => $etudiants, 'pageTitle' => 'Ajouter un paiement']);
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        $data = [
            'etudiant_id' => $_POST['etudiant_id'] ?? null,
            'montant' => $_POST['montant'] ?? null,
            'date_paiement' => $_POST['date_paiement'] ?? null,
            'statut' => $_POST['statut'] ?? 'Validé',
        ];
        $this->repository->create($data);
        redirect('/finance');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        $paiement = $this->repository->findById($id);
        if (!$paiement) {
            abort(404, 'Paiement introuvable');
        }
        $etudiants = $this->etudiantRepository->getAll();
        $this->render('Finance.edit', ['paiement' => $paiement, 'etudiants' => $etudiants, 'pageTitle' => 'Modifier un paiement']);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        $data = [
            'etudiant_id' => $_POST['etudiant_id'] ?? null,
            'montant' => $_POST['montant'] ?? null,
            'date_paiement' => $_POST['date_paiement'] ?? null,
            'statut' => $_POST['statut'] ?? null,
        ];
        $this->repository->update($id, $data);
        redirect('/finance');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'finance']);
        $this->repository->delete($id);
        redirect('/finance');
    }
}
