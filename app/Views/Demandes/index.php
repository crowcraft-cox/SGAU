<div class="demandes-page-container">
    <div class="page-title-modern">
        <div>
            <h2>
                <i class="fas fa-file-signature text-primary"></i> 
                <?= $userRole === 'admin' ? 'Secrétariat Général Académique - Demandes de Dérogation' : 'Mes Demandes de Modification de Cotes' ?>
            </h2>
            <p class="subtitle">
                <?= $userRole === 'admin' 
                    ? 'Examinez les demandes de modification de cotes soumises par les enseignants et accordez les dérogations nécessaires.' 
                    : 'Suivez l\'état de vos demandes de modification de cotes adressées au Secrétariat Général Académique.' ?>
            </p>
        </div>
        <div class="header-stats">
            <?php if ($userRole === 'admin'): ?>
                <span class="badge-pending-total">
                    <i class="fas fa-hourglass-half"></i> <?= $pendingCount ?> demande(s) en attente
                </span>
            <?php else: ?>
                <a href="<?= base_url('/notes') ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Nouvelle demande (depuis un cours)
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Messages Flash -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Onglets de filtre -->
    <div class="filter-tabs-bar">
        <a href="<?= base_url('/demandes-modification-notes') ?>" class="tab-link <?= empty($statut) ? 'active' : '' ?>">
            Toutes les demandes (<?= count($demandes) ?>)
        </a>
        <a href="<?= base_url('/demandes-modification-notes?statut=en_attente') ?>" class="tab-link <?= $statut === 'en_attente' ? 'active' : '' ?>">
            <i class="fas fa-clock text-warning"></i> En attente
        </a>
        <a href="<?= base_url('/demandes-modification-notes?statut=approuvee') ?>" class="tab-link <?= $statut === 'approuvee' ? 'active' : '' ?>">
            <i class="fas fa-check-circle text-success"></i> Approuvées
        </a>
        <a href="<?= base_url('/demandes-modification-notes?statut=rejetee') ?>" class="tab-link <?= $statut === 'rejetee' ? 'active' : '' ?>">
            <i class="fas fa-times-circle text-danger"></i> Rejetées
        </a>
    </div>

    <?php if (empty($demandes)): ?>
        <div class="empty-state-card">
            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
            <h3>Aucune demande trouvée</h3>
            <p class="text-muted">
                <?= empty($statut) ? 'Aucune demande de dérogation enregistrée.' : 'Aucune demande avec ce statut.' ?>
            </p>
        </div>
    <?php else: ?>
        <div class="table-container-modern">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th width="130">Date</th>
                        <?php if ($userRole === 'admin'): ?>
                            <th>Enseignant</th>
                        <?php endif; ?>
                        <th>Cours</th>
                        <th>Étudiant</th>
                        <th>Motif de la demande</th>
                        <th class="text-center" width="130">Statut</th>
                        <?php if ($userRole === 'admin'): ?>
                            <th class="text-right" width="200">Actions SGA</th>
                        <?php else: ?>
                            <th>Décision SGA</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($demandes as $demande): ?>
                        <tr>
                            <td>
                                <span class="date-badge">
                                    <i class="far fa-calendar-alt"></i> <?= date('d/m/Y H:i', strtotime($demande['created_at'])) ?>
                                </span>
                            </td>
                            <?php if ($userRole === 'admin'): ?>
                                <td>
                                    <strong><?= htmlspecialchars(trim(($demande['enseignant_nom'] ?? '') . ' ' . ($demande['enseignant_prenom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <small class="text-muted d-block"><?= htmlspecialchars($demande['enseignant_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                                </td>
                            <?php endif; ?>
                            <td>
                                <strong class="course-name-text"><?= htmlspecialchars($demande['cours_nom'] ?? 'Cours', ENT_QUOTES, 'UTF-8') ?></strong>
                                <span class="badge-code-sm"><?= htmlspecialchars($demande['cours_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <div class="student-info-cell">
                                    <strong><?= htmlspecialchars(trim(($demande['etudiant_nom'] ?? '') . ' ' . ($demande['etudiant_prenom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span class="badge-mat"><?= htmlspecialchars($demande['etudiant_matricule'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="motif-box">
                                    <?= nl2br(htmlspecialchars($demande['motif'], ENT_QUOTES, 'UTF-8')) ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($demande['statut'] === 'en_attente'): ?>
                                    <span class="badge-statut badge-pending">
                                        <i class="fas fa-hourglass-half"></i> En attente
                                    </span>
                                <?php elseif ($demande['statut'] === 'approuvee'): ?>
                                    <span class="badge-statut badge-approved">
                                        <i class="fas fa-check-circle"></i> Approuvée
                                    </span>
                                <?php else: ?>
                                    <span class="badge-statut badge-rejected">
                                        <i class="fas fa-times-circle"></i> Rejetée
                                    </span>
                                <?php endif; ?>
                            </td>
                            <?php if ($userRole === 'admin'): ?>
                                <td class="text-right">
                                    <?php if ($demande['statut'] === 'en_attente'): ?>
                                            <button type="button" 
                                                    class="btn btn-success btn-xs" 
                                                    data-demande-id="<?= (int)$demande['id'] ?>"
                                                    data-student-name="<?= htmlspecialchars(trim(($demande['etudiant_nom'] ?? '') . ' ' . ($demande['etudiant_prenom'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick="openApproveModalFromBtn(this)">
                                                <i class="fas fa-check"></i> Valider
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger btn-xs" 
                                                    data-demande-id="<?= (int)$demande['id'] ?>"
                                                    data-student-name="<?= htmlspecialchars(trim(($demande['etudiant_nom'] ?? '') . ' ' . ($demande['etudiant_prenom'] ?? '')), ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick="openRejectModalFromBtn(this)">
                                                <i class="fas fa-times"></i> Rejeter
                                            </button>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted">
                                            Traité le <?= !empty($demande['traite_le']) ? date('d/m/Y', strtotime($demande['traite_le'])) : '-' ?>
                                            <?php if (!empty($demande['traite_par_nom'])): ?>
                                                par <?= htmlspecialchars($demande['traite_par_nom'], ENT_QUOTES, 'UTF-8') ?>
                                            <?php endif; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                            <?php else: ?>
                                <td>
                                    <?php if (!empty($demande['reponse_sga'])): ?>
                                        <div class="sga-reply-box">
                                            <i class="fas fa-reply text-muted"></i> <?= htmlspecialchars($demande['reponse_sga'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php elseif ($demande['statut'] === 'approuvee'): ?>
                                        <span class="text-success font-weight-500"><i class="fas fa-unlock"></i> Dérogation active dans la fiche de cotes</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL APPROBATION (SGA) -->
<div id="approveModal" class="custom-modal-backdrop" style="display: none;">
    <div class="custom-modal-card">
        <div class="modal-header-box">
            <h3><i class="fas fa-check-circle text-success"></i> Accorder la dérogation de modification</h3>
            <button type="button" class="btn-close-modal" onclick="closeApproveModal()">&times;</button>
        </div>
        <form id="approveForm" method="POST" action="">
            <?= csrf_field() ?>
            <div class="modal-body-box">
                <p>Vous êtes sur le point d'autoriser l'enseignant à modifier les cotes de l'étudiant <strong id="approveStudentName"></strong>.</p>
                <div class="form-group">
                    <label for="reponse_sga_appr">Remarque / Autorisation SGA (facultatif) :</label>
                    <textarea name="reponse_sga" id="reponse_sga_appr" rows="3" class="form-control" placeholder="Ex: Dérogation accordée suite à vérification de la pièce justificative..."></textarea>
                </div>
            </div>
            <div class="modal-footer-box">
                <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">Annuler</button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-unlock"></i> Confirmer & Débloquer la note
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL REJET (SGA) -->
<div id="rejectModal" class="custom-modal-backdrop" style="display: none;">
    <div class="custom-modal-card">
        <div class="modal-header-box">
            <h3><i class="fas fa-times-circle text-danger"></i> Rejeter la demande de dérogation</h3>
            <button type="button" class="btn-close-modal" onclick="closeRejectModal()">&times;</button>
        </div>
        <form id="rejectForm" method="POST" action="">
            <?= csrf_field() ?>
            <div class="modal-body-box">
                <p>Vous refusez la modification de cote pour l'étudiant <strong id="rejectStudentName"></strong>.</p>
                <div class="form-group">
                    <label for="reponse_sga_rej">Motif du rejet (obligatoire) :</label>
                    <textarea name="reponse_sga" id="reponse_sga_rej" rows="3" required class="form-control" placeholder="Précisez la raison du refus (ex: délai dépassé, motif insuffisant...)"></textarea>
                </div>
            </div>
            <div class="modal-footer-box">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Annuler</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-ban"></i> Confirmer le Rejet
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.demandes-page-container {
    padding: 10px 0 40px 0;
}

.page-title-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 15px;
}

.page-title-modern h2 {
    margin: 0 0 4px 0;
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}

.page-title-modern .subtitle {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.badge-pending-total {
    color: #b45309;
    font-weight: 700;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.filter-tabs-bar {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.tab-link {
    padding: 8px 16px;
    border-radius: 8px;
    background: white;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.tab-link:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.tab-link.active {
    background: #0056b3;
    color: white;
    border-color: #0056b3;
}

.tab-link.active i {
    color: white !important;
}

.table-container-modern {
    background: transparent;
    border-radius: 8px;
    box-shadow: none;
    border: 1px solid #e2e8f0;
    overflow-x: auto;
}

.table-modern {
    width: 100%;
    border-collapse: collapse;
}

.table-modern thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    font-size: 13px;
    padding: 14px 16px;
    border-bottom: 2px solid #e2e8f0;
}

.table-modern tbody td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13.5px;
}

.date-badge {
    font-size: 12px;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.course-name-text {
    color: #0f172a;
    display: block;
}

.badge-code-sm {
    background: #e0f2fe;
    color: #0369a1;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: monospace;
    font-size: 11px;
}

.student-info-cell {
    display: flex;
    flex-direction: column;
}

.badge-mat {
    font-size: 11.5px;
    color: #64748b;
    font-family: monospace;
}

.cell-motif {
    font-size: 12.5px;
    color: #334155;
    max-width: 320px;
    line-height: 1.4;
}

.badge-statut {
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge-pending {
    color: #b45309;
}

.badge-approved {
    color: #15803d;
}

.badge-rejected {
    color: #b91c1c;
}

.sga-actions-group {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}

.sga-reply-box {
    font-size: 12px;
    color: #475569;
    background: #f1f5f9;
    padding: 6px 10px;
    border-radius: 6px;
}

/* Modales */
.custom-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.6);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}

.custom-modal-card {
    background: white;
    width: 90%;
    max-width: 520px;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    animation: modalIn 0.2s ease-out;
}

.modal-header-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.modal-header-box h3 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-close-modal {
    background: none;
    border: none;
    font-size: 24px;
    color: #94a3b8;
    cursor: pointer;
}

.modal-body-box {
    padding: 20px 24px;
}

.modal-footer-box {
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<script>
function openApproveModalFromBtn(btn) {
    if (!btn) return;
    const id = btn.getAttribute('data-demande-id');
    const name = btn.getAttribute('data-student-name') || '';
    openApproveModal(id, name);
}

function openApproveModal(demandeId, studentName) {
    document.getElementById('approveStudentName').textContent = studentName;
    document.getElementById('approveForm').action = '<?= base_url('/demandes-modification-notes/') ?>' + demandeId + '/approuver';
    document.getElementById('approveModal').style.display = 'flex';
}

function closeApproveModal() {
    document.getElementById('approveModal').style.display = 'none';
}

function openRejectModalFromBtn(btn) {
    if (!btn) return;
    const id = btn.getAttribute('data-demande-id');
    const name = btn.getAttribute('data-student-name') || '';
    openRejectModal(id, name);
}

function openRejectModal(demandeId, studentName) {
    document.getElementById('rejectStudentName').textContent = studentName;
    document.getElementById('rejectForm').action = '<?= base_url('/demandes-modification-notes/') ?>' + demandeId + '/rejeter';
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}
</script>
