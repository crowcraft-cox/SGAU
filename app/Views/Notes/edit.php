<div class="form-header">
    <h2>Modifier une Note</h2>
    <p class="form-subtitle">Mettez à jour les notes d'un étudiant (Interro, TP, Examen)</p>
</div>

<form method="POST" action="<?= base_url('/notes/' . $note['id'] . '/update') ?>" class="form-card form-modern">
    <?= csrf_field() ?>
    <div class="form-section">
        <h3>Informations de l'Étudiant</h3>
        
        <div class="form-group">
            <label for="etudiant_id">Étudiant <span class="required">*</span></label>
            <select id="etudiant_id" name="etudiant_id" required class="form-control">
                <option value="">- Sélectionner un étudiant -</option>
                <?php foreach ($etudiants as $etudiant): ?>
                    <option value="<?= $etudiant['id'] ?>" <?= $note['etudiant_id'] == $etudiant['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="cours_id">Cours <span class="required">*</span></label>
            <select id="cours_id" name="cours_id" required class="form-control">
                <option value="">- Sélectionner un cours -</option>
                <?php foreach ($cours as $item): ?>
                    <option value="<?= $item['id'] ?>" <?= $note['cours_id'] == $item['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($item['nom'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="semestre">Semestre <span class="required">*</span></label>
            <select id="semestre" name="semestre" required class="form-control">
                <option value="">- Sélectionner un semestre -</option>
                <option value="1" <?= $note['semestre'] == 1 ? 'selected' : '' ?>>Semestre 1</option>
                <option value="2" <?= $note['semestre'] == 2 ? 'selected' : '' ?>>Semestre 2</option>
                <option value="3" <?= $note['semestre'] == 3 ? 'selected' : '' ?>>Semestre 3</option>
                <option value="4" <?= $note['semestre'] == 4 ? 'selected' : '' ?>>Semestre 4</option>
                <option value="5" <?= $note['semestre'] == 5 ? 'selected' : '' ?>>Semestre 5</option>
                <option value="6" <?= $note['semestre'] == 6 ? 'selected' : '' ?>>Semestre 6</option>
            </select>
        </div>
    </div>

    <div class="form-section">
        <h3>Notes</h3>
        
        <div class="form-group">
            <label for="interro">Interro (sur 20)</label>
            <input type="number" id="interro" name="interro" min="0" max="20" step="0.5" placeholder="Ex: 12" value="<?= htmlspecialchars($note['interro'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="tp">TP (sur 20)</label>
            <input type="number" id="tp" name="tp" min="0" max="20" step="0.5" placeholder="Ex: 16" value="<?= htmlspecialchars($note['tp'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control">
        </div>

        <div class="form-group">
            <label for="examen">Examen (sur 20)</label>
            <input type="number" id="examen" name="examen" min="0" max="20" step="0.5" placeholder="Ex: 14" value="<?= htmlspecialchars($note['examen'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control">
        </div>

        <div class="form-group">
            <label>Note Générale (calculée automatiquement)</label>
            <input type="number" id="note" name="note" min="0" max="20" step="0.5" placeholder="Optionnel - calcul auto" value="<?= htmlspecialchars($note['note'] ?? '', ENT_QUOTES, 'UTF-8') ?>" class="form-control">
            <small class="form-text">Ce champ est optionnel. La moyenne sera affichée comme (Interro + TP + Examen) / 3</small>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?= base_url('/notes') ?>" class="btn btn-secondary">Annuler</a>
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
</style>
