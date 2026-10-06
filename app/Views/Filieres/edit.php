<h2>Modifier une filière</h2>
<form method="POST" action="<?= base_url('/filieres/' . $filiere['id'] . '/update') ?>" class="form-card">
    <?= csrf_field() ?>
    <label>Code</label>
    <input type="text" name="code" value="<?= htmlspecialchars($filiere['code'], ENT_QUOTES, 'UTF-8') ?>" required>

    <label>Nom</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($filiere['nom'], ENT_QUOTES, 'UTF-8') ?>" required>

    <label>Description</label>
    <textarea name="description" rows="4"><?= htmlspecialchars($filiere['description'], ENT_QUOTES, 'UTF-8') ?></textarea>

    <button class="btn btn-primary" type="submit">Mettre à jour</button>
</form>
