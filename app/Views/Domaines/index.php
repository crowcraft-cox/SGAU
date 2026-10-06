<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Domaines, Filières et Orientations</h2>
    <a href="<?= base_url('/domaines/create') ?>" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Ajouter un Domaine
    </a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Nom du Domaine</th>
                        <th>Filières & Orientations</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($domaines)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucun domaine enregistré</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($domaines as $domaine): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($domaine['code'] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="fw-bold"><?= htmlspecialchars($domaine['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if (!empty($domaine['filieres'])): ?>
                                        <ul class="list-unstyled mb-0">
                                        <?php foreach ($domaine['filieres'] as $filiere): ?>
                                            <li>
                                                <i class="fa-solid fa-diagram-project text-primary me-1"></i> <strong><?= htmlspecialchars($filiere['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                                <?php if (!empty($filiere['orientations'])): ?>
                                                    <ul class="mb-2" style="font-size: 0.9em; color: #555;">
                                                        <?php foreach ($filiere['orientations'] as $ori): ?>
                                                            <li><i class="fa-solid fa-arrow-right text-success me-1"></i> <?= htmlspecialchars($ori['nom'] ?? '', ENT_QUOTES, 'UTF-8') ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">Aucune filière</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="<?= base_url('/domaines/' . $domaine['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="Modifier">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="<?= base_url('/domaines/' . $domaine['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce domaine ? Toutes les filières et orientations associées seront perdues.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
