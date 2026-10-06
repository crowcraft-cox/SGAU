<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Core/Database.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../../Helpers/functions.php';

class DashboardController extends Controller {

    public function index() {
        require_auth();

        $db       = (new Database())->connect();
        $user     = $_SESSION['user'] ?? [];
        $userRole = $user['role'] ?? 'etudiant';

        // ════════════════════════════════════════════════════════════
        // CAS 1 : TABLEAU DE BORD ENSEIGNANT
        // ════════════════════════════════════════════════════════════
        if ($userRole === 'enseignant') {
            $userEmail = $user['email'] ?? '';
            $userName  = $user['name'] ?? '';

            // Trouver le profil enseignant associé
            $stmtEns = $db->prepare("SELECT * FROM enseignants WHERE email = ? LIMIT 1");
            $stmtEns->execute([$userEmail]);
            $enseignant = $stmtEns->fetch(PDO::FETCH_ASSOC);

            if (!$enseignant && !empty($userName)) {
                $stmtEns = $db->prepare("SELECT * FROM enseignants WHERE nom LIKE ? OR prenom LIKE ? LIMIT 1");
                $stmtEns->execute(['%' . $userName . '%', '%' . $userName . '%']);
                $enseignant = $stmtEns->fetch(PDO::FETCH_ASSOC);
            }

            $enseignantId = (int)($enseignant['id'] ?? 0);

            // Liste des cours de l'enseignant avec statistiques d'évaluation
            $stmtCours = $db->prepare("
                SELECT c.*, 
                       f.nom as filiere_nom,
                       COUNT(n.id) as nb_notes, 
                       ROUND(AVG(n.total), 1) as moyenne
                FROM cours c
                LEFT JOIN orientations o ON o.id = c.orientation_id
                LEFT JOIN filieres f ON f.id = o.filiere_id
                LEFT JOIN notes n ON n.cours_id = c.id
                WHERE c.enseignant_id = ?
                GROUP BY c.id
                ORDER BY nb_notes DESC, c.nom ASC
            ");
            $stmtCours->execute([$enseignantId]);
            $mesCours = $stmtCours->fetchAll(PDO::FETCH_ASSOC);

            // Si aucun cours n'est explicitement rattaché, récupérer les cours du même département
            if (empty($mesCours) && !empty($enseignant['departement'])) {
                $stmtCoursDept = $db->prepare("
                    SELECT c.*, 
                           f.nom as filiere_nom,
                           COUNT(n.id) as nb_notes, 
                           ROUND(AVG(n.total), 1) as moyenne
                    FROM cours c
                    LEFT JOIN orientations o ON o.id = c.orientation_id
                    LEFT JOIN filieres f ON f.id = o.filiere_id
                    LEFT JOIN notes n ON n.cours_id = c.id
                    WHERE f.nom LIKE ?
                    GROUP BY c.id
                    ORDER BY nb_notes DESC, c.nom ASC
                    LIMIT 8
                ");
                $stmtCoursDept->execute(['%' . $enseignant['departement'] . '%']);
                $mesCours = $stmtCoursDept->fetchAll(PDO::FETCH_ASSOC);
            }

            // Statistiques globales de l'enseignant
            $totalCoursAssigne   = count($mesCours);
            $totalCreditsAssigne = array_sum(array_column($mesCours, 'credit'));
            $totalNotesSaisies   = array_sum(array_column($mesCours, 'nb_notes'));

            $moyennesValid = array_filter(array_column($mesCours, 'moyenne'), fn($m) => $m !== null && $m > 0);
            $moyenneEnseignant = count($moyennesValid) > 0 ? round(array_sum($moyennesValid) / count($moyennesValid), 1) : '0.0';

            // Mes demandes de dérogation (SGA)
            $stmtDemandes = $db->prepare("
                SELECT d.*, c.nom as cours_nom, c.code as cours_code, e.nom as etudiant_nom, e.prenom as etudiant_prenom
                FROM demandes_modification_notes d
                JOIN cours c ON c.id = d.cours_id
                JOIN etudiants e ON e.id = d.etudiant_id
                WHERE d.enseignant_id = ?
                ORDER BY d.created_at DESC
                LIMIT 5
            ");
            $stmtDemandes->execute([$enseignantId]);
            $mesDemandes = $stmtDemandes->fetchAll(PDO::FETCH_ASSOC);

            $demandesEnAttenteCount = 0;
            foreach ($mesDemandes as $dem) {
                if (($dem['statut'] ?? '') === 'en_attente') $demandesEnAttenteCount++;
            }

            // Dernières notes saisies dans les cours de cet enseignant
            $coursIds = array_column($mesCours, 'id');
            $dernieresNotes = [];
            if (!empty($coursIds)) {
                $placeholders = implode(',', array_fill(0, count($coursIds), '?'));
                $stmtNotesRecentes = $db->prepare("
                    SELECT n.*, c.nom as cours_nom, c.code as cours_code, e.nom as etudiant_nom, e.prenom as etudiant_prenom
                    FROM notes n
                    JOIN cours c ON c.id = n.cours_id
                    JOIN etudiants e ON e.id = n.etudiant_id
                    WHERE n.cours_id IN ($placeholders)
                    ORDER BY n.created_at DESC
                    LIMIT 6
                ");
                $stmtNotesRecentes->execute($coursIds);
                $dernieresNotes = $stmtNotesRecentes->fetchAll(PDO::FETCH_ASSOC);
            }

            $this->render('Dashboard.index', [
                'userRole'               => 'enseignant',
                'enseignant'             => $enseignant,
                'mesCours'               => $mesCours,
                'totalCoursAssigne'      => $totalCoursAssigne,
                'totalCreditsAssigne'    => $totalCreditsAssigne,
                'totalNotesSaisies'      => $totalNotesSaisies,
                'moyenneEnseignant'      => $moyenneEnseignant,
                'mesDemandes'            => $mesDemandes,
                'demandesEnAttenteCount' => $demandesEnAttenteCount,
                'dernieresNotes'         => $dernieresNotes,
            ]);
            return;
        }

        // ════════════════════════════════════════════════════════════
        // CAS 2 : TABLEAU DE BORD ÉTUDIANT
        // ════════════════════════════════════════════════════════════
        if ($userRole === 'etudiant') {
            $userEmail = $user['email'] ?? '';
            $userName  = $user['name'] ?? '';

            // Trouver le profil étudiant associé
            $stmtEt = $db->prepare("
                SELECT e.*, o.nom as orientation_nom, f.nom as filiere_nom
                FROM etudiants e
                LEFT JOIN orientations o ON o.id = e.orientation_id
                LEFT JOIN filieres f ON f.id = o.filiere_id
                WHERE e.email = ? LIMIT 1
            ");
            $stmtEt->execute([$userEmail]);
            $etudiant = $stmtEt->fetch(PDO::FETCH_ASSOC);

            if (!$etudiant && !empty($userName)) {
                $stmtEt = $db->prepare("
                    SELECT e.*, o.nom as orientation_nom, f.nom as filiere_nom
                    FROM etudiants e
                    LEFT JOIN orientations o ON o.id = e.orientation_id
                    LEFT JOIN filieres f ON f.id = o.filiere_id
                    WHERE e.nom LIKE ? OR e.prenom LIKE ? LIMIT 1
                ");
                $stmtEt->execute(['%' . $userName . '%', '%' . $userName . '%']);
                $etudiant = $stmtEt->fetch(PDO::FETCH_ASSOC);
            }

            $etudiantId = (int)($etudiant['id'] ?? 0);

            // Notes de l'étudiant
            $stmtNotes = $db->prepare("
                SELECT n.*, c.nom as cours_nom, c.code as cours_code, c.credit, c.semestre as cours_semestre
                FROM notes n
                JOIN cours c ON c.id = n.cours_id
                WHERE n.etudiant_id = ?
                ORDER BY c.nom ASC
            ");
            $stmtNotes->execute([$etudiantId]);
            $mesNotes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

            // Paiements de l'étudiant
            $stmtP = $db->prepare("
                SELECT * FROM paiements 
                WHERE etudiant_id = ? 
                ORDER BY date_paiement DESC, created_at DESC 
                LIMIT 5
            ");
            $stmtP->execute([$etudiantId]);
            $mesPaiements = $stmtP->fetchAll(PDO::FETCH_ASSOC);

            $totalPaye = 0;
            foreach ($mesPaiements as $p) {
                if (($p['statut'] ?? '') === 'Validé') {
                    $totalPaye += (float)$p['montant'];
                }
            }

            // Calculs académiques de l'étudiant
            $totalCoursEvalues = count($mesNotes);
            $coursValides      = 0;
            $coursAjournes     = 0;
            $creditsValides    = 0;
            $sommeNotes        = 0;

            foreach ($mesNotes as $n) {
                $tot = (float)$n['total'];
                $sommeNotes += $tot;
                // Validation si total >= 50 sur 100 ou >= 10 sur 20
                if ($tot >= 10) {
                    $coursValides++;
                    $creditsValides += (int)($n['credit'] ?? 0);
                } else {
                    $coursAjournes++;
                }
            }

            $moyenneEtudiant = $totalCoursEvalues > 0 ? round($sommeNotes / $totalCoursEvalues, 1) : '0.0';

            $this->render('Dashboard.index', [
                'userRole'          => 'etudiant',
                'etudiant'          => $etudiant,
                'mesNotes'          => $mesNotes,
                'mesPaiements'      => $mesPaiements,
                'totalPaye'         => $totalPaye,
                'totalCoursEvalues' => $totalCoursEvalues,
                'coursValides'      => $coursValides,
                'coursAjournes'     => $coursAjournes,
                'creditsValides'    => $creditsValides,
                'moyenneEtudiant'   => $moyenneEtudiant,
            ]);
            return;
        }

        // ════════════════════════════════════════════════════════════
        // CAS 3 : TABLEAU DE BORD ADMINISTRATEUR & FINANCE
        // ════════════════════════════════════════════════════════════
        $stats = [
            'etudiants'   => (int) $db->query('SELECT COUNT(*) FROM etudiants')->fetchColumn(),
            'filieres'    => (int) $db->query('SELECT COUNT(*) FROM filieres')->fetchColumn(),
            'cours'       => (int) $db->query('SELECT COUNT(*) FROM cours')->fetchColumn(),
            'enseignants' => (int) $db->query('SELECT COUNT(*) FROM enseignants')->fetchColumn(),
        ];

        $statsNotes = $db->query("
            SELECT
                COUNT(*) as total,
                ROUND(AVG(total), 1) as moyenne,
                SUM(CASE WHEN total >= 10 THEN 1 ELSE 0 END) as reussis,
                SUM(CASE WHEN total < 10 THEN 1 ELSE 0 END) as ajournes
            FROM notes
        ")->fetch(PDO::FETCH_ASSOC);

        $statsPaiements = $db->query("
            SELECT
                COUNT(*) as total_trans,
                COALESCE(SUM(montant), 0) as total_montant
            FROM paiements
        ")->fetch(PDO::FETCH_ASSOC);

        $demandesEnAttente = (int) $db->query("
            SELECT COUNT(*) FROM demandes_modification_notes WHERE statut = 'en_attente'
        ")->fetchColumn();

        $inscriptionsParFiliere = $db->query("
            SELECT f.nom as filiere, COUNT(e.id) as count
            FROM filieres f
            LEFT JOIN orientations o ON o.filiere_id = f.id
            LEFT JOIN etudiants e ON e.orientation_id = o.id
            GROUP BY f.id, f.nom
            ORDER BY count DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $topCours = $db->query("
            SELECT c.nom, c.code, COUNT(n.id) as nb_notes, ROUND(AVG(n.total), 1) as moy
            FROM cours c
            LEFT JOIN notes n ON n.cours_id = c.id
            GROUP BY c.id, c.nom, c.code
            HAVING nb_notes > 0
            ORDER BY nb_notes DESC, c.nom ASC
            LIMIT 8
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($topCours)) {
            $topCours = $db->query("
                SELECT c.nom, c.code, 0 as nb_notes, 0 as moy
                FROM cours c
                ORDER BY c.nom ASC
                LIMIT 8
            ")->fetchAll(PDO::FETCH_ASSOC);
        }

        try {
            $activities = $db->query("
                (SELECT 'note' as type,
                    CONCAT('Note ajoutée — ', e.nom, ' ', e.prenom, ' (', c.nom, ')') as label,
                    n.created_at as date
                FROM notes n
                JOIN etudiants e ON e.id = n.etudiant_id
                JOIN cours c ON c.id = n.cours_id
                WHERE n.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY n.created_at DESC LIMIT 4)
                UNION ALL
                (SELECT 'paiement' as type,
                    CONCAT('Paiement enregistré — ', e.nom, ' ', e.prenom, ' (', FORMAT(p.montant,0), ' FC)') as label,
                    p.created_at as date
                FROM paiements p
                JOIN etudiants e ON e.id = p.etudiant_id
                WHERE p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY p.created_at DESC LIMIT 3)
                UNION ALL
                (SELECT 'etudiant' as type,
                    CONCAT('Nouvel étudiant inscrit — ', e.nom, ' ', e.prenom) as label,
                    e.created_at as date
                FROM etudiants e
                WHERE e.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY e.created_at DESC LIMIT 3)
                UNION ALL
                (SELECT 'releve' as type,
                    CONCAT('Relevé généré — ', e.nom, ' ', e.prenom, ' (', r.semestre, ')') as label,
                    r.created_at as date
                FROM releves r
                JOIN etudiants e ON e.id = r.etudiant_id
                WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY r.created_at DESC LIMIT 2)
                ORDER BY date DESC
                LIMIT 8
            ")->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $activities = [];
        }

        $this->render('Dashboard.index', [
            'userRole'               => $userRole,
            'stats'                  => $stats,
            'statsNotes'             => $statsNotes,
            'statsPaiements'         => $statsPaiements,
            'demandesEnAttente'      => $demandesEnAttente,
            'inscriptionsParFiliere' => $inscriptionsParFiliere,
            'topCours'               => $topCours,
            'activities'             => $activities,
        ]);
    }

    public function apiInscriptionsParFiliere() {
        header('Content-Type: application/json');
        try {
            $db = (new Database())->connect();
            $stmt = $db->query("
                SELECT f.nom as filiere, COUNT(e.id) as count
                FROM filieres f
                LEFT JOIN orientations o ON o.filiere_id = f.id
                LEFT JOIN etudiants e ON e.orientation_id = o.id
                GROUP BY f.id, f.nom
                ORDER BY f.nom ASC
            ");
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $maxCount = 0;
            foreach ($results as $row) {
                if ($row['count'] > $maxCount) $maxCount = $row['count'];
            }
            $data = [];
            foreach ($results as $row) {
                $height = $maxCount > 0 ? ($row['count'] / $maxCount) * 100 : 0;
                $data[] = [
                    'filiere' => mb_substr($row['filiere'], 0, 4),
                    'nom_complet' => $row['filiere'],
                    'count'   => (int)$row['count'],
                    'height'  => round($height, 2)
                ];
            }
            echo json_encode($data);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur DB: ' . $e->getMessage()]);
        }
    }
}
