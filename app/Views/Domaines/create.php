<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Ajouter un Domaine</h5>
            </div>
            <div class="card-body">
                <form action="<?= base_url('/domaines/store') ?>" method="POST" id="domaineForm">
                    <?= csrf_field() ?>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Code du Domaine <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control" required placeholder="Ex: INFO">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Nom du Domaine <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" required placeholder="Ex: Sciences Informatiques">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nom du Doyen du Domaine <span class="text-danger">*</span></label>
                        <input type="text" name="doyen" class="form-control" required placeholder="Ex: Prof. John Doe">
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <hr>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Filières et Orientations</h5>
                        <button type="button" class="btn btn-sm btn-success" id="addFiliereBtn">
                            <i class="fa-solid fa-plus"></i> Ajouter une Filière
                        </button>
                    </div>

                    <div id="filieresContainer">
                        <!-- Les filières seront ajoutées ici dynamiquement -->
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?= base_url('/domaines') ?>" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
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
                <input type="text" class="form-control filiere-nom-input" required placeholder="Ex: Génie Logiciel">
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
        <input type="text" class="form-control orientation-nom-input" required placeholder="Nom de l'orientation">
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

    function addFiliere() {
        const clone = filiereTemplate.content.cloneNode(true);
        const filiereItem = clone.querySelector('.filiere-item');
        
        // Update names for form submission
        const nomInput = clone.querySelector('.filiere-nom-input');
        nomInput.name = `filieres[${filiereIndex}][nom]`;
        
        filiereItem.dataset.index = filiereIndex;

        // Events
        clone.querySelector('.remove-filiere-btn').addEventListener('click', function(e) {
            e.target.closest('.filiere-item').remove();
        });

        clone.querySelector('.add-orientation-btn').addEventListener('click', function(e) {
            addOrientation(e.target.closest('.filiere-item'));
        });

        filieresContainer.appendChild(clone);
        
        // Ajouter une orientation par défaut vide
        addOrientation(filieresContainer.lastElementChild);
        
        filiereIndex++;
    }

    function addOrientation(filiereElement) {
        const index = filiereElement.dataset.index;
        const orientationsContainer = filiereElement.querySelector('.orientations-container');
        
        const clone = orientationTemplate.content.cloneNode(true);
        const nomInput = clone.querySelector('.orientation-nom-input');
        
        nomInput.name = `filieres[${index}][orientations][]`;

        clone.querySelector('.remove-orientation-btn').addEventListener('click', function(e) {
            e.target.closest('.orientation-item').remove();
        });

        orientationsContainer.appendChild(clone);
    }

    addFiliereBtn.addEventListener('click', addFiliere);

    // Ajouter une filière par défaut au chargement
    addFiliere();
});
</script>
