<div class="page-title-modern">
    <div>
        <h2><i class="fas fa-user-graduate text-primary"></i> Ajouter un Étudiant</h2>
        <p class="subtitle">Renseignez les informations personnelles et académiques du nouvel étudiant.</p>
    </div>
    <a class="btn btn-secondary" href="<?= base_url('/etudiants') ?>">
        <i class="fas fa-arrow-left"></i> Annuler
    </a>
</div>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert-form alert-form-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert-form alert-form-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?= base_url('/etudiants/store') ?>" class="form-card form-modern-card" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- ── Section : Identité ── -->
    <div class="form-section-title">
        <i class="fas fa-id-card"></i> Informations personnelles
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="nom">Nom <span class="text-danger">*</span></label>
            <input type="text" id="nom" name="nom" placeholder="Ex : Kabamba" required class="form-control">
        </div>
        <div class="form-group flex-1">
            <label for="prenom">Prénom <span class="text-danger">*</span></label>
            <input type="text" id="prenom" name="prenom" placeholder="Ex : Jean-Pierre" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="lieu_naissance">Lieu de naissance</label>
            <input type="text" id="lieu_naissance" name="lieu_naissance" placeholder="Ex : Goma" class="form-control">
        </div>
        <div class="form-group flex-1">
            <label for="date_naissance">Date de naissance</label>
            <input type="date" id="date_naissance" name="date_naissance" class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group flex-2">
            <label for="email">Email <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" placeholder="Ex : jean.kabamba@universite.cd" required class="form-control">
        </div>
        <div class="form-group flex-1">
            <label for="matricule">Matricule <span class="text-danger">*</span></label>
            <input type="text" id="matricule" name="matricule" placeholder="Ex : 2024001" required class="form-control">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="password">Mot de passe initial</label>
            <input type="password" id="password" name="password" placeholder="Laisser vide pour générer automatiquement" class="form-control">
            <span class="form-hint"><i class="fas fa-info-circle"></i> Si vide, un mot de passe sécurisé sera généré et affiché après l'enregistrement.</span>
        </div>
    </div>

    <!-- ── Section : Académique ── -->
    <div class="form-section-title" style="margin-top: 10px;">
        <i class="fas fa-graduation-cap"></i> Informations académiques
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="domaine_id">Domaine <span class="text-danger">*</span></label>
            <select id="domaine_id" class="form-control" required>
                <option value="">Sélectionner un domaine</option>
                <?php foreach ($domaines as $domaine): ?>
                    <option value="<?= $domaine['id'] ?>"><?= htmlspecialchars($domaine['nom'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group flex-1">
            <label for="filiere_id">Filière <span class="text-danger">*</span></label>
            <select id="filiere_id" class="form-control" required disabled>
                <option value="">Sélectionner d'abord un domaine</option>
            </select>
        </div>
        <div class="form-group flex-1">
            <label for="orientation_id">Orientation <span class="text-danger">*</span></label>
            <select id="orientation_id" name="orientation_id" class="form-control" required disabled>
                <option value="">Sélectionner d'abord une filière</option>
            </select>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <label for="niveau">Niveau <span class="text-danger">*</span></label>
            <select id="niveau" name="niveau" required class="form-control">
                <option value="">Sélectionner un niveau</option>
                <optgroup label="Licence">
                    <option value="L1">L1 — Licence 1ère année</option>
                    <option value="L2">L2 — Licence 2ème année</option>
                    <option value="L3">L3 — Licence 3ème année</option>
                </optgroup>
                <optgroup label="Master">
                    <option value="M1">M1 — Master 1ère année</option>
                    <option value="M2">M2 — Master 2ème année</option>
                </optgroup>
            </select>
        </div>
    </div>

    <!-- ── Section : Photo ── -->
    <div class="form-section-title" style="margin-top: 10px;">
        <i class="fas fa-camera"></i> Photo de profil <span style="font-size:0.85em; color:#64748b; font-weight:400;">(facultatif)</span>
    </div>

    <div class="form-row">
        <div class="form-group flex-1">
            <div class="photo-drop-zone" id="photoDropZone" onclick="document.getElementById('photo').click()">
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/gif" style="display:none;" onchange="previewEtudiantPhoto(this)">
                <div id="photoPlaceholder">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <p>Cliquez ou glissez-déposez une image ici</p>
                    <small>JPG, PNG — Max 5 MB</small>
                </div>
                <div id="photoPreview" style="display:none; text-align:center;">
                    <img id="previewImg" src="" alt="Aperçu" style="max-width:160px; max-height:160px; border-radius:8px; object-fit:cover;">
                    <br>
                    <button type="button" class="btn-change-photo" onclick="event.stopPropagation(); removeEtudiantPhoto()">
                        <i class="fas fa-sync-alt"></i> Changer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="form-actions-bar">
        <button class="btn btn-primary btn-lg" type="submit">
            <i class="fas fa-user-plus"></i> Enregistrer l'étudiant
        </button>
    </div>
</form>

<script>
    const domainesData = <?= json_encode($domaines) ?>;
    const selectDomaine    = document.getElementById('domaine_id');
    const selectFiliere    = document.getElementById('filiere_id');
    const selectOrientation = document.getElementById('orientation_id');

    selectDomaine.addEventListener('change', function() {
        const domaineId = this.value;
        selectFiliere.innerHTML    = '<option value="">Sélectionner une filière</option>';
        selectOrientation.innerHTML = '<option value="">Sélectionner d\'abord une filière</option>';
        selectOrientation.disabled = true;

        if (domaineId) {
            const domaine = domainesData.find(d => d.id == domaineId);
            if (domaine && domaine.filieres) {
                domaine.filieres.forEach(f => {
                    const opt = document.createElement('option');
                    opt.value = f.id;
                    opt.textContent = f.nom;
                    selectFiliere.appendChild(opt);
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
                    const opt = document.createElement('option');
                    opt.value = o.id;
                    opt.textContent = o.nom;
                    selectOrientation.appendChild(opt);
                });
            }
            selectOrientation.disabled = false;
        } else {
            selectOrientation.disabled = true;
        }
    });

    function previewEtudiantPhoto(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('photoPlaceholder').style.display = 'none';
                document.getElementById('photoPreview').style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeEtudiantPhoto() {
        document.getElementById('photo').value = '';
        document.getElementById('previewImg').src = '';
        document.getElementById('photoPreview').style.display = 'none';
        document.getElementById('photoPlaceholder').style.display = 'block';
    }

    // Drag & drop
    const dropZone = document.getElementById('photoDropZone');
    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('photo').files = files;
            previewEtudiantPhoto({ files });
        }
    });
</script>

<style>
/* ── Shared with cours/create : form-modern-card ── */
.form-modern-card {
    background: white;
    padding: 32px 36px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    max-width: 960px;
}

.form-section-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    margin-bottom: 18px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 4px;
    flex-wrap: wrap;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 18px;
}

.flex-1 { flex: 1; min-width: 200px; }
.flex-2 { flex: 2; min-width: 280px; }

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
    width: 100%;
    box-sizing: border-box;
    font-family: inherit;
    color: #1e293b;
}

.form-control:focus {
    outline: none;
    background: white;
    border-color: #0056b3;
    box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.12);
}

.form-hint {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* ── Photo drop zone ── */
.photo-drop-zone {
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    padding: 30px 20px;
    text-align: center;
    cursor: pointer;
    background: #f8fafc;
    transition: all 0.2s;
}

.photo-drop-zone:hover,
.photo-drop-zone.drag-over {
    border-color: #0056b3;
    background: #eff6ff;
}

.photo-drop-zone i {
    font-size: 36px;
    color: #0056b3;
    margin-bottom: 10px;
    display: block;
}

.photo-drop-zone p {
    margin: 8px 0 4px;
    font-weight: 500;
    color: #334155;
    font-size: 14px;
}

.photo-drop-zone small {
    color: #94a3b8;
    font-size: 12px;
}

.btn-change-photo {
    margin-top: 12px;
    padding: 6px 14px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    cursor: pointer;
    color: #334155;
    transition: all 0.2s;
}

.btn-change-photo:hover {
    background: #e2e8f0;
}

/* ── Alerts ── */
.alert-form {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 20px;
    max-width: 960px;
}

.alert-form-danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}

.alert-form-success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}

/* ── Actions bar ── */
.form-actions-bar {
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
}

.btn-lg {
    padding: 12px 26px;
    font-size: 15px;
    font-weight: 600;
}
</style>
