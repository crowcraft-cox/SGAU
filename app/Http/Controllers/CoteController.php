<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/CoteRepository.php';

class CoteController extends Controller {
    private $repository;

    public function __construct() {
        $this->repository = new CoteRepository();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant']);
        $cotes = $this->repository->getAll();
        $this->render('Cotes.index', ['cotes' => $cotes, 'pageTitle' => 'Cotes']);
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $this->render('Cotes.create', ['pageTitle' => 'Ajouter une cote']);
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $data = [
            'code' => $_POST['code'] ?? null,
            'nom' => $_POST['nom'] ?? null,
        ];
        $this->repository->create($data);
        redirect('/cotes');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $cote = $this->repository->findById($id);
        if (!$cote) {
            abort(404, 'Cote introuvable');
        }
        $this->render('Cotes.edit', ['cote' => $cote, 'pageTitle' => 'Modifier une cote']);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $data = [
            'code' => $_POST['code'] ?? null,
            'nom' => $_POST['nom'] ?? null,
        ];
        $this->repository->update($id, $data);
        redirect('/cotes');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $this->repository->delete($id);
        redirect('/cotes');
    }
}
