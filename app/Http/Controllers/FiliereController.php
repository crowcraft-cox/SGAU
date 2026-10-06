<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/FiliereRepository.php';

class FiliereController extends Controller {
    private $repository;

    public function __construct() {
        $this->repository = new FiliereRepository();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $filieres = $this->repository->getAll();
        $this->render('Filieres.index', ['filieres' => $filieres, 'pageTitle' => 'Filières']);
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $this->render('Filieres.create', ['pageTitle' => 'Ajouter une filière']);
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $data = [
            'code' => $_POST['code'] ?? null,
            'nom' => $_POST['nom'] ?? null,
            'description' => $_POST['description'] ?? null,
        ];
        $this->repository->create($data);
        redirect('/filieres');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $filiere = $this->repository->findById($id);
        if (!$filiere) {
            abort(404, 'Filière introuvable');
        }
        $this->render('Filieres.edit', ['filiere' => $filiere, 'pageTitle' => 'Modifier une filière']);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $data = [
            'code' => $_POST['code'] ?? null,
            'nom' => $_POST['nom'] ?? null,
            'description' => $_POST['description'] ?? null,
        ];
        $this->repository->update($id, $data);
        redirect('/filieres');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        try {
            $this->repository->delete($id);
            $_SESSION['success'] = "La filière a été supprimée avec succès.";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
        redirect('/filieres');
    }
}
