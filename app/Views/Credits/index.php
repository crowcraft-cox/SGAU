<div class="header-section">
    <div class="header-left">
        <h2>Gestion des Crédits</h2>
    </div>
    <div class="header-right">
        <div class="filter-section">
            <select id="verification-filter" class="form-select" onchange="filterCourses()">
                <option value="">Vérification</option>
                <option value="all">Tous les cours</option>
                <option value="verified">Cours vérifiés</option>
                <option value="pending">En attente</option>
            </select>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success" style="padding: 15px; border-radius: 5px; margin-bottom: 20px; background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; display: flex; align-items: center; gap: 10px; font-weight: 500;">
        <i class="fas fa-check-circle" style="font-size: 18px;"></i>
        <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (empty($credits)): ?>
    <div class="empty-state">
        <p>Aucun cours trouvé.</p>
    </div>
<?php else: ?>
    <form method="POST" action="<?= base_url('/credits/update-bulk') ?>" class="credits-form">
        <?= csrf_field() ?>
        <div class="table-container">
            <table class="table table-modern">
                <thead>
                    <tr>
                        <th>Cours</th>
                        <th>Filière</th>
                        <th>Niveau</th>
                        <th>Semestre</th>
                        <th>Crédits</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($credits as $credit): ?>
                        <tr class="credit-row" data-credit-id="<?= $credit['id'] ?>">
                            <td class="course-name">
                                <strong><?= htmlspecialchars($credit['cours_nom'] ?? $credit['nom'] ?? '-', ENT_QUOTES, 'UTF-8') ?></strong>
                                <br>
                                <small><?= htmlspecialchars($credit['code'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                            </td>
                            <td class="filiere-name">
                                <?= htmlspecialchars($credit['filiere_nom'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="level-name">
                                <?= htmlspecialchars($credit['niveau'] ?? 'L1', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="semester-name">
                                <?php if (RoleMiddleware::isAdmin()): ?>
                                    <select name="semestre[<?= $credit['id'] ?>]" class="form-select-inline">
                                        <option value="S1" <?= ($credit['semestre'] ?? '') == 'S1' ? 'selected' : '' ?>>S1</option>
                                        <option value="S2" <?= ($credit['semestre'] ?? '') == 'S2' ? 'selected' : '' ?>>S2</option>
                                        <option value="S3" <?= ($credit['semestre'] ?? '') == 'S3' ? 'selected' : '' ?>>S3</option>
                                        <option value="S4" <?= ($credit['semestre'] ?? '') == 'S4' ? 'selected' : '' ?>>S4</option>
                                        <option value="S5" <?= ($credit['semestre'] ?? '') == 'S5' ? 'selected' : '' ?>>S5</option>
                                        <option value="S6" <?= ($credit['semestre'] ?? '') == 'S6' ? 'selected' : '' ?>>S6</option>
                                    </select>
                                <?php else: ?>
                                    <?= htmlspecialchars($credit['semestre'] ?? 'S1', ENT_QUOTES, 'UTF-8') ?>
                                <?php endif; ?>
                            </td>
                            <td class="credits-input">
                                <?php if (RoleMiddleware::isAdmin()): ?>
                                    <select name="credits[<?= $credit['id'] ?>]" class="form-select-inline">
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($credit['credit'] ?? $credit['credits'] ?? '') == $i ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                <?php else: ?>
                                    <strong><?= htmlspecialchars($credit['credit'] ?? '0', ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (RoleMiddleware::isAdmin()): ?>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </div>
        <?php endif; ?>
    </form>
<?php endif; ?>

<style>
.header-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 0 5px;
}

.header-left h2 {
    margin: 0;
    color: #333;
    font-size: 28px;
}

.header-right {
    display: flex;
    gap: 15px;
    align-items: center;
}

.filter-section {
    display: flex;
    gap: 10px;
}

.form-select {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: white;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    cursor: pointer;
    transition: all 0.2s;
    min-width: 180px;
}

.form-select:focus {
    outline: none;
    border-color: #0056b3;
    box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
}

.table-container {
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
}

.table-modern {
    width: 100%;
    border-collapse: collapse;
}

.table-modern thead {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
}

.table-modern thead th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.table-modern tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
}

.table-modern tbody tr:hover {
    background-color: #f8f9fa;
}

.credit-row td {
    padding: 15px;
    vertical-align: middle;
}

.course-name {
    min-width: 250px;
}

.course-name small {
    color: #999;
    font-size: 12px;
}

.filiere-name,
.level-name,
.semester-name {
    text-align: center;
    font-weight: 500;
}

.credits-input {
    text-align: center;
    min-width: 100px;
}

.form-select-inline {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f8f9fa;
    font-size: 13px;
    font-family: inherit;
    cursor: pointer;
    transition: all 0.2s;
    width: auto;
    min-width: 80px;
}

.form-select-inline:focus {
    outline: none;
    background-color: white;
    border-color: #0056b3;
    box-shadow: 0 0 0 2px rgba(0, 86, 179, 0.1);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 15px;
    padding: 20px 0;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background-color: #0056b3;
    color: white;
}

.btn-primary:hover {
    background-color: #004085;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}

@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .table-modern {
        font-size: 12px;
    }

    .credit-row td {
        padding: 10px;
    }

    .form-select {
        width: 100%;
    }
}

.credits-form {
    display: contents;
}

@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .table-modern {
        font-size: 12px;
    }

    .credit-row td {
        padding: 10px;
    }

    .form-select {
        width: 100%;
    }
}
</style>

<script>
function filterCourses() {
    const filter = document.getElementById('verification-filter').value;
    const rows = document.querySelectorAll('.credit-row');
    
    rows.forEach(row => {
        if (filter === '' || filter === 'all') {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
