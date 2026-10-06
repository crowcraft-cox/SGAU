<h2>Modifier un crédit</h2>
<form method="POST" action="<?= base_url('/credits/' . $credit['id'] . '/update') ?>" class="form-card">
    <?= csrf_field() ?>
    <label>Étudiant</label>
    <select name="etudiant_id" required>
        <option value="">Sélectionner un étudiant</option>
        <?php foreach ($etudiants as $etudiant): ?>
            <option value="<?= $etudiant['id'] ?>" <?= $credit['etudiant_id'] == $etudiant['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom'], ENT_QUOTES, 'UTF-8') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Crédits acquis</label>
    <input type="number" name="credits_acquis" value="<?= htmlspecialchars($credit['credits_acquis'], ENT_QUOTES, 'UTF-8') ?>" required>

    <label>Crédits requis</label>
    <input type="number" name="credits_requis" value="<?= htmlspecialchars($credit['credits_requis'], ENT_QUOTES, 'UTF-8') ?>" required>

    <button class="btn btn-primary" type="submit">Mettre à jour</button>
</form>
