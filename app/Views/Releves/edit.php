<h2>Modifier un relevé</h2>
<form method="POST" action="<?= base_url('/releves/' . $releve['id'] . '/update') ?>" class="form-card">
    <?= csrf_field() ?>
    <label>Étudiant</label>
    <select name="etudiant_id" required>
        <option value="">Sélectionner un étudiant</option>
        <?php foreach ($etudiants as $etudiant): ?>
            <option value="<?= $etudiant['id'] ?>" <?= $releve['etudiant_id'] == $etudiant['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom'], ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Semestre</label>
    <input type="number" name="semestre" value="<?= htmlspecialchars($releve['semestre'], ENT_QUOTES, 'UTF-8') ?>" required>

    <label>Moyenne</label>
    <input type="number" step="0.01" name="moyenne" value="<?= htmlspecialchars($releve['moyenne'], ENT_QUOTES, 'UTF-8') ?>" required>

    <label>Statut</label>
    <input type="text" name="statut" value="<?= htmlspecialchars($releve['statut'], ENT_QUOTES, 'UTF-8') ?>" required>

    <button class="btn btn-primary" type="submit">Mettre à jour</button>
</form>
