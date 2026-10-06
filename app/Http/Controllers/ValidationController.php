<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/ValidationRepository.php';
require_once __DIR__ . '/../../Repositories/EtudiantRepository.php';

class ValidationController extends Controller {
    private $validationRepository;
    private $etudiantRepository;

    public function __construct() {
        $this->validationRepository = new ValidationRepository();
        $this->etudiantRepository = new EtudiantRepository();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant', 'etudiant']);
        
        $user = auth();
        if ($user['role'] === 'etudiant') {
            $etudiant = $this->etudiantRepository->findByEmail($user['email']);
            // Ne récupérer que lui-même s'il est étudiant
            $students = $etudiant ? $this->validationRepository->getStudentWithValidations($etudiant['id']) : [];
            // Si getStudentWithValidations renvoie un seul objet, on le met dans un tableau
            if ($students && !isset($students[0])) {
                $students = [$students];
            }
        } else {
            $students = $this->validationRepository->getAllStudentsWithValidations();
        }
        
        $this->render('Validations.index', [
            'students' => $students,
            'pageTitle' => 'Gestion des Validations'
        ]);
    }

    public function show($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant', 'etudiant']);
        
        $user = auth();
        if ($user['role'] === 'etudiant') {
            $etudiant = $this->etudiantRepository->findByEmail($user['email']);
            if (!$etudiant || $etudiant['id'] != $id) {
                abort(403, 'Accès refusé : Vous ne pouvez consulter que vos propres validations.');
            }
        }

        // Récupérer l'étudiant
        $student = $this->etudiantRepository->findById($id);
        if (!$student) {
            abort(404, 'Étudiant introuvable');
        }

        // Récupérer les validations de l'étudiant
        $validations = $this->validationRepository->getStudentValidations($id);
        
        // Récupérer les statistiques
        $stats = $this->validationRepository->getValidationStats($id);
        
        // Récupérer les semestres disponibles
        $semesters = [];
        foreach ($validations as $validation) {
            if ($validation['semestre'] && !in_array($validation['semestre'], $semesters)) {
                $semesters[] = $validation['semestre'];
            }
        }
        sort($semesters);
        
        $this->render('Validations.show', [
            'student' => $student,
            'validations' => $validations,
            'stats' => $stats,
            'semesters' => $semesters,
            'pageTitle' => 'Validations - ' . $student['nom'] . ' ' . $student['prenom']
        ]);
    }

    public function bySemester($id, $semestre) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant', 'etudiant']);
        
        $user = auth();
        if ($user['role'] === 'etudiant') {
            $etudiant = $this->etudiantRepository->findByEmail($user['email']);
            if (!$etudiant || $etudiant['id'] != $id) {
                abort(403, 'Accès refusé : Vous ne pouvez consulter que vos propres validations.');
            }
        }

        // Récupérer l'étudiant
        $student = $this->etudiantRepository->findById($id);
        if (!$student) {
            abort(404, 'Étudiant introuvable');
        }

        // Récupérer les validations par semestre
        $validations = $this->validationRepository->getValidationsBySemester($id, $semestre);
        
        // Calculer les statistiques pour ce semestre
        $coursValides = 0;
        $coursNonValides = 0;
        $creditsAcquis = 0;
        
        foreach ($validations as $validation) {
            if ($validation['statut'] === 'Validé') {
                $coursValides++;
                $creditsAcquis += $validation['credit'] ?? 0;
            } else {
                $coursNonValides++;
            }
        }
        
        $this->render('Validations.bySemester', [
            'student' => $student,
            'validations' => $validations,
            'semestre' => $semestre,
            'coursValides' => $coursValides,
            'coursNonValides' => $coursNonValides,
            'creditsAcquis' => $creditsAcquis,
            'pageTitle' => 'Validations S' . $semestre . ' - ' . $student['nom'] . ' ' . $student['prenom']
        ]);
    }

    /**
     * API endpoint pour les validations
     */
    public function apiGetValidations($etudiantId) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'enseignant', 'etudiant']);
        header('Content-Type: application/json');
        try {
            $validations = $this->validationRepository->getStudentValidations($etudiantId);
            $stats = $this->validationRepository->getValidationStats($etudiantId);
            
            echo json_encode([
                'success' => true,
                'validations' => $validations,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
