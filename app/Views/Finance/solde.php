<div class="container-fluid p-4">
    <!-- En-tête de la page -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-wallet text-primary"></i> 
            Solde : <?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom'], ENT_QUOTES, 'UTF-8') ?>
        </h2>
        <div>
            <?php if (isset($etudiant['releve_bloque']) && $etudiant['releve_bloque'] == 1): ?>
                <form action="<?= base_url('/finance/' . $etudiant['id'] . '/debloquer-releve') ?>" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Êtes-vous sûr de vouloir débloquer le relevé de cet étudiant ?');">
                        <i class="fas fa-unlock"></i> Débloquer le relevé
                    </button>
                </form>
            <?php else: ?>
                <form action="<?= base_url('/finance/' . $etudiant['id'] . '/bloquer-releve') ?>" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir bloquer le relevé de cet étudiant ?');">
                        <i class="fas fa-lock"></i> Bloquer le relevé
                    </button>
                </form>
            <?php endif; ?>
            <?php if (RoleMiddleware::isEtudiant()): ?>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-mobile-alt"></i> Payer par Mobile Money
                </button>
            <?php endif; ?>
            <a href="<?= base_url('/finance') ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Cartes de statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-0">Total Validé</p>
                            <h4 class="text-success"><?= format_currency($montantTotal ?? 0) ?></h4>
                        </div>
                        <i class="fas fa-check-circle fa-3x text-success opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-0">En Attente</p>
                            <h4 class="text-warning"><?= format_currency($enAttente) ?></h4>
                        </div>
                        <i class="fas fa-hourglass-half fa-3x text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-0">Montant Refusé</p>
                            <h4 class="text-danger"><?= format_currency($refuse) ?></h4>
                        </div>
                        <i class="fas fa-times-circle fa-3x text-danger opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tableau des paiements -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Historique des Paiements
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($paiements)): ?>
                                <?php foreach ($paiements as $paiement): ?>
                                    <tr>
                                        <td>
                                            <strong><?= date('d/m/Y', strtotime($paiement['date_paiement'])) ?></strong>
                                        </td>
                                        <td>
                                            <strong><?= format_currency($paiement['montant']) ?></strong>
                                        </td>
                                        <td>
                                            <?php 
                                                $statut = $paiement['statut'];
                                                $couleur = '';
                                                
                                                if ($statut === 'Autorisé') {
                                                    $couleur = '#15803d';
                                                } elseif ($statut === 'Refusé') {
                                                    $couleur = '#b91c1c';
                                                } else {
                                                    $couleur = '#b45309';
                                                }
                                            ?>
                                            <strong style="color:<?= $couleur ?>"><?= htmlspecialchars($statut) ?></strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center py-4">
                                        <p class="text-muted">Aucun paiement enregistré</p>
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

<!-- Modal Paiement Mobile Money -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-mobile-alt text-primary"></i> Payer par Mobile Money</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('/payment/initiate') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Opérateur</label>
                        <select name="provider" id="mm_provider" class="form-select" required onchange="updateProviderInfo()">
                            <option value="">Choisir un opérateur...</option>
                            <option value="Orange Money">Orange Money</option>
                            <option value="M-Pesa">M-Pesa</option>
                            <option value="Airtel Money">Airtel Money</option>
                        </select>
                        <div id="provider_info" class="mt-2 small text-muted" style="display:none;">
                            Compte de réception (Université) : <strong id="provider_number" class="text-dark"></strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Numéro de téléphone</label>
                        <input type="text" name="phone" class="form-control" placeholder="Ex: 0812345678" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Montant à payer (USD)</label>
                        <input type="number" name="montant" class="form-control" step="0.01" min="1" required>
                    </div>
                    <div class="alert alert-info py-2 mb-0">
                        <i class="fas fa-info-circle"></i> Une demande de confirmation sera envoyée sur votre téléphone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Confirmer le paiement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const mmNumbers = {
        'Orange Money': '<?= htmlspecialchars(settings('mobile_money_orange', 'Non configuré')) ?>',
        'M-Pesa': '<?= htmlspecialchars(settings('mobile_money_mpesa', 'Non configuré')) ?>',
        'Airtel Money': '<?= htmlspecialchars(settings('mobile_money_airtel', 'Non configuré')) ?>'
    };
    
    function updateProviderInfo() {
        const provider = document.getElementById('mm_provider').value;
        const infoDiv = document.getElementById('provider_info');
        const numberSpan = document.getElementById('provider_number');
        
        if (provider && mmNumbers[provider] !== 'Non configuré' && mmNumbers[provider] !== '') {
            numberSpan.textContent = mmNumbers[provider];
            infoDiv.style.display = 'block';
        } else {
            infoDiv.style.display = 'none';
        }
    }
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}
</style>
