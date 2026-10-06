<?php include APP_ROOT . '/app/Views/Layouts/header.php'; ?>
<?php include APP_ROOT . '/app/Views/Layouts/navbar.php'; ?>

<style>
    .finance-bg {
        background: #f8fafc;
        min-height: calc(100vh - 60px);
        padding: 2.5rem 0;
    }
    .premium-card {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        background: #ffffff;
        overflow: hidden;
    }
    .premium-header {
        background: #001A72;
        color: white;
        padding: 1.5rem 2rem;
    }
    .stat-card {
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    .table-hover tbody tr:hover {
        background-color: #f8fafc;
    }
</style>

<div class="finance-bg">
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-wallet me-2 text-primary"></i> Mon Bilan Financier</h2>
                <p class="text-muted mb-0">Consultez l'historique de vos paiements et votre situation financière</p>
            </div>
            <button class="btn btn-primary" style="border-radius: 10px;" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Imprimer
            </button>
        </div>

        <div class="row mb-5 g-4">
            <!-- Stat Card 1 -->
            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase fw-bold small">Total Validé</p>
                            <h3 class="mb-0 text-success fw-bold"><?= format_currency($totalValide) ?></h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success">
                            <i class="fas fa-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Stat Card 2 -->
            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase fw-bold small">Paiements En Attente</p>
                            <h3 class="mb-0 text-warning fw-bold"><?= format_currency($totalEnAttente) ?></h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning">
                            <i class="fas fa-hourglass-half fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Stat Card 3 -->
            <div class="col-md-4">
                <div class="card stat-card bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 text-uppercase fw-bold small">Paiements Refusés</p>
                            <h3 class="mb-0 text-danger fw-bold"><?= format_currency($totalRefuse) ?></h3>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded-circle text-danger">
                            <i class="fas fa-times-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableau des paiements -->
        <div class="premium-card">
            <div class="premium-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i> Historique de mes paiements</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted">
                            <tr>
                                <th class="border-0 ps-4 py-3">Réf Transaction</th>
                                <th class="border-0 py-3">Date</th>
                                <th class="border-0 py-3">Montant</th>
                                <th class="border-0 py-3">Moyen</th>
                                <th class="border-0 py-3">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($paiements)): ?>
                                <?php foreach ($paiements as $p): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($p['id']) ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-medium"><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></span>
                                        </td>
                                        <td>
                                            <strong class="text-primary"><?= format_currency($p['montant']) ?></strong>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($p['moyen_paiement'] ?? 'Espèces') ?>
                                        </td>
                                        <td>
                                            <?php if ($p['statut'] === 'Validé'): ?>
                                                <strong style="color:#15803d;"><i class="fas fa-check me-1"></i> Validé</strong>
                                            <?php elseif ($p['statut'] === 'En attente'): ?>
                                                <strong style="color:#b45309;"><i class="fas fa-clock me-1"></i> En attente</strong>
                                            <?php else: ?>
                                                <strong style="color:#b91c1c;"><i class="fas fa-times me-1"></i> <?= htmlspecialchars($p['statut']) ?></strong>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="fas fa-box-open fa-3x mb-3 opacity-25 d-block"></i>
                                        <p class="mb-0 fs-5">Aucun paiement enregistré pour le moment.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php include APP_ROOT . '/app/Views/Layouts/footer.php'; ?>
