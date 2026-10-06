<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Helpers/auth.php';
require_once __DIR__ . '/../Middleware/RoleMiddleware.php';
require_once __DIR__ . '/../../Repositories/ReleveRepository.php';
require_once __DIR__ . '/../../Repositories/EtudiantRepository.php';
require_once __DIR__ . '/../../Services/ReleveService.php';

class ReleveController extends Controller {
    private $repository;
    private $etudiantRepository;
    private $releveService;

    public function __construct() {
        $this->repository = new ReleveRepository();
        $this->etudiantRepository = new EtudiantRepository();
        $this->releveService = new ReleveService();
    }

    public function index() {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'etudiant']);
        
        $user = auth();
        if ($user['role'] === 'etudiant') {
            $etudiant = $this->etudiantRepository->findByEmail($user['email']);
            $releves = $etudiant ? $this->repository->getByEtudiantId($etudiant['id']) : [];
        } else {
            $releves = $this->repository->getAll();
        }
        
        $this->render('Releves.index', ['releves' => $releves, 'pageTitle' => 'Relevés']);
    }

    public function create() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $etudiants = $this->etudiantRepository->getAll();
        $this->render('Releves.create', ['etudiants' => $etudiants, 'pageTitle' => 'Ajouter un relevé']);
    }

    public function store() {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        
        // Validation stricte
        $etudiant_id = $_POST['etudiant_id'] ?? null;
        $semestre = $_POST['semestre'] ?? null;
        $moyenne = $_POST['moyenne'] ?? 0;
        $statut = $_POST['statut'] ?? 'Échoué';
        
        if (empty($etudiant_id) || empty($semestre)) {
            $_SESSION['error'] = 'Veuillez sélectionner un étudiant et un semestre';
            redirect('/releves/create');
            return;
        }
        
        // Vérifier que l'étudiant existe
        $etudiant = $this->etudiantRepository->findById($etudiant_id);
        if (!$etudiant) {
            $_SESSION['error'] = 'Étudiant introuvable';
            redirect('/releves/create');
            return;
        }
        
        $data = [
            'etudiant_id' => $etudiant_id,
            'semestre' => intval($semestre),
            'moyenne' => floatval($moyenne),
            'statut' => $statut,
        ];
        
        $this->repository->create($data);
        redirect('/releves');
    }

    public function edit($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $releve = $this->repository->findById($id);
        if (!$releve) {
            abort(404, 'Relevé introuvable');
        }
        $etudiants = $this->etudiantRepository->getAll();
        $this->render('Releves.edit', ['releve' => $releve, 'etudiants' => $etudiants, 'pageTitle' => 'Modifier un relevé']);
    }

    public function update($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $data = [
            'etudiant_id' => $_POST['etudiant_id'] ?? null,
            'semestre' => $_POST['semestre'] ?? null,
            'moyenne' => $_POST['moyenne'] ?? null,
            'statut' => $_POST['statut'] ?? null,
        ];
        $this->repository->update($id, $data);
        redirect('/releves');
    }

    public function delete($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin']);
        $this->repository->delete($id);
        redirect('/releves');
    }

    /**
     * Affiche la page de détail d'un relevé
     */
    public function show($id) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'etudiant']);
        $releve = $this->repository->findById($id);
        
        if (!$releve) {
            abort(404, 'Relevé introuvable');
        }

        // Récupérer l'étudiant complet
        $etudiant = $this->etudiantRepository->findById($releve['etudiant_id']);
        
        if (!$etudiant) {
            abort(404, 'Étudiant associé introuvable');
        }

        // Vérifier si le relevé est bloqué par la finance
        if (isset($etudiant['releve_bloque']) && $etudiant['releve_bloque'] == 1) {
            if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'etudiant') {
                abort(403, 'Accès refusé : Votre relevé de notes a été bloqué par la finance. Veuillez régulariser votre situation.');
            }
        }

        // On se concentre uniquement sur le semestre du relevé sélectionné
        $selectedSemestre = intval($releve['semestre']);
        $allNotes = [];
        
        $dataSem = $this->releveService->getEtudiantReleveData($releve['etudiant_id'], $selectedSemestre);
        if ($dataSem && !empty($dataSem['detailNotes'])) {
            $allNotes[$selectedSemestre] = $dataSem['detailNotes'];
        }

        // Si l'étudiant a des notes pour d'autres semestres, on peut décider de les afficher 
        // seulement si on veut un relevé de fin d'année (ex: sélection du Semestre 2)
        if ($selectedSemestre >= 2) {
            $semestresPrecedents = $this->releveService->getSemestresDisponibles($releve['etudiant_id']);
            foreach ($semestresPrecedents as $sem) {
                $sem = intval($sem);
                if ($sem < $selectedSemestre) {
                    $dataPrev = $this->releveService->getEtudiantReleveData($releve['etudiant_id'], $sem);
                    if ($dataPrev && !empty($dataPrev['detailNotes'])) {
                        $allNotes[$sem] = $dataPrev['detailNotes'];
                    }
                }
            }
        }

        // Si toujours vide, forcer au moins le tableau vide pour éviter les erreurs
        if (empty($allNotes)) {
            $allNotes[$releve['semestre']] = [];
        }
        
        // Si aucune note n'est trouvée du tout, on force l'entrée pour le semestre sélectionné 
        // afin que la boucle de génération HTML puisse afficher le message "Aucune note"
        if (empty($allNotes)) {
            $allNotes[$selectedSemestre] = [];
        }

        ksort($allNotes);

        // Récupérer les paramètres système
        $db = (new Database())->connect();
        $settings = $db->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);

        $templateFile = $settings['releve_template'] ?? null;
        $templatePath = APP_ROOT . '/public/assets/images/' . $templateFile;

        $sgaName = $settings['sga_name'] ?? '';
        $sgaInitials = '';
        if (!empty($sgaName)) {
            $cleanedName = preg_replace('/\b(dr|prof|ir|mr|mme|ms|phd|doc)\.?\b/i', '', $sgaName);
            $words = preg_split('/\s+/', trim($cleanedName));
            foreach ($words as $w) {
                if ($w !== '') {
                    $sgaInitials .= mb_strtoupper(mb_substr($w, 0, 1, 'UTF-8'), 'UTF-8');
                }
            }
        }
        $sgaPart = !empty($sgaInitials) ? $sgaInitials . '/' : '';
        $univName = $settings['university_name'] ?? 'SGAU-OPENLU';

        // Données pour l'affichage
        $academicYear = $settings['academic_year'] ?? (date('Y') - 1 . '-' . date('Y'));
        $nomComplet = htmlspecialchars($etudiant['nom'] . ' ' . ($etudiant['postnom'] ?? '') . ' ' . $etudiant['prenom']);
        $data = [
            'domaine' => htmlspecialchars($etudiant['domaine_nom'] ?? 'N/A'),
            'num_releve' => 'RELEVE DES COTES N°........../........../........../.........../..........',
            'filiere' => htmlspecialchars($etudiant['filiere_nom'] ?? 'N/A'),
            'promotion' => htmlspecialchars(($etudiant['niveau'] ?? 'L1') . ' ' . ($etudiant['orientation_nom'] ?? '')),
            'session' => 'ORDINAIRE - SEMESTRE ' . ($releve['semestre'] ?? '1'),
            'annee_academique' => htmlspecialchars($academicYear),
            'semestre' => htmlspecialchars($releve['semestre'] ?? '1'),
            'nom_etudiant' => $nomComplet,
            'matricule' => htmlspecialchars($etudiant['matricule'] ?? 'N/A'),
            'lieu_date_naissance' => htmlspecialchars(($etudiant['lieu_naissance'] ?? '-') . ', le ' . (!empty($etudiant['date_naissance']) ? date('d/m/Y', strtotime($etudiant['date_naissance'])) : '-')),
            'lieu_date_jour' => htmlspecialchars(($settings['university_city'] ?? 'Beni') . ', le ' . date('d/m/Y')),
            'sec_gen_acad' => htmlspecialchars(!empty($settings['sga_name']) ? $settings['sga_name'] : '......................................................'),
            'doyen' => htmlspecialchars(!empty($etudiant['doyen_nom']) ? $etudiant['doyen_nom'] : '......................................................'),
            'notes' => $allNotes,
            'backgroundImage' => ($templateFile ? base_url('/assets/images/' . $templateFile) : null)
        ];

        // Utilisation exclusive du fichier template physique (Source of Truth)
        $templatePath = APP_ROOT . '/app/Views/Releves/template.html';
        $htmlTemplate = '';
        
        if (file_exists($templatePath)) {
            $htmlTemplate = file_get_contents($templatePath);
        } else {
            die("Erreur critique : Le fichier de modèle (template.html) est introuvable dans " . $templatePath);
        }

        if (!empty($htmlTemplate)) {
            // Préparer le tableau des notes en HTML
            $tableauNotesHtml = '<table class="transcript-table"><thead><tr><th width="10%">CODE</th><th width="38%">INTITULE</th><th width="6%">Cr</th><th width="14%">VOLUME HORAIRE</th><th width="10%">COTE/20</th><th width="10%">JURY</th><th width="12%">MENTION</th></tr></thead><tbody>';
            
            $grandTotalCredits = 0;
            $grandTotalPoints = 0;
            
            foreach ($allNotes as $semNum => $semNotes) {
                if (empty($semNotes)) {
                    $tableauNotesHtml .= '<tr><td colspan="7" style="text-align: center; color: #666; font-style: italic; padding: 10px;">Aucune note enregistrée pour ce semestre</td></tr>';
                    continue;
                }
                
                $tableauNotesHtml .= '<tr><td colspan="7" class="sub-header uppercase">SEMESTRE ' . $semNum . '</td></tr>';
                
                $semCredits = 0;
                $semPoints = 0;
                foreach ($semNotes as $note) {
                    $cr = floatval($note['credit'] ?: 0);
                    $cote = floatval($note['note'] ?: 0);
                    $pondere = $cr * $cote;
                    $vh = (isset($note['volume_horaire']) && is_numeric($note['volume_horaire'])) ? $note['volume_horaire'] : ($cr > 0 ? ($cr * 25) : '-');
                    
                    $semCredits += $cr;
                    $semPoints += $pondere;
                    $grandTotalCredits += $cr;
                    $grandTotalPoints += $pondere;
                    
                    $tableauNotesHtml .= '<tr>';
                    $tableauNotesHtml .= '<td style="text-align: center;">' . htmlspecialchars($note['cours_code']) . '</td>';
                    $tableauNotesHtml .= '<td>' . htmlspecialchars($note['cours_nom']) . '</td>';
                    $tableauNotesHtml .= '<td style="text-align: center;">' . $cr . '</td>';
                    $tableauNotesHtml .= '<td style="text-align: center;">' . $vh . '</td>';
                    $tableauNotesHtml .= '<td style="text-align: center;">' . number_format($cote, 1) . '</td>';
                    $tableauNotesHtml .= '<td style="text-align: center;" class="' . ($cote >= 10 ? 'note-v' : 'note-nv') . '">' . ($cote >= 10 ? 'Validé' : 'Non Validé') . '</td>';
                    $tableauNotesHtml .= '<td style="text-align: center;">' . $this->getMentionLettre($cote) . '</td>';
                    $tableauNotesHtml .= '</tr>';
                }
                
                // Résumé du semestre
                $tableauNotesHtml .= '<tr class="result-row"><td></td><td class="text-left pl-8 italic">Moyenne</td><td colspan="5" class="text-center">' . ($semCredits > 0 ? number_format($semPoints / $semCredits, 2) : '0.00') . ' / 20</td></tr>';
                $tableauNotesHtml .= '<tr class="result-row"><td></td><td class="text-left pl-8 italic">Total Points Obtenus</td><td colspan="5" class="text-center">' . number_format($semPoints, 1) . '</td></tr>';
                $tableauNotesHtml .= '<tr class="result-row"><td></td><td class="text-left pl-8 italic">Crédits Capitalisés</td><td colspan="5" class="text-center">' . $semCredits . '</td></tr>';
            }
            
            // Calculs finaux
            $moyenneAnnuelle = $grandTotalCredits > 0 ? number_format($grandTotalPoints / $grandTotalCredits, 2) : '0.00';
            $pourcentage = $grandTotalCredits > 0 ? number_format(($grandTotalPoints / ($grandTotalCredits * 20)) * 100, 1) : '0.0';
            $decisionJury = ($grandTotalCredits > 0 && ($grandTotalPoints / $grandTotalCredits) >= 10) ? 'Réussi' : 'Ajourné';

            // Section Résultat Annuel - Affichée UNIQUEMENT pour le Semestre 2 ou plus
            if ($selectedSemestre >= 2) {
                $tableauNotesHtml .= '<tr><td colspan="7" class="green-separator"></td></tr>';
                $tableauNotesHtml .= '<tr class="annuel-header"><td></td><td class="text-left pl-2 uppercase">Resultat Annuel</td><td colspan="5"></td></tr>';
                $tableauNotesHtml .= '<tr class="result-row"><td></td><td class="text-left pl-4">Moyenne Annuelle</td><td colspan="5" class="text-center font-black">' . $moyenneAnnuelle . ' / 20</td></tr>';
                $tableauNotesHtml .= '<tr class="result-row"><td></td><td class="text-left pl-4">Total Points Obtenus</td><td colspan="5" class="text-center font-black">' . number_format($grandTotalPoints, 1) . '</td></tr>';
                $tableauNotesHtml .= '<tr class="result-row"><td></td><td class="text-left pl-4">Pourcentages</td><td colspan="5" class="text-center font-black">' . $pourcentage . ' %</td></tr>';
                $tableauNotesHtml .= '<tr class="result-row"><td></td><td class="text-left pl-4">Nombre des Crédits Capitalisés S1+S2</td><td colspan="5" class="text-center font-black">' . $grandTotalCredits . '</td></tr>';
                $tableauNotesHtml .= '<tr class="result-row"><td></td><td class="text-left pl-4 uppercase font-black">Décision du jury</td><td colspan="5" class="text-center font-black">' . $decisionJury . '</td></tr>';
            }

            $tableauNotesHtml .= '</tbody></table>';

            $replacements = [
                '{{domaine}}' => $data['domaine'],
                '{{num_releve}}' => $data['num_releve'],
                '{{filiere}}' => $data['filiere'],
                '{{promotion}}' => $data['promotion'],
                '{{session}}' => $data['session'],
                '{{annee_academique}}' => $data['annee_academique'],
                '{{semestre}}' => $data['semestre'],
                '{{nom_etudiant}}' => $data['nom_etudiant'],
                '{{matricule}}' => $data['matricule'],
                '{{lieu_date_naissance}}' => $data['lieu_date_naissance'],
                '{{tableau_notes}}' => $tableauNotesHtml,
                '{{lieu_date_jour}}' => $data['lieu_date_jour'],
                '{{sec_gen_acad}}' => $data['sec_gen_acad'],
                '{{doyen}}' => $data['doyen'],
                '{{moyenne_annuelle}}' => $moyenneAnnuelle,
                '{{total_points}}' => number_format($grandTotalPoints, 1),
                '{{pourcentage}}' => $pourcentage,
                '{{credits_total}}' => $grandTotalCredits,
                '{{decision_jury}}' => $decisionJury,
                '{{university_email}}' => htmlspecialchars($settings['university_email'] ?? 'info@openlu.org'),
                '{{university_phone}}' => htmlspecialchars(!empty($settings['university_phone']) ? $settings['university_phone'] : '+243 828 016 729, +66 959 100 486'),
                '{{university_website}}' => htmlspecialchars($settings['university_website'] ?? 'http://www.openlu.org'),
                '{{university_website_display}}' => htmlspecialchars(preg_replace('#^https?://#', '', $settings['university_website'] ?? 'www.openlu.org')),
                '{{university_bp}}' => htmlspecialchars($settings['university_bp'] ?? '218 BENI'),
                '{{university_logo}}' => !empty($settings['university_logo']) ? base_url('/assets/images/' . $settings['university_logo']) : base_url('/assets/images/openlu v1.jpg')
            ];

            $renderedHtml = strtr($htmlTemplate, $replacements);

            // Affichage final
            ?>
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <title>Relevé de Notes - <?= $data['nom_etudiant'] ?></title>
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
                    body { font-family: 'Inter', sans-serif; margin: 0; padding: 0; background-color: #e2e8f0; }
                    .action-bar { position: fixed; top: 20px; right: 20px; z-index: 1000; display: flex; gap: 10px; flex-wrap: wrap; }
                    .btn { padding: 10px 20px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; font-size: 14px; }
                    .btn-print { background: #1e40af; color: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15); }
                    .btn-print:hover { background: #1e3a8a; transform: translateY(-1px); }
                    .btn-pdf { background: #059669; color: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.15); }
                    .btn-pdf:hover { background: #047857; transform: translateY(-1px); }
                    .btn-back { background: white; color: #475569; border: 1px solid #e2e8f0; }
                    .btn-back:hover { background: #f8fafc; }
                    .btn-pdf.loading { opacity: 0.7; cursor: wait; }
                    @media print { .action-bar { display: none; } body { background: white; } }
                </style>
            </head>
            <body>
                <div class="action-bar">
                    <button onclick="downloadPDF(this)" class="btn btn-pdf">
                        <i class="fas fa-file-pdf"></i> Télécharger PDF
                    </button>
                    <button onclick="window.print()" class="btn btn-print">
                        <i class="fas fa-print"></i> Imprimer
                    </button>
                    <a href="<?= base_url('/releves') ?>" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                <?= $renderedHtml ?>
                <script>
                function downloadPDF(btn) {
                    btn.classList.add('loading');
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Génération...';

                    const element = document.querySelector('.a4-page');
                    const nomEtudiant = '<?= addslashes(preg_replace('/\s+/', '-', $data['nom_etudiant'])) ?>';

                    html2canvas(element, {
                        scale: 2,
                        useCORS: true,
                        allowTaint: true,
                        logging: false,
                        backgroundColor: '#ffffff'
                    }).then(function(canvas) {
                        const { jsPDF } = window.jspdf;
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'mm',
                            format: 'a4'
                        });

                        // Force toujours UNE SEULE page A4 (210x297mm)
                        const imgData = canvas.toDataURL('image/jpeg', 0.98);
                        pdf.addImage(imgData, 'JPEG', 0, 0, 210, 297);
                        pdf.save('Releve-' + nomEtudiant + '.pdf');

                        btn.classList.remove('loading');
                        btn.innerHTML = '<i class="fas fa-file-pdf"></i> Télécharger PDF';
                    }).catch(function(err) {
                        console.error(err);
                        btn.classList.remove('loading');
                        btn.innerHTML = '<i class="fas fa-file-pdf"></i> Télécharger PDF';
                        alert('Erreur lors de la génération du PDF. Utilisez Imprimer > Enregistrer en PDF.');
                    });
                }
                </script>
            </body>
            </html>
            <?php
            return;
        }

        // Sinon, on continue avec l'ancien système (superposition sur image)
        if ($templateFile && file_exists($templatePath)) {
            ?>
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <title>Relevé de Notes - <?= $data['nom_etudiant'] ?></title>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
                    
                    body { margin: 0; padding: 0; font-family: 'Roboto', sans-serif; background: #f0f2f5; }
                    .print-container { 
                        width: 210mm; 
                        height: 297mm; 
                        margin: 20px auto; 
                        background: white; 
                        position: relative; 
                        box-sizing: border-box; 
                        overflow: hidden;
                    }
                    .template-bg { 
                        position: absolute; 
                        top: 0; 
                        left: 0; 
                        width: 100%; 
                        height: 100%; 
                        z-index: 1; 
                    }
                    .overlay-content { 
                        position: relative; 
                        z-index: 2; 
                        width: 100%; 
                        height: 100%; 
                        padding: 0;
                        box-sizing: border-box; 
                    }
                    
                    /* Positionnement précis basé sur le modèle */
                    .domaine-text { 
                        position: absolute; 
                        top: 182px; 
                        width: 100%; 
                        text-align: center; 
                        font-weight: bold; 
                        color: #2e3192; 
                        font-size: 14pt;
                        text-transform: uppercase;
                    }
                    
                    .releve-num { 
                        position: absolute; 
                        top: 236px; 
                        width: 100%; 
                        text-align: center; 
                        font-weight: bold; 
                        font-size: 13pt;
                    }

                    .student-info-block {
                        position: absolute;
                        top: 270px;
                        left: 75px;
                        font-size: 11pt;
                        line-height: 1.4;
                    }

                    .student-name-line {
                        position: absolute;
                        top: 325px;
                        left: 75px;
                        font-size: 11pt;
                        font-weight: bold;
                    }

                    .table-container { 
                        position: absolute;
                        top: 350px;
                        left: 75px;
                        right: 75px;
                    }
                    
                    table { 
                        width: 100%; 
                        border-collapse: collapse; 
                    }
                    th, td { 
                        border: 1px solid #000; 
                        padding: 2px 5px; 
                        text-align: left; 
                        font-size: 9pt; 
                        height: 20px;
                    }
                    th { text-align: center; font-weight: bold; }
                    
                    .semester-header {
                        background-color: #f2f2f2;
                        font-weight: bold;
                        text-transform: uppercase;
                    }

                    .signatures-block { 
                        position: absolute;
                        bottom: 120px;
                        width: 100%;
                    }
                    
                    .sig-dean {
                        position: absolute;
                        left: 75px;
                        text-align: center;
                        width: 250px;
                    }

                    .sig-sga {
                        position: absolute;
                        right: 75px;
                        text-align: center;
                        width: 300px;
                    }

                    .date-line {
                        position: absolute;
                        right: 75px;
                        bottom: 180px;
                        font-size: 11pt;
                    }

                    .action-bar { position: fixed; top: 20px; right: 20px; z-index: 1000; }
                    .btn { padding: 10px 20px; border-radius: 5px; border: none; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; }
                    .btn-print { background: #28a745; color: white; }
                    .btn-back { background: #007bff; color: white; margin-left: 10px; }

                    @media print {
                        .action-bar { display: none; }
                        body { background: white; padding: 0; margin: 0; }
                        .print-container { margin: 0; width: 210mm; height: 297mm; }
                    }
                </style>
            </head>
            <body>
                <div class="action-bar">
                    <button onclick="window.print()" class="btn btn-print">Imprimer / PDF</button>
                    <a href="<?= base_url('/releves') ?>" class="btn btn-back">Retour</a>
                </div>

                <div class="print-container">
                    <img src="<?= $data['backgroundImage'] ?>" class="template-bg" alt="Modèle">
                    
                    <div class="overlay-content">
                        <div class="domaine-text">DOMAINE : <?= $data['domaine'] ?></div>
                        
                        <div class="releve-num">
                            <?= $data['num_releve'] ?>
                        </div>

                        <div class="student-name-line">
                            NOM POST-NOM PRENOM : <?= $data['nom_etudiant'] ?> &nbsp;&nbsp;&nbsp; 
                            MATRICULE : <?= $data['matricule'] ?> &nbsp;&nbsp;&nbsp; 
                            Né(e) à : <?= $data['lieu_date_naissance'] ?>
                        </div>

                        <div class="student-info-block">
                            <div>DOMAINE : <?= $data['domaine'] ?></div>
                            <div>FILIÈRE : <?= $data['filiere'] ?></div>
                            <div>PROMOTION : <?= $data['promotion'] ?></div>
                            <div>ANNÉE ACADÉMIQUE : <?= $data['annee_academique'] ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; SEMESTRE : <?= $data['semestre'] ?></div>
                        </div>

                        <div class="table-container">
                            <table>
                                <thead>
                                    <tr>
                                        <th style="width: 10%;">CODE</th>
                                        <th style="width: 38%;">INTITULE</th>
                                        <th style="width: 6%;">Cr</th>
                                        <th style="width: 14%;">VOLUME HORAIRE</th>
                                        <th style="width: 10%;">COTE/20</th>
                                        <th style="width: 10%;">JURY</th>
                                        <th style="width: 12%;">MENTION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="semester-header">
                                        <td colspan="7">SEMESTRE <?= $releve['semestre'] ?></td>
                                    </tr>
                                    <?php 
                                    $totalCredits = 0;
                                    $totalNotes = 0;
                                    $count = 0;
                                    foreach ($data['notes'] as $note): 
                                        $cr = floatval($note['credit'] ?: 0);
                                        $totalCredits += $cr;
                                        $totalNotes += $note['note'];
                                        $vh = (isset($note['volume_horaire']) && is_numeric($note['volume_horaire'])) ? $note['volume_horaire'] : ($cr > 0 ? ($cr * 25) : '-');
                                        $count++;
                                    ?>
                                    <tr>
                                        <td style="text-align: center;"><?= htmlspecialchars($note['cours_code']) ?></td>
                                        <td><?= htmlspecialchars($note['cours_nom']) ?></td>
                                        <td style="text-align: center;"><?= $cr ?></td>
                                        <td style="text-align: center;"><?= $vh ?></td>
                                        <td style="text-align: center;"><?= number_format($note['note'], 1) ?></td>
                                        <td style="text-align: center;"><?= $note['note'] >= 10 ? 'Validé' : 'Non Validé' ?></td>
                                        <td style="text-align: center;"><?= $this->getMentionLettre($note['note']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php 
                                    // Remplir les lignes vides pour atteindre un certain nombre
                                    $emptyLines = 10 - $count;
                                    for($i = 0; $i < $emptyLines; $i++): ?>
                                    <tr>
                                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    </tr>
                                    <?php endfor; ?>

                                    <tr style="font-weight: bold;">
                                        <td colspan="2" style="text-align: right; padding-right: 20px;">Moyenne</td>
                                        <td colspan="5" style="text-align: center;"><?= $count > 0 ? number_format($totalNotes / $count, 2) : '0.00' ?> / 20</td>
                                    </tr>
                                    <tr style="font-weight: bold;">
                                        <td colspan="2" style="text-align: right; padding-right: 20px;">Total Points Obtenus</td>
                                        <td colspan="5" style="text-align: center;"><?= number_format($totalNotes, 1) ?></td>
                                    </tr>
                                    <tr style="font-weight: bold;">
                                        <td colspan="2" style="text-align: right; padding-right: 20px;">Crédits Capitalisés</td>
                                        <td colspan="5" style="text-align: center;"><?= $totalCredits ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="date-line">
                            <?= $data['lieu_date_jour'] ?>
                        </div>

                        <div class="signatures-block">
                            <div class="sig-sga" style="right: 75px; text-align: center; width: 300px;">
                                <div style="font-weight: bold; margin-bottom: 70px;">Le Secrétaire Général Académique</div>
                                <div style="border-top: 1px dotted #000; width: 80%; margin: 0 auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
            return;
        }

        // Préparer les données pour la vue par défaut
        $viewData = [
            'releve' => $releve,
            'etudiant' => $etudiant,
            'allNotes' => $allNotes,
            'settings' => $settings,
            'filiereNom' => $etudiant['filiere_nom'] ?? 'N/A',
            'pageTitle' => 'Relevé de ' . htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom'], ENT_QUOTES, 'UTF-8')
        ];

        $this->render('Releves.show', $viewData);
    }

    private function getMentionLettre($cote) {
        if ($cote >= 18) return 'A';
        if ($cote >= 16) return 'B';
        if ($cote >= 14) return 'C';
        if ($cote >= 12) return 'D';
        if ($cote >= 10) return 'E';
        if ($cote >= 8)  return 'F';
        return 'G';
    }

    /**
     * API: Récupère les données de relevé pour un étudiant et un semestre
     */
    public function apiGetEtudiantReleve($etudiantId) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'etudiant']);
        header('Content-Type: application/json');
        
        try {
            $semestre = $_GET['semestre'] ?? 1;
            
            // Vérifier le blocage
            $etudiant = $this->etudiantRepository->findById($etudiantId);
            if ($etudiant && isset($etudiant['releve_bloque']) && $etudiant['releve_bloque'] == 1) {
                if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'etudiant') {
                    http_response_code(403);
                    echo json_encode(['error' => 'Accès refusé : Votre relevé de notes a été bloqué par la finance.']);
                    exit;
                }
            }

            $releveData = $this->releveService->getEtudiantReleveData($etudiantId, $semestre);

            if (!$releveData) {
                http_response_code(404);
                echo json_encode(['error' => 'Étudiant ou données non trouvés']);
                exit;
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'etudiant' => $releveData['etudiant'],
                    'semestre' => $releveData['semestre'],
                    'moyenne' => $releveData['moyenne'],
                    'statut' => $releveData['statut'],
                    'nombreCours' => $releveData['nombreCours'],
                    'notes' => $releveData['detailNotes']
                ]
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * API: Récupère les semestres disponibles pour un étudiant
     */
    public function apiGetSemestres($etudiantId) {
        require_auth();
        RoleMiddleware::requireRole(['admin', 'etudiant']);
        header('Content-Type: application/json');
        
        try {
            $semestres = $this->releveService->getSemestresDisponibles($etudiantId);

            echo json_encode([
                'success' => true,
                'semestres' => $semestres
            ]);
            exit;
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur: ' . $e->getMessage()]);
            exit;
        }
    }
}
