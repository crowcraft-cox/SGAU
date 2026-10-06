<h2>Ajouter une notification</h2>
<form method="POST" action="<?= base_url('/notifications/store') ?>" class="form-card">
    <?= csrf_field() ?>
    <label>Titre</label>
    <input type="text" name="titre" required>

    <label>Lien de redirection (facultatif)</label>
    <input type="text" name="lien" placeholder="Ex: /notes, /cours, /demandes-modification-notes">

    <label>Message</label>
    <textarea name="message" rows="5" required></textarea>

    <label>
        <input type="checkbox" name="lue"> Marquer comme lue
    </label>

    <button class="btn btn-primary" type="submit">Enregistrer</button>
</form>
