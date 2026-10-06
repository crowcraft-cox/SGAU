<h2>Modifier une notification</h2>
<form method="POST" action="<?= base_url('/notifications/' . $notification['id'] . '/update') ?>" class="form-card">
    <?= csrf_field() ?>
    <label>Titre</label>
    <input type="text" name="titre" value="<?= htmlspecialchars($notification['titre'], ENT_QUOTES, 'UTF-8') ?>" required>

    <label>Lien de redirection (facultatif)</label>
    <input type="text" name="lien" value="<?= htmlspecialchars($notification['lien'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: /notes, /cours, /demandes-modification-notes">

    <label>Message</label>
    <textarea name="message" rows="5" required><?= htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8') ?></textarea>

    <label>
        <input type="checkbox" name="lue" <?= $notification['lue'] ? 'checked' : '' ?>> Marquer comme lue
    </label>

    <button class="btn btn-primary" type="submit">Mettre à jour</button>
</form>
