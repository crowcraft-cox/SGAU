<div class="page-title-modern">
    <div>
        <h2><i class="fas fa-plus-circle text-primary"></i> Ajouter un Cours</h2>
        <p class="subtitle">Renseignez les détails du cours. Après l'enregistrement, vous serez immédiatement redirigé vers l'enrôlement des étudiants.</p>
    </div>
    <a class="btn btn-secondary" href="<?= base_url('/cours') ?>">
        <i class="fas fa-arrow-left"></i> Annuler
    </a>
</div>

<form method="POST" action="<?= base_url('/cours/store') ?>" class="form-card form-modern-card">
    <?= csrf_field() ?>
    
    <div class="form-row">
        <div class="form-group flex-1">
            <label for="code">Code du Cours <span class="text-danger">*</span></label>
            <input type="text" id="code" name="code" placeholder="Ex: INFO101, MATH201..." required class="form-control">
        </div>
        <div class="form-group flex-2">
            <label for="nom">Intitulé du Cours <span class="text-danger">*</span></label>
            <input type="text" id="nom" name="nom" placeholder="Ex: Algorithmique et Structures de Données" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="domaine_id">Domaine <span class="text-danger">*</span></label>
            <select id="domaine_id" required class="form-control">
                <option value="">Sélectionner un domaine</option>
                <?php foreach ($domaines as $domaine): ?>
                    <option value="<?= $domaine['id'] ?>"><?= htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8') ?></option>
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
            <input type="number" id="credit" name="credit" min="1" max="30" value="3" required class="form-control">
        </div>

        <div class="form-group flex-2">
            <label for="enseignant_id">Enseignant Titulaire <span class="text-danger">*</span></label>
            <?php if (isset($currentEnseignant) && $currentEnseignant): ?>
                <input type="hidden" name="enseignant_id" value="<?= $currentEnseignant['id'] ?>">
                <input type="text" class="form-control" value="<?= htmlspecialchars($currentEnseignant['nom'] . ' ' . $currentEnseignant['prenom'] . ' (' . $currentEnseignant['matricule'] . ')', ENT_QUOTES, 'UTF-8') ?>" disabled readonly>
                <small class="text-muted">Vous êtes automatiquement désigné comme le titulaire de ce cours.</small>
            <?php else: ?>
                <select id="enseignant_id" name="enseignant_id" required class="form-control">
                    <option value="">Sélectionner un enseignant</option>
                    <?php foreach ($enseignants as $enseignant): ?>
                        <option value="<?= $enseignant['id'] ?>"><?= htmlspecialchars($enseignant['nom'] . ' ' . $enseignant['prenom'] . ' (' . $enseignant['matricule'] . ')', ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group">
        <label for="description">Description / Objectifs du cours</label>
        <textarea id="description" name="description" rows="3" placeholder="Description générale du contenu du cours..." class="form-control"></textarea>
    </div>

    <div class="form-actions-bar">
        <button class="btn btn-primary btn-lg" type="submit">
            <i class="fas fa-save"></i> Enregistrer et Enrôler les étudiants <i class="fas fa-arrow-right ml-1"></i>
        </button>
    </div>
</form>

<script>
    const domainesData = <?= json_encode($domaines) ?>;
    const selectDomaine = document.getElementById('domaine_id');
    const selectFiliere = document.getElementById('filiere_id');
    const selectOrientation = document.getElementById('orientation_id');

    selectDomaine.addEventListener('change', function() {
        const domaineId = this.value;
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
                    selectFiliere.appendChild(option);
                });
            }
            selectFiliere.disabled = false;
        } else {
            selectFiliere.disabled = true;
        }
    });

    selectFiliere.addEventListener('change', function() {
        const filiereId = this.value;
        const domaineId = selectDomaine.value;
        selectOrientation.innerHTML = '<option value="">Aucune orientation</option>';

        if (filiereId && domaineId) {
            const domaine = domainesData.find(d => d.id == domaineId);
            const filiere = domaine ? domaine.filieres.find(f => f.id == filiereId) : null;
            if (filiere && filiere.orientations) {
                filiere.orientations.forEach(o => {
                    const option = document.createElement('option');
                    option.value = o.id;
                    option.textContent = o.nom;
                    selectOrientation.appendChild(option);
                });
            }
            selectOrientation.disabled = false;
        } else {
            selectOrientation.disabled = true;
        }
    });
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
