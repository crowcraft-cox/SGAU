<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Modifier le Domaine</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('/domaines/' . $domaine['id'] . '/update') ?>" method="POST" id="domaineForm">
                    <?= csrf_field() ?>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Code du Domaine <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required value="<?= htmlspecialchars($domaine['code'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nom du Domaine <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" required value="<?= htmlspecialchars($domaine['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nom du Doyen du Domaine <span class="text-danger">*</span></label>
                        <input type="text" name="doyen" class="form-control" required value="<?= htmlspecialchars($domaine['doyen'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: Prof. John Doe">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"><?= htmlspecialchars($domaine['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Filières et Orientations</h5>
                        <button type="button" class="btn btn-sm btn-success" id="addFiliereBtn">
                            <i class="fa-solid fa-plus"></i> Ajouter une Filière
                        </button>
                    </div>

                    <div id="filieresContainer">
                        <!-- Les filières existantes seront générées ici par PHP/JS -->
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?= base_url('/domaines') ?>" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<template id="filiereTemplate">
    <div class="card mb-3 border-primary filiere-item">
        <div class="card-header bg-light d-flex justify-content-between align-items-center p-2">
            <strong>Filière</strong>
            <button type="button" class="btn btn-sm btn-outline-danger remove-filiere-btn" title="Supprimer la filière"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="card-body p-3">
            <div class="mb-3">
                <label class="form-label">Nom de la Filière <span class="text-danger">*</span></label>
                <input type="text" class="form-control filiere-nom-input" required>
            </div>
            
            <div class="orientations-section ms-4 border-start border-2 ps-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0 text-muted">Orientations associées</label>
                    <button type="button" class="btn btn-sm btn-outline-success add-orientation-btn">
                        <i class="fa-solid fa-plus"></i> Ajouter une Orientation
                    </button>
                </div>
                <div class="orientations-container">
                    <!-- Les orientations seront ajoutées ici dynamiquement -->
                </div>
            </div>
        </div>
    </div>
</template>

<template id="orientationTemplate">
    <div class="input-group mb-2 orientation-item">
        <span class="input-group-text"><i class="fa-solid fa-arrow-right"></i></span>
        <input type="text" class="form-control orientation-nom-input" required>
        <button class="btn btn-outline-danger remove-orientation-btn" type="button"><i class="fa-solid fa-trash"></i></button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filieresContainer = document.getElementById('filieresContainer');
    const addFiliereBtn = document.getElementById('addFiliereBtn');
    const filiereTemplate = document.getElementById('filiereTemplate');
    const orientationTemplate = document.getElementById('orientationTemplate');
    let filiereIndex = 0;

    // Données existantes injectées depuis PHP
    const existingData = <?= json_encode($domaine['filieres'] ?? []) ?>;

    function addFiliere(nom = '') {
        const clone = filiereTemplate.content.cloneNode(true);
        const filiereItem = clone.querySelector('.filiere-item');
        
        const nomInput = clone.querySelector('.filiere-nom-input');
        nomInput.name = `filieres[${filiereIndex}][nom]`;
        nomInput.value = nom;
        
        filiereItem.dataset.index = filiereIndex;

        clone.querySelector('.remove-filiere-btn').addEventListener('click', function(e) {
            if(confirm('Supprimer cette filière ?')) {
                e.target.closest('.filiere-item').remove();
            }
        });

        clone.querySelector('.add-orientation-btn').addEventListener('click', function(e) {
            addOrientation(e.target.closest('.filiere-item'));
        });

        filieresContainer.appendChild(clone);
        
        const addedFiliere = filieresContainer.lastElementChild;
        filiereIndex++;
        return addedFiliere;
    }

    function addOrientation(filiereElement, nom = '') {
        const index = filiereElement.dataset.index;
        const orientationsContainer = filiereElement.querySelector('.orientations-container');
        
        const clone = orientationTemplate.content.cloneNode(true);
        const nomInput = clone.querySelector('.orientation-nom-input');
        
        nomInput.name = `filieres[${index}][orientations][]`;
        nomInput.value = nom;

        clone.querySelector('.remove-orientation-btn').addEventListener('click', function(e) {
            e.target.closest('.orientation-item').remove();
        });

        orientationsContainer.appendChild(clone);
    }

    addFiliereBtn.addEventListener('click', () => {
        const el = addFiliere();
        addOrientation(el);
    });

    // Charger les données existantes
    if (existingData.length > 0) {
        existingData.forEach(filiere => {
            const filiereEl = addFiliere(filiere.nom);
            if (filiere.orientations && filiere.orientations.length > 0) {
                filiere.orientations.forEach(ori => {
                    addOrientation(filiereEl, ori.nom);
                });
            } else {
                addOrientation(filiereEl); // one empty orientation by default
            }
        });
    } else {
        // Ajouter une filière par défaut si aucune donnée
        const el = addFiliere();
        addOrientation(el);
    }
});
</script>
