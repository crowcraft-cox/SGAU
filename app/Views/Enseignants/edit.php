<div class="form-header">
    <h2>Modifier le Professeur</h2>
    <p class="form-subtitle">Mettez à jour les informations de l'enseignant</p>
</div>

<?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<form method="POST" action="<?= base_url('/enseignants/' . $enseignant['id'] . '/update') ?>" class="form-card form-modern" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="form-section">
        <h3>Informations Personnelles</h3>
        
        <div class="form-group">
            <label for="nom">Nom <span class="required">*</span></label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($enseignant['nom'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control" placeholder="Ex: Kabaya">
        </div>

        <div class="form-group">
            <label for="prenom">Prénom <span class="required">*</span></label>
            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($enseignant['prenom'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control" placeholder="Ex: Alain">
        </div>

        <div class="form-group">
            <label for="email">Email <span class="required">*</span></label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($enseignant['email'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control" placeholder="Ex: alain.kabaya@university.com">
        </div>

        <div class="form-group">
            <label for="telephone">Téléphone</label>
            <input type="text" id="telephone" name="telephone" value="<?= htmlspecialchars($enseignant['telephone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control" placeholder="Ex: 0799999195">
        </div>
    </div>

    <div class="form-section">
        <h3>Informations Académiques</h3>
        
        <div class="form-group">
            <label for="matricule">Matricule <span class="required">*</span></label>
            <input type="text" id="matricule" name="matricule" value="<?= htmlspecialchars($enseignant['matricule'], ENT_QUOTES, 'UTF-8') ?>" required class="form-control" placeholder="Ex: ENS2024001">
        </div>

        <div class="form-group">
            <label for="departement">Spécialité / Département</label>
            <input type="text" id="departement" name="departement" value="<?= htmlspecialchars($enseignant['departement'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control" placeholder="Ex: Informatique">
        </div>
    </div>

    <div class="form-section">
        <h3>Photo de Profil</h3>
        
        <div class="form-group">
            <label for="photo">Photo</label>
            <div class="photo-upload">
                <input type="file" id="photo" name="photo" class="photo-input" accept="image/jpeg,image/png,image/gif" onchange="previewPhoto(event)">
                
                <?php if (!empty($enseignant['photo'])): ?>
                    <div id="photo-preview" class="photo-preview">
                        <img id="preview-img" src="<?= base_url($enseignant['photo']) ?>" alt="Photo actuelle" class="preview-img">
                        <button type="button" class="btn-remove-photo" onclick="removePhoto()">Changer de photo</button>
                    </div>
                    <div id="photo-placeholder" class="photo-placeholder" style="display: none;">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Cliquez ou glissez une image</p>
                        <small>JPG, PNG ou GIF - Max 5MB</small>
                    </div>
                <?php else: ?>
                    <div id="photo-preview" class="photo-preview" style="display: none;">
                        <img id="preview-img" src="" alt="Aperçu" class="preview-img">
                        <button type="button" class="btn-remove-photo" onclick="removePhoto()">Changer</button>
                    </div>
                    <div id="photo-placeholder" class="photo-placeholder">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Cliquez ou glissez une image</p>
                        <small>JPG, PNG ou GIF - Max 5MB</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?= base_url('/enseignants') ?>" class="btn btn-secondary">Annuler</a>
        <button class="btn btn-primary" type="submit">
            <i class="fas fa-save"></i> Mettre à jour
        </button>
    </div>
</form>

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
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    background-color: white;
    border-color: #0056b3;
    box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
}

.photo-upload {
    position: relative;
}

.photo-input {
    display: none;
}

.photo-placeholder {
    padding: 40px;
    border: 2px dashed #ddd;
    border-radius: 8px;
    text-align: center;
    background-color: #f8f9fa;
    cursor: pointer;
    transition: all 0.2s;
}

.photo-placeholder:hover {
    border-color: #0056b3;
    background-color: #f0f5ff;
}

.photo-placeholder i {
    font-size: 40px;
    color: #0056b3;
    margin-bottom: 10px;
    display: block;
}

.photo-placeholder p {
    margin: 10px 0 5px 0;
    font-weight: 500;
    color: #333;
}

.photo-placeholder small {
    display: block;
    color: #999;
}

.photo-preview {
    position: relative;
    text-align: center;
}

.preview-img {
    max-width: 200px;
    max-height: 200px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.btn-remove-photo {
    padding: 8px 16px;
    background-color: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-remove-photo:hover {
    background-color: #c82333;
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
</style>

<script>
function previewPhoto(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('photo-preview').style.display = 'block';
            document.getElementById('photo-placeholder').style.display = 'none';
        };
        reader.readAsDataURL(file);
    }
}

function removePhoto() {
    document.getElementById('photo').value = '';
    document.getElementById('photo-preview').style.display = 'none';
    document.getElementById('photo-placeholder').style.display = 'block';
    document.getElementById('preview-img').src = '';
}

// Cliquer sur le placeholder pour ouvrir le sélecteur de fichier
document.addEventListener('DOMContentLoaded', function() {
    const placeholder = document.querySelector('.photo-placeholder');
    const photoInput = document.getElementById('photo');
    if (placeholder) {
        placeholder.addEventListener('click', function() {
            photoInput.click();
        });
        
        // Drag and drop
        placeholder.addEventListener('dragover', function(e) {
            e.preventDefault();
            placeholder.style.borderColor = '#0056b3';
            placeholder.style.backgroundColor = '#f0f5ff';
        });
        
        placeholder.addEventListener('dragleave', function() {
            placeholder.style.borderColor = '#ddd';
            placeholder.style.backgroundColor = '#f8f9fa';
        });
        
        placeholder.addEventListener('drop', function(e) {
            e.preventDefault();
            placeholder.style.borderColor = '#ddd';
            placeholder.style.backgroundColor = '#f8f9fa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                photoInput.files = files;
                previewPhoto({target: {files: files}});
            }
        });
    }
});
</script>
