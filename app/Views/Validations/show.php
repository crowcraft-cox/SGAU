<?php
// Vue pour afficher les détails des validations d'un étudiant
$pageTitle = 'Détails des Validations';
?>

<div class="student-card">
    <div class="student-header">
        <div class="student-info">
            <h2><?= htmlspecialchars($student['nom'] . ' ' . $student['prenom'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="student-details">
                <strong>Matricule:</strong> <?= htmlspecialchars($student['matricule'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?> |
                <strong>Niveau:</strong> <?= htmlspecialchars($student['niveau'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?> |
                <strong>Filière:</strong> <?= htmlspecialchars($student['filiere_nom'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
        <a href="<?= base_url('/validations') ?>" class="btn btn-secondary">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $stats['total_cours'] ?? 0 ?></div>
        <div class="stat-label">Cours Total</div>
    </div>
    <div class="stat-card success">
        <div class="stat-number"><?= $stats['cours_valides'] ?? 0 ?></div>
        <div class="stat-label">Cours Validés</div>
    </div>
    <div class="stat-card danger">
        <div class="stat-number"><?= $stats['cours_non_valides'] ?? 0 ?></div>
        <div class="stat-label">Cours Non Validés</div>
    </div>
    <div class="stat-card info">
        <div class="stat-number"><?= $stats['credits_acquis'] ?? 0 ?></div>
        <div class="stat-label">Crédits Acquis</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $stats['credits_requis'] ?? 0 ?></div>
        <div class="stat-label">Crédits Requis</div>
    </div>
</div>

<?php if (!empty($semesters)): ?>
    <div class="semesters-section">
        <h3>Résultats par Semestre</h3>
        <div class="semester-tabs">
            <?php foreach ($semesters as $index => $sem): ?>
                <a href="<?= base_url('/validations/' . $student['id'] . '/semestre/' . $sem) ?>" 
                   class="semester-tab">
                    Semestre <?= htmlspecialchars($sem, ENT_QUOTES, 'UTF-8') ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="validations-section">
    <h3>Tous les Cours</h3>
    
    <?php if (!empty($validations)): ?>
        <div class="table-responsive">
            <table class="validations-table">
                <thead>
                    <tr>
                        <th>Code Cours</th>
                        <th>Cours</th>
                        <th>Crédits</th>
                        <th>Semestre</th>
                        <th>Interro</th>
                        <th>TP</th>
                        <th>Examen</th>
                        <th>Moyenne</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($validations as $validation): ?>
                        <tr class="<?= $validation['statut'] === 'Validé' ? 'row-validated' : 'row-not-validated' ?>">
                            <td><span class="code-badge"><?= htmlspecialchars($validation['code'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td class="course-name"><?= htmlspecialchars($validation['cours_nom'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-center"><strong><?= $validation['credit'] ?? 0 ?></strong></td>
                            <td class="text-center">
                                <?php if ($validation['semestre']): ?>
                                    <span class="semestre-badge">S<?= htmlspecialchars($validation['semestre'], ENT_QUOTES, 'UTF-8') ?></span>
                                <?php else: ?>
                                    <span style="color: #ccc;">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $validation['interro'] ?? 0 ?></td>
                            <td class="text-center"><?= $validation['tp'] ?? 0 ?></td>
                            <td class="text-center"><?= $validation['examen'] ?? 0 ?></td>
                            <td class="text-center">
                                <span class="moyenne-value"><?= $validation['moyenne'] ?? 0 ?>/20</span>
                            </td>
                            <td class="text-center">
                                <span class="status-pill <?= $validation['statut'] === 'Validé' ? 'status-valid' : 'status-invalid' ?>">
                                    <i class="fas <?= $validation['statut'] === 'Validé' ? 'fa-check-circle' : 'fa-times-circle' ?>"></i>
                                    <?= $validation['statut'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="no-data">
            <p>Aucun cours trouvé pour cet étudiant</p>
        </div>
    <?php endif; ?>
</div>

<style>
.student-card {
    background: #ffffff;
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.student-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.student-info h2 {
    margin: 0 0 0.75rem 0;
    color: #1e293b;
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.025em;
}

.student-details {
    margin: 0;
    color: #64748b;
    font-size: 1rem;
}

.student-details strong {
    color: #334155;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.stat-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
}

.stat-number {
    font-size: 2.25rem;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 0.25rem;
    display: block;
}

.stat-label {
    color: #64748b;
    font-size: 0.875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.semesters-section {
    background: white;
    border-radius: 6px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid #e2e8f0;
}

.semesters-section h3, .validations-section h3 {
    margin: 0 0 1.25rem 0;
    color: #0f172a;
    font-size: 1.15rem;
    font-weight: 700;
}

.semester-tabs {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.semester-tab {
    display: inline-block;
    padding: 0.6rem 1.2rem;
    background: #f8fafc;
    color: #334155;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    font-weight: 600;
    font-size: 13px;
    border: 1px solid #cbd5e1;
}

.semester-tab:hover {
    background: #001A72;
    color: #ffffff;
    border-color: #001A72;
}

.validations-section {
    background: white;
    border-radius: 6px;
    padding: 1.5rem;
    border: 1px solid #e2e8f0;
}

.table-responsive {
    overflow-x: auto;
    border-radius: 6px;
}

.validations-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.validations-table th {
    padding: 1.25rem 1rem;
    background: #f8fafc;
    text-align: left;
    font-weight: 700;
    color: #475569;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 2px solid #e2e8f0;
}

.validations-table td {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
    font-size: 0.95rem;
    vertical-align: middle;
}

.course-name {
    font-weight: 600;
    color: #1e293b;
    min-width: 200px;
}

.code-badge {
    display: inline-block;
    color: #475569;
    font-family: monospace;
    font-weight: 700;
    font-size: 0.875rem;
}

.semestre-badge {
    color: #3b82f6;
    font-weight: 700;
    font-size: 0.813rem;
}

.moyenne-value {
    font-weight: 800;
    color: #3b82f6;
    font-size: 1.1rem;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    font-size: 0.813rem;
    white-space: nowrap;
}

.status-valid {
    color: #059669;
}

.status-invalid {
    color: #dc2626;
}

.btn-secondary {
    background-color: #f8fafc;
    color: #475569;
    padding: 0.625rem 1.25rem;
    border-radius: 10px;
    text-decoration: none;
    transition: all 0.2s;
    font-weight: 600;
    border: 1px solid #e2e8f0;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-secondary:hover {
    background-color: #f1f5f9;
    color: #1e293b;
    border-color: #cbd5e1;
}

.no-data {
    text-align: center;
    padding: 4rem 1rem;
    color: #94a3b8;
}

.no-data p {
    font-size: 1.125rem;
    font-weight: 500;
}

@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .student-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1.5rem;
    }

    .btn-secondary {
        width: 100%;
        justify-content: center;
    }

    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .validations-table th,
    .validations-table td {
        padding: 1rem 0.75rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>
