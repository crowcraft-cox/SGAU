<div class="header-section">
    <div class="header-left">
        <h2>Professeurs</h2>
    </div>
    <div class="header-right">
        <a class="btn btn-primary" href="<?= base_url('/enseignants/create') ?>">
            <i class="fas fa-plus"></i> Ajouter Professeur
        </a>
    </div>
</div>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="search-section">
    <input type="text" id="search-input" class="search-input" placeholder="Ajouter un enseignant..." onkeyup="searchTeachers()">
</div>

<?php if (empty($enseignants)): ?>
    <div class="empty-state">
        <p>Aucun professeur trouvé.</p>
    </div>
<?php else: ?>
    <div class="table-container">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Spécialité</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enseignants as $enseignant): ?>
                    <tr class="teacher-row" data-search-text="<?= strtolower($enseignant['nom'] . ' ' . $enseignant['prenom'] . ' ' . $enseignant['email'] . ' ' . $enseignant['departement']) ?>">
                        <td class="teacher-column">
                            <div class="teacher-info">
                                <div class="teacher-avatar">
                                    <?php if (!empty($enseignant['photo'])): ?>
                                        <img src="<?= base_url($enseignant['photo']) ?>" alt="<?= htmlspecialchars($enseignant['nom'], ENT_QUOTES, 'UTF-8') ?>" class="avatar-img">
                                    <?php else: ?>
                                        <div class="avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <strong><?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <br>
                                    <small><?= htmlspecialchars('Dr. ' . $enseignant['nom'], ENT_QUOTES, 'UTF-8') ?></small>
                                </div>
                            </div>
                        </td>
                        <td class="speciality-column">
                            <?= htmlspecialchars($enseignant['departement'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="phone-column">
                            <?= htmlspecialchars($enseignant['telephone'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="email-column">
                            <?= htmlspecialchars($enseignant['email'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td class="actions-column">
                            <div class="action-buttons">
                                <a class="btn btn-sm btn-info" href="<?= base_url('/enseignants/' . $enseignant['id'] . '/edit') ?>" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="<?= base_url('/enseignants/' . $enseignant['id'] . '/delete') ?>" class="inline-form" onsubmit="return confirm('Supprimer cet enseignant ?');">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-danger" type="submit" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<style>
.alert {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.alert-danger {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert i {
    font-size: 18px;
}

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

.search-section {
    margin-bottom: 25px;
}

.search-input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    background-color: #f8f9fa;
    transition: all 0.2s;
}

.search-input:focus {
    outline: none;
    background-color: white;
    border-color: #0056b3;
    box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
}

.table-container {
    background-color: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-modern {
    width: 100%;
    border-collapse: collapse;
}

.table-modern thead {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e9ecef;
}

.table-modern thead th {
    padding: 15px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    font-size: 14px;
}

.table-modern tbody tr {
    border-bottom: 1px solid #e9ecef;
    transition: background-color 0.2s;
}

.table-modern tbody tr:hover {
    background-color: #f8f9fa;
}

.teacher-row td {
    padding: 15px;
    vertical-align: middle;
}

.teacher-column {
    min-width: 250px;
}

.teacher-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.teacher-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    font-size: 20px;
    color: #999;
}

.teacher-info small {
    color: #999;
    font-size: 12px;
}

.speciality-column,
.phone-column,
.email-column {
    font-weight: 500;
}

.phone-column,
.email-column {
    color: #666;
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

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background-color: #0056b3;
    color: white;
}

.btn-primary:hover {
    background-color: #004085;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #999;
}

@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
    }

    .table-modern {
        font-size: 12px;
    }

    .teacher-row td {
        padding: 10px;
    }
}
</style>

<script>
function searchTeachers() {
    const searchText = document.getElementById('search-input').value.toLowerCase();
    const rows = document.querySelectorAll('.teacher-row');
    
    rows.forEach(row => {
        const searchContent = row.getAttribute('data-search-text');
        if (searchContent.includes(searchText)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
