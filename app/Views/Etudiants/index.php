<div class="header-section">
    <div class="header-left">
        <h2>Étudiants</h2>
    </div>
    <div class="header-right">
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Recherche d'étudiants..." class="search-input">
        </div>
        <?php if (RoleMiddleware::isAdmin()): ?>
            <button class="btn btn-secondary" onclick="document.getElementById('importModal').style.display='block'">
                <i class="fas fa-file-import"></i> Importer CSV
            </button>
            <a class="btn btn-primary" href="<?= base_url('/etudiants/create') ?>">
                <i class="fas fa-plus"></i> Ajouter Étudiant
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Modal d'Importation -->
<div id="importModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Importer des étudiants</h2>
            <span class="close" onclick="document.getElementById('importModal').style.display='none'">&times;</span>
        </div>
        <div class="modal-body">
            <p>Importez vos étudiants à partir d'un fichier CSV. Assurez-vous que les colonnes correspondent au modèle.</p>
            <div class="template-download">
                <a href="<?= base_url('/etudiants/template') ?>" class="btn-link">
                    <i class="fas fa-download"></i> Télécharger le modèle CSV
                </a>
            </div>
            <form action="<?= base_url('/etudiants/import') ?>" method="POST" enctype="multipart/form-data" class="import-form">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label>Sélectionner le fichier CSV</label>
                    <input type="file" name="csv_file" accept=".csv" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('importModal').style.display='none'">Annuler</button>
                    <button type="submit" class="btn btn-primary">Lancer l'importation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if (empty($etudiants)): ?>
    <div class="empty-state">
        <i class="fa-solid fa-user-slash empty-icon"></i>
        <h3>Aucun étudiant enregistré</h3>
        <p>La liste des étudiants est actuellement vide.</p>
        <?php if (RoleMiddleware::isAdmin()): ?>
            <a href="<?= base_url('/etudiants/create') ?>" class="btn btn-primary" style="margin-top: 12px;">
                <i class="fas fa-plus"></i> Ajouter un premier étudiant
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom</th>
                    <th>Domaine & Orientation</th>
                    <th>Niveau</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $etudiant): ?>
                    <tr class="student-row">
                        <td class="photo-column">
                            <div class="student-avatar">
                                <?php if (!empty($etudiant['photo'])): ?>
                                    <img src="<?= base_url($etudiant['photo']) ?>" alt="<?= htmlspecialchars($etudiant['nom'], ENT_QUOTES, 'UTF-8') ?>" class="avatar-img">
                                <?php else: ?>
                                    <div class="avatar-placeholder">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="name-column">
                            <strong><?= htmlspecialchars($etudiant['nom'], ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($etudiant['prenom'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($etudiant['email'], ENT_QUOTES, 'UTF-8') ?> | Mat: <?= htmlspecialchars($etudiant['matricule'], ENT_QUOTES, 'UTF-8') ?></small>
                        </td>
                        <td>
                            <?php if (!empty($etudiant['domaine_nom'])): ?>
                                <strong><?= htmlspecialchars($etudiant['domaine_nom'], ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <small class="text-muted">
                                    <?= htmlspecialchars($etudiant['filiere_nom'] ?? '', ENT_QUOTES, 'UTF-8') ?> 
                                    <i class="fas fa-angle-right" style="font-size:10px; margin:0 3px;"></i> 
                                    <span class="text-primary"><?= htmlspecialchars($etudiant['orientation_nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                                </small>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="niveau-badge niveau-<?= strtolower($etudiant['niveau'] ?? '') ?>">
                                <?= htmlspecialchars($etudiant['niveau'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td class="actions-column">
                            <div class="action-buttons">
                                <a class="btn btn-sm btn-info" href="<?= base_url('/etudiants/' . $etudiant['id'] . '/edit') ?>" title="<?= RoleMiddleware::isAdmin() ? 'Modifier' : 'Consulter' ?>">
                                    <i class="fas <?= RoleMiddleware::isAdmin() ? 'fa-edit' : 'fa-eye' ?>"></i>
                                </a>
                                <?php if (RoleMiddleware::isAdmin()): ?>
                                    <form method="POST" action="<?= base_url('/etudiants/' . $etudiant['id'] . '/delete') ?>" class="inline-form" onsubmit="return confirm('Supprimer cet étudiant ?');">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-danger" type="submit" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
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

.search-bar {
    position: relative;
}

.search-input {
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    width: 250px;
    font-size: 14px;
    background-color: #f8f9fa;
}

.search-input:focus {
    outline: none;
    border-color: #0056b3;
    background-color: white;
}

.table-modern {
    width: 100%;
    border-collapse: collapse;
    background-color: transparent;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: none;
    border: 1px solid #e2e8f0;
}

.table-modern thead {
    background-color: #001A72 !important;
    border-bottom: 2px solid #000d3b !important;
}

.table-modern thead th {
    padding: 13px 16px;
    text-align: left;
    font-weight: 600;
    color: white !important;
    font-size: 13px;
}

.table-modern tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
}

.table-modern tbody tr:hover {
    background-color: #f8f9fa;
}

.student-row td {
    padding: 15px;
    vertical-align: middle;
}

.photo-column {
    width: 70px;
    text-align: center;
}

.student-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    font-size: 24px;
    color: #999;
}

.name-column {
    font-weight: 500;
}

.text-muted {
    color: #999;
    font-size: 12px;
}

.actions-column {
    text-align: center;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-sm {
    padding: 8px 12px;
    font-size: 13px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
}

.btn-info {
    background-color: #0056b3;
    color: white;
}

.btn-info:hover {
    background-color: #004085;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}

.inline-form {
    display: inline;
    margin: 0;
}

.empty-state {
    text-align: center;
    padding: 50px 20px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    color: #64748b;
}

.empty-state .empty-icon {
    font-size: 40px;
    color: #cbd5e1;
    margin-bottom: 12px;
    display: block;
}

.empty-state h3 {
    margin: 0 0 6px 0;
    color: #1e293b;
    font-size: 16px;
    font-weight: 600;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    z-index: 3000;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 500px;
    margin: 10vh auto;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.modal-header {
    padding: 1.25rem 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 { margin: 0; font-size: 1.25rem; color: #1e293b; }
.close { cursor: pointer; font-size: 1.5rem; color: #64748b; }

.modal-body { padding: 1.5rem; }

.template-download {
    margin: 1rem 0;
    padding: 1rem;
    background: #f0f7ff;
    border: 1px dashed #007bff;
    border-radius: 8px;
    text-align: center;
}

.btn-link {
    color: #007bff;
    text-decoration: none;
    font-weight: 600;
}

.import-form .form-group {
    margin-top: 1.5rem;
}

.import-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #334155;
}

.import-form input[type="file"] {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .header-right {
        flex-direction: column;
    }

    .search-input {
        width: 100%;
    }

    .table-modern {
        font-size: 13px;
    }

    .student-row td {
        padding: 12px;
    }
}

.niveau-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.niveau-l1,
.niveau-l2,
.niveau-l3 {
    background-color: #e7f3ff;
    color: #004085;
    border: 1px solid #b3d9ff;
}

.niveau-m1,
.niveau-m2 {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.hidden-row {
    display: none;
}

.no-results {
    text-align: center;
    padding: 40px;
    color: #999;
    font-size: 16px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.querySelector('tbody');
    const tableRows = document.querySelectorAll('.student-row');
    
    if (!searchInput || !tableBody) return;

    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let visibleCount = 0;

        tableRows.forEach(row => {
            const nom = row.querySelector('.name-column strong')?.textContent.toLowerCase() || '';
            const email = row.querySelector('.text-muted')?.textContent.toLowerCase() || '';
            const filiere = row.cells[2]?.textContent.toLowerCase() || '';
            const niveau = row.cells[3]?.textContent.toLowerCase() || '';

            if (nom.includes(searchTerm) || 
                email.includes(searchTerm) || 
                filiere.includes(searchTerm) || 
                niveau.includes(searchTerm)) {
                row.classList.remove('hidden-row');
                visibleCount++;
            } else {
                row.classList.add('hidden-row');
            }
        });

        // Afficher/masquer le message "Aucun résultat"
        let noResultsMsg = tableBody.querySelector('.no-results');
        if (visibleCount === 0) {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('tr');
                noResultsMsg.className = 'no-results';
                noResultsMsg.innerHTML = '<td colspan="5" style="padding: 40px; text-align: center; color: #999;">Aucun étudiant ne correspond à votre recherche.</td>';
                tableBody.appendChild(noResultsMsg);
            }
        } else {
            if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    });

    // Ajouter un effet visuel au focus
    searchInput.addEventListener('focus', function() {
        this.style.boxShadow = '0 0 0 3px rgba(0, 86, 179, 0.1)';
    });

    searchInput.addEventListener('blur', function() {
        this.style.boxShadow = 'none';
    });
});
</script>
