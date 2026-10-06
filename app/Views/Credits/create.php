<h2>Ajouter un crédit</h2>
<form method="POST" action="<?= base_url('/credits/store') ?>" class="form-card">
    <?= csrf_field() ?>
    <label>Étudiant</label>
    <select name="etudiant_id" required>
        <option value="">Sélectionner un étudiant</option>
        <?php foreach ($etudiants as $etudiant): ?>
            <option value="<?= $etudiant['id'] ?>"><?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom'], ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>

    <label>Crédits acquis</label>
    <input type="number" name="credits_acquis" required>

    <label>Crédits requis</label>
    <input type="number" name="credits_requis" required>

    <button class="btn btn-primary" type="submit">Enregistrer</button>
</form>
