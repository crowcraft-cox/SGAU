<?php
// Vue pour afficher les validations par semestre
$pageTitle = 'Validations - Semestre ' . $semestre;
?>

<div class="semester-header">
    <div class="header-content">
        <h2>
            <i class="fa-solid fa-calendar"></i>
            Semestre <?= htmlspecialchars($semestre, ENT_QUOTES, 'UTF-8') ?>
        </h2>
        <p class="student-name">
            <?= htmlspecialchars($student['nom'] . ' ' . $student['prenom'], ENT_QUOTES, 'UTF-8') ?>
        </p>
        <p class="student-info">
            Matricule: <strong><?= htmlspecialchars($student['matricule'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong> |
            Niveau: <strong><?= htmlspecialchars($student['niveau'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong>
        </p>
    </div>
    <a href="<?= base_url('/validations/' . $student['id']) ?>" class="btn btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="semester-stats">
    <div class="stat-item">
        <div class="stat-value"><?= $coursValides ?></div>
        <div class="stat-label"><i class="fa-solid fa-check"></i> Validés</div>
    </div>
    <div class="stat-item">
        <div class="stat-value"><?= $coursNonValides ?></div>
        <div class="stat-label"><i class="fa-solid fa-xmark"></i> Non Validés</div>
    </div>
    <div class="stat-item">
        <div class="stat-value"><?= $creditsAcquis ?></div>
        <div class="stat-label"><i class="fa-solid fa-award"></i> Crédits Acquis</div>
    </div>
    <div class="progress-bar-container">
        <div class="progress-bar">
            <div class="progress-fill" style="width: <?= ($coursValides > 0 && ($coursValides + $coursNonValides) > 0) ? round(($coursValides / ($coursValides + $coursNonValides)) * 100) : 0 ?>%"></div>
        </div>
        <p class="progress-text">
            <?= ($coursValides > 0 && ($coursValides + $coursNonValides) > 0) ? round(($coursValides / ($coursValides + $coursNonValides)) * 100) : 0 ?>% de réussite
        </p>
    </div>
</div>

<?php if (!empty($validations)): ?>
    <div class="table-responsive">
        <table class="semester-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Cours</th>
                    <th>Crédits</th>
                    <th>Interro</th>
                    <th>TP</th>
                    <th>Examen</th>
                    <th>Moyenne</th>
                    <th>Résultat</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($validations as $validation): ?>
                    <tr class="<?= $validation['statut'] === 'Validé' ? 'row-success' : 'row-danger' ?>">
                        <td class="code-cell">
                            <strong><?= htmlspecialchars($validation['code'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong>
                        </td>
                        <td>
                            <?= htmlspecialchars($validation['nom'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="text-center">
                            <span class="credit-badge"><?= $validation['credit'] ?? 0 ?></span>
                        </td>
                        <td class="text-center"><?= $validation['interro'] ?? 0 ?></td>
                        <td class="text-center"><?= $validation['tp'] ?? 0 ?></td>
                        <td class="text-center"><?= $validation['examen'] ?? 0 ?></td>
                        <td class="text-center">
                            <strong class="moyenne">
                                <?= round(($validation['interro'] + $validation['tp'] + $validation['examen']) / 3, 2) ?>/20
                            </strong>
                        </td>
                        <td class="text-center">
                            <?php if ($validation['statut'] === 'Validé'): ?>
                                <span class="badge badge-success">
                                    <i class="fa-solid fa-check-circle"></i> Validé
                                </span>
                            <?php else: ?>
                                <span class="badge badge-danger">
                                    <i class="fa-solid fa-times-circle"></i> Non Validé
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="no-data">
        <p><i class="fa-solid fa-folder-open"></i> Aucun cours pour ce semestre</p>
    </div>
<?php endif; ?>

<style>
.semester-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: #001A72;
    color: white;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.header-content h2 {
    margin: 0 0 1rem 0;
    font-size: 2rem;
}

.student-name {
    margin: 0.5rem 0;
    font-size: 1.1rem;
    font-weight: 500;
}

.student-info {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

.semester-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-item {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: #667eea;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
    font-weight: 500;
}

.progress-bar-container {
    grid-column: 1 / -1;
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.progress-bar {
    width: 100%;
    height: 25px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.progress-fill {
    height: 100%;
    background: #059669;
    transition: width 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
}

.progress-text {
    margin: 0;
    text-align: center;
    color: #666;
    font-weight: 500;
}

.table-responsive {
    overflow-x: auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.semester-table {
    width: 100%;
    border-collapse: collapse;
}

.semester-table thead {
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
}

.semester-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #495057;
    white-space: nowrap;
}

.semester-table tbody tr {
    border-bottom: 1px solid #dee2e6;
    transition: background-color 0.2s;
}

.semester-table tbody tr:hover {
    background-color: #f8f9fa;
}

.semester-table td {
    padding: 1rem;
}

.text-center {
    text-align: center;
}

.code-cell {
    font-weight: 600;
    color: #667eea;
}

.credit-badge {
    display: inline-block;
    color: #0066cc;
    font-weight: 600;
}

.moyenne {
    color: #333;
    font-size: 1.1rem;
}

.row-success {
    background-color: #f1f8f4;
}

.row-success .moyenne {
    color: #28a745;
}

.row-danger {
    background-color: #fef5f4;
}

.row-danger .moyenne {
    color: #dc3545;
}

.badge {
    display: inline-block;
    font-weight: 600;
    font-size: 0.85rem;
}

.badge-success {
    color: #155724;
}

.badge-danger {
    color: #721c24;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
}

.btn-secondary {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
}

.btn-secondary:hover {
    background-color: rgba(255, 255, 255, 0.3);
}

.no-data {
    background: white;
    border-radius: 6px;
    padding: 3rem 1rem;
    text-align: center;
    color: #64748b;
    border: 1px solid #e2e8f0;
}

@media (max-width: 768px) {
    .semester-header {
        flex-direction: column;
        gap: 1.5rem;
    }

    .semester-table {
        font-size: 0.9rem;
    }

    .semester-table th,
    .semester-table td {
        padding: 0.75rem;
    }

    .semester-stats {
        grid-template-columns: 1fr 1fr;
    }
}
</style>
