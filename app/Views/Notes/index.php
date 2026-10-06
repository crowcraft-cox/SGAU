<div class="notes-page-container">

<?php if (isset($userRole) && $userRole === 'etudiant'): ?>
    <!-- VUE ÉTUDIANT (Consultation Personnelle) -->
    <div class="header-section">
        <div>
            <h2><i class="fas fa-graduation-cap text-primary"></i> Mes Notes & Résultats</h2>
            <p class="subtitle">Consultez vos résultats pour les travaux journaliers, mi-session et examens finaux.</p>
        </div>
    </div>

    <?php if (empty($notes)): ?>
        <div class="empty-state-card">
            <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
            <h3>Aucune note disponible pour le moment</h3>
            <p class="text-muted">Vos notes s'afficheront ici dès que vos enseignants les auront enregistrées.</p>
        </div>
    <?php else: ?>
        <div class="table-container-modern">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th width="100">Code</th>
                        <th>Intitulé du Cours</th>
                        <th class="text-center" width="80">Semestre</th>
                        <th class="text-center" width="90">Interro /30</th>
                        <th class="text-center" width="90">TP /40</th>
                        <th class="text-center" width="90">TD /30</th>
                        <th class="text-center" width="90">Mi-Session /50</th>
                        <th class="text-center" width="90">Examen /50</th>
                        <th class="text-center" width="100">Total /200</th>
                        <th class="text-center" width="100">Moyenne /20</th>
                        <th class="text-center" width="90">Mention</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notes as $note): ?>
                        <?php 
                            $interro = isset($note['interro']) && is_numeric($note['interro']) ? floatval($note['interro']) : null;
                            $tp = isset($note['tp']) && is_numeric($note['tp']) ? floatval($note['tp']) : null;
                            $td = isset($note['td']) && is_numeric($note['td']) ? floatval($note['td']) : null;
                            $mi_session = isset($note['mi_session']) && is_numeric($note['mi_session']) ? floatval($note['mi_session']) : null;
                            $examen = isset($note['examen']) && is_numeric($note['examen']) ? floatval($note['examen']) : null;
                            
                            $sum = ($interro ?? 0) + ($tp ?? 0) + ($td ?? 0) + ($mi_session ?? 0) + ($examen ?? 0);
                            $hasGrades = ($interro !== null || $tp !== null || $td !== null || $mi_session !== null || $examen !== null);
                            
                            $total = $hasGrades ? $sum : ($note['total'] ?? '-');
                            $moyenne = $hasGrades ? round($sum / 10, 2) : ($note['note'] ?? '-');

                            $badgeClass = '';
                            $mention = '-';
                            if (is_numeric($moyenne)) {
                                if ($moyenne >= 16) { $mention = 'A'; $badgeClass = 'badge-pass-excellent'; }
                                elseif ($moyenne >= 14) { $mention = 'B'; $badgeClass = 'badge-pass-good'; }
                                elseif ($moyenne >= 12) { $mention = 'C'; $badgeClass = 'badge-pass'; }
                                elseif ($moyenne >= 10) { $mention = 'D'; $badgeClass = 'badge-pass-fair'; }
                                else { $mention = 'E'; $badgeClass = 'badge-fail'; }
                            }
                        ?>
                        <tr>
                            <td><span class="badge-code"><?= htmlspecialchars($note['cours_code'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><strong><?= htmlspecialchars($note['cours_nom'] ?? 'Cours', ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td class="text-center"><span class="badge-semestre">S<?= htmlspecialchars($note['semestre'] ?? '1', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="text-center"><?= $interro !== null ? number_format($interro, 2) : '-' ?></td>
                            <td class="text-center"><?= $tp !== null ? number_format($tp, 2) : '-' ?></td>
                            <td class="text-center"><?= $td !== null ? number_format($td, 2) : '-' ?></td>
                            <td class="text-center"><?= $mi_session !== null ? number_format($mi_session, 2) : '-' ?></td>
                            <td class="text-center"><?= $examen !== null ? number_format($examen, 2) : '-' ?></td>
                            <td class="text-center font-weight-bold text-primary"><?= is_numeric($total) ? number_format($total, 2) . ' / 200' : '-' ?></td>
                            <td class="text-center font-weight-bold <?= (is_numeric($moyenne) && $moyenne >= 10) ? 'text-success' : ((is_numeric($moyenne) && $moyenne < 10) ? 'text-danger' : '') ?>">
                                <?= is_numeric($moyenne) ? number_format($moyenne, 2) . ' / 20' : '-' ?>
                            </td>
                            <td class="text-center">
                                <span class="mention-badge <?= $badgeClass ?>"><?= $mention ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php else: ?>
    <!-- VUE ENSEIGNANT & ADMINISTRATEUR (Sélection du cours pour notation) -->
    <div class="header-section">
        <div>
            <h2><i class="fas fa-clipboard-check text-primary"></i> Module Notes & Fiches de Cotes</h2>
            <p class="subtitle">Sélectionnez le cours concerné pour accéder directement à la grille de cotation de tous les étudiants enrôlés.</p>
        </div>
        <div class="header-actions">
            <a href="<?= base_url('/cours/create') ?>" class="btn btn-outline-primary">
                <i class="fas fa-plus"></i> Nouveau Cours
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cours)): ?>
        <div class="empty-state-card">
            <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
            <h3>Aucun cours assigné</h3>
            <p class="text-muted">Vous n'avez aucun cours pour le moment. Créez un cours pour commencer à enrôler des étudiants et saisir leurs notes.</p>
            <a class="btn btn-primary mt-3" href="<?= base_url('/cours/create') ?>">
                <i class="fas fa-plus"></i> Ajouter un cours
            </a>
        </div>
    <?php else: ?>
        <div class="courses-grid">
            <?php foreach ($cours as $item): ?>
                <?php 
                    $enrolled = (int)($item['total_enroles'] ?? 0); 
                    $graded = (int)($item['total_notes'] ?? 0);
                    $progress = $enrolled > 0 ? min(100, round(($graded / $enrolled) * 100)) : 0;
                ?>
                <div class="course-card">
                    <div class="course-card-header">
                        <span class="badge-code"><?= htmlspecialchars($item['code'] ?? 'COURS', ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="badge-credit"><?= htmlspecialchars($item['credit'] ?? '1', ENT_QUOTES, 'UTF-8') ?> Cr</span>
                    </div>

                    <div class="course-card-body">
                        <h3 class="course-card-title"><?= htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8') ?></h3>
                        
                        <p class="course-card-dept">
                            <i class="fas fa-sitemap text-muted"></i>
                            <?= htmlspecialchars($item['domaine_nom'] ?? ($item['filiere_nom'] ?? 'Tronc Commun'), ENT_QUOTES, 'UTF-8') ?>
                            <?php if (!empty($item['orientation_nom'])): ?>
                                &raquo; <?= htmlspecialchars($item['orientation_nom'], ENT_QUOTES, 'UTF-8') ?>
                            <?php endif; ?>
                        </p>

                        <?php if (RoleMiddleware::isAdmin() && !empty($item['enseignant_nom'])): ?>
                            <p class="course-card-teacher">
                                <i class="fas fa-chalkboard-user text-muted"></i>
                                <?= htmlspecialchars(trim($item['enseignant_nom'] . ' ' . ($item['enseignant_prenom'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        <?php endif; ?>

                        <div class="course-stats-bar">
                            <div class="stat-box">
                                <span class="stat-number"><i class="fas fa-users text-primary"></i> <?= $enrolled ?></span>
                                <span class="stat-label">Étudiant(s) enrôlé(s)</span>
                            </div>
                            <div class="stat-box">
                                <span class="stat-number"><i class="fas fa-check-double text-success"></i> <?= $graded ?></span>
                                <span class="stat-label">Noté(s)</span>
                            </div>
                        </div>

                        <div class="progress-container">
                            <div class="progress-bar-wrapper">
                                <div class="progress-bar" style="width: <?= $progress ?>%;"></div>
                            </div>
                            <small class="text-muted"><?= $progress ?>% complété</small>
                        </div>
                    </div>

                    <div class="course-card-footer">
                        <a href="<?= base_url('/cours/' . $item['id'] . '/enrollement') ?>" class="btn btn-secondary-subtle" title="Gérer les étudiants enrôlés">
                            <i class="fas fa-user-plus"></i> Enrôlement (<?= $enrolled ?>)
                        </a>

                        <a href="<?= base_url('/notes/cours/' . $item['id']) ?>" class="btn btn-primary-custom" title="Ouvrir la Fiche de Cotes">
                            <i class="fas fa-clipboard-list"></i> Noter ce cours <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php endif; ?>

</div>

<style>
.notes-page-container {
    padding: 10px 0 40px 0;
}

.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.header-section h2 {
    margin: 0 0 6px 0;
    font-size: 26px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.header-section .subtitle {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.courses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 22px;
}

.course-card {
    background: #fafbfc;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: none;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.2s ease;
    overflow: hidden;
}

.course-card:hover {
    background: #ffffff;
    border-color: #001A72;
}

.course-card-header {
    padding: 16px 20px 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.course-card-body {
    padding: 12px 20px 20px 20px;
    flex: 1;
}

.course-card-title {
    margin: 8px 0 10px 0;
    font-size: 17px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
}

.course-card-dept, .course-card-teacher {
    margin: 0 0 6px 0;
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 7px;
}

.course-stats-bar {
    display: flex;
    justify-content: space-between;
    background: #f8fafc;
    border-radius: 8px;
    padding: 10px 14px;
    margin: 16px 0 10px 0;
    border: 1px solid #f1f5f9;
}

.stat-box {
    display: flex;
    flex-direction: column;
}

.stat-number {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
}

.stat-label {
    font-size: 11px;
    color: #64748b;
}

.progress-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.progress-bar-wrapper {
    flex: 1;
    height: 6px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #10b981);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.course-card-footer {
    padding: 14px 20px;
    background: #fafafa;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    gap: 10px;
}

.btn-primary-custom {
    background: #0056b3;
    color: white;
    padding: 8px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-primary-custom:hover {
    background: #00145a;
    color: white;
}

.btn-secondary-subtle {
    background: #eff6ff;
    color: #1d4ed8;
    border: 1px solid #bfdbfe;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-secondary-subtle:hover {
    background: #dbeafe;
    color: #1e40af;
}

.badge-code {
    color: #0369a1;
    font-family: monospace;
    font-weight: 700;
    font-size: 12px;
}

.badge-credit {
    color: #334155;
    font-weight: 600;
    font-size: 12px;
}

.badge-semestre {
    font-size: 12px;
    font-weight: 600;
}

.empty-state-card {
    background: white;
    padding: 60px 20px;
    border-radius: 12px;
    text-align: center;
    border: 1px dashed #cbd5e1;
}

.table-container-modern {
    background: transparent;
    border-radius: 8px;
    box-shadow: none;
    border: 1px solid #e2e8f0;
    overflow-x: auto;
}

.table-modern {
    width: 100%;
    border-collapse: collapse;
}

.table-modern thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    font-size: 13px;
    padding: 14px 16px;
    border-bottom: 2px solid #e2e8f0;
}

.table-modern tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
}

.mention-badge {
    display: inline-block;
    font-weight: 700;
    font-size: 13px;
}

.badge-pass-excellent { color: #15803d; }
.badge-pass-good      { color: #0284c7; }
.badge-pass           { color: #001A72; }
.badge-pass-fair      { color: #b45309; }
.badge-fail           { color: #b91c1c; }
</style>
