<h2>Ajouter un relevé</h2>

<div class="form-card" style="max-width: 600px; margin: 20px auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <form method="POST" action="<?= base_url('/releves/store') ?>" id="releveForm">
        <?= csrf_field() ?>

        <!-- Étudiant -->
        <div style="margin-bottom: 20px;">
            <label for="etudiant_id" style="display: block; font-weight: 600; margin-bottom: 5px;">
                Étudiant <span style="color: red;">*</span>
            </label>
            <select id="etudiant_id" name="etudiant_id" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                <option value="">-- Sélectionner un étudiant --</option>
                <?php foreach ($etudiants as $etudiant): ?>
                    <option value="<?= $etudiant['id'] ?>">
                        <?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Spinner de chargement -->
        <div id="loadingSpinner" style="display:none; text-align:center; padding: 15px;">
            <i class="fa-solid fa-circle-notch fa-spin" style="font-size:1.5rem; color:#001A72;"></i>
            <p style="margin-top:8px; color:#64748b;">Chargement des données...</p>
        </div>

        <!-- Contenu généré dynamiquement via AJAX -->
        <div id="contenuDynamique"></div>

        <!-- Champs cachés -->
        <input type="hidden" id="moyenne" name="moyenne" value="0">
        <input type="hidden" id="statut" name="statut" value="Échoué">

        <!-- Boutons -->
        <div style="margin-top: 25px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary" style="flex: 1; padding: 12px; background: #001A72; color: white; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">
                <i class="fa-solid fa-check"></i> Enregistrer le relevé
            </button>
            <a href="<?= base_url('/releves') ?>" class="btn btn-secondary" style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 4px; font-weight: 600; text-align: center; text-decoration: none;">
                <i class="fa-solid fa-xmark"></i> Annuler
            </a>
        </div>
    </form>
</div>

<style>
    .badge {
        display: inline-block;
        font-weight: 600;
        font-size: 12px;
    }
    .badge-success { color: #155724; }
    .badge-danger  { color: #721c24; }
    .releve-table {
        width: 100%;
        border-collapse: collapse;
        margin: 15px 0;
    }
    .releve-table th, .releve-table td {
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid #e2e8f0;
        font-size: 13px;
    }
    .releve-table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .info-box {
        background: #e7f3ff;
        padding: 15px;
        margin: 15px 0;
        border-radius: 4px;
    }
</style>

<script>
const apiSemestresBase = '<?= rtrim(base_url('/api/etudiants/'), '/') ?>/';
const apiReleveBase    = '<?= rtrim(base_url('/api/etudiants/'), '/') ?>';

document.getElementById('etudiant_id').addEventListener('change', function () {
    const etudiantId = this.value;
    const conteneur = document.getElementById('contenuDynamique');
    const spinner  = document.getElementById('loadingSpinner');

    conteneur.innerHTML = '';
    document.getElementById('moyenne').value = '0';
    document.getElementById('statut').value  = 'Échoué';

    if (!etudiantId) return;

    // Afficher le spinner
    spinner.style.display = 'block';

    // Appel AJAX pour récupérer les semestres disponibles
    fetch(apiSemestresBase + etudiantId + '/semestres')
        .then(r => r.json())
        .then(data => {
            spinner.style.display = 'none';

            if (!data.success || !data.semestres || data.semestres.length === 0) {
                conteneur.innerHTML = `
                    <div class="info-box" style="color: #856404; background: #fff3cd;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        Cet étudiant n'a pas de notes enregistrées.
                    </div>`;
                return;
            }

            // Créer le sélecteur de semestres
            let html = `
                <div style="margin-bottom: 20px;">
                    <label for="semestre" style="display:block; font-weight:600; margin-bottom:5px;">
                        Semestre <span style="color:red;">*</span>
                    </label>
                    <select id="semestre" name="semestre" required
                            style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px; font-size:14px;">
                        <option value="">-- Sélectionner un semestre --</option>`;

            for (const s of data.semestres) {
                html += `<option value="${s}">Semestre ${s}</option>`;
            }

            html += `   </select>
                </div>
                <div id="releveData"></div>`;

            conteneur.innerHTML = html;

            document.getElementById('semestre').addEventListener('change', function () {
                chargerReleve(etudiantId, this.value);
            });
        })
        .catch(err => {
            spinner.style.display = 'none';
            console.error(err);
            conteneur.innerHTML = `<div class="info-box" style="color:#721c24; background:#f8d7da;">
                <i class="fa-solid fa-circle-exclamation"></i> Erreur lors du chargement des données.
            </div>`;
        });
});

function chargerReleve(etudiantId, semestre) {
    if (!semestre) return;

    const releveDiv = document.getElementById('releveData');
    releveDiv.innerHTML = '<p style="color:#64748b;"><i class="fa-solid fa-circle-notch fa-spin"></i> Chargement du relevé...</p>';

    fetch(apiReleveBase + '/' + etudiantId + '/releve?semestre=' + semestre)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                releveDiv.innerHTML = `<p style="color:#dc2626;">
                    <i class="fa-solid fa-circle-exclamation"></i> ${data.error || 'Pas de données pour ce semestre.'}
                </p>`;
                return;
            }

            const d = data.data;

            // Mettre à jour les champs cachés
            document.getElementById('moyenne').value = d.moyenne || 0;
            document.getElementById('statut').value  = d.statut  || 'Échoué';

            const statutBadge = d.statut === 'Réussi'
                ? '<span class="badge badge-success">RÉUSSI</span>'
                : '<span class="badge badge-danger">ÉCHOUÉ</span>';

            let html = `
                <div class="info-box">
                    <h4 style="margin-top:0;"><i class="fa-solid fa-circle-info"></i> Informations du Relevé</h4>
                    <p><strong>Nombre de cours :</strong> ${d.nombreCours || 0}</p>
                    <p><strong>Moyenne Générale :</strong>
                        <span style="font-size:1.3em; color:#001A72; font-weight:700;">
                            ${parseFloat(d.moyenne || 0).toFixed(2)}
                        </span> / 20
                    </p>
                    <p><strong>Statut :</strong> ${statutBadge}</p>
                </div>`;

            if (d.notes && d.notes.length > 0) {
                html += `
                    <h4><i class="fa-solid fa-list-check"></i> Détail des notes par cours</h4>
                    <table class="releve-table">
                        <thead>
                            <tr>
                                <th>Cours</th>
                                <th>Contrôle Continu</th>
                                <th>TP</th>
                                <th>Examen</th>
                                <th>Note Finale</th>
                            </tr>
                        </thead>
                        <tbody>`;

                for (const note of d.notes) {
                    const cc = typeof note.interro === 'number' ? note.interro.toFixed(2) : (note.interro || '-');
                    const tp = typeof note.tp      === 'number' ? note.tp.toFixed(2)      : (note.tp      || '-');
                    const ex = typeof note.examen  === 'number' ? note.examen.toFixed(2)  : (note.examen  || '-');
                    const nf = typeof note.note    === 'number' ? note.note.toFixed(2)    : (note.note    || '-');

                    html += `
                        <tr>
                            <td><strong>${note.cours_nom || 'Sans titre'}</strong></td>
                            <td>${cc}</td>
                            <td>${tp}</td>
                            <td>${ex}</td>
                            <td><strong style="color:#0052CC;">${nf}</strong></td>
                        </tr>`;
                }

                html += `</tbody></table>`;
            } else {
                html += `<p style="color:#666;">Aucune note disponible pour ce semestre.</p>`;
            }

            releveDiv.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            releveDiv.innerHTML = `<p style="color:#dc2626;">
                <i class="fa-solid fa-circle-exclamation"></i> Erreur lors du chargement du relevé.
            </p>`;
        });
}

// Validation avant soumission
document.getElementById('releveForm').addEventListener('submit', function (e) {
    const etudiant = document.getElementById('etudiant_id').value;
    const semestreEl = document.getElementById('semestre');
    const semestre = semestreEl ? semestreEl.value : '';

    if (!etudiant) {
        e.preventDefault();
        alert('Veuillez sélectionner un étudiant.');
        return false;
    }
    if (!semestre) {
        e.preventDefault();
        alert('Veuillez sélectionner un semestre.');
        return false;
    }
    return true;
});
</script>
