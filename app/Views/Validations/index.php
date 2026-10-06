<?php
$pageTitle = 'Gestion des Validations';
?>

<div class="header-section">
    <div class="header-left">
        <h2>Gestion des Validations</h2>
        <p>Suivi des cours validés et non validés par semestre</p>
    </div>
    <div class="header-right">
        <span class="badge-info">Total: <?= count($students) ?? 0 ?> Étudiants</span>
    </div>
</div>

<div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Étudiant</th>
                <th>Filière</th>
                <th>Niveau</th>
                <th>Total Cours</th>
                <th> Validés</th>
                <th> Non Validés</th>
                <th>Crédits Acquis</th>
                <th>Crédits Requis</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($students)): ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($student['matricule'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></strong></td>
                        <td>
                            <?= htmlspecialchars($student['nom'] . ' ' . $student['prenom'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td><?= htmlspecialchars($student['filiere_nom'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <span class="badge badge-level">
                                <?= htmlspecialchars($student['niveau'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td><strong><?= $student['total_cours'] ?? 0 ?></strong></td>
                        <td>
                            <span class="badge badge-success">
                                <?= $student['cours_valides'] ?? 0 ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-danger">
                                <?= $student['cours_non_valides'] ?? 0 ?>
                            </span>
                        </td>
                        <td>
                            <strong><?= $student['credits_acquis'] ?? 0 ?></strong>
                        </td>
                        <td>
                            <?= $student['credits_requis'] ?? 0 ?>
                        </td>
                        <td>
                            <a href="<?= base_url('/validations/' . $student['id']) ?>" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-eye"></i> Détails
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="text-center">
                        <p class="no-data"><i class="fa-solid fa-folder-open"></i> Aucun étudiant trouvé</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.validations-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #001A72;
    color: white;
    border-radius: 8px;
}

.header-left h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.8rem;
}

.header-left p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

.header-right {
    text-align: right;
}

.badge-info {
    display: inline-block;
    font-weight: 600;
}

.table-responsive {
    overflow-x: auto;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}

.data-table thead {
    background: #001A72;
    border-bottom: 3px solid #003366;
    color: white;
}

.data-table thead th {
    padding: 1rem;
    text-align: left;
    font-weight: 700;
    color: white;
    white-space: nowrap;
}

.data-table tbody tr {
    border-bottom: 1px solid #e0e0e0;
    transition: background-color 0.2s;
}

.data-table tbody tr:hover {
    background-color: #f0f5ff;
}

.data-table td {
    padding: 1rem;
}

.badge {
    display: inline-block;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-success {
    color: #155724;
}

.badge-danger {
    color: #721c24;
}

.badge-info {
    color: #084298;
}

.badge-level {
    color: #28a745;
    font-weight: 700;
}

.btn {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.2s;
    cursor: pointer;
    border: none;
}

.btn-primary {
    background-color: #001A72;
    color: white;
}

.btn-primary:hover {
    background-color: #00145a;
}

.btn-sm {
    padding: 0.35rem 0.65rem;
    font-size: 0.85rem;
}

.text-center {
    text-align: center;
}

.no-data {
    color: #6c757d;
    margin: 2rem 0;
}

@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        align-items: flex-start;
    }

    .header-right {
        margin-top: 1rem;
        text-align: left;
    }

    .data-table {
        font-size: 0.9rem;
    }

    .data-table th,
    .data-table td {
        padding: 0.75rem;
    }
}
</style>
