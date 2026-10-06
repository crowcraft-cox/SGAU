<h2>Ajouter une filière</h2>
<form method="POST" action="<?= base_url('/filieres/store') ?>" class="form-card">
    <?= csrf_field() ?>
    <label>Code</label>
    <input type="text" name="code" required>

    <label>Nom</label>
    <input type="text" name="nom" required>

    <label>Description</label>
    <textarea name="description" rows="4"></textarea>

    <button class="btn btn-primary" type="submit">Enregistrer</button>
</form>
