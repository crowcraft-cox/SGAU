<?php include APP_ROOT . '/app/Views/Layouts/header.php'; ?>
<?php include APP_ROOT . '/app/Views/Layouts/navbar.php'; ?>

<style>
    .main-content { padding: 20px; }
    .table-hover tbody tr:hover {
        background-color: #f1f8ff;
    }
    .groupe-row {
        font-weight: bold;
        background-color: #f8f9fa;
    }
    .indent-1 { padding-left: 20px !important; }
    .indent-2 { padding-left: 40px !important; }
    .indent-3 { padding-left: 60px !important; }
</style>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-list-alt text-primary"></i> Plan Comptable (Syscohada)</h2>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                <i class="fas fa-plus"></i> Ajouter un compte
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle border">
                    <thead class="table-light">
                        <tr>
                            <th width="15%">Numéro</th>
                            <th width="50%">Intitulé du compte</th>
                            <th width="20%">Type</th>
                            <th width="15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Simple affichage hiérarchique plat pour le moment
                        foreach ($comptes as $c): 
                            $indentClass = '';
                            $len = strlen($c['numero']);
                            if ($len == 2) $indentClass = 'indent-1';
                            if ($len == 3) $indentClass = 'indent-2';
                            if ($len >= 4) $indentClass = 'indent-3';
                            $isGroup = $c['is_groupe'];
                        ?>
                        <tr class="<?= $isGroup ? 'groupe-row' : '' ?>">
                            <td class="<?= $indentClass ?>">
                                <?= $isGroup ? '<i class="fas fa-folder text-warning me-2"></i>' : '<i class="fas fa-file-alt text-secondary me-2"></i>' ?>
                                <?= htmlspecialchars($c['numero']) ?>
                            </td>
                            <td><?= htmlspecialchars($c['libelle']) ?></td>
                            <td><span style="font-weight:600; color:#475569;"><?= htmlspecialchars($c['type_compte']) ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-light" title="Modifier"><i class="fas fa-edit"></i></button>
                                <?php if (!$isGroup): ?>
                                    <button class="btn btn-sm btn-light text-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($comptes)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Aucun compte trouvé</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Account Modal Placeholder -->
<div class="modal fade" id="addAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Compte</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Fonctionnalité d'ajout manuel à implémenter selon les besoins du comptable.</p>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/Views/Layouts/footer.php'; ?>
