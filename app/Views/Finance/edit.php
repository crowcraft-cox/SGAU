<?php include APP_ROOT . '/app/Views/Layouts/header.php'; ?>
<?php include APP_ROOT . '/app/Views/Layouts/navbar.php'; ?>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit text-warning"></i> Modifier un Paiement</h2>
        <a href="<?= base_url('/finance') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Formulaire -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="<?= base_url('/finance/' . $paiement['id'] . '/update') ?>" method="POST">
                        <?= csrf_field() ?>
                        
                        <!-- Étudiant -->
                        <div class="mb-3">
                            <label for="etudiant_id" class="form-label">Étudiant <span class="text-danger">*</span></label>
                            <select class="form-select" id="etudiant_id" name="etudiant_id" required>
                                <option value="">-- Sélectionner un étudiant --</option>
                                <?php foreach ($etudiants as $etudiant): ?>
                                    <option value="<?= htmlspecialchars($etudiant['id']) ?>"
                                        <?= $etudiant['id'] == $paiement['etudiant_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($etudiant['matricule']) ?> - <?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Montant -->
                        <div class="mb-3">
                            <label for="montant" class="form-label">Montant (USD) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="montant" name="montant" 
                                   value="<?= htmlspecialchars($paiement['montant']) ?>" step="0.01" min="1" required>
                        </div>

                        <!-- Date de paiement -->
                        <div class="mb-3">
                            <label for="date_paiement" class="form-label">Date de Paiement <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="date_paiement" name="date_paiement" 
                                   value="<?= htmlspecialchars($paiement['date_paiement']) ?>" required>
                        </div>

                        <!-- Statut (lecture seule) -->
                        <div class="mb-3">
                            <label for="statut" class="form-label">Statut</label>
                            <input type="text" class="form-control" id="statut" 
                                   value="<?= htmlspecialchars($paiement['statut']) ?>" disabled>
                            <small class="text-muted">Le statut est géré automatiquement lors de la validation ou via Mobile Money.</small>
                        </div>

                        <!-- Boutons -->
                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Mettre à jour
                            </button>
                            <a href="<?= base_url('/finance') ?>" class="btn btn-outline-secondary">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/Views/Layouts/footer.php'; ?>
