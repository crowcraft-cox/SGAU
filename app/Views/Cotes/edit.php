<h2>Modifier une cote</h2>
<form method="POST" action="<?= base_url('/cotes/' . $cote['id'] . '/update') ?>" class="form-card">
    <?= csrf_field() ?>
    <label>Code</label>
    <input type="text" name="code" value="<?= htmlspecialchars($cote['code'], ENT_QUOTES, 'UTF-8') ?>" required>

    <label>Nom</label>
    <input type="text" name="nom" value="<?= htmlspecialchars($cote['nom'], ENT_QUOTES, 'UTF-8') ?>" required>

    <button class="btn btn-primary" type="submit">Mettre à jour</button>
</form>
