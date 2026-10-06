<?php include APP_ROOT . '/app/Views/Layouts/header.php'; ?>
<?php include APP_ROOT . '/app/Views/Layouts/navbar.php'; ?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-wallet text-primary"></i> Gestion Financière</h2>
        <a href="<?= base_url('/finance/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Enregistrer un Paiement
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Étudiant</th>
                            <th>Montant</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($paiements)): ?>
                            <?php foreach ($paiements as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['id']) ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($p['photo'])): ?>
                                                <img src="<?= base_url('/' . ltrim($p['photo'], '/')) ?>" alt="Photo" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></strong>
                                                <div class="text-muted small"><?= htmlspecialchars($p['matricule']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><strong><?= format_currency($p['montant']) ?></strong></td>
                                    <td><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                                    <td>
                                        <?php if ($p['statut'] === 'Validé'): ?>
                                            <strong style="color:#15803d;">Validé</strong>
                                        <?php elseif ($p['statut'] === 'En attente'): ?>
                                            <strong style="color:#b45309;">En attente</strong>
                                        <?php else: ?>
                                            <strong style="color:#b91c1c;"><?= htmlspecialchars($p['statut']) ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('/finance/' . $p['etudiant_id'] . '/solde') ?>" class="btn btn-sm btn-info text-white" title="Voir le solde et bloquer/débloquer les relevés">
                                            <i class="fas fa-eye"></i> Solde
                                        </a>
                                        <a href="<?= base_url('/finance/' . $p['id'] . '/edit') ?>" class="btn btn-sm btn-warning text-white" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="<?= base_url('/finance/' . $p['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-wallet fa-3x mb-3 d-block opacity-50"></i>
                                    Aucun paiement enregistré pour le moment.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/Views/Layouts/footer.php'; ?>