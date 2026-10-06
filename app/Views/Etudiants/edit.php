<div class="form-header">
    <h2>Modifier l'étudiant</h2>
    <p class="form-subtitle">Mettez à jour les informations de l'étudiant</p>
</div>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger" style="margin-bottom: 20px;">
        <strong><i class="fa-solid fa-circle-exclamation"></i> Erreur :</strong> <?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <strong><i class="fa-solid fa-circle-check"></i> Succès :</strong> <?= htmlspecialchars($_SESSION['success']) ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= base_url('/etudiants/' . $etudiant['id'] . '/update') ?>" class="form-card form-modern" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-section">
        <h3>Informations personnelles</h3>
        
        <div class="form-group">
            <label for="nom">Nom <span class="required">*</span></label>
            <input type="text" id="nom" name="nom" placeholder="Entrez le nom" value="<?= htmlspecialchars($etudiant['nom'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="prenom">Prénom <span class="required">*</span></label>
            <input type="text" id="prenom" name="prenom" placeholder="Entrez le prénom" value="<?= htmlspecialchars($etudiant['prenom'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
        </div>

        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label for="lieu_naissance">Lieu de naissance</label>
                <input type="text" id="lieu_naissance" name="lieu_naissance" placeholder="ex: Goma" value="<?= htmlspecialchars($etudiant['lieu_naissance'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control">
            </div>
            <div class="form-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" value="<?= htmlspecialchars($etudiant['date_naissance'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" placeholder="Entrez l'email" value="<?= htmlspecialchars($etudiant['email'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
        </div>

        <div class="form-group">
            <label for="matricule">Matricule <span class="required">*</span></label>
            <input type="text" id="matricule" name="matricule" placeholder="Entrez le matricule" value="<?= htmlspecialchars($etudiant['matricule'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control">
        </div>
    </div>

    <div class="form-section">
        <h3>Informations académiques</h3>
        
        <div class="form-group">
            <label for="domaine_id">Domaine <span class="required">*</span></label>
            <select id="domaine_id" class="form-control" required>
                <option value="">- Sélectionner un domaine -</option>
                <?php foreach ($domaines as $domaine): ?>
                    <option value="<?= $domaine['id'] ?>" <?= (isset($etudiant['domaine_id']) && $etudiant['domaine_id'] == $domaine['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="filiere_id">Filière <span class="required">*</span></label>
            <select id="filiere_id" class="form-control" required disabled>
                <option value="">- Sélectionner d'abord un domaine -</option>
            </select>
        </div>

        <div class="form-group">
            <label for="orientation_id">Orientation <span class="required">*</span></label>
            <select id="orientation_id" name="orientation_id" class="form-control" required disabled>
                <option value="">- Sélectionner d'abord une filière -</option>
            </select>
        </div>

        <script>
            const domainesData = <?= json_encode($domaines) ?>;
            const selectDomaine = document.getElementById('domaine_id');
            const selectFiliere = document.getElementById('filiere_id');
            const selectOrientation = document.getElementById('orientation_id');

            const currentDomaineId = "<?= $etudiant['domaine_id'] ?? '' ?>";
            const currentFiliereId = "<?= $etudiant['filiere_id'] ?? '' ?>";
            const currentOrientationId = "<?= $etudiant['orientation_id'] ?? '' ?>";

            function populateFilieres(domaineId, selectedFiliereId = null) {
                selectFiliere.innerHTML = '<option value="">- Sélectionner une filière -</option>';
                selectOrientation.innerHTML = '<option value="">- Sélectionner d\'abord une filière -</option>';
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
                selectOrientation.innerHTML = '<option value="">- Sélectionner une orientation -</option>';

                if (filiereId && domaineId) {
                    const domaine = domainesData.find(d => d.id == domaineId);
                    const filiere = domaine.filieres.find(f => f.id == filiereId);
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

            // Initialize on load
            if (currentDomaineId) {
                populateFilieres(currentDomaineId, currentFiliereId);
                if (currentFiliereId) {
                    populateOrientations(currentDomaineId, currentFiliereId, currentOrientationId);
                }
            }

            function previewPhoto(input) {
                const preview = document.getElementById('photoPreview');
                const previewImg = document.getElementById('previewImg');
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
        </script>

        <div class="form-group">
            <label for="niveau">Niveau <span class="required">*</span></label>
            <select id="niveau" name="niveau" required class="form-control">
                <option value="">- Sélectionner un niveau -</option>
                <optgroup label="Licence">
                    <option value="L1" <?= $etudiant['niveau'] === 'L1' ? 'selected' : '' ?>>L1 - Licence 1ère année</option>
                    <option value="L2" <?= $etudiant['niveau'] === 'L2' ? 'selected' : '' ?>>L2 - Licence 2ème année</option>
                    <option value="L3" <?= $etudiant['niveau'] === 'L3' ? 'selected' : '' ?>>L3 - Licence 3ème année</option>
                </optgroup>
                <optgroup label="Master">
                    <option value="M1" <?= $etudiant['niveau'] === 'M1' ? 'selected' : '' ?>>M1 - Master 1ère année</option>
                    <option value="M2" <?= $etudiant['niveau'] === 'M2' ? 'selected' : '' ?>>M2 - Master 2ème année</option>
                </optgroup>
            </select>
        </div>
    </div>

    <div class="form-section">
        <h3>Photo de profil</h3>
        
        <div class="form-group">
            <?php if (!empty($etudiant['photo'])): ?>
                <div class="current-photo-section">
                    <p class="label-text">Photo actuelle:</p>
                    <div class="current-photo">
                        <img src="<?= base_url($etudiant['photo']) ?>" alt="Photo actuelle">
                    </div>
                </div>
            <?php endif; ?>
            
            <label for="photo">Changer la photo (optionnel)</label>
            <div class="photo-upload" style="border: 2px dashed #ddd; padding: 20px; border-radius: 5px; cursor: pointer; transition: all 0.2s;">
                <input type="file" id="photo" name="photo" accept="image/*" class="form-control" onchange="previewPhoto(this)" style="cursor: pointer;">
                <div class="photo-preview" id="photoPreview" style="display: none;">
                    <img id="previewImg" src="" alt="Aperçu photo">
                </div>
                <small class="form-text">
                    <i class="fas fa-cloud-upload-alt"></i> Cliquez ou glissez-déposez une image ici
                    <br>Formats acceptés: JPG, PNG, GIF | Taille max: 5MB
                </small>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?= base_url('/etudiants') ?>" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary" type="submit">
            <i class="fas fa-save"></i> Mettre à jour
        </button>
    </div>
</form>

<style>
.form-header {
    margin-bottom: 30px;
}

.form-header h2 {
    margin: 0 0 8px 0;
    font-size: 28px;
    color: #333;
}

.form-subtitle {
    margin: 0;
    color: #999;
    font-size: 14px;
}

.form-modern {
    max-width: 600px;
    background-color: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.form-section {
    margin-bottom: 30px;
}

.form-section:last-of-type {
    margin-bottom: 20px;
}

.form-section h3 {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0 0 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0f0f0;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
    font-size: 14px;
}

.label-text {
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
    font-size: 14px;
}

.required {
    color: #dc3545;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    font-family: inherit;
    transition: all 0.2s;
    background-color: #f8f9fa;
}

.form-control:focus {
    outline: none;
    background-color: white;
    border-color: #0056b3;
    box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
}

.current-photo-section {
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 5px;
}

.current-photo {
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 10px;
    display: inline-block;
}

.current-photo img {
    max-width: 150px;
    max-height: 150px;
    border-radius: 3px;
    object-fit: cover;
}

.photo-upload {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.photo-preview {
    border: 2px dashed #ddd;
    border-radius: 5px;
    padding: 15px;
    text-align: center;
}

.photo-preview img {
    max-width: 150px;
    max-height: 150px;
    border-radius: 5px;
    object-fit: cover;
}

.form-text {
    display: block;
    color: #999;
    font-size: 12px;
    margin-top: 8px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #f0f0f0;
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

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.alert {
    padding: 15px;
    border-radius: 5px;
    font-size: 14px;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
}

.alert strong {
    font-weight: 600;
}
</style>
