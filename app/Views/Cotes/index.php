<div class="page-title">
    <h2>Liste des cotes</h2>
    <a class="btn btn-primary" href="<?= base_url('/cotes/create') ?>">Ajouter une cote</a>
</div>

<?php if (empty($cotes)): ?>
    <p>Aucune cote trouvée.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Nom</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cotes as $cote): ?>
                <tr>
                    <td><?= htmlspecialchars($cote['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($cote['code'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($cote['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <a class="btn btn-secondary" href="/cotes/<?= $cote['id'] ?>/edit">Modifier</a>
                        <form method="POST" action="<?= base_url('/cotes/' . $cote['id'] . '/delete') ?>" class="inline-form" onsubmit="return confirm('Supprimer cette cote ?');">
                            <?= csrf_field() ?>
                            <button class="btn btn-danger" type="submit">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
