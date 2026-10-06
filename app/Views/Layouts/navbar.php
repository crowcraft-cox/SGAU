<div class="navbar">
    <div class="left"></div>

    <div class="center">
        <a href="<?= base_url('/chat') ?>" class="nav-menu-item" title="Chat et messages">
            <i class="fa-solid fa-comments"></i>
            <span>Chat</span>
        </a>
        <?php if (RoleMiddleware::isAdmin() || RoleMiddleware::isEnseignant() || RoleMiddleware::isEtudiant()): ?>
            <a href="<?= base_url('/validations') ?>" class="nav-menu-item" title="Gestion des validations">
                <i class="fa-solid fa-certificate"></i>
                <span>Valve</span>
            </a>
        <?php endif; ?>
        <?php if (RoleMiddleware::isEtudiant()): ?>
            <a href="<?= base_url('/mon-bilan-financier') ?>" class="nav-menu-item" title="Mon bilan financier">
                <i class="fa-solid fa-wallet"></i>
                <span>Mon Bilan</span>
            </a>
        <?php endif; ?>
    </div>

    <div class="right">
        <a href="<?= base_url('/notifications') ?>" class="notification-bell" title="Notifications">
            <i class="fa-solid fa-bell"></i>
            <span id="notification-badge" class="badge" style="display: none;">0</span>
        </a>
        <span class="user-name"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Utilisateur', ENT_QUOTES, 'UTF-8') ?></span>
        <a href="<?= base_url('/logout') ?>" class="logout-btn">Déconnexion</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const badge = document.getElementById('notification-badge');
    
    function updateNotificationCount() {
        fetch('<?= base_url('/api/notifications/unread-count') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.count > 0) {
                    badge.textContent = data.count > 99 ? '99+' : data.count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            })
            .catch(err => console.error('Erreur notifications:', err));
    }

    // Mettre à jour immédiatement puis toutes les 30 secondes
    updateNotificationCount();
    window.updateNotificationCount = updateNotificationCount; // Make it global for other scripts
    setInterval(updateNotificationCount, 30000);
});
</script>

<style>
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 1rem;
    background-color: #001A72;
    color: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar .left h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.navbar .center {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.nav-menu-item {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.35rem 0.75rem;
    color: white;
    text-decoration: none;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    transition: all 0.2s;
    font-weight: 500;
    font-size: 0.9rem;
}

.nav-menu-item:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.navbar .right {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 1.5rem;
}

.notification-bell {
    position: relative;
    color: white;
    text-decoration: none;
    font-size: 1.1rem;
    padding: 0.5rem;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.15s ease;
    width: 34px;
    height: 34px;
}

.notification-bell:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.notification-bell .badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: #ef4444;
    color: white;
    font-size: 0.7rem;
    font-weight: bold;
    min-width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2px;
    border: 2px solid #1e3a8a;
}

.user-name {
    font-weight: 500;
}

.logout-btn {
    color: white;
    text-decoration: none;
    font-weight: 500;
    padding: 0.5rem 1rem;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    transition: all 0.2s;
}

.logout-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

@media (max-width: 768px) {
    .navbar {
        flex-wrap: wrap;
        gap: 1rem;
    }
    .navbar .left, .navbar .center, .navbar .right {
        flex: 1 1 auto;
        justify-content: center;
    }
    .nav-menu-item span {
        display: none;
    }
}
</style>
