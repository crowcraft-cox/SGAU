<div class="notifications-container">
    <div class="notifications-header">
        <h2><i class="fa-solid fa-bell"></i> Mes Notifications</h2>
        <div class="actions">
            <?php if (!empty($notifications)): ?>
                <form action="<?= base_url('/notifications/mark-all-read') ?>" method="POST" class="inline-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-check-double"></i> Tout marquer comme lu
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                <a href="<?= base_url('/notifications/create') ?>" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Créer
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-bell-slash"></i>
            <p>Vous n'avez aucune notification pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="notifications-list">
            <?php foreach ($notifications as $notification): ?>
                <?php
                    $hasLink = !empty($notification['lien']);
                    $targetUrl = $hasLink ? base_url($notification['lien']) : null;
                ?>
                <div class="notification-item <?= $notification['lue'] ? 'read' : 'unread' ?>" 
                     id="notification-<?= $notification['id'] ?>"
                     <?= $hasLink ? 'role="button" tabindex="0" data-href="' . htmlspecialchars($targetUrl, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
                    
                    <div class="notification-icon">
                        <?php
                            // Icône selon le type de notification basé sur le titre ou lien
                            $icon = 'fa-envelope';
                            if ($notification['lue']) {
                                $icon = 'fa-envelope-open';
                            } elseif (!empty($notification['lien'])) {
                                if (strpos($notification['lien'], '/notes') !== false) $icon = 'fa-file-lines';
                                elseif (strpos($notification['lien'], '/demandes') !== false) $icon = 'fa-file-signature';
                                elseif (strpos($notification['lien'], '/cours') !== false) $icon = 'fa-book-open';
                                elseif (strpos($notification['lien'], '/etudiants') !== false) $icon = 'fa-user-graduate';
                                elseif (strpos($notification['lien'], '/finance') !== false) $icon = 'fa-wallet';
                                else $icon = 'fa-bell';
                            }
                        ?>
                        <i class="fa-solid <?= $icon ?>"></i>
                    </div>

                    <div class="notification-content">
                        <div class="notification-top">
                            <h4 class="notification-title"><?= htmlspecialchars($notification['titre'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <div class="notification-meta-right">
                                <span class="notification-date"><?= timeAgo($notification['created_at']) ?></span>
                                <?php if ($hasLink): ?>
                                    <span class="notification-link-badge" title="Cliquez pour accéder au module concerné">
                                        <i class="fas fa-arrow-right"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <p class="notification-message"><?= htmlspecialchars($notification['message'], ENT_QUOTES, 'UTF-8') ?></p>
                        
                        <div class="notification-actions">
                            <?php if (!$notification['lue']): ?>
                                <button onclick="markAsRead(<?= $notification['id'] ?>, event)" class="btn-link text-primary">
                                    <i class="fas fa-check fa-xs"></i> Marquer comme lu
                                </button>
                            <?php endif; ?>
                            <form action="<?= base_url('/notifications/' . $notification['id'] . '/delete') ?>" method="POST" class="inline-form" onsubmit="return confirm('Supprimer cette notification ?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-link text-danger"><i class="fas fa-trash-alt fa-xs"></i> Supprimer</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
// Rendre les lignes cliquables si elles ont un lien
document.querySelectorAll('.notification-item[data-href]').forEach(function(item) {
    item.style.cursor = 'pointer';

    item.addEventListener('click', function(e) {
        // Ne pas déclencher si on clique sur un bouton ou lien interne
        if (e.target.closest('button') || e.target.closest('a') || e.target.closest('form')) {
            return;
        }
        const href = this.getAttribute('data-href');
        const notifId = this.id.replace('notification-', '');

        // Marquer comme lu en arrière plan puis rediriger
        markAsReadAndRedirect(notifId, href);
    });

    // Support clavier (Enter / Space)
    item.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            const href = this.getAttribute('data-href');
            const notifId = this.id.replace('notification-', '');
            markAsReadAndRedirect(notifId, href);
        }
    });
});

function markAsReadAndRedirect(id, href) {
    // Marquer comme lu via AJAX, puis rediriger dès la réponse (ou immédiatement)
    fetch(`<?= base_url('/notifications/') ?>${id}/mark-as-read?ajax=1`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '<?= csrf_token() ?>',
            'Content-Type': 'application/json'
        }
    })
    .catch(function() {
        // En cas d'erreur réseau, on redirige quand même
    })
    .finally(function() {
        window.location.href = href;
    });
}

function markAsRead(id, event) {
    event.stopPropagation();
    fetch(`<?= base_url('/notifications/') ?>${id}/mark-as-read?ajax=1`, { 
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '<?= csrf_token() ?>' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const item = document.getElementById(`notification-${id}`);
            item.classList.remove('unread');
            item.classList.add('read');
            item.querySelector('.notification-icon i').className = 'fa-solid fa-envelope-open';
            const btn = item.querySelector('button[onclick]');
            if (btn) btn.remove();
            if (typeof updateNotificationCount === 'function') {
                updateNotificationCount();
            }
        }
    });
}
</script>

<style>
.notifications-container {
    max-width: 860px;
    margin: 0 auto;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: none;
    overflow: hidden;
}

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #edf2f7;
    background: #f8fafc;
}

.notifications-header h2 {
    margin: 0;
    font-size: 1.25rem;
    color: #1a202c;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.notifications-header h2 i { color: #1e3a8a; }

.actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

.notifications-list {
    display: flex;
    flex-direction: column;
}

/* === Notification Item === */
.notification-item {
    display: flex;
    gap: 1rem;
    padding: 1.1rem 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    transition: background-color 0.15s ease;
    position: relative;
    background-color: #ffffff;
}

.notification-item:last-child { border-bottom: none; }

.notification-item.unread {
    background-color: #f8fafc;
}

.notification-item.read {
    background-color: #ffffff;
}

.notification-item[data-href]:hover {
    background-color: #f1f5f9;
}

/* === Icon === */
.notification-icon {
    font-size: 1.1rem;
    color: #94a3b8;
    padding-top: 0.2rem;
    min-width: 24px;
    text-align: center;
}

.unread .notification-icon { color: #001A72; }

/* === Content === */
.notification-content { flex: 1; }

.notification-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 4px;
    gap: 10px;
}

.notification-title {
    margin: 0;
    font-size: 14px;
    color: #0f172a;
}

.unread .notification-title { font-weight: 700; color: #001A72; }

.notification-meta-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.notification-date {
    font-size: 12px;
    color: #94a3b8;
    white-space: nowrap;
}

.notification-link-badge {
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.notification-message {
    margin: 0.35rem 0 0.6rem 0;
    color: #4a5568;
    line-height: 1.55;
    font-size: 0.9rem;
}

/* === Actions === */
.notification-actions {
    display: flex;
    gap: 14px;
    align-items: center;
    margin-top: 4px;
}

.btn-link {
    background: none;
    border: none;
    padding: 0;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: opacity 0.2s;
}

.btn-link:hover { opacity: 0.7; text-decoration: underline; }

.text-primary { color: #1e3a8a; }
.text-danger  { color: #ef4444; }
.text-info    { color: #0284c7; }

/* === Empty State === */
.empty-state {
    padding: 4rem 2rem;
    text-align: center;
    color: #94a3b8;
}

.empty-state i { font-size: 3rem; margin-bottom: 1rem; display: block; }
.empty-state p { font-size: 1.1rem; }

.btn-outline-primary {
    border: 1px solid #1e3a8a;
    color: #1e3a8a;
    background: transparent;
    border-radius: 6px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-outline-primary:hover {
    background: #1e3a8a;
    color: white;
}

.inline-form { display: inline; }
</style>
