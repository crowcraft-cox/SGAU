<?php
/**
 * Tableau de Bord Multi-Rôles — SGAU (Système de Gestion Académique Universitaire)
 * Rendu spécifique et contextualisé selon le rôle : Enseignant, Étudiant ou Administrateur.
 * Visualisation de données Chart.js haute précision (/future-dev /ui-ux-pro-max)
 */

$currentUser = $_SESSION['user'] ?? [];
$userName    = htmlspecialchars($currentUser['name'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8');
$userRole    = $userRole ?? ($currentUser['role'] ?? 'etudiant');

$jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
$mois  = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
$dateCourante = $jours[(int)date('w')] . ' ' . date('d') . ' ' . $mois[(int)date('n')] . ' ' . date('Y');
?>

<div class="erp-dashboard">

    <?php if ($userRole === 'enseignant'): ?>
    <!-- ════════════════════════════════════════════════════════════
         TABLEAU DE BORD : ENSEIGNANT (CENTRÉ SUR SES COURS)
    ════════════════════════════════════════════════════════════ -->
    <?php
        $ensNomDept   = !empty($enseignant['departement']) ? htmlspecialchars($enseignant['departement'], ENT_QUOTES, 'UTF-8') : 'Département académique';
        $ensMatricule = !empty($enseignant['matricule']) ? htmlspecialchars($enseignant['matricule'], ENT_QUOTES, 'UTF-8') : '—';
        $mesCours     = $mesCours ?? [];

        // Préparation données graphiques enseignant
        $chartNotesParCours = [];
        $chartMoyParCours   = [];
        foreach ($mesCours as $c) {
            $nomCourt = mb_strlen($c['nom']) > 18 ? mb_substr($c['nom'], 0, 16) . '…' : $c['nom'];
            $chartNotesParCours[] = [
                'nom'      => $nomCourt,
                'nomComplet'=> $c['nom'],
                'code'     => $c['code'] ?: '—',
                'nb_notes' => (int)$c['nb_notes']
            ];
            $chartMoyParCours[] = [
                'nom'      => $nomCourt,
                'nomComplet'=> $c['nom'],
                'code'     => $c['code'] ?: '—',
                'moyenne'  => (float)($c['moyenne'] ?? 0)
            ];
        }
    ?>

    <!-- En-tête Enseignant -->
    <div class="erp-page-header">
        <div class="erp-header-meta">
            <h1 class="erp-page-title">Espace Pédagogique Enseignant</h1>
            <p class="erp-page-subtitle">
                <span>Professeur : <?= $userName ?></span>
                <span class="erp-sep">•</span>
                <span>Matricule : <?= $ensMatricule ?></span>
                <span class="erp-sep">•</span>
                <span><?= $ensNomDept ?></span>
                <span class="erp-sep">•</span>
                <span><?= $dateCourante ?></span>
            </p>
        </div>
        <div class="erp-header-actions">
            <button type="button" class="btn btn-secondary" onclick="location.reload();">
                <i class="fa-solid fa-rotate-right"></i> Actualiser
            </button>
            <a href="<?= base_url('/notes') ?>" class="btn btn-primary">
                <i class="fa-solid fa-file-pen"></i> Saisie des notes
            </a>
        </div>
    </div>

    <!-- 4 KPIs Enseignant -->
    <div class="erp-kpi-grid">
        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Mes Cours Assignés</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-book-open"></i></div>
            </div>
            <div class="erp-kpi-number"><?= (int)$totalCoursAssigne ?></div>
            <div class="erp-kpi-footer">
                <span class="erp-kpi-info"><?= (int)$totalCreditsAssigne ?> crédits au total</span>
            </div>
        </div>

        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Évaluations Saisies</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-file-lines"></i></div>
            </div>
            <div class="erp-kpi-number"><?= (int)$totalNotesSaisies ?></div>
            <div class="erp-kpi-footer">
                <a href="<?= base_url('/notes') ?>" class="erp-kpi-link">Voir toutes les notes &rarr;</a>
            </div>
        </div>

        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Moyenne de mes Cours</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-chart-line"></i></div>
            </div>
            <div class="erp-kpi-number"><?= $moyenneEnseignant ?></div>
            <div class="erp-kpi-footer">
                <span class="erp-kpi-info">Sur l'ensemble de mes cours</span>
            </div>
        </div>

        <div class="erp-kpi-card <?= $demandesEnAttenteCount > 0 ? 'highlight-alert' : '' ?>">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Mes Demandes SGA</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-file-signature"></i></div>
            </div>
            <div class="erp-kpi-number"><?= $demandesEnAttenteCount ?></div>
            <div class="erp-kpi-footer">
                <a href="<?= base_url('/demandes-modification-notes') ?>" class="erp-kpi-link">
                    <?= count($mesDemandes) ?> demande(s) enregistrée(s) &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- 2 Graphiques Spécifiques Enseignant -->
    <div class="erp-charts-grid">
        <!-- Graphique 1 : Nombre de notes par cours de l'enseignant -->
        <div class="erp-card erp-chart-card">
            <div class="erp-card-header">
                <div>
                    <h3 class="erp-card-title">
                        <i class="fa-solid fa-chart-column erp-chart-title-icon"></i>
                        Volume d'évaluations par cours
                    </h3>
                    <p class="erp-card-caption">Nombre d'étudiants ayant une note enregistrée dans vos cours</p>
                </div>
                <span class="erp-badge-info"><?= count($mesCours) ?> cours sous votre charge</span>
            </div>
            <div class="erp-card-body">
                <div class="erp-chart-wrapper" style="position: relative; height: 280px;">
                    <canvas id="chartEnseignantEvaluations"></canvas>
                </div>
            </div>
        </div>

        <!-- Graphique 2 : Moyenne obtenue par cours de l'enseignant -->
        <div class="erp-card erp-chart-card">
            <div class="erp-card-header">
                <div>
                    <h3 class="erp-card-title">
                        <i class="fa-solid fa-chart-bar erp-chart-title-icon"></i>
                        Moyenne académique de mes cours
                    </h3>
                    <p class="erp-card-caption">Note moyenne calculée sur les cotes saisies par matière</p>
                </div>
                <a href="<?= base_url('/notes') ?>" class="erp-card-action">Saisir de nouvelles notes &rarr;</a>
            </div>
            <div class="erp-card-body">
                <div class="erp-chart-wrapper" style="position: relative; height: 280px;">
                    <canvas id="chartEnseignantMoyennes"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau détaillé des cours de l'enseignant & Journal -->
    <div class="erp-layout-grid" style="margin-top: 20px;">
        <!-- Mes cours assignés -->
        <div class="erp-col-left">
            <div class="erp-card">
                <div class="erp-card-header">
                    <div>
                        <h3 class="erp-card-title">Détail de mes cours et évaluations</h3>
                        <p class="erp-card-caption">Unités d'enseignement sous votre responsabilité pédagogique</p>
                    </div>
                </div>
                <div class="erp-card-body no-padding">
                    <?php if (empty($mesCours)): ?>
                        <div class="erp-empty-msg">Aucun cours n'est actuellement assigné à votre compte.</div>
                    <?php else: ?>
                        <table class="erp-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Intitulé du cours</th>
                                    <th>Crédits</th>
                                    <th class="text-right">Notes</th>
                                    <th class="text-right">Moyenne</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mesCours as $c): ?>
                                    <tr>
                                        <td class="font-mono"><?= htmlspecialchars($c['code'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="font-medium"><?= htmlspecialchars($c['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int)$c['credit'] ?></td>
                                        <td class="text-right"><?= (int)$c['nb_notes'] ?></td>
                                        <td class="text-right font-bold"><?= $c['moyenne'] !== null ? number_format((float)$c['moyenne'], 1) : '—' ?></td>
                                        <td class="text-right">
                                            <a href="<?= base_url('/notes') ?>" class="erp-btn-sm">
                                                <i class="fa-solid fa-pen-to-square"></i> Cotes
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Dernières notes saisies & dérogations -->
        <div class="erp-col-right">
            <div class="erp-card">
                <div class="erp-card-header">
                    <div>
                        <h3 class="erp-card-title">Dernières notes enregistrées</h3>
                        <p class="erp-card-caption">Activités récentes dans vos cours</p>
                    </div>
                </div>
                <div class="erp-card-body no-padding">
                    <?php if (empty($dernieresNotes)): ?>
                        <div class="erp-empty-msg">Aucune note enregistrée récemment.</div>
                    <?php else: ?>
                        <ul class="erp-activity-list">
                            <?php foreach ($dernieresNotes as $n): ?>
                                <li class="erp-activity-row">
                                    <span class="erp-act-tag badge-note"><?= htmlspecialchars($n['cours_code'] ?: 'COURS', ENT_QUOTES, 'UTF-8') ?></span>
                                    <div class="erp-act-detail">
                                        <p class="erp-act-text">
                                            <strong><?= htmlspecialchars($n['etudiant_nom'] . ' ' . $n['etudiant_prenom'], ENT_QUOTES, 'UTF-8') ?></strong>
                                            — Note totale : <strong><?= number_format((float)$n['total'], 1) ?></strong>
                                        </p>
                                        <span class="erp-act-date"><?= htmlspecialchars($n['cours_nom'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>


    <?php elseif ($userRole === 'etudiant'): ?>
    <!-- ════════════════════════════════════════════════════════════
         TABLEAU DE BORD : ÉTUDIANT (CENTRÉ SUR SES NOTES & RELEVÉ)
    ════════════════════════════════════════════════════════════ -->
    <?php
        $etudiantMatricule = !empty($etudiant['matricule']) ? htmlspecialchars($etudiant['matricule'], ENT_QUOTES, 'UTF-8') : '—';
        $etudiantFiliere   = !empty($etudiant['filiere_nom']) ? htmlspecialchars($etudiant['filiere_nom'], ENT_QUOTES, 'UTF-8') : 'Filière académique';
        $etudiantNiveau    = !empty($etudiant['niveau']) ? htmlspecialchars($etudiant['niveau'], ENT_QUOTES, 'UTF-8') : 'L1';
        $etudiantReleveBloque = !empty($etudiant['releve_bloque']);
        $mesNotes          = $mesNotes ?? [];

        // Préparation données graphiques étudiant
        $chartEtudiantNotes = [];
        foreach ($mesNotes as $n) {
            $nomCourt = mb_strlen($n['cours_nom']) > 16 ? mb_substr($n['cours_nom'], 0, 14) . '…' : $n['cours_nom'];
            $chartEtudiantNotes[] = [
                'cours'     => $nomCourt,
                'nomComplet'=> $n['cours_nom'],
                'code'      => $n['cours_code'] ?: '—',
                'note'      => (float)$n['total'],
                'examen'    => (float)($n['examen'] ?? 0),
                'tp'        => (float)($n['tp'] ?? 0)
            ];
        }
    ?>

    <!-- En-tête Étudiant -->
    <div class="erp-page-header">
        <div class="erp-header-meta">
            <h1 class="erp-page-title">Portail Académique Étudiant</h1>
            <p class="erp-page-subtitle">
                <span>Étudiant : <?= $userName ?></span>
                <span class="erp-sep">•</span>
                <span>Matricule : <?= $etudiantMatricule ?></span>
                <span class="erp-sep">•</span>
                <span><?= $etudiantFiliere ?> (<?= $etudiantNiveau ?>)</span>
                <span class="erp-sep">•</span>
                <span><?= $dateCourante ?></span>
            </p>
        </div>
        <div class="erp-header-actions">
            <a href="<?= base_url('/releves') ?>" class="btn btn-primary">
                <i class="fa-solid fa-file-invoice"></i> Mon relevé de cotes
            </a>
            <a href="<?= base_url('/dossiers-etudiants') ?>" class="btn btn-secondary">
                <i class="fa-solid fa-folder-open"></i> Mon dossier
            </a>
        </div>
    </div>

    <!-- 4 KPIs Étudiant -->
    <div class="erp-kpi-grid">
        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Cours Évalués</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-book-bookmark"></i></div>
            </div>
            <div class="erp-kpi-number"><?= (int)$totalCoursEvalues ?></div>
            <div class="erp-kpi-footer">
                <span class="erp-kpi-info"><?= (int)$coursValides ?> cours validés</span>
            </div>
        </div>

        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Moyenne Générale</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-graduation-cap"></i></div>
            </div>
            <div class="erp-kpi-number"><?= $moyenneEtudiant ?></div>
            <div class="erp-kpi-footer">
                <span class="erp-kpi-info"><?= (float)$moyenneEtudiant >= 10 ? 'Résultat admissible' : 'En dessous du seuil' ?></span>
            </div>
        </div>

        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Crédits Validés</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-certificate"></i></div>
            </div>
            <div class="erp-kpi-number"><?= (int)$creditsValides ?></div>
            <div class="erp-kpi-footer">
                <span class="erp-kpi-info">Crédits académiques acquis</span>
            </div>
        </div>

        <div class="erp-kpi-card <?= $etudiantReleveBloque ? 'highlight-alert' : '' ?>">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Statut du Relevé</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-file-invoice"></i></div>
            </div>
            <div class="erp-kpi-number" style="font-size: 20px; margin-top: 4px;">
                <?= $etudiantReleveBloque ? 'Bloqué (Finance)' : 'Débloqué & Accessible' ?>
            </div>
            <div class="erp-kpi-footer">
                <span class="erp-kpi-info"><?= number_format($totalPaye, 0, ',', ' ') ?> FC payés</span>
            </div>
        </div>
    </div>

    <!-- 2 Graphiques Spécifiques Étudiant -->
    <div class="erp-charts-grid">
        <!-- Graphique 1 : Mes résultats par cours -->
        <div class="erp-card erp-chart-card">
            <div class="erp-card-header">
                <div>
                    <h3 class="erp-card-title">
                        <i class="fa-solid fa-chart-column erp-chart-title-icon"></i>
                        Mes notes par cours
                    </h3>
                    <p class="erp-card-caption">Note totale obtenue dans chaque unité d'enseignement</p>
                </div>
                <span class="erp-badge-info"><?= count($mesNotes) ?> cours notés</span>
            </div>
            <div class="erp-card-body">
                <div class="erp-chart-wrapper" style="position: relative; height: 280px;">
                    <canvas id="chartEtudiantNotes"></canvas>
                </div>
            </div>
        </div>

        <!-- Graphique 2 : Bilan de validation des matières -->
        <div class="erp-card erp-chart-card">
            <div class="erp-card-header">
                <div>
                    <h3 class="erp-card-title">
                        <i class="fa-solid fa-chart-pie erp-chart-title-icon"></i>
                        Bilan de validation des matières
                    </h3>
                    <p class="erp-card-caption">Proportion des cours validés par rapport aux ajournements</p>
                </div>
                <a href="<?= base_url('/releves') ?>" class="erp-card-action">Relevé complet &rarr;</a>
            </div>
            <div class="erp-card-body">
                <div class="erp-chart-wrapper" style="position: relative; height: 280px;">
                    <canvas id="chartEtudiantValidation"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau détaillé des notes de l'étudiant -->
    <div class="erp-card" style="margin-top: 20px;">
        <div class="erp-card-header">
            <div>
                <h3 class="erp-card-title">Mon bulletin récapitulatif des cours</h3>
                <p class="erp-card-caption">Détail des notes et évaluations enregistrées</p>
            </div>
            <a href="<?= base_url('/releves') ?>" class="erp-card-action">
                <i class="fa-solid fa-print"></i> Imprimer mon relevé de cotes
            </a>
        </div>
        <div class="erp-card-body no-padding">
            <?php if (empty($mesNotes)): ?>
                <div class="erp-empty-msg">Aucune note n'a encore été saisie pour votre dossier pour cette session.</div>
            <?php else: ?>
                <table class="erp-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Matière / Cours</th>
                            <th>Crédits</th>
                            <th class="text-right">Interro</th>
                            <th class="text-right">TP</th>
                            <th class="text-right">Examen</th>
                            <th class="text-right">Note Totale</th>
                            <th class="text-right">Décision</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mesNotes as $n):
                            $totalVal = (float)$n['total'];
                            $isValid = ($totalVal >= 10);
                        ?>
                            <tr>
                                <td class="font-mono"><?= htmlspecialchars($n['cours_code'] ?: '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="font-medium"><?= htmlspecialchars($n['cours_nom'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int)($n['credit'] ?? 0) ?></td>
                                <td class="text-right"><?= number_format((float)($n['interro'] ?? 0), 1) ?></td>
                                <td class="text-right"><?= number_format((float)($n['tp'] ?? 0), 1) ?></td>
                                <td class="text-right"><?= number_format((float)($n['examen'] ?? 0), 1) ?></td>
                                <td class="text-right font-bold"><?= number_format($totalVal, 1) ?></td>
                                <td class="text-right">
                                    <span class="erp-metric-badge <?= $isValid ? 'valid' : 'alert' ?>">
                                        <?= $isValid ? 'Validé' : 'Ajourné' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>


    <?php else: ?>
    <!-- ════════════════════════════════════════════════════════════
         TABLEAU DE BORD : ADMINISTRATEUR & FINANCE (VUE GLOBALE)
    ════════════════════════════════════════════════════════════ -->
    <?php
        $stats                  = $stats ?? [];
        $statsNotes             = $statsNotes ?? ['total' => 0, 'moyenne' => 0, 'reussis' => 0, 'ajournes' => 0];
        $statsPaiements         = $statsPaiements ?? ['total_trans' => 0, 'total_montant' => 0];
        $demandesEnAttente      = (int)($demandesEnAttente ?? 0);
        $inscriptionsParFiliere = $inscriptionsParFiliere ?? [];
        $topCours               = $topCours ?? [];
        $activities             = $activities ?? [];

        $totalNotes   = (int)($statsNotes['total'] ?? 0);
        $reussis      = (int)($statsNotes['reussis'] ?? 0);
        $ajournes     = (int)($statsNotes['ajournes'] ?? 0);
        $tauxReussite = $totalNotes > 0 ? round(($reussis / $totalNotes) * 100, 1) : 0;
        $moyenneGen   = isset($statsNotes['moyenne']) ? number_format((float)$statsNotes['moyenne'], 1) : '0.0';

        $totalEtudiantsInscrits = 0;
        $chartFilieresData = [];
        foreach ($inscriptionsParFiliere as $f) {
            $count = (int)$f['count'];
            $totalEtudiantsInscrits += $count;
            $chartFilieresData[] = ['filiere' => $f['filiere'], 'count' => $count];
        }

        $chartCoursData = [];
        foreach ($topCours as $c) {
            $chartCoursData[] = [
                'nom'      => $c['nom'],
                'code'     => $c['code'] ?: '—',
                'nb_notes' => (int)$c['nb_notes'],
                'moy'      => $c['moy'] !== null ? (float)$c['moy'] : 0.0
            ];
        }
    ?>

    <!-- En-tête Administrateur -->
    <div class="erp-page-header">
        <div class="erp-header-meta">
            <h1 class="erp-page-title">Tableau de bord académique — Direction Générale</h1>
            <p class="erp-page-subtitle">
                <span>Session académique en cours</span>
                <span class="erp-sep">•</span>
                <span><?= $dateCourante ?></span>
                <span class="erp-sep">•</span>
                <span class="erp-user-badge">Compte : <?= $userName ?> (<?= ucfirst($userRole) ?>)</span>
            </p>
        </div>
        <div class="erp-header-actions">
            <button type="button" class="btn btn-secondary" onclick="location.reload();">
                <i class="fa-solid fa-rotate-right"></i> Actualiser
            </button>
            <?php if (RoleMiddleware::isAdmin()): ?>
                <a href="<?= base_url('/parametres') ?>" class="btn btn-secondary">
                    <i class="fa-solid fa-sliders"></i> Configuration
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4 KPIs Admin -->
    <div class="erp-kpi-grid">
        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Effectif Étudiants</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-user-graduate"></i></div>
            </div>
            <div class="erp-kpi-number"><?= number_format((int)($stats['etudiants'] ?? 0)) ?></div>
            <div class="erp-kpi-footer">
                <a href="<?= base_url('/etudiants') ?>" class="erp-kpi-link">Registre des étudiants &rarr;</a>
            </div>
        </div>

        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Corps Enseignant</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
            </div>
            <div class="erp-kpi-number"><?= number_format((int)($stats['enseignants'] ?? 0)) ?></div>
            <div class="erp-kpi-footer">
                <a href="<?= base_url('/enseignants') ?>" class="erp-kpi-link">Gestion professorale &rarr;</a>
            </div>
        </div>

        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Filières Académiques</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-sitemap"></i></div>
            </div>
            <div class="erp-kpi-number"><?= number_format((int)($stats['filieres'] ?? 0)) ?></div>
            <div class="erp-kpi-footer">
                <a href="<?= base_url('/domaines') ?>" class="erp-kpi-link">Domaines & filières &rarr;</a>
            </div>
        </div>

        <div class="erp-kpi-card">
            <div class="erp-kpi-header">
                <span class="erp-kpi-label">Cours & Modules</span>
                <div class="erp-kpi-icon"><i class="fa-solid fa-book-open"></i></div>
            </div>
            <div class="erp-kpi-number"><?= number_format((int)($stats['cours'] ?? 0)) ?></div>
            <div class="erp-kpi-footer">
                <a href="<?= base_url('/cours') ?>" class="erp-kpi-link">Catalogue des cours &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Statistiques secondaires de gestion -->
    <div class="erp-sub-metrics">
        <div class="erp-metric-item">
            <div class="erp-metric-desc">Taux de validation des évaluations</div>
            <div class="erp-metric-stat">
                <span class="erp-metric-val"><?= $tauxReussite ?>%</span>
                <span class="erp-metric-badge <?= $tauxReussite >= 50 ? 'valid' : 'alert' ?>">
                    <?= $reussis ?> validés / <?= $totalNotes ?> notes
                </span>
            </div>
        </div>

        <div class="erp-metric-item">
            <div class="erp-metric-desc">Moyenne générale académique</div>
            <div class="erp-metric-stat">
                <span class="erp-metric-val"><?= $moyenneGen ?></span>
                <span class="erp-metric-badge normal">Sur 100 points</span>
            </div>
        </div>

        <?php if (RoleMiddleware::isAdmin() || RoleMiddleware::isFinance()): ?>
            <div class="erp-metric-item">
                <div class="erp-metric-desc">Recettes perçues</div>
                <div class="erp-metric-stat">
                    <span class="erp-metric-val"><?= number_format((float)($statsPaiements['total_montant'] ?? 0), 0, ',', ' ') ?> FC</span>
                    <span class="erp-metric-badge normal"><?= (int)($statsPaiements['total_trans'] ?? 0) ?> reçus émis</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="erp-metric-item <?= $demandesEnAttente > 0 ? 'highlight-alert' : '' ?>">
            <div class="erp-metric-desc">Demandes de dérogation (SGA)</div>
            <div class="erp-metric-stat">
                <span class="erp-metric-val"><?= $demandesEnAttente ?></span>
                <?php if ($demandesEnAttente > 0): ?>
                    <a href="<?= base_url('/demandes-modification-notes') ?>" class="erp-metric-badge alert-action">
                        À traiter &rarr;
                    </a>
                <?php else: ?>
                    <span class="erp-metric-badge valid">À jour</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 2 Graphiques Haute Qualité Admin -->
    <div class="erp-charts-grid">
        <!-- Effectifs par filière -->
        <div class="erp-card erp-chart-card">
            <div class="erp-card-header">
                <div>
                    <h3 class="erp-card-title">
                        <i class="fa-solid fa-chart-column erp-chart-title-icon"></i>
                        Effectifs par filière
                    </h3>
                    <p class="erp-card-caption">Répartition des étudiants inscrits selon la filière d'études</p>
                </div>
                <div class="erp-chart-controls">
                    <span class="erp-badge-info"><?= $totalEtudiantsInscrits ?> inscrits</span>
                </div>
            </div>
            <div class="erp-card-body">
                <div class="erp-chart-wrapper" style="position: relative; height: 280px;">
                    <canvas id="chartInscriptionsFiliere"></canvas>
                </div>
            </div>
        </div>

        <!-- Cours les plus évalués -->
        <div class="erp-card erp-chart-card">
            <div class="erp-card-header">
                <div>
                    <h3 class="erp-card-title">
                        <i class="fa-solid fa-chart-bar erp-chart-title-icon"></i>
                        Cours les plus évalués
                    </h3>
                    <p class="erp-card-caption">Volume d'évaluations et cotes enregistrées par unité d'enseignement</p>
                </div>
                <span class="erp-badge-info"><?= $totalNotes ?> évaluations</span>
            </div>
            <div class="erp-card-body">
                <div class="erp-chart-wrapper" style="position: relative; height: 280px;">
                    <canvas id="chartCoursEvalues"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Section secondaire Admin : Accès directs & Activités -->
    <div class="erp-layout-grid" style="margin-top: 20px;">
        <div class="erp-col-left">
            <div class="erp-card">
                <div class="erp-card-header">
                    <h3 class="erp-card-title">Opérations & modules clés</h3>
                </div>
                <div class="erp-card-body">
                    <div class="erp-shortcuts">
                        <a href="<?= base_url('/etudiants') ?>" class="erp-shortcut-btn">
                            <i class="fa-solid fa-user-graduate"></i>
                            <div><strong>Gestion Étudiants</strong><small>Inscriptions</small></div>
                        </a>
                        <a href="<?= base_url('/notes') ?>" class="erp-shortcut-btn">
                            <i class="fa-solid fa-file-pen"></i>
                            <div><strong>Saisie des Notes</strong><small>Évaluations</small></div>
                        </a>
                        <a href="<?= base_url('/finance') ?>" class="erp-shortcut-btn">
                            <i class="fa-solid fa-wallet"></i>
                            <div><strong>Gestion Finance</strong><small>Frais académiques</small></div>
                        </a>
                        <a href="<?= base_url('/releves') ?>" class="erp-shortcut-btn">
                            <i class="fa-solid fa-file-invoice"></i>
                            <div><strong>Relevés de Cotes</strong><small>Bulletins</small></div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="erp-col-right">
            <div class="erp-card">
                <div class="erp-card-header">
                    <div>
                        <h3 class="erp-card-title">Journal d'activité académique</h3>
                        <p class="erp-card-caption">7 derniers jours</p>
                    </div>
                </div>
                <div class="erp-card-body no-padding">
                    <?php if (empty($activities)): ?>
                        <div class="erp-empty-msg">Aucun mouvement académique récent.</div>
                    <?php else: ?>
                        <ul class="erp-activity-list">
                            <?php foreach ($activities as $act): ?>
                                <li class="erp-activity-row">
                                    <span class="erp-act-tag badge-note"><?= ucfirst($act['type']) ?></span>
                                    <div class="erp-act-detail">
                                        <p class="erp-act-text"><?= htmlspecialchars($act['label'], ENT_QUOTES, 'UTF-8') ?></p>
                                        <span class="erp-act-date"><?= date('d/m/Y H:i', strtotime($act['date'])) ?></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Pied de page institutionnel -->
    <footer class="erp-footer">
        <div class="erp-footer-content">
            <span><?= htmlspecialchars(settings('university_name', 'SGAU-OPENLU'), ENT_QUOTES, 'UTF-8') ?> &copy; <?= date('Y') ?> &bull; Système de Gestion Académique Universitaire</span>
            <span class="erp-footer-legal">Conformité LMD &bull; Tous droits réservés</span>
        </div>
    </footer>

</div>

<!-- ════════════════════════════════════════════════════════════════
     STYLES CSS INSTITUTIONNELS (AUCUN ARTIFICE IA)
════════════════════════════════════════════════════════════════ -->
<style>
.erp-dashboard {
    width: 100%;
    box-sizing: border-box;
    color: #0f172a;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    padding-bottom: 24px;
}

.erp-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    padding-bottom: 18px;
    margin-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}
.erp-page-title {
    margin: 0 0 6px;
    font-size: 20px;
    font-weight: 700;
    color: #001A72;
    letter-spacing: -0.01em;
}
.erp-page-subtitle {
    margin: 0;
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.erp-sep { color: #cbd5e1; }
.erp-user-badge { font-weight: 600; color: #334155; }
.erp-header-actions { display: flex; gap: 10px; }

/* Grille KPI */
.erp-kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 18px;
}
.erp-kpi-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 18px 20px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}
.erp-kpi-card.highlight-alert {
    border-color: #f59e0b;
    background: #fffbeb;
}
.erp-kpi-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.erp-kpi-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: #64748b;
}
.erp-kpi-icon {
    width: 32px;
    height: 32px;
    background: #f1f5f9;
    color: #001A72;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.erp-kpi-number {
    font-size: 26px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.1;
    margin-bottom: 8px;
}
.erp-kpi-footer {
    font-size: 12px;
    color: #64748b;
    border-top: 1px solid #f8fafc;
    padding-top: 8px;
}
.erp-kpi-link {
    color: #001A72;
    text-decoration: none;
    font-weight: 600;
}
.erp-kpi-link:hover { text-decoration: underline; }
.erp-kpi-info { color: #94a3b8; }

/* Statistiques secondaires */
.erp-sub-metrics {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
    margin-bottom: 22px;
}
.erp-metric-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 14px 18px;
}
.erp-metric-item.highlight-alert {
    border-color: #f59e0b;
    background: #fffbeb;
}
.erp-metric-desc {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 6px;
    font-weight: 500;
}
.erp-metric-stat {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
}
.erp-metric-val {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
}
.erp-metric-badge {
    font-size: 11.5px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
}
.erp-metric-badge.valid { background: #dcfce7; color: #166534; }
.erp-metric-badge.alert { background: #fee2e2; color: #991b1b; }
.erp-metric-badge.normal { background: #f1f5f9; color: #475569; }
.erp-metric-badge.alert-action {
    background: #f59e0b;
    color: #ffffff;
    text-decoration: none;
}

/* Grille des graphiques */
.erp-charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.erp-chart-card {
    display: flex;
    flex-direction: column;
}
.erp-chart-title-icon {
    color: #001A72;
    margin-right: 6px;
    font-size: 14px;
}
.erp-badge-info {
    font-size: 11.5px;
    font-weight: 600;
    color: #001A72;
    background: #eff6ff;
    padding: 3px 10px;
    border-radius: 4px;
    border: 1px solid #dbeafe;
}
.erp-chart-wrapper {
    width: 100%;
}

/* Grille secondaire */
.erp-layout-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 20px;
}
.erp-col-left, .erp-col-right {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.erp-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
}
.erp-card-header {
    padding: 14px 18px;
    border-bottom: 1px solid #edf2f7;
    background: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.erp-card-title {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
}
.erp-card-caption {
    margin: 2px 0 0;
    font-size: 11.5px;
    color: #64748b;
}
.erp-card-action {
    font-size: 12px;
    font-weight: 600;
    color: #001A72;
    text-decoration: none;
}
.erp-card-action:hover { text-decoration: underline; }
.erp-badge-subtle {
    font-size: 11px;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 4px;
    font-weight: 500;
}
.erp-card-body { padding: 18px; }
.erp-card-body.no-padding { padding: 0; }
.erp-empty-msg {
    padding: 24px;
    text-align: center;
    color: #94a3b8;
    font-size: 13px;
}

/* Tableaux */
.erp-table {
    width: 100%;
    border-collapse: collapse;
}
.erp-table th {
    background: #f8fafc;
    color: #475569;
    font-size: 11.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 10px 16px;
    border-bottom: 1px solid #e2e8f0;
    text-align: left;
}
.erp-table td {
    padding: 10px 16px;
    font-size: 13px;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
}
.erp-table tbody tr:last-child td { border-bottom: none; }
.erp-table tbody tr:hover { background: #fafafa; }
.text-right { text-align: right; }
.font-mono { font-family: monospace; font-size: 12px; color: #475569; }
.font-medium { font-weight: 500; }
.font-bold { font-weight: 700; color: #0f172a; }

.erp-btn-sm {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: #f1f5f9;
    color: #001A72;
    border-radius: 4px;
    text-decoration: none;
    font-size: 11.5px;
    font-weight: 600;
}
.erp-btn-sm:hover { background: #e2e8f0; }

/* Raccourcis */
.erp-shortcuts {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}
.erp-shortcut-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    color: #0f172a;
    text-decoration: none;
    transition: background-color 0.15s ease, border-color 0.15s ease;
}
.erp-shortcut-btn:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.erp-shortcut-btn i {
    color: #001A72;
    font-size: 16px;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}
.erp-shortcut-btn div {
    display: flex;
    flex-direction: column;
    gap: 1px;
}
.erp-shortcut-btn strong { font-size: 13px; color: #0f172a; }
.erp-shortcut-btn small { font-size: 11px; color: #64748b; }

/* Activités */
.erp-activity-list {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 380px;
    overflow-y: auto;
}
.erp-activity-row {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f8fafc;
}
.erp-activity-row:last-child { border-bottom: none; }
.erp-act-tag {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    padding: 3px 6px;
    border-radius: 3px;
    white-space: nowrap;
    margin-top: 1px;
}
.badge-note     { background: #eff6ff; color: #1d4ed8; }
.badge-paiement { background: #f0fdf4; color: #15803d; }
.badge-etudiant { background: #fff7ed; color: #c2410c; }
.badge-releve   { background: #f5f3ff; color: #6d28d9; }
.badge-default  { background: #f1f5f9; color: #475569; }

.erp-act-detail { flex: 1; min-width: 0; }
.erp-act-text {
    margin: 0 0 2px;
    font-size: 12.5px;
    color: #1e293b;
    line-height: 1.4;
}
.erp-act-date { font-size: 11px; color: #94a3b8; }

.erp-footer {
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    font-size: 11.5px;
    color: #94a3b8;
}
.erp-footer-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

@media (max-width: 1200px) {
    .erp-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .erp-charts-grid { grid-template-columns: 1fr; }
    .erp-layout-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
    .erp-kpi-grid { grid-template-columns: 1fr; }
    .erp-sub-metrics { grid-template-columns: 1fr; }
    .erp-page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
    .erp-shortcuts { grid-template-columns: 1fr; }
}
</style>

<!-- ════════════════════════════════════════════════════════════════
     SCRIPT GRAPHIQUES ADAPTATIFS SELON LE RÔLE
════════════════════════════════════════════════════════════════ -->
<script>
function loadChartJs(callback) {
    if (window.Chart) {
        callback();
        return;
    }
    const scriptLocal = document.createElement('script');
    scriptLocal.src = '<?= base_url('/assets/js/chart.min.js') ?>';
    scriptLocal.onload = () => callback();
    scriptLocal.onerror = () => {
        const scriptCdn = document.createElement('script');
        scriptCdn.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
        scriptCdn.onload = () => callback();
        document.head.appendChild(scriptCdn);
    };
    document.head.appendChild(scriptLocal);
}

document.addEventListener('DOMContentLoaded', function() {
    loadChartJs(function() {
        const role = '<?= $userRole ?>';

        if (role === 'enseignant') {
            initEnseignantCharts();
        } else if (role === 'etudiant') {
            initEtudiantCharts();
        } else {
            initAdminCharts();
        }
    });
});

/* ─── GRAPHIQUES ENSEIGNANT ─── */
function initEnseignantCharts() {
    const dataNotes = <?= json_encode($chartNotesParCours ?? []) ?>;
    const dataMoy   = <?= json_encode($chartMoyParCours ?? []) ?>;

    // 1. Évaluations par cours de l'enseignant
    const canvas1 = document.getElementById('chartEnseignantEvaluations');
    if (canvas1) {
        new Chart(canvas1.getContext('2d'), {
            type: 'bar',
            data: {
                labels: dataNotes.map(d => d.nom),
                datasets: [{
                    label: 'Notes saisies',
                    data: dataNotes.map(d => d.nb_notes),
                    backgroundColor: '#001A72',
                    hoverBackgroundColor: '#1d4ed8',
                    borderRadius: 4,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            title: (items) => dataNotes[items[0].dataIndex].nomComplet + ' [' + dataNotes[items[0].dataIndex].code + ']',
                            label: (item) => item.raw + ' note(s) saisie(s)'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11, family: 'Inter' } } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, color: '#64748b', font: { size: 11, family: 'Inter' } } }
                }
            }
        });
    }

    // 2. Moyenne par cours de l'enseignant
    const canvas2 = document.getElementById('chartEnseignantMoyennes');
    if (canvas2) {
        new Chart(canvas2.getContext('2d'), {
            type: 'bar',
            data: {
                labels: dataMoy.map(d => d.nom),
                datasets: [{
                    label: 'Moyenne du cours',
                    data: dataMoy.map(d => d.moyenne),
                    backgroundColor: '#1d4ed8',
                    hoverBackgroundColor: '#2563eb',
                    borderRadius: 4,
                    maxBarThickness: 36
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            title: (items) => dataMoy[items[0].dataIndex].nomComplet + ' [' + dataMoy[items[0].dataIndex].code + ']',
                            label: (item) => 'Moyenne : ' + item.raw + ' / 100'
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, max: 100, ticks: { color: '#64748b', font: { size: 11, family: 'Inter' } } },
                    y: { grid: { display: false }, ticks: { color: '#334155', font: { size: 11, family: 'Inter', weight: '600' } } }
                }
            }
        });
    }
}

/* ─── GRAPHIQUES ÉTUDIANT ─── */
function initEtudiantCharts() {
    const dataNotes = <?= json_encode($chartEtudiantNotes ?? []) ?>;
    const coursValides  = <?= (int)($coursValides ?? 0) ?>;
    const coursAjournes = <?= (int)($coursAjournes ?? 0) ?>;

    // 1. Notes de l'étudiant par cours
    const canvas1 = document.getElementById('chartEtudiantNotes');
    if (canvas1) {
        new Chart(canvas1.getContext('2d'), {
            type: 'bar',
            data: {
                labels: dataNotes.map(d => d.cours),
                datasets: [{
                    label: 'Note totale',
                    data: dataNotes.map(d => d.note),
                    backgroundColor: dataNotes.map(d => d.note >= 10 ? '#001A72' : '#dc2626'),
                    borderRadius: 4,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            title: (items) => dataNotes[items[0].dataIndex].nomComplet + ' [' + dataNotes[items[0].dataIndex].code + ']',
                            label: (item) => {
                                const d = dataNotes[item.dataIndex];
                                return [
                                    'Note finale : ' + item.raw,
                                    'Examen : ' + d.examen + ' • TP : ' + d.tp,
                                    item.raw >= 10 ? 'Statut : Validé' : 'Statut : Ajourné'
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11, family: 'Inter' } } },
                    y: { beginAtZero: true, ticks: { color: '#64748b', font: { size: 11, family: 'Inter' } } }
                }
            }
        });
    }

    // 2. Bilan de validation des matières (Donut / Bar)
    const canvas2 = document.getElementById('chartEtudiantValidation');
    if (canvas2) {
        new Chart(canvas2.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Matières validées', 'Matières ajournées'],
                datasets: [{
                    data: [coursValides, coursAjournes],
                    backgroundColor: ['#001A72', '#dc2626'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { font: { family: 'Inter', size: 12 }, color: '#334155', boxWidth: 14 }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            label: (item) => ` ${item.label} : ${item.raw} cours`
                        }
                    }
                }
            }
        });
    }
}

/* ─── GRAPHIQUES ADMINISTRATEUR ─── */
function initAdminCharts() {
    const dataFilieres = <?= json_encode($chartFilieresData ?? []) ?>;
    const dataCours    = <?= json_encode($chartCoursData ?? []) ?>;

    const canvas1 = document.getElementById('chartInscriptionsFiliere');
    if (canvas1) {
        new Chart(canvas1.getContext('2d'), {
            type: 'bar',
            data: {
                labels: dataFilieres.map(d => d.filiere.length > 18 ? d.filiere.substring(0, 16) + '…' : d.filiere),
                datasets: [{
                    data: dataFilieres.map(d => d.count),
                    backgroundColor: '#001A72',
                    hoverBackgroundColor: '#1d4ed8',
                    borderRadius: 4,
                    maxBarThickness: 42
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            title: (items) => dataFilieres[items[0].dataIndex].filiere,
                            label: (item) => item.raw + ' étudiant(s) inscrit(s)'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11, family: 'Inter' } } },
                    y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, color: '#64748b', font: { size: 11, family: 'Inter' } } }
                }
            }
        });
    }

    const canvas2 = document.getElementById('chartCoursEvalues');
    if (canvas2) {
        new Chart(canvas2.getContext('2d'), {
            type: 'bar',
            data: {
                labels: dataCours.map(d => d.nom.length > 20 ? d.nom.substring(0, 18) + '…' : d.nom),
                datasets: [{
                    data: dataCours.map(d => d.nb_notes),
                    backgroundColor: '#1d4ed8',
                    hoverBackgroundColor: '#2563eb',
                    borderRadius: 4,
                    maxBarThickness: 36
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        callbacks: {
                            title: (items) => dataCours[items[0].dataIndex].nom + ' [' + dataCours[items[0].dataIndex].code + ']',
                            label: (item) => item.raw + ' évaluation(s) saisie(s)'
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0, color: '#64748b', font: { size: 11, family: 'Inter' } } },
                    y: { grid: { display: false }, ticks: { color: '#334155', font: { size: 11, family: 'Inter', weight: '600' } } }
                }
            }
        });
    }
}
</script>
