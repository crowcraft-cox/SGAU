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
    .form-control, .form-select {
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 0.6rem 0.9rem;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        background-color: #ffffff;
    }
    .form-control:focus, .form-select:focus {
        border-color: #001A72;
        box-shadow: 0 0 0 2px rgba(0, 26, 114, 0.1);
        background-color: #ffffff;
    }
    .input-group-text {
        border-radius: 6px 0 0 6px;
        border: 1px solid #cbd5e1;
        background-color: #f8fafc;
    }
    .form-control.border-start-0, .form-select.border-start-0 {
        border-radius: 0 6px 6px 0;
    }
    .btn-premium {
        background: #001A72;
        border: 1px solid #001A72;
        color: #ffffff;
        font-weight: 600;
        border-radius: 6px;
        padding: 0.6rem 1.2rem;
        transition: background-color 0.15s ease;
    }
    .btn-premium:hover {
        background: #00145a;
        border-color: #00145a;
        color: #ffffff;
    }
    .student-info-card {
        background: #f8fafc;
        border-radius: 6px;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
    }
    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .stat-item:last-child {
        border-bottom: none;
    }
</style>

<div class="finance-bg">
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold mb-1" style="color: #1e293b;"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i> Nouveau Paiement</h2>
                <p class="text-muted mb-0">Enregistrez et validez une transaction financière manuellement</p>
            </div>
            <a href="<?= base_url('/finance') ?>" class="btn btn-outline-dark" style="border-radius: 10px; padding: 0.6rem 1.2rem; font-weight: 500;">
                <i class="fas fa-arrow-left me-2"></i> Retour à la liste
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 py-3 mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle fs-4 me-3 text-danger"></i>
                    <div><?= htmlspecialchars($_SESSION['error']) ?></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Formulaire -->
            <div class="col-lg-7">
                <div class="premium-card h-100">
                    <div class="premium-header">
                        <h5 class="mb-0"><i class="fas fa-pen-nib me-2"></i> Détails de la transaction</h5>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <form action="<?= base_url('/finance/store') ?>" method="POST">
                            <?= csrf_field() ?>
                            
                            <!-- Étudiant -->
                            <div class="mb-4">
                                <label for="etudiant_id" class="form-label fw-bold text-secondary small text-uppercase letter-spacing-1">Étudiant cible <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text text-primary"><i class="fas fa-user-graduate"></i></span>
                                    <select class="form-select border-start-0 ps-0" id="etudiant_id" name="etudiant_id" required onchange="updateStudentInfo()">
                                        <option value="">-- Sélectionnez un étudiant --</option>
                                        <?php foreach ($etudiants as $etudiant): ?>
                                            <option value="<?= htmlspecialchars($etudiant['id']) ?>" 
                                                    data-nom="<?= htmlspecialchars($etudiant['nom']) ?>" 
                                                    data-prenom="<?= htmlspecialchars($etudiant['prenom']) ?>">
                                                <?= htmlspecialchars($etudiant['matricule']) ?> - <?= htmlspecialchars($etudiant['prenom'] . ' ' . $etudiant['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Montant -->
                                <div class="col-md-6 mb-4">
                                    <label for="montant" class="form-label fw-bold text-secondary small text-uppercase letter-spacing-1">Montant (USD) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text text-success"><i class="fas fa-dollar-sign"></i></span>
                                        <input type="number" class="form-control border-start-0 ps-0 fw-bold" id="montant" name="montant" 
                                            placeholder="0.00" step="0.01" min="1" required>
                                    </div>
                                </div>

                                <!-- Date de paiement -->
                                <div class="col-md-6 mb-4">
                                    <label for="date_paiement" class="form-label fw-bold text-secondary small text-uppercase letter-spacing-1">Date du Paiement <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text text-secondary"><i class="fas fa-calendar-alt"></i></span>
                                        <input type="date" class="form-control border-start-0 ps-0 text-muted" id="date_paiement" name="date_paiement" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Boutons -->
                            <div class="mt-4 pt-2">
                                <button type="submit" class="btn btn-premium w-100 fs-6">
                                    <i class="fas fa-check-circle me-2"></i> Valider et Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Informations étudiant (Aperçu) -->
            <div class="col-lg-5">
                <div class="premium-card h-100">
                    <div class="card-body p-4 p-md-5">
                        <div class="d-flex align-items-center mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                <i class="fas fa-chart-pie text-primary fs-4"></i>
                            </div>
                            <h5 class="mb-0 fw-bold" style="color: #1e293b;">Aperçu Financier</h5>
                        </div>
                        
                        <div id="studentInfo" class="student-info-card">
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-hand-pointer fa-2x mb-3 text-secondary opacity-50"></i>
                                <p class="mb-0">Sélectionnez un étudiant dans la liste pour consulter instantanément son bilan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function formatCurrency(amount) {
    const amt = parseFloat(amount);
    const usd = amt.toFixed(2);
    const fc = (amt * 2850).toLocaleString('fr-FR', {maximumFractionDigits: 0}).replace(/\s/g, ' ');
    return `<span class="fw-bold fs-5 text-dark">$${usd}</span> <span class="text-muted small ms-1">(${fc} FC)</span>`;
}

// Initialise la date du jour
document.getElementById('date_paiement').valueAsDate = new Date();

function updateStudentInfo() {
    const select = document.getElementById('etudiant_id');
    const option = select.options[select.selectedIndex];
    const studentInfo = document.getElementById('studentInfo');
    
    if (!select.value) {
        studentInfo.innerHTML = `
            <div class="text-center py-5 text-muted">
                <i class="fas fa-hand-pointer fa-2x mb-3 text-secondary opacity-50"></i>
                <p class="mb-0">Sélectionnez un étudiant dans la liste pour consulter instantanément son bilan.</p>
            </div>
        `;
        return;
    }
    
    studentInfo.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
            </div>
            <p class="text-muted mt-3 small fw-medium">Récupération des données financières...</p>
        </div>
    `;
    
    const nom = option.getAttribute('data-nom');
    const prenom = option.getAttribute('data-prenom');
    
    fetch(`<?= base_url('/api/finance/solde/') ?>${select.value}`)
        .then(response => response.json())
        .then(result => {
            if (result.success && result.data) {
                const data = result.data;
                studentInfo.innerHTML = `
                    <h6 class="fw-bold mb-4 text-primary d-flex align-items-center">
                        <i class="fas fa-user-circle fs-4 me-2"></i> ${prenom} ${nom}
                    </h6>
                    <div class="stat-item">
                        <span class="text-muted fw-medium"><i class="fas fa-check-circle text-success me-2"></i> Total Validé</span>
                        <div class="text-end">${formatCurrency(data.total_valide)}</div>
                    </div>
                    <div class="stat-item">
                        <span class="text-muted fw-medium"><i class="fas fa-hourglass-half text-warning me-2"></i> En Attente</span>
                        <div class="text-end">${formatCurrency(data.total_en_attente)}</div>
                    </div>
                    <div class="stat-item">
                        <span class="text-muted fw-medium"><i class="fas fa-times-circle text-danger me-2"></i> Refusé</span>
                        <div class="text-end">${formatCurrency(data.total_refuse)}</div>
                    </div>
                `;
            } else {
                studentInfo.innerHTML = '<div class="alert alert-danger mb-0 d-flex align-items-center"><i class="fas fa-exclamation-triangle fs-4 me-3"></i> Données non disponibles</div>';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            studentInfo.innerHTML = '<div class="alert alert-danger mb-0 d-flex align-items-center"><i class="fas fa-wifi fs-4 me-3"></i> Erreur de connexion au serveur</div>';
        });
}
</script>

<?php include APP_ROOT . '/app/Views/Layouts/footer.php'; ?>
