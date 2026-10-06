<div class="enrollment-container">
    <!-- En-tête et Fil d'Ariane -->
    <div class="page-header-enroll">
        <div class="header-breadcrumb">
            <a href="<?= base_url('/cours') ?>"><i class="fas fa-arrow-left"></i> Retour aux Cours</a>
        </div>
        <div class="header-main-action">
            <div class="header-title-block">
                <h2><i class="fas fa-user-plus text-primary"></i> Enrôlement des Étudiants au Cours</h2>
                <p class="subtitle">Gérez la liste des étudiants inscrits et autorisés à être notés pour ce cours.</p>
            </div>
            <div class="header-cta">
                <a href="<?= base_url('/notes/cours/' . $cours['id']) ?>" class="btn btn-success-custom">
                    <i class="fas fa-clipboard-list"></i> Passer à la Fiche de Cotes
                </a>
            </div>
        </div>
    </div>

    <!-- Carte Récapitulative du Cours -->
    <div class="course-summary-card">
        <div class="summary-item">
            <span class="label"><i class="fas fa-hashtag"></i> Code</span>
            <span class="value badge-code"><?= htmlspecialchars($cours['code'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="summary-item main-info">
            <span class="label"><i class="fas fa-book"></i> Intitulé du cours</span>
            <span class="value course-name"><?= htmlspecialchars($cours['nom'] ?? 'Sans titre', ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="summary-item">
            <span class="label"><i class="fas fa-award"></i> Crédits</span>
            <span class="value"><?= htmlspecialchars($cours['credit'] ?? '1', ENT_QUOTES, 'UTF-8') ?> Crédit(s)</span>
        </div>
        <div class="summary-item">
            <span class="label"><i class="fas fa-chalkboard-teacher"></i> Titulaire</span>
            <span class="value"><?= htmlspecialchars(trim(($cours['enseignant_nom'] ?? '') . ' ' . ($cours['enseignant_prenom'] ?? 'Non assigné')), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="summary-item">
            <span class="label"><i class="fas fa-graduation-cap"></i> Filière / Orientation</span>
            <span class="value"><?= htmlspecialchars($cours['orientation_nom'] ?? ($cours['filiere_nom'] ?? 'Générale'), ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="summary-item stat-item">
            <span class="label"><i class="fas fa-users"></i> Étudiants Enrôlés</span>
            <span class="value badge-enrolled-count" id="totalEnrolledBadge"><?= $totalEnroles ?></span>
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

    <!-- Barre d'outils et de Recherche -->
    <div class="toolbar-card">
        <div class="search-box">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="studentSearchInput" placeholder="Rechercher par nom, prénom ou matricule..." autocomplete="off">
            <button type="button" id="clearSearchBtn" class="btn-clear" style="display: none;"><i class="fas fa-times"></i></button>
        </div>

        <div class="filter-tabs">
            <button type="button" class="tab-btn active" data-filter="all">Tous (<span id="countAll"><?= count($etudiants) ?></span>)</button>
            <button type="button" class="tab-btn" data-filter="enrolled">Déjà Enrôlés (<span id="countEnrolled"><?= $totalEnroles ?></span>)</button>
            <button type="button" class="tab-btn" data-filter="not-enrolled">Non Enrôlés (<span id="countNotEnrolled"><?= count($etudiants) - $totalEnroles ?></span>)</button>
        </div>
    </div>

    <!-- Formulaire d'Enrôlement en Masse -->
    <form method="POST" action="<?= base_url('/cours/' . $cours['id'] . '/enrollement') ?>" id="bulkEnrollForm">
        <?= csrf_field() ?>
        
        <div class="bulk-actions-bar" id="bulkActionsBar" style="display: none;">
            <div class="selected-info">
                <span class="badge-count" id="selectedCountBadge">0</span> étudiant(s) sélectionné(s)
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-user-plus"></i> Enrôler la sélection
            </button>
        </div>

        <div class="table-responsive enrollment-table-wrapper">
            <table class="table table-hover enrollment-table" id="studentsTable">
                <thead>
                    <tr>
                        <th width="45" class="text-center">
                            <input type="checkbox" id="selectAllCheckbox" title="Tout sélectionner / désélectionner">
                        </th>
                        <th>Étudiant</th>
                        <th>Matricule</th>
                        <th>Filière & Promotion</th>
                        <th class="text-center">Statut</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($etudiants)): ?>
                        <tr class="no-data-row">
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                                <p class="text-muted">Aucun étudiant trouvé dans le système.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($etudiants as $etudiant): ?>
                            <?php 
                                $isEnrolled = !empty($etudiant['is_enrolled']); 
                                $rowFilter = $isEnrolled ? 'enrolled' : 'not-enrolled';
                                $fullName = trim(($etudiant['nom'] ?? '') . ' ' . ($etudiant['prenom'] ?? ''));
                            ?>
                            <tr class="student-row" 
                                data-enrolled="<?= $rowFilter ?>" 
                                data-name="<?= strtolower(htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8')) ?>" 
                                data-matricule="<?= strtolower(htmlspecialchars($etudiant['matricule'] ?? '', ENT_QUOTES, 'UTF-8')) ?>"
                                data-email="<?= strtolower(htmlspecialchars($etudiant['email'] ?? '', ENT_QUOTES, 'UTF-8')) ?>">
                                <td class="text-center">
                                    <?php if (!$isEnrolled): ?>
                                        <input type="checkbox" name="etudiant_ids[]" value="<?= $etudiant['id'] ?>" class="student-checkbox">
                                    <?php else: ?>
                                        <i class="fas fa-check-circle text-success" title="Déjà enrôlé"></i>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="student-avatar-block">
                                        <div class="avatar-circle">
                                            <?php if (!empty($etudiant['photo'])): ?>
                                                <img src="<?= base_url($etudiant['photo']) ?>" alt="Photo">
                                            <?php else: ?>
                                                <i class="fas fa-user"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="student-details">
                                            <strong class="student-name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small class="student-email"><?= htmlspecialchars($etudiant['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-matricule"><?= htmlspecialchars($etudiant['matricule'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <span class="badge-filiere"><?= htmlspecialchars($etudiant['filiere_nom'] ?? ($etudiant['orientation_nom'] ?? 'Général'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="badge-level"><?= htmlspecialchars($etudiant['niveau'] ?? 'L1', ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($isEnrolled): ?>
                                        <span class="badge badge-success-subtle">
                                            <i class="fas fa-check"></i> Enrôlé
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary-subtle">
                                            Non enrôlé
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <?php if ($isEnrolled): ?>
                                        <button type="button" class="btn btn-outline-danger btn-xs" onclick="unenrollStudent(<?= $cours['id'] ?>, <?= $etudiant['id'] ?>, '<?= addslashes($fullName) ?>')">
                                            <i class="fas fa-user-minus"></i> Retirer
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-outline-primary btn-xs" onclick="enrollSingleStudent(<?= $cours['id'] ?>, <?= $etudiant['id'] ?>)">
                                            <i class="fas fa-user-plus"></i> Enrôler
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </form>

    <!-- Formulaires cachés pour les actions unitaires -->
    <form id="singleEnrollForm" method="POST" action="<?= base_url('/cours/' . $cours['id'] . '/enrollement') ?>" style="display:none;">
        <?= csrf_field() ?>
        <input type="hidden" name="etudiant_id" id="singleEnrollEtudiantId">
    </form>

    <form id="singleUnenrollForm" method="POST" action="<?= base_url('/cours/' . $cours['id'] . '/desenrollement') ?>" style="display:none;">
        <?= csrf_field() ?>
        <input type="hidden" name="etudiant_id" id="singleUnenrollEtudiantId">
    </form>
</div>

<style>
.enrollment-container {
    padding: 10px 0 40px 0;
}

.page-header-enroll {
    margin-bottom: 25px;
}

.header-breadcrumb a {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #475569;
    text-decoration: none;
    font-weight: 500;
    font-size: 14px;
    margin-bottom: 12px;
    transition: color 0.2s;
}

.header-breadcrumb a:hover {
    color: #0056b3;
}

.header-main-action {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.header-title-block h2 {
    margin: 0 0 6px 0;
    font-size: 26px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-title-block .subtitle {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.btn-success-custom {
    background: #059669;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: 1px solid #15803d;
    transition: background-color 0.15s ease;
}

.btn-success-custom:hover {
    background: #166534;
    border-color: #166534;
}

/* Carte Résumé du Cours */
.course-summary-card {
    background: white;
    border-radius: 6px;
    padding: 18px 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.summary-item.main-info {
    grid-column: span 2;
}

.summary-item .label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.summary-item .value {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
}

.summary-item .course-name {
    font-size: 17px;
    color: #0f172a;
    font-weight: 700;
}

.badge-code {
    display: inline-block;
    background: #e0f2fe;
    color: #0369a1;
    padding: 2px 8px;
    border-radius: 6px;
    font-family: monospace;
    font-size: 13px;
    width: fit-content;
}

.badge-enrolled-count {
    color: #15803d;
    font-size: 16px;
    font-weight: 700;
    width: fit-content;
}

/* Barre d'outils et Recherche */
.toolbar-card {
    background: white;
    border-radius: 10px;
    padding: 16px 20px;
    border: 1px solid #e2e8f0;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 280px;
    max-width: 480px;
}

.search-box input {
    width: 100%;
    padding: 10px 38px 10px 38px;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    font-size: 14px;
    transition: all 0.2s;
}

.search-box input:focus {
    outline: none;
    background: white;
    border-color: #0056b3;
    box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.12);
}

.search-box .search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.search-box .btn-clear {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
}

.filter-tabs {
    display: flex;
    gap: 8px;
}

.tab-btn {
    padding: 8px 16px;
    border: 1px solid #cbd5e1;
    background: white;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s;
}

.tab-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.tab-btn.active {
    background: #0056b3;
    color: white;
    border-color: #0056b3;
}

/* Barre d'action groupée */
.bulk-actions-bar {
    background: #f0fdf4;
    border: 1px solid #86efac;
    border-radius: 8px;
    padding: 12px 20px;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: fadeIn 0.2s ease-in-out;
}

.selected-info {
    font-weight: 600;
    color: #166534;
    font-size: 14px;
}

.badge-count {
    background: #16a34a;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

/* Tableau */
.enrollment-table-wrapper {
    background: white;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    overflow: hidden;
}

.enrollment-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.enrollment-table thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    font-size: 13px;
    padding: 14px 18px;
    border-bottom: 2px solid #e2e8f0;
}

.enrollment-table tbody td {
    padding: 12px 18px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 14px;
}

.student-avatar-block {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar-circle {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    color: #64748b;
    font-size: 14px;
    flex-shrink: 0;
}

.avatar-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-details {
    display: flex;
    flex-direction: column;
}

.student-name {
    color: #1e293b;
    font-size: 14px;
}

.student-email {
    color: #94a3b8;
    font-size: 12px;
}

.badge-matricule {
    background: #f1f5f9;
    color: #334155;
    padding: 4px 8px;
    border-radius: 6px;
    font-family: monospace;
    font-weight: 600;
    font-size: 12px;
}

.badge-filiere {
    background: #eff6ff;
    color: #1d4ed8;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.badge-level {
    background: #f8fafc;
    color: #475569;
    border: 1px solid #cbd5e1;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    margin-left: 4px;
}

.badge-success-subtle {
    color: #15803d;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.badge-secondary-subtle {
    color: #64748b;
    font-size: 12px;
    font-weight: 500;
}

.btn-xs {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-outline-primary {
    background: transparent;
    border: 1px solid #0056b3;
    color: #0056b3;
}

.btn-outline-primary:hover {
    background: #0056b3;
    color: white;
}

.btn-outline-danger {
    background: transparent;
    border: 1px solid #ef4444;
    color: #ef4444;
}

.btn-outline-danger:hover {
    background: #ef4444;
    color: white;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('studentSearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const tabButtons = document.querySelectorAll('.tab-btn');
    const studentRows = document.querySelectorAll('.student-row');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountBadge = document.getElementById('selectedCountBadge');

    let currentFilter = 'all';

    function filterTable() {
        const query = searchInput.value.toLowerCase().trim();
        clearSearchBtn.style.display = query.length > 0 ? 'block' : 'none';

        studentRows.forEach(row => {
            const name = row.getAttribute('data-name') || '';
            const matricule = row.getAttribute('data-matricule') || '';
            const email = row.getAttribute('data-email') || '';
            const isEnrolledType = row.getAttribute('data-enrolled');

            const matchesSearch = query === '' || name.includes(query) || matricule.includes(query) || email.includes(query);
            const matchesTab = currentFilter === 'all' || isEnrolledType === currentFilter;

            if (matchesSearch && matchesTab) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    searchInput.addEventListener('input', filterTable);

    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        filterTable();
        searchInput.focus();
    });

    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            tabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            filterTable();
        });
    });

    function updateBulkBar() {
        const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
        const count = checkedBoxes.length;
        if (count > 0) {
            bulkActionsBar.style.display = 'flex';
            selectedCountBadge.textContent = count;
        } else {
            bulkActionsBar.style.display = 'none';
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            studentCheckboxes.forEach(box => {
                const row = box.closest('tr');
                if (row && row.style.display !== 'none') {
                    box.checked = isChecked;
                }
            });
            updateBulkBar();
        });
    }

    studentCheckboxes.forEach(box => {
        box.addEventListener('change', updateBulkBar);
    });
});

function enrollSingleStudent(coursId, etudiantId) {
    document.getElementById('singleEnrollEtudiantId').value = etudiantId;
    document.getElementById('singleEnrollForm').submit();
}

function unenrollStudent(coursId, etudiantId, studentName) {
    if (confirm('Voulez-vous vraiment retirer ' + studentName + ' de ce cours ?')) {
        document.getElementById('singleUnenrollEtudiantId').value = etudiantId;
        document.getElementById('singleUnenrollForm').submit();
    }
}
</script>
