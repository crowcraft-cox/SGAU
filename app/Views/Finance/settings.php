<?php include APP_ROOT . '/app/Views/Layouts/header.php'; ?>
<?php include APP_ROOT . '/app/Views/Layouts/navbar.php'; ?>

<div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-cog text-primary"></i> Paramètres Financiers</h2>
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

        <div class="card shadow-sm border-0" style="max-width: 800px;">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-money-bill-transfer text-primary me-2"></i> Configuration des paiements</h5>
            </div>
            <div class="card-body p-4">
                <form action="<?= base_url('/finance/update-settings') ?>" method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Taux de change (1 USD = ? FC)</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-exchange-alt"></i></span>
                            <input type="number" class="form-control" name="taux_change_usd_fc" value="<?= htmlspecialchars(settings('taux_change_usd_fc', '2850')) ?>" required>
                            <span class="input-group-text">Francs Congolais</span>
                        </div>
                        <div class="form-text">Ce taux sera appliqué à toutes les conversions dans le système.</div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h5 class="fw-bold mb-3"><i class="fas fa-mobile-alt me-2"></i> Numéros de réception Mobile Money</h5>
                    <p class="text-muted small mb-4">Ces numéros seront affichés aux étudiants lorsqu'ils choisiront de payer via Mobile Money.</p>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #ff6600;">Orange Money</label>
                            <input type="text" class="form-control" name="mobile_money_orange" value="<?= htmlspecialchars(settings('mobile_money_orange', '+243000000000')) ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #e60000;">Airtel Money</label>
                            <input type="text" class="form-control" name="mobile_money_airtel" value="<?= htmlspecialchars(settings('mobile_money_airtel', '+243000000000')) ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold" style="color: #009900;">M-Pesa (Vodacom)</label>
                            <input type="text" class="form-control" name="mobile_money_mpesa" value="<?= htmlspecialchars(settings('mobile_money_mpesa', '+243000000000')) ?>">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Enregistrer les paramètres</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

<?php include APP_ROOT . '/app/Views/Layouts/footer.php'; ?>
