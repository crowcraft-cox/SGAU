<div class="sidebar">
    <h2 class="logo" style="display: flex; align-items: center; gap: 10px;">
        <img src="<?= base_url('/assets/images/' . settings('university_logo', 'openlu v1.jpg')) ?>" alt="Logo" style="height: 35px; width: auto; border-radius: 4px; background-color: white; padding: 2px;">
        <?= htmlspecialchars(settings('university_name', 'SGAU-OPENLU'), ENT_QUOTES, 'UTF-8') ?>
    </h2>

    <ul>
        <li><a href="<?= base_url('/dashboard') ?>"><span class="icon"><i class="fa-solid fa-house"></i></span> Dashboard</a></li>
        
        <?php if (RoleMiddleware::isAdmin() || RoleMiddleware::isEnseignant() || RoleMiddleware::isEtudiant()): ?>
            <li><a href="<?= base_url('/etudiants') ?>"><span class="icon"><i class="fa-solid fa-user-graduate"></i></span> Étudiants</a></li>
            <li><a href="<?= base_url('/dossiers-etudiants') ?>"><span class="icon"><i class="fa-solid fa-folder-open"></i></span> Dossiers Étudiant</a></li>
        <?php endif; ?>

        <?php if (RoleMiddleware::isAdmin()): ?>
            <li><a href="<?= base_url('/enseignants') ?>"><span class="icon"><i class="fa-solid fa-chalkboard-user"></i></span> Enseignants</a></li>
            <li><a href="<?= base_url('/domaines') ?>"><span class="icon"><i class="fa-solid fa-sitemap"></i></span> Domaines & Niveaux</a></li>
            <li><a href="<?= base_url('/cours') ?>"><span class="icon"><i class="fa-solid fa-book-open"></i></span> Cours</a></li>
            <li><a href="<?= base_url('/credits') ?>"><span class="icon"><i class="fa-solid fa-credit-card"></i></span> Crédits</a></li>
        <?php elseif (RoleMiddleware::isEnseignant()): ?>
            <li><a href="<?= base_url('/cours') ?>"><span class="icon"><i class="fa-solid fa-book-open"></i></span> Cours</a></li>
        <?php endif; ?>

        <?php if (RoleMiddleware::isAdmin() || RoleMiddleware::isFinance()): ?>
            <li><a href="<?= base_url('/finance') ?>"><span class="icon"><i class="fa-solid fa-wallet"></i></span> Finance (Recettes)</a></li>
        <?php endif; ?>

        <?php if (RoleMiddleware::isAdmin() || RoleMiddleware::isEnseignant() || RoleMiddleware::isEtudiant()): ?>
            <li><a href="<?= base_url('/notes') ?>"><span class="icon"><i class="fa-solid fa-file-lines"></i></span> Notes</a></li>
        <?php endif; ?>

        <?php if (RoleMiddleware::isAdmin()): ?>
            <li>
                <a href="<?= base_url('/demandes-modification-notes') ?>" style="display: flex; justify-content: space-between; align-items: center;">
                    <span><span class="icon"><i class="fa-solid fa-file-signature"></i></span> Dérogations Notes (SGA)</span>
                </a>
            </li>
        <?php elseif (RoleMiddleware::isEnseignant()): ?>
            <li>
                <a href="<?= base_url('/demandes-modification-notes') ?>">
                    <span class="icon"><i class="fa-solid fa-file-signature"></i></span> Mes Demandes SGA
                </a>
            </li>
        <?php endif; ?>


        <?php if (RoleMiddleware::isAdmin() || RoleMiddleware::isEtudiant()): ?>
            <li><a href="<?= base_url('/releves') ?>"><span class="icon"><i class="fa-solid fa-file-invoice"></i></span> Relevé de cotes</a></li>
        <?php endif; ?>



        <?php if (RoleMiddleware::isAdmin()): ?>
            <li><a href="<?= base_url('/parametres') ?>"><span class="icon"><i class="fa-solid fa-gear"></i></span> Paramètres</a></li>
        <?php endif; ?>

    </ul>
</div>

<script>
(function() {
    var currentPath = window.location.pathname;
    var links = document.querySelectorAll('.sidebar ul li a');
    var bestMatch = null;
    var bestLen = 0;

    links.forEach(function(link) {
        var href = link.getAttribute('href');
        if (!href) return;
        // Exact match or starts-with for sub-paths (excluding root '/')
        if (currentPath === href || (href.length > 1 && currentPath.startsWith(href))) {
            if (href.length > bestLen) {
                bestLen = href.length;
                bestMatch = link;
            }
        }
    });

    if (bestMatch) {
        bestMatch.classList.add('active');
    }
})();
</script>