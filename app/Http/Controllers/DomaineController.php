<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/DomaineRepository.php';

class DomaineController extends Controller {
    private $repository;

    public function __construct() {
        $this->repository = new DomaineRepository();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $domaines = $this->repository->getAll();
        $this->render('Domaines.index', ['domaines' => $domaines, 'pageTitle' => 'Domaines']);
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $this->render('Domaines.create', ['pageTitle' => 'Ajouter un domaine']);
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $data = [
            'code' => $_POST['code'] ?? null,
            'nom' => $_POST['nom'] ?? null,
            'doyen' => $_POST['doyen'] ?? null,
            'description' => $_POST['description'] ?? null,
            'filieres' => []
        ];
        
        // Structure POST attendue:
        // $_POST['filieres'][0]['nom'] = 'Reseaux'
        // $_POST['filieres'][0]['orientations'][] = 'Cyber'
        if (isset($_POST['filieres']) && is_array($_POST['filieres'])) {
            foreach ($_POST['filieres'] as $filiereData) {
                if (!empty($filiereData['nom'])) {
                    $orientations = [];
                    if (isset($filiereData['orientations']) && is_array($filiereData['orientations'])) {
                        foreach ($filiereData['orientations'] as $orientationNom) {
                            if (!empty($orientationNom)) {
                                $orientations[] = $orientationNom;
                            }
                        }
                    }
                    $data['filieres'][] = [
                        'nom' => $filiereData['nom'],
                        'orientations' => $orientations
                    ];
                }
            }
        }

        $this->repository->create($data);
        redirect('/domaines');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $domaine = $this->repository->findById($id);
        if (!$domaine) {
            abort(404, 'Domaine introuvable');
        }
        $this->render('Domaines.edit', ['domaine' => $domaine, 'pageTitle' => 'Modifier un domaine']);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $data = [
            'code' => $_POST['code'] ?? null,
            'nom' => $_POST['nom'] ?? null,
            'doyen' => $_POST['doyen'] ?? null,
            'description' => $_POST['description'] ?? null,
            'filieres' => []
        ];

        if (isset($_POST['filieres']) && is_array($_POST['filieres'])) {
            foreach ($_POST['filieres'] as $filiereData) {
                if (!empty($filiereData['nom'])) {
                    $orientations = [];
                    if (isset($filiereData['orientations']) && is_array($filiereData['orientations'])) {
                        foreach ($filiereData['orientations'] as $orientationNom) {
                            if (!empty($orientationNom)) {
                                $orientations[] = $orientationNom;
                            }
                        }
                    }
                    $data['filieres'][] = [
                        'nom' => $filiereData['nom'],
                        'orientations' => $orientations
                    ];
                }
            }
        }

        $this->repository->update($id, $data);
        redirect('/domaines');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        try {
            $this->repository->delete($id);
            $_SESSION['success'] = "Le domaine a été supprimé avec succès.";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/domaines');
    }
}
