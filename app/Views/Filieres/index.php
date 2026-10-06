<div class="page-title">
    <h2>Liste des filières</h2>
    <a class="btn btn-primary" href="<?= base_url('/filieres/create') ?>">Ajouter une filière</a>
</div>

<?php if (empty($filieres)): ?>
    <p>Aucune filière trouvée.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Nom</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($filieres as $filiere): ?>
                <tr>
                    <td><?= htmlspecialchars($filiere['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($filiere['code'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($filiere['nom'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($filiere['description'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td class="actions-column">
                        <div class="action-buttons">
                            <a class="btn btn-sm btn-info" href="<?= base_url('/filieres/' . $filiere['id'] . '/edit') ?>" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="<?= base_url('/filieres/' . $filiere['id'] . '/delete') ?>" class="inline-form" onsubmit="return confirm('Supprimer cette filière ?');">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-primary" type="submit" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
