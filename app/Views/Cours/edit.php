<div class="page-title-modern">
    <div>
        <h2><i class="fas fa-edit text-primary"></i> Modifier le Cours</h2>
        <p class="subtitle">Mettez à jour les informations du cours <?= htmlspecialchars($cours['nom'], ENT_QUOTES, 'UTF-8') ?></p>
    </div>
    <div class="header-actions">
        <a class="btn btn-outline-primary" href="<?= base_url('/cours/' . $cours['id'] . '/enrollement') ?>">
            <i class="fas fa-user-plus"></i> Gérer l'enrôlement
        </a>
        <a class="btn btn-secondary" href="<?= base_url('/cours') ?>">
            <i class="fas fa-arrow-left"></i> Annuler
        </a>
    </div>
</div>

<form method="POST" action="<?= base_url('/cours/' . $cours['id'] . '/update') ?>" class="form-card form-modern-card">
    <?= csrf_field() ?>
    
    <div class="form-row">
        <div class="form-group flex-1">
            <label for="code">Code du Cours <span class="text-danger">*</span></label>
            <input type="text" id="code" name="code" value="<?= htmlspecialchars($cours['code'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
        </div>
        <div class="form-group flex-2">
            <label for="nom">Intitulé du Cours <span class="text-danger">*</span></label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($cours['nom'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="domaine_id">Domaine <span class="text-danger">*</span></label>
            <select id="domaine_id" required class="form-control">
                <option value="">Sélectionner un domaine</option>
                <?php foreach ($domaines as $domaine): ?>
                    <option value="<?= $domaine['id'] ?>" <?= (isset($cours['domaine_id']) && $cours['domaine_id'] == $domaine['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group flex-1">
            <label for="filiere_id">Filière <span class="text-danger">*</span></label>
            <select id="filiere_id" required disabled class="form-control">
                <option value="">Sélectionner d'abord un domaine</option>
            </select>
        </div>

        <div class="form-group flex-1">
            <label for="orientation_id">Orientation <span style="font-size:0.85em; color:#64748b; font-weight:400;">(facultatif)</span></label>
            <select id="orientation_id" name="orientation_id" disabled class="form-control">
                <option value="">Aucune orientation</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="credit">Nombre de Crédits <span class="text-danger">*</span></label>
            <input type="number" id="credit" name="credit" min="1" max="30" value="<?= htmlspecialchars($cours['credit'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
        </div>

        <div class="form-group flex-2">
            <label for="enseignant_id">Enseignant Titulaire <span class="text-danger">*</span></label>
            <select id="enseignant_id" name="enseignant_id" required class="form-control">
                <option value="">Sélectionner un enseignant</option>
                <?php foreach ($enseignants as $enseignant): ?>
                    <option value="<?= $enseignant['id'] ?>" <?= $cours['enseignant_id'] == $enseignant['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom'] . ' (' . $enseignant['matricule'] . ')', ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="description">Description / Objectifs du cours</label>
        <textarea id="description" name="description" rows="3" class="form-control"><?= htmlspecialchars($cours['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
    </div>

    <div class="form-actions-bar">
        <button class="btn btn-primary btn-lg" type="submit">
            <i class="fas fa-save"></i> Enregistrer les modifications
        </button>
    </div>
</form>

<script>
    const domainesData = <?= json_encode($domaines) ?>;
    const selectDomaine = document.getElementById('domaine_id');
    const selectFiliere = document.getElementById('filiere_id');
    const selectOrientation = document.getElementById('orientation_id');

    const currentDomaineId = "<?= $cours['domaine_id'] ?? '' ?>";
    const currentFiliereId = "<?= $cours['filiere_id'] ?? '' ?>";
    const currentOrientationId = "<?= $cours['orientation_id'] ?? '' ?>";

    function populateFilieres(domaineId, selectedFiliereId = null) {
        selectFiliere.innerHTML = '<option value="">Sélectionner une filière</option>';
        selectOrientation.innerHTML = '<option value="">Sélectionner d\'abord une filière</option>';
        selectOrientation.disabled = true;

        if (domaineId) {
            const domaine = domainesData.find(d => d.id == domaineId);
            if (domaine && domaine.filieres) {
                domaine.filieres.forEach(f => {
                    const option = document.createElement('option');
                    option.value = f.id;
                    option.textContent = f.nom;
                    if (f.id == selectedFiliereId) option.selected = true;
                    selectFiliere.appendChild(option);
                });
            }
            selectFiliere.disabled = false;
        } else {
            selectFiliere.disabled = true;
        }
    }

    function populateOrientations(domaineId, filiereId, selectedOrientationId = null) {
        selectOrientation.innerHTML = '<option value="">Aucune orientation</option>';

        if (filiereId && domaineId) {
            const domaine = domainesData.find(d => d.id == domaineId);
            const filiere = domaine ? domaine.filieres.find(f => f.id == filiereId) : null;
            if (filiere && filiere.orientations) {
                filiere.orientations.forEach(o => {
                    const option = document.createElement('option');
                    option.value = o.id;
                    option.textContent = o.nom;
                    if (o.id == selectedOrientationId) option.selected = true;
                    selectOrientation.appendChild(option);
                });
            }
            selectOrientation.disabled = false;
        } else {
            selectOrientation.disabled = true;
        }
    }

    selectDomaine.addEventListener('change', function() {
        populateFilieres(this.value);
    });

    selectFiliere.addEventListener('change', function() {
        populateOrientations(selectDomaine.value, this.value);
    });

    // Initialisation
    if (currentDomaineId) {
        populateFilieres(currentDomaineId, currentFiliereId);
        if (currentFiliereId) {
            populateOrientations(currentDomaineId, currentFiliereId, currentOrientationId);
        }
    }
</script>

<style>
.form-modern-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    max-width: 900px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 15px;
}

.flex-1 { flex: 1; min-width: 220px; }
.flex-2 { flex: 2; min-width: 300px; }

.form-group label {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
}

.form-control {
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    background: #f8fafc;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    background: white;
    border-color: #0056b3;
    box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.12);
}

.form-actions-bar {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 15px;
    font-weight: 600;
}
</style>
