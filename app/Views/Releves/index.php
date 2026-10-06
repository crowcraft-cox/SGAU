<div class="page-title">
    <h2>Liste des relevés</h2>
    <?php if (RoleMiddleware::isAdmin()): ?>
        <a class="btn btn-primary" href="<?= base_url('/releves/create') ?>">Ajouter un relevé</a>
    <?php endif; ?>
</div>

<?php if (empty($releves)): ?>
    <p>Aucun relevé trouvé.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Étudiant</th>
                <th>Semestre</th>
                <th>Moyenne</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($releves as $releve): ?>
                <tr>
                    <td><?= htmlspecialchars($releve['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <a href="<?= base_url('/releves/' . $releve['id']) ?>" style="color: #0052CC; text-decoration: none; font-weight: 500;">
                            <?= htmlspecialchars($releve['etudiant_nom'] . ' ' . $releve['etudiant_prenom'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($releve['semestre'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <strong><?= htmlspecialchars($releve['moyenne'], ENT_QUOTES, 'UTF-8') ?></strong>%
                    </td>
                    <td>
                        <?php if ($releve['statut'] === 'Réussi'): ?>
                            <span style="background-color: #f0fdf4; color: #166534; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; border: 1px solid #bbf7d0;">Réussi</span>
                        <?php else: ?>
                            <span style="background-color: #fef2f2; color: #991b1b; padding: 4px 12px; border-radius: 4px; font-weight: 600; font-size: 12px; border: 1px solid #fecaca;">Échoué</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (isset($releve['releve_bloque']) && $releve['releve_bloque'] == 1 && RoleMiddleware::isEtudiant()): ?>
                            <span style="color: #d32f2f; font-size: 13px; font-weight: 500;">
                                <i class="fas fa-lock"></i> Bloqué par la finance
                            </span>
                        <?php else: ?>
                            <a class="btn btn-secondary" href="<?= base_url('/releves/' . $releve['id']) ?>">Voir / Télécharger</a>
                            <?php if (RoleMiddleware::isAdmin()): ?>
                                <a class="btn btn-secondary" href="<?= base_url('/releves/' . $releve['id'] . '/edit') ?>">Modifier</a>
                                <form method="POST" action="<?= base_url('/releves/' . $releve['id'] . '/delete') ?>" class="inline-form" onsubmit="return confirm('Supprimer ce relevé ?');">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-danger" type="submit">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<style>
    .page-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e0e0e0;
    }

    .page-title h2 {
        margin: 0;
        font-size: 28px;
        color: #333;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        border: none;
        cursor: pointer;
        font-size: 14px;
        display: inline-block;
    }

    .btn-primary {
        background-color: #0052CC;
        color: white;
    }

    .btn-primary:hover {
        background-color: #003d99;
    }

    .btn-secondary {
        background-color: #f0f2f5;
        color: #333;
    }

    .btn-secondary:hover {
        background-color: #e4e6eb;
    }

    .btn-danger {
        background-color: #f44336;
        color: white;
    }

    .btn-danger:hover {
        background-color: #da190b;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 4px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .table thead {
        background-color: #f5f5f5;
        border-bottom: 2px solid #e0e0e0;
    }

    .table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }

    .table td {
        padding: 15px;
        border-bottom: 1px solid #e0e0e0;
        color: #666;
    }

    .table tbody tr:hover {
        background-color: #fafafa;
    }

    .inline-form {
        display: inline;
        margin: 0;
    }
</style>
