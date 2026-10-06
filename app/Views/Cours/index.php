<div class="page-title-modern">
    <div>
        <h2><i class="fas fa-book-open text-primary"></i> Gestion des Cours</h2>
        <p class="subtitle">Consultez vos cours, gérez l'enrôlement des étudiants et complétez les fiches de cotes.</p>
    </div>
    <?php if (RoleMiddleware::isAdmin() || RoleMiddleware::isEnseignant()): ?>
        <a class="btn btn-primary" href="<?= base_url('/cours/create') ?>">
            <i class="fas fa-plus"></i> Ajouter un cours
        </a>
    <?php endif; ?>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (empty($cours)): ?>
    <div class="empty-state-card">
        <i class="fas fa-book-reader fa-3x text-muted mb-3"></i>
        <h3>Aucun cours disponible</h3>
        <p class="text-muted">Commencez par ajouter un cours pour pouvoir y enrôler des étudiants et saisir leurs notes.</p>
        <?php if (RoleMiddleware::isAdmin() || RoleMiddleware::isEnseignant()): ?>
            <a class="btn btn-primary mt-3" href="<?= base_url('/cours/create') ?>">
                <i class="fas fa-plus"></i> Créer mon premier cours
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="table-container-modern">
        <table class="table-modern">
            <thead>
                <tr>
                    <th width="90">Code</th>
                    <th>Intitulé du Cours</th>
                    <th>Domaine & Filière</th>
                    <th class="text-center" width="80">Crédits</th>
                    <th>Titulaire</th>
                    <th class="text-center" width="130">Enrôlés</th>
                    <th class="text-right" width="300">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cours as $item): ?>
                    <?php $enrolledCount = $item['total_enroles'] ?? 0; ?>
                    <tr>
                        <td>
                            <span class="badge-code"><?= htmlspecialchars($item['code'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td>
                            <strong class="course-title"><?= htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <?php if (!empty($item['description'])): ?>
                                <small class="course-desc text-muted d-block"><?= htmlspecialchars(mb_substr($item['description'], 0, 60), ENT_QUOTES, 'UTF-8') ?><?= mb_strlen($item['description']) > 60 ? '...' : '' ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($item['domaine_nom'])): ?>
                                <div class="domaine-badge-group">
                                    <span class="badge-domaine"><?= htmlspecialchars($item['domaine_nom'], ENT_QUOTES, 'UTF-8') ?></span>
                                    <small class="text-muted d-block">
                                        <?= htmlspecialchars($item['filiere_nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                        <?php if (!empty($item['orientation_nom'])): ?>
                                            &raquo; <span class="text-primary font-weight-500"><?= htmlspecialchars($item['orientation_nom'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                    </small>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">Tronc commun</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge-credit"><?= htmlspecialchars($item['credit'] ?? '1', ENT_QUOTES, 'UTF-8') ?></span>
                        </td>
                        <td>
                            <div class="teacher-info">
                                <i class="fas fa-chalkboard-user text-muted"></i>
                                <span><?= htmlspecialchars(trim(($item['enseignant_nom'] ?? '') . ' ' . ($item['enseignant_prenom'] ?? 'Non assigné')), ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('/cours/' . $item['id'] . '/enrollement') ?>" class="badge-enrolled-link" title="Gérer les étudiants">
                                <i class="fas fa-users"></i> <?= $enrolledCount ?> étudiant(s)
                            </a>
                        </td>
                        <td class="text-right">
                            <div class="action-buttons-group">
                                <!-- Enrôler -->
                                <a class="btn-action btn-enroll" href="<?= base_url('/cours/' . $item['id'] . '/enrollement') ?>" title="Enrôler des étudiants">
                                    <i class="fas fa-user-plus"></i> Enrôler
                                </a>

                                <!-- Fiche de Cotes -->
                                <a class="btn-action btn-notes" href="<?= base_url('/notes/cours/' . $item['id']) ?>" title="Saisir les notes (Fiche de Cotes)">
                                    <i class="fas fa-clipboard-list"></i> Coter
                                </a>

                                <?php if (RoleMiddleware::isAdmin() || RoleMiddleware::isEnseignant()): ?>
                                    <a class="btn-action btn-edit" href="<?= base_url('/cours/' . $item['id'] . '/edit') ?>" title="Modifier le cours">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    
                                    <?php if (RoleMiddleware::isAdmin()): ?>
                                        <form method="POST" action="<?= base_url('/cours/' . $item['id'] . '/delete') ?>" class="inline-form" onsubmit="return confirm('Supprimer ce cours et toutes ses données associées ?');">
                                            <?= csrf_field() ?>
                                            <button class="btn-action btn-delete" type="submit" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
.page-title-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.page-title-modern h2 {
    margin: 0 0 4px 0;
    font-size: 26px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-title-modern .subtitle {
    margin: 0;
    color: #64748b;
    font-size: 14px;
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

.table-modern tbody tr:hover {
    background-color: #f8fafc;
}

.badge-code {
    background: #e0f2fe;
    color: #0369a1;
    padding: 4px 8px;
    border-radius: 6px;
    font-family: monospace;
    font-weight: 600;
    font-size: 12px;
}

.course-title {
    color: #0f172a;
    font-size: 15px;
}

.badge-domaine {
    font-weight: 600;
    color: #334155;
    font-size: 13px;
}

.badge-credit {
    color: #334155;
    font-weight: 700;
    font-size: 13px;
}

.teacher-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #334155;
}

.badge-enrolled-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #15803d;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: color 0.15s ease;
}

.badge-enrolled-link:hover {
    color: #166534;
    text-decoration: underline;
}

.action-buttons-group {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-enroll {
    background: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
}
.btn-enroll:hover {
    background: #1d4ed8;
    color: white;
}

.btn-notes {
    background: #f0fdf4;
    color: #15803d;
    border-color: #bbf7d0;
}
.btn-notes:hover {
    background: #15803d;
    color: white;
}

.btn-edit {
    background: #f8fafc;
    color: #475569;
    border-color: #cbd5e1;
    padding: 6px 9px;
}
.btn-edit:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.btn-delete {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fecaca;
    padding: 6px 9px;
}
.btn-delete:hover {
    background: #dc2626;
    color: white;
}

.inline-form {
    display: inline;
    margin: 0;
}
</style>
