/**
 * Gestion des aperçus de photos
 */

function previewPhoto(input) {
    const preview = document.getElementById('photoPreview');
    const previewImg = document.getElementById('previewImg');

    if (!preview || !previewImg) {
        console.error('Éléments de prévisualisation non trouvés');
        return;
    }

    const file = input.files ? input.files[0] : null;

    if (file) {
        // Vérifier la taille du fichier (5MB)
        const maxSize = 5 * 1024 * 1024;
        if (file.size > maxSize) {
            alert('La photo dépasse la taille maximale autorisée (5MB)');
            input.value = '';
            preview.style.display = 'none';
            return;
        }

        // Vérifier le type de fichier
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format de fichier non autorisé. Formats acceptés: JPG, PNG, GIF');
            input.value = '';
            preview.style.display = 'none';
            return;
        }

        // Afficher l'aperçu
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.onerror = function() {
            alert('Erreur lors de la lecture du fichier');
            input.value = '';
            preview.style.display = 'none';
        };
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
}

/**
 * Gestion du drag and drop pour les photos
 */
function setupPhotoDragDrop(fileInputId) {
    const fileInput = document.getElementById(fileInputId);
    if (!fileInput) return;

    const dropZone = fileInput.closest('.photo-upload') || fileInput;

    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.backgroundColor = '#f0f0f0';
        dropZone.style.borderColor = '#0056b3';
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.backgroundColor = '';
        dropZone.style.borderColor = '';
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.backgroundColor = '';
        dropZone.style.borderColor = '';

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            previewPhoto(fileInput);
        }
    });
}

// Initialiser les aperçus au chargement du DOM
document.addEventListener('DOMContentLoaded', function() {
    const photoInputs = document.querySelectorAll('input[type="file"][name="photo"]');
    photoInputs.forEach(input => {
        setupPhotoDragDrop(input.id);
    });
});