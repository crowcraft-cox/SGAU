<h2>Ajouter une cote</h2>
<form method="POST" action="<?= base_url('/cotes/store') ?>" class="form-card">
    <?= csrf_field() ?>
    <label>Code</label>
    <input type="text" name="code" required>

    <label>Nom</label>
    <input type="text" name="nom" required>

    <button class="btn btn-primary" type="submit">Enregistrer</button>
</form>
