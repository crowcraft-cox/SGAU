<div class="page-header">
    <h1>Gestion des Rôles</h1>
    <p class="subtitle">Configurez les rôles et les permissions des utilisateurs</p>
</div>

<div class="roles-grid">
    <?php foreach ($roleDescriptions as $roleKey => $roleDesc): ?>
        <div class="role-card">
            <div class="role-card-header">
                <h3><?= htmlspecialchars($roleDesc['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                <span class="user-count">
                    <i class="fa-solid fa-users"></i>
                    <?= $stats[$roleKey] ?> utilisateur<?= $stats[$roleKey] > 1 ? 's' : '' ?>
                </span>
            </div>

            <div class="role-card-content">
                <p class="description"><?= htmlspecialchars($roleDesc['description'], ENT_QUOTES, 'UTF-8') ?></p>

                <div class="permissions">
                    <h4>Permissions:</h4>
                    <ul>
                        <?php foreach ($roleDesc['permissions'] as $permission): ?>
                            <li>
                                <i class="fa-solid fa-check"></i>
                                <?= htmlspecialchars($permission, ENT_QUOTES, 'UTF-8') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <?php if (isset($roleDesc['restrictions'])): ?>
                    <div class="restrictions">
                        <h4>Restrictions:</h4>
                        <ul>
                            <?php foreach ($roleDesc['restrictions'] as $restriction): ?>
                                <li>
                                    <i class="fa-solid fa-xmark"></i>
                                    <?= htmlspecialchars($restriction, ENT_QUOTES, 'UTF-8') ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="role-actions">
                    <a href="<?= base_url('/parametres/users?role=' . $roleKey) ?>" class="btn btn-secondary">
                        <i class="fa-solid fa-users"></i> Voir les utilisateurs
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="info-section">
    <h2>Résumé des Rôles</h2>
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon admin">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="stat-content">
                <h4>Administrateurs</h4>
                <p class="stat-number"><?= $stats['admin'] ?></p>
                <p class="stat-description">Accès complet au système</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon enseignant">
                <i class="fa-solid fa-chalkboard-user"></i>
            </div>
            <div class="stat-content">
                <h4>Enseignants</h4>
                <p class="stat-number"><?= $stats['enseignant'] ?></p>
                <p class="stat-description">Gestion des cours et notes</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon etudiant">
                <i class="fa-solid fa-user-graduate"></i>
            </div>
            <div class="stat-content">
                <h4>Étudiants</h4>
                <p class="stat-number"><?= $stats['etudiant'] ?></p>
                <p class="stat-description">Accès à leur profil</p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon finance">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="stat-content">
                <h4>Service Financier</h4>
                <p class="stat-number"><?= $stats['finance'] ?></p>
                <p class="stat-description">Gestion des paiements</p>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    color: #1e293b;
}

.subtitle {
    margin: 0;
    color: #64748b;
}

.roles-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 3rem;
}

.role-card {
    background: white;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.role-card:hover {
    border-color: #cbd5e1;
}

.role-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.25rem;
    background: #001A72;
    color: white;
}

.role-card-header h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
    font-weight: 700;
}

.user-count {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: rgba(255, 255, 255, 0.2);
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.role-card-content {
    padding: 1.5rem;
}

.description {
    margin: 0 0 1rem 0;
    color: #475569;
    font-style: italic;
}

.permissions {
    margin-bottom: 1.5rem;
}

.permissions h4 {
    margin: 0 0 0.75rem 0;
    font-size: 0.95rem;
    color: #1e293b;
}

.permissions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.permissions li {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.5rem 0;
    color: #475569;
    font-size: 0.9rem;
}

.permissions i {
    color: #10b981;
    margin-top: 0.2rem;
}

.restrictions h4 {
    margin: 0.75rem 0 0.5rem 0;
    font-size: 0.95rem;
    color: #991b1b;
}

.restrictions ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.restrictions li {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding: 0.3rem 0;
    color: #991b1b;
    font-size: 0.9rem;
    opacity: 0.8;
}

.restrictions i {
    color: #dc2626;
    margin-top: 0.2rem;
}

.role-actions {
    display: flex;
    gap: 0.75rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    flex: 1;
    text-align: center;
}

.btn-secondary {
    background: #e2e8f0;
    color: #1e293b;
}

.btn-secondary:hover {
    background: #cbd5e1;
}

.info-section {
    margin-top: 3rem;
}

.info-section h2 {
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
    color: #1e293b;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.stat-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.stat-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 12px;
    font-size: 1.5rem;
    color: white;
}

.stat-icon.admin {
    background: #dc2626;
}

.stat-icon.enseignant {
    background: #2563eb;
}

.stat-icon.etudiant {
    background: #059669;
}

.stat-icon.finance {
    background: #d97706;
}

.stat-content h4 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
}

.stat-number {
    margin: 0 0 0.25rem 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e40af;
}

.stat-description {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
}
</style>
