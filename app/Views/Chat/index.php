<?php
require_once APP_ROOT . '/app/Helpers/auth.php';
require_once APP_ROOT . '/app/Helpers/functions.php';
require_once APP_ROOT . '/app/Helpers/security.php';
if (!check_auth()) { redirect('/login'); }

$currentUserId   = (int)($_SESSION['user']['id'] ?? 0);
$currentUserName = $_SESSION['user']['name'] ?? 'Moi';
$availableUsers  = $availableUsers ?? [];
$conversations   = $conversations  ?? [];
$unreadCount     = $unreadCount    ?? 0;
$receiver        = $receiver       ?? null;
$messages        = $messages       ?? [];

// Couleurs d'avatars harmonieuses
$avatarPalette = [
    '#2563eb', '#0d9488', '#7c3aed', '#db2777', 
    '#ea580c', '#0284c7', '#16a34a', '#4f46e5'
];

if (!function_exists('getAvatarColor')) {
    function getAvatarColor($name, $palette) {
        return $palette[abs(crc32($name ?? 'User')) % count($palette)];
    }
}

$roleConfig = [
    'admin'      => ['label' => 'Administrateur', 'badge' => 'role-admin',      'icon' => 'fa-shield-halved'],
    'enseignant' => ['label' => 'Enseignant',     'badge' => 'role-enseignant', 'icon' => 'fa-chalkboard-user'],
    'etudiant'   => ['label' => 'Étudiant',       'badge' => 'role-etudiant',   'icon' => 'fa-user-graduate'],
    'finance'    => ['label' => 'Finance',        'badge' => 'role-finance',    'icon' => 'fa-wallet'],
];

// Mapper les derniers messages par utilisateur
$convMap = [];
foreach ($conversations as $c) {
    $convMap[$c['id']] = $c;
}
?>

<div class="sg-chat-wrapper">
    <div class="sg-chat-shell <?= $receiver ? 'conversation-open' : '' ?>" id="chatShell">

        <!-- ════════════════════════════════════════════════════════════════
             PANNEAU GAUCHE : SIDEBAR CONTACTS & CONVERSATIONS
        ════════════════════════════════════════════════════════════════ -->
        <aside class="sg-chat-sidebar">
            
            <!-- En-tête Sidebar -->
            <div class="sg-sidebar-header">
                <div class="sg-user-profile">
                    <div class="sg-avatar" style="background: <?= getAvatarColor($currentUserName, $avatarPalette) ?>">
                        <?= strtoupper(mb_substr($currentUserName, 0, 1)) ?>
                    </div>
                    <div class="sg-user-info">
                        <span class="sg-user-title">Messagerie SGAU</span>
                        <span class="sg-user-sub">
                            <span class="sg-status-dot online"></span>
                            <span id="sidebarOnlineCount">0</span> en ligne
                        </span>
                    </div>
                </div>
                <div class="sg-header-actions">
                    <button class="sg-btn-icon" id="btnOpenNewModal" title="Nouvelle conversation">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                </div>
            </div>

            <!-- Barre de recherche -->
            <div class="sg-sidebar-search">
                <div class="sg-search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="contactSearchInput" placeholder="Rechercher un contact..." autocomplete="off">
                    <button type="button" class="sg-search-clear" id="btnSearchClear" style="display:none;">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Filtres par Rôle (Tabs) -->
            <div class="sg-sidebar-tabs">
                <button class="sg-tab-btn active" data-filter="all">Tous</button>
                <button class="sg-tab-btn" data-filter="enseignant">Enseignants</button>
                <button class="sg-tab-btn" data-filter="etudiant">Étudiants</button>
                <button class="sg-tab-btn" data-filter="admin">Admins</button>
                <button class="sg-tab-btn" data-filter="finance">Finance</button>
            </div>

            <!-- Liste des Contacts -->
            <div class="sg-contact-list" id="contactsContainer">
                <?php if (empty($availableUsers)): ?>
                    <div class="sg-empty-list">
                        <i class="fa-regular fa-comments"></i>
                        <p>Aucun utilisateur disponible.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($availableUsers as $user): ?>
                        <?php
                            $uId      = (int)$user['id'];
                            $uName    = htmlspecialchars($user['name'] ?: 'Utilisateur', ENT_QUOTES, 'UTF-8');
                            $uRole    = $user['role'] ?? 'etudiant';
                            $uRoleInf = $roleConfig[$uRole] ?? ['label' => ucfirst($uRole), 'badge' => 'role-default', 'icon' => 'fa-user'];
                            $uColor   = getAvatarColor($user['name'], $avatarPalette);
                            $uInitial = strtoupper(mb_substr($user['name'] ?: 'U', 0, 1));
                            $uOnline  = !empty($user['is_online']);
                            
                            $lastData = $convMap[$uId] ?? null;
                            $lastMsg  = $lastData['last_message'] ?? '';
                            $lastTime = !empty($lastData['last_message_time']) ? timeAgo($lastData['last_message_time']) : '';
                            $unread   = (int)($lastData['unread_count'] ?? 0);
                            $isActive = ($receiver && (int)$receiver['id'] === $uId);
                        ?>
                        <div class="sg-contact-card <?= $isActive ? 'active' : '' ?>" 
                             data-user-id="<?= $uId ?>"
                             data-name="<?= strtolower($uName) ?>"
                             data-role="<?= strtolower($uRole) ?>"
                             onclick="selectUserChat(<?= $uId ?>)">
                            
                            <div class="sg-avatar-wrapper">
                                <div class="sg-avatar" style="background: <?= $uColor ?>;">
                                    <?= $uInitial ?>
                                </div>
                                <span class="sg-presence-indicator <?= $uOnline ? 'online' : 'offline' ?>" id="presence-<?= $uId ?>"></span>
                            </div>

                            <div class="sg-contact-content">
                                <div class="sg-contact-row">
                                    <h4 class="sg-contact-name"><?= $uName ?></h4>
                                    <span class="sg-contact-time"><?= $lastTime ?></span>
                                </div>
                                <div class="sg-contact-row sub">
                                    <span class="sg-role-pill <?= $uRoleInf['badge'] ?>">
                                        <i class="fa-solid <?= $uRoleInf['icon'] ?>"></i>
                                        <?= $uRoleInf['label'] ?>
                                    </span>
                                    <?php if ($unread > 0): ?>
                                        <span class="sg-unread-badge" id="unread-badge-<?= $uId ?>"><?= $unread ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($lastMsg): ?>
                                    <p class="sg-contact-snippet"><?= htmlspecialchars(mb_substr($lastMsg, 0, 42), ENT_QUOTES, 'UTF-8') ?><?= mb_strlen($lastMsg) > 42 ? '…' : '' ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <!-- ════════════════════════════════════════════════════════════════
             PANNEAU DROIT : CONVERSATION OU ÉTAT D'ACCUEIL
        ════════════════════════════════════════════════════════════════ -->
        <main class="sg-chat-main">
            
            <!-- ÉTAT 1 : ACCUEIL (Quand aucun contact n'est sélectionné) -->
            <div class="sg-welcome-view" id="welcomeView" style="<?= $receiver ? 'display:none;' : 'display:flex;' ?>">
                <div class="sg-welcome-card">
                    <div class="sg-welcome-icon-wrap">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <h2>Messagerie Universitaire SGAU</h2>
                    <p class="sg-welcome-text">
                        Échangez en temps réel avec vos enseignants, étudiants et l'administration académique en toute sécurité.
                    </p>
                    
                    <div class="sg-welcome-features">
                        <div class="sg-feature-item">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Messagerie instantanée</span>
                        </div>
                        <div class="sg-feature-item">
                            <i class="fa-solid fa-file-arrow-up"></i>
                            <span>Partage de documents</span>
                        </div>
                        <div class="sg-feature-item">
                            <i class="fa-solid fa-lock"></i>
                            <span>Communications sécurisées</span>
                        </div>
                    </div>

                    <button class="sg-btn-primary" onclick="openNewModal()">
                        <i class="fa-solid fa-plus"></i> Démarrer une conversation
                    </button>
                </div>
            </div>

            <!-- ÉTAT 2 : CONVERSATION ACTIVE -->
            <div class="sg-conversation-view" id="conversationView" style="<?= $receiver ? 'display:flex;' : 'display:none;' ?>">
                
                <!-- En-tête de conversation -->
                <header class="sg-conv-header" id="convHeader">
                    <?php
                        $recName    = htmlspecialchars($receiver['name'] ?? '', ENT_QUOTES, 'UTF-8');
                        $recRole    = $receiver['role'] ?? 'etudiant';
                        $recRoleInf = $roleConfig[$recRole] ?? ['label' => ucfirst($recRole), 'badge' => 'role-default', 'icon' => 'fa-user'];
                        $recColor   = getAvatarColor($receiver['name'] ?? 'User', $avatarPalette);
                        $recInitial = strtoupper(mb_substr($receiver['name'] ?? 'U', 0, 1));
                        $recOnline  = !empty($receiver['is_online']);
                    ?>
                    
                    <!-- Bouton retour sur mobile -->
                    <button class="sg-btn-back" onclick="closeConversationMobile()" title="Retour à la liste">
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>

                    <div class="sg-avatar-wrapper">
                        <div class="sg-avatar lg" id="headerAvatar" style="background: <?= $recColor ?>;">
                            <?= $recInitial ?>
                        </div>
                        <span class="sg-presence-indicator <?= $recOnline ? 'online' : 'offline' ?>" id="headerPresence"></span>
                    </div>

                    <div class="sg-conv-header-info">
                        <div class="sg-conv-header-name-row">
                            <h3 id="headerUserName"><?= $recName ?></h3>
                            <span class="sg-role-pill <?= $recRoleInf['badge'] ?>" id="headerRoleBadge">
                                <i class="fa-solid <?= $recRoleInf['icon'] ?>" id="headerRoleIcon"></i>
                                <span id="headerRoleLabel"><?= $recRoleInf['label'] ?></span>
                            </span>
                        </div>
                        <span class="sg-conv-header-status <?= $recOnline ? 'online' : '' ?>" id="headerUserStatus">
                            <?= $recOnline ? 'En ligne' : 'Hors ligne' ?>
                        </span>
                    </div>

                    <div class="sg-conv-header-actions">
                        <button class="sg-btn-icon" onclick="toggleMessageSearch()" title="Rechercher dans les messages">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                        <button class="sg-btn-icon" onclick="reloadActiveMessages()" title="Actualiser la conversation">
                            <i class="fa-solid fa-arrows-rotate" id="refreshIcon"></i>
                        </button>
                    </div>
                </header>

                <!-- Barre de recherche interne aux messages -->
                <div class="sg-conv-search-bar" id="convSearchBar" style="display: none;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="inConvSearchInput" placeholder="Rechercher dans la conversation...">
                    <button onclick="toggleMessageSearch()"><i class="fa-solid fa-xmark"></i></button>
                </div>

                <!-- Zone de flux des messages -->
                <div class="sg-messages-feed" id="messagesFeed">
                    <?php if ($receiver && !empty($messages)): ?>
                        <?php
                            $currentDay = null;
                            foreach ($messages as $msg):
                                $day = date('Y-m-d', strtotime($msg['created_at']));
                                $isMine = ((int)$msg['sender_id'] === $currentUserId);
                                if ($day !== $currentDay):
                                    $currentDay = $day;
                                    $dayLabel = (new DateTime($day))->format('d/m/Y');
                                    if ($day === date('Y-m-d')) $dayLabel = "Aujourd'hui";
                                    elseif ($day === date('Y-m-d', strtotime('-1 day'))) $dayLabel = "Hier";
                        ?>
                            <div class="sg-date-divider"><span><?= $dayLabel ?></span></div>
                        <?php endif; ?>

                            <div class="sg-msg-row <?= $isMine ? 'outgoing' : 'incoming' ?>" id="msg-<?= $msg['id'] ?>">
                                <?php if (!$isMine): ?>
                                    <div class="sg-msg-avatar" style="background: <?= $recColor ?>"><?= $recInitial ?></div>
                                <?php endif; ?>

                                <div class="sg-bubble <?= $isMine ? 'outgoing' : 'incoming' ?>">
                                    <?php if (!empty($msg['message'])): ?>
                                        <div class="sg-bubble-text"><?= nl2br(htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')) ?></div>
                                    <?php endif; ?>

                                    <?php if (!empty($msg['file_path'])): ?>
                                        <?php
                                            $fName = $msg['file_name'] ?? 'Fichier joint';
                                            $fUrl  = $msg['file_url'] ?? base_url('/storage/uploads/chat/' . basename($msg['file_path']));
                                            $isImg = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $msg['file_path']);
                                        ?>
                                        <?php if ($isImg): ?>
                                            <div class="sg-msg-attachment img">
                                                <img src="<?= $fUrl ?>" alt="<?= htmlspecialchars($fName, ENT_QUOTES, 'UTF-8') ?>" onclick="openLightbox('<?= $fUrl ?>')">
                                            </div>
                                        <?php else: ?>
                                            <a href="<?= $fUrl ?>" download="<?= htmlspecialchars($fName, ENT_QUOTES, 'UTF-8') ?>" class="sg-msg-attachment file">
                                                <div class="sg-file-icon"><i class="fa-solid fa-file-lines"></i></div>
                                                <div class="sg-file-meta">
                                                    <span class="sg-file-name"><?= htmlspecialchars($fName, ENT_QUOTES, 'UTF-8') ?></span>
                                                    <span class="sg-file-action">Télécharger <i class="fa-solid fa-arrow-down"></i></span>
                                                </div>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <div class="sg-bubble-footer">
                                        <span class="sg-msg-time"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                                        <?php if ($isMine): ?>
                                            <span class="sg-msg-status <?= $msg['is_read'] ? 'read' : '' ?>">
                                                <i class="fa-solid fa-check-double"></i>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($isMine): ?>
                                        <button class="sg-msg-action-btn" title="Options" onclick="toggleMsgMenu(event, <?= $msg['id'] ?>)">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <div class="sg-msg-menu" id="menu-msg-<?= $msg['id'] ?>">
                                            <button onclick="deleteSingleMessage(<?= $msg['id'] ?>)">
                                                <i class="fa-solid fa-trash"></i> Supprimer
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($receiver && empty($messages)): ?>
                        <div class="sg-conversation-empty">
                            <div class="sg-empty-wave">👋</div>
                            <h4>Commencez la discussion !</h4>
                            <p>Envoyez un message pour démarrer la conversation avec <strong><?= $recName ?></strong>.</p>
                        </div>
                    <?php endif; ?>
                    <div id="messagesAnchor"></div>
                </div>

                <!-- Indicateur "En train d'écrire" -->
                <div class="sg-typing-indicator" id="typingIndicator" style="display: none;">
                    <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                    <span id="typingName">Le correspondant</span> écrit...
                </div>

                <!-- Barre d'actions & saisie (Émojis supprimés) -->
                <div class="sg-input-bar">
                    <!-- Prévisualisation de pièce jointe -->
                    <div class="sg-file-preview-card" id="filePreviewCard" style="display: none;">
                        <i class="fa-solid fa-paperclip"></i>
                        <span class="sg-preview-filename" id="previewFileName">fichier.pdf</span>
                        <button type="button" class="sg-preview-remove" onclick="removeSelectedFile()" title="Retirer le fichier">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form id="chatSendForm" onsubmit="handleMessageSubmit(event); return false;" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="hidden" name="receiver_id" id="inputReceiverId" value="<?= $receiver['id'] ?? 0 ?>">

                        <!-- Bouton trombone pièce jointe -->
                        <label class="sg-input-action-btn" title="Joindre un fichier">
                            <i class="fa-solid fa-paperclip"></i>
                            <input type="file" id="chatFileInput" name="file" onchange="handleFileSelect(this)" style="display: none;">
                        </label>

                        <!-- Textarea auto-agrandissant -->
                        <div class="sg-textarea-wrap">
                            <textarea id="chatTextarea" name="message" 
                                      placeholder="Écrivez votre message... (Entrée pour envoyer, Shift+Entrée pour saut de ligne)"
                                      rows="1" 
                                      oninput="adjustTextareaHeight(this)"></textarea>
                        </div>

                        <!-- Bouton d'envoi -->
                        <button type="submit" class="sg-send-btn" id="btnSendMessage" title="Envoyer le message">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Loader central pour chargement dynamique -->
            <div class="sg-chat-loader" id="chatDynamicLoader" style="display: none;">
                <div class="sg-spinner"></div>
                <span>Chargement de la conversation...</span>
            </div>
        </main>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     MODAL NOUVELLE CONVERSATION
════════════════════════════════════════════════════════════════ -->
<div class="sg-modal-overlay" id="newConvModal">
    <div class="sg-modal-card">
        <div class="sg-modal-header">
            <h3><i class="fa-solid fa-pen-to-square"></i> Nouvelle discussion</h3>
            <button class="sg-btn-icon" onclick="closeNewModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="sg-modal-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="modalSearchInput" placeholder="Rechercher par nom ou rôle...">
        </div>
        <div class="sg-modal-list" id="modalUsersList">
            <?php foreach ($availableUsers as $user): ?>
                <?php
                    $mId      = (int)$user['id'];
                    $mName    = htmlspecialchars($user['name'] ?: 'Utilisateur', ENT_QUOTES, 'UTF-8');
                    $mRole    = $user['role'] ?? 'etudiant';
                    $mRoleInf = $roleConfig[$mRole] ?? ['label' => ucfirst($mRole), 'badge' => 'role-default', 'icon' => 'fa-user'];
                    $mColor   = getAvatarColor($user['name'], $avatarPalette);
                    $mInitial = strtoupper(mb_substr($user['name'] ?: 'U', 0, 1));
                    $mOnline  = !empty($user['is_online']);
                ?>
                <div class="sg-modal-user-item" 
                     data-name="<?= strtolower($mName) ?>" 
                     data-role="<?= strtolower($mRole) ?>"
                     onclick="selectUserChat(<?= $mId ?>); closeNewModal();">
                    <div class="sg-avatar" style="background: <?= $mColor ?>;"><?= $mInitial ?></div>
                    <div class="sg-modal-user-meta">
                        <span class="sg-modal-user-name"><?= $mName ?></span>
                        <span class="sg-role-pill <?= $mRoleInf['badge'] ?> sm">
                            <i class="fa-solid <?= $mRoleInf['icon'] ?>"></i> <?= $mRoleInf['label'] ?>
                        </span>
                    </div>
                    <?php if ($mOnline): ?>
                        <span class="sg-presence-pill online">En ligne</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     LIGHTBOX IMAGE
════════════════════════════════════════════════════════════════ -->
<div class="sg-lightbox-overlay" id="sgLightbox" onclick="closeLightbox()">
    <img id="lightboxImg" src="" alt="Aperçu">
</div>

<!-- ════════════════════════════════════════════════════════════════
     STYLES CSS MODERNES
════════════════════════════════════════════════════════════════ -->
<style>
/* --- CONTENEUR GLOBAL --- */
.sg-chat-wrapper {
    width: 100%;
    margin: -10px 0 0 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
}

.sg-chat-shell {
    display: flex;
    height: calc(100vh - 120px);
    min-height: 560px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 10px 30px -10px rgba(0, 26, 114, 0.08), 0 2px 6px rgba(0, 0, 0, 0.04);
}

/* --- SIDEBAR GAUCHE --- */
.sg-chat-sidebar {
    width: 380px;
    min-width: 320px;
    background: #ffffff;
    border-right: 1px solid #edf2f7;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.sg-sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #edf2f7;
}

.sg-user-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}

.sg-user-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.sg-user-title {
    font-size: 15px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.01em;
}

.sg-user-sub {
    font-size: 12px;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 6px;
}

.sg-status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #cbd5e1;
    display: inline-block;
}
.sg-status-dot.online {
    background: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* Barre de recherche */
.sg-sidebar-search {
    padding: 12px 18px;
    border-bottom: 1px solid #f1f5f9;
}

.sg-search-box {
    display: flex;
    align-items: center;
    background: #f1f5f9;
    border-radius: 10px;
    padding: 8px 14px;
    gap: 10px;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}
.sg-search-box:focus-within {
    background: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
.sg-search-box i {
    color: #94a3b8;
    font-size: 13.5px;
}
.sg-search-box input {
    border: none;
    background: transparent;
    outline: none;
    font-size: 13.5px;
    color: #0f172a;
    width: 100%;
}
.sg-search-clear {
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 12px;
    padding: 2px;
}

/* Onglets de filtrage rapide */
.sg-sidebar-tabs {
    display: flex;
    gap: 6px;
    padding: 8px 16px;
    overflow-x: auto;
    border-bottom: 1px solid #f1f5f9;
    scrollbar-width: none;
}
.sg-sidebar-tabs::-webkit-scrollbar { display: none; }
.sg-tab-btn {
    border: none;
    background: #f1f5f9;
    color: #64748b;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.15s ease;
}
.sg-tab-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.sg-tab-btn.active {
    background: #001A72;
    color: #ffffff;
    font-weight: 600;
}

/* Liste des contacts */
.sg-contact-list {
    flex: 1;
    overflow-y: auto;
    padding: 6px 8px;
}

.sg-contact-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    margin-bottom: 4px;
    position: relative;
}
.sg-contact-card:hover {
    background: #f8fafc;
    transform: translateX(2px);
}
.sg-contact-card.active {
    background: #eff6ff;
    border-left: 3px solid #2563eb;
}

.sg-avatar-wrapper {
    position: relative;
    flex-shrink: 0;
}

.sg-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    color: #ffffff;
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.sg-avatar.lg {
    width: 48px;
    height: 48px;
    font-size: 18px;
}

.sg-presence-indicator {
    position: absolute;
    bottom: 0px;
    right: 0px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #ffffff;
}
.sg-presence-indicator.online {
    background: #10b981;
}
.sg-presence-indicator.offline {
    background: #cbd5e1;
}

.sg-contact-content {
    flex: 1;
    min-width: 0;
}

.sg-contact-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3px;
}
.sg-contact-row.sub {
    margin-bottom: 2px;
}

.sg-contact-name {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sg-contact-time {
    font-size: 11.5px;
    color: #94a3b8;
    white-space: nowrap;
}

.sg-contact-snippet {
    margin: 0;
    font-size: 12.5px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.sg-unread-badge {
    background: #ef4444;
    color: #ffffff;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    padding: 1px 7px;
    min-width: 18px;
    text-align: center;
}

/* Badges de Rôles */
.sg-role-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 6px;
    line-height: 1.2;
}
.sg-role-pill.sm { font-size: 10px; padding: 1px 6px; }

.role-admin { background: #fee2e2; color: #b91c1c; }
.role-enseignant { background: #dbeafe; color: #1d4ed8; }
.role-etudiant { background: #dcfce7; color: #15803d; }
.role-finance { background: #fef3c7; color: #b45309; }
.role-default { background: #f1f5f9; color: #475569; }

/* Boutons Icones */
.sg-btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: #ffffff;
    color: #475569;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.15s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.sg-btn-icon:hover {
    background: #f1f5f9;
    color: #0f172a;
    transform: scale(1.05);
}

/* --- PANNEAU PRINCIPAL DROIT --- */
.sg-chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f8fafc;
    position: relative;
    overflow: hidden;
}

/* Vue Accueil */
.sg-welcome-view {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px;
    background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
}

.sg-welcome-card {
    max-width: 480px;
    text-align: center;
    background: #ffffff;
    padding: 40px 36px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
}

.sg-welcome-icon-wrap {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: linear-gradient(135deg, #001A72 0%, #2563eb 100%);
    color: #ffffff;
    font-size: 34px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 8px 20px rgba(0, 26, 114, 0.25);
}

.sg-welcome-card h2 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 10px;
}

.sg-welcome-text {
    font-size: 14px;
    color: #64748b;
    line-height: 1.6;
    margin: 0 0 24px;
}

.sg-welcome-features {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-bottom: 28px;
    text-align: left;
    background: #f8fafc;
    padding: 16px 20px;
    border-radius: 12px;
}

.sg-feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13.5px;
    color: #334155;
    font-weight: 500;
}
.sg-feature-item i {
    color: #2563eb;
    font-size: 15px;
    width: 18px;
}

.sg-btn-primary {
    background: #001A72;
    color: #ffffff;
    border: none;
    padding: 12px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(0, 26, 114, 0.2);
}
.sg-btn-primary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(0, 26, 114, 0.3);
}

/* Vue Conversation */
.sg-conversation-view {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.sg-conv-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 22px;
    background: #ffffff;
    border-bottom: 1px solid #edf2f7;
    z-index: 10;
}

.sg-btn-back {
    display: none;
    background: none;
    border: none;
    color: #0f172a;
    font-size: 18px;
    cursor: pointer;
    padding: 4px 8px 4px 0;
}

.sg-conv-header-info {
    flex: 1;
    min-width: 0;
}

.sg-conv-header-name-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.sg-conv-header-name-row h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}

.sg-conv-header-status {
    font-size: 12px;
    color: #94a3b8;
}
.sg-conv-header-status.online {
    color: #10b981;
    font-weight: 500;
}

.sg-conv-header-actions {
    display: flex;
    gap: 6px;
}

/* Recherche interne */
.sg-conv-search-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}
.sg-conv-search-bar input {
    flex: 1;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 7px 12px;
    font-size: 13.5px;
    outline: none;
}
.sg-conv-search-bar button {
    background: none;
    border: none;
    color: #64748b;
    font-size: 16px;
    cursor: pointer;
}

/* Flux de messages */
.sg-messages-feed {
    flex: 1;
    overflow-y: auto;
    padding: 20px 24px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f8fafc;
    background-image: radial-gradient(#cbd5e1 0.75px, transparent 0.75px);
    background-size: 24px 24px;
}

/* Séparateur de date */
.sg-date-divider {
    text-align: center;
    margin: 12px 0;
}
.sg-date-divider span {
    background: #e2e8f0;
    color: #475569;
    font-size: 11.5px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 12px;
}

/* Rangée de message */
.sg-msg-row {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    position: relative;
    max-width: 75%;
}
.sg-msg-row.outgoing {
    align-self: flex-end;
    flex-direction: row-reverse;
}
.sg-msg-row.incoming {
    align-self: flex-start;
}

.sg-msg-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-bottom: 2px;
}

/* Bulles */
.sg-bubble {
    position: relative;
    padding: 10px 14px 8px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.5;
    word-break: break-word;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.sg-bubble.outgoing {
    background: linear-gradient(135deg, #001A72 0%, #1d4ed8 100%);
    color: #ffffff;
    border-bottom-right-radius: 4px;
}
.sg-bubble.incoming {
    background: #ffffff;
    color: #0f172a;
    border: 1px solid #e2e8f0;
    border-bottom-left-radius: 4px;
}

.sg-bubble-text {
    margin-bottom: 4px;
}

.sg-bubble-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    font-size: 11px;
}
.sg-bubble.outgoing .sg-bubble-footer { color: rgba(255, 255, 255, 0.75); }
.sg-bubble.incoming .sg-bubble-footer { color: #94a3b8; }

.sg-msg-status {
    font-size: 12px;
}
.sg-msg-status.read {
    color: #38bdf8;
}

/* Attachements */
.sg-msg-attachment.img img {
    max-width: 280px;
    max-height: 280px;
    border-radius: 10px;
    margin-bottom: 6px;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.sg-msg-attachment.img img:hover {
    transform: scale(1.02);
}

.sg-msg-attachment.file {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(0, 0, 0, 0.05);
    border-radius: 10px;
    padding: 8px 12px;
    text-decoration: none;
    color: inherit;
    margin-bottom: 6px;
    transition: background 0.15s ease;
}
.sg-bubble.outgoing .sg-msg-attachment.file {
    background: rgba(255, 255, 255, 0.15);
}
.sg-file-icon {
    font-size: 20px;
}
.sg-file-meta {
    display: flex;
    flex-direction: column;
}
.sg-file-name {
    font-weight: 600;
    font-size: 12.5px;
}
.sg-file-action {
    font-size: 11px;
    opacity: 0.8;
}

/* Menu contextuel bulle */
.sg-msg-action-btn {
    display: none;
    position: absolute;
    top: 6px;
    right: 6px;
    background: rgba(0,0,0,0.15);
    border: none;
    color: #ffffff;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    font-size: 10px;
    cursor: pointer;
    align-items: center;
    justify-content: center;
}
.sg-bubble:hover .sg-msg-action-btn { display: flex; }
.sg-msg-menu {
    display: none;
    position: absolute;
    top: 28px;
    right: 0;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    z-index: 20;
    min-width: 120px;
    overflow: hidden;
}
.sg-msg-menu.show { display: block; }
.sg-msg-menu button {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 12px;
    border: none;
    background: none;
    font-size: 12.5px;
    color: #ef4444;
    cursor: pointer;
    text-align: left;
}
.sg-msg-menu button:hover { background: #fee2e2; }

/* Conversation vide */
.sg-conversation-empty {
    margin: auto;
    text-align: center;
    padding: 30px;
    color: #64748b;
}
.sg-empty-wave {
    font-size: 40px;
    margin-bottom: 12px;
}
.sg-conversation-empty h4 {
    margin: 0 0 6px;
    color: #0f172a;
    font-size: 16px;
}
.sg-conversation-empty p {
    margin: 0;
    font-size: 13.5px;
}

/* Indicateur frappe */
.sg-typing-indicator {
    padding: 6px 24px;
    font-size: 12px;
    color: #64748b;
    background: rgba(248, 250, 252, 0.9);
    display: flex;
    align-items: center;
    gap: 4px;
}
.sg-typing-indicator .dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #94a3b8;
    display: inline-block;
    animation: typingPulse 1.2s infinite;
}
.sg-typing-indicator .dot:nth-child(2) { animation-delay: 0.2s; }
.sg-typing-indicator .dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes typingPulse {
    0%, 80%, 100% { transform: scale(0.8); opacity: 0.4; }
    40% { transform: scale(1.2); opacity: 1; }
}

/* Zone d'envoi */
.sg-input-bar {
    background: #ffffff;
    border-top: 1px solid #edf2f7;
    padding: 12px 18px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    position: relative;
    z-index: 5;
}

.sg-file-preview-card {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12.5px;
    color: #1e40af;
}
.sg-preview-filename {
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 500;
}
.sg-preview-remove {
    background: none;
    border: none;
    color: #ef4444;
    cursor: pointer;
    font-size: 14px;
}

.sg-input-bar form {
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

.sg-input-action-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-size: 18px;
    cursor: pointer;
    transition: all 0.15s ease;
    flex-shrink: 0;
}
.sg-input-action-btn:hover {
    background: #f1f5f9;
    color: #0f172a;
}

.sg-textarea-wrap {
    flex: 1;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    padding: 8px 14px;
    display: flex;
    align-items: flex-end;
    transition: all 0.2s ease;
}
.sg-textarea-wrap:focus-within {
    background: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.sg-textarea-wrap textarea {
    width: 100%;
    border: none;
    background: transparent;
    outline: none;
    font-family: inherit;
    font-size: 14px;
    color: #0f172a;
    resize: none;
    max-height: 120px;
    line-height: 1.45;
}

.sg-send-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #001A72 0%, #2563eb 100%);
    color: #ffffff;
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(0, 26, 114, 0.25);
}
.sg-send-btn:hover {
    transform: scale(1.05);
    background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 100%);
}
.sg-send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Loader */
.sg-chat-loader {
    position: absolute;
    inset: 0;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(4px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    z-index: 50;
    font-size: 14px;
    font-weight: 500;
    color: #001A72;
}
.sg-spinner {
    width: 36px;
    height: 36px;
    border: 3px solid #e2e8f0;
    border-top-color: #001A72;
    border-radius: 50%;
    animation: sgSpin 0.8s linear infinite;
}
@keyframes sgSpin {
    to { transform: rotate(360deg); }
}

/* Modals */
.sg-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}
.sg-modal-overlay.open { display: flex; }

.sg-modal-card {
    background: #ffffff;
    width: 440px;
    max-width: 90vw;
    max-height: 80vh;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
}

.sg-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #001A72;
    color: #ffffff;
}
.sg-modal-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sg-modal-header .sg-btn-icon {
    background: transparent;
    color: #ffffff;
}
.sg-modal-header .sg-btn-icon:hover {
    background: rgba(255,255,255,0.15);
}

.sg-modal-search {
    padding: 12px 18px;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
}
.sg-modal-search input {
    flex: 1;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 13.5px;
    outline: none;
}

.sg-modal-list {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.sg-modal-user-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    cursor: pointer;
    transition: background 0.15s ease;
}
.sg-modal-user-item:hover {
    background: #f1f5f9;
}
.sg-modal-user-meta {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.sg-modal-user-name {
    font-size: 14px;
    font-weight: 600;
    color: #0f172a;
}
.sg-presence-pill {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 10px;
    background: #dcfce7;
    color: #15803d;
}

/* Lightbox */
.sg-lightbox-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.85);
    backdrop-filter: blur(6px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    cursor: pointer;
}
.sg-lightbox-overlay.open { display: flex; }
.sg-lightbox-overlay img {
    max-width: 90vw;
    max-height: 90vh;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

/* --- RESPONSIVE MOBILE (< 768px) --- */
@media (max-width: 768px) {
    .sg-chat-shell {
        height: calc(100vh - 80px);
        border-radius: 0;
        border: none;
    }
    .sg-chat-sidebar {
        width: 100%;
        min-width: 100%;
    }
    .sg-chat-main {
        display: none;
        width: 100%;
    }
    
    .sg-chat-shell.conversation-open .sg-chat-sidebar {
        display: none;
    }
    .sg-chat-shell.conversation-open .sg-chat-main {
        display: flex;
    }
    .sg-btn-back {
        display: inline-block;
    }
}
</style>

<!-- ════════════════════════════════════════════════════════════════
     SCRIPT JS REACTIF ET ROBUSTE
════════════════════════════════════════════════════════════════ -->
<script>
const SG_CHAT = {
    csrfToken:      '<?= csrf_token() ?>',
    baseUrl:        '<?= rtrim(base_url(''), '/') ?>',
    currentUserId:  <?= $currentUserId ?>,
    activeUserId:   <?= $receiver['id'] ?? 0 ?>,
    lastMessageId:  <?= !empty($messages) ? end($messages)['id'] : 0 ?>,
    pollInterval:   null,
    avatarPalette:  <?= json_encode($avatarPalette) ?>,
    roleConfig:     <?= json_encode($roleConfig) ?>
};

/* --- Initialisation --- */
document.addEventListener('DOMContentLoaded', () => {
    initScrollToBottom(false);
    setupSearchFilters();
    setupTextareaKeydown();
    setupFormSubmit();
    setupModalListeners();
    setupWindowPopState();
    startOnlinePresencePinger();

    if (SG_CHAT.activeUserId > 0) {
        startMessagesPolling();
    }
});

/* --- Défilement en bas --- */
function initScrollToBottom(smooth = false) {
    const feed = document.getElementById('messagesFeed');
    const anchor = document.getElementById('messagesAnchor');
    if (anchor) {
        anchor.scrollIntoView({ behavior: smooth ? 'smooth' : 'instant' });
    } else if (feed) {
        feed.scrollTop = feed.scrollHeight;
    }
}

/* --- Clic sur un contact : chargement instantané de la conversation --- */
function selectUserChat(userId, pushState = true) {
    if (!userId || userId === SG_CHAT.currentUserId) return;

    // Mise à jour de l'état actif dans la sidebar
    document.querySelectorAll('.sg-contact-card').forEach(card => {
        card.classList.toggle('active', parseInt(card.dataset.userId) === userId);
    });

    // Nettoyage de l'éventuel badge non lu
    const unreadBadge = document.getElementById('unread-badge-' + userId);
    if (unreadBadge) unreadBadge.remove();

    // Affichage immédiat du conteneur de conversation
    const welcomeView = document.getElementById('welcomeView');
    const convView    = document.getElementById('conversationView');
    const loader      = document.getElementById('chatDynamicLoader');
    const shell       = document.getElementById('chatShell');

    welcomeView.style.display = 'none';
    convView.style.display    = 'flex';
    loader.style.display      = 'flex';
    shell.classList.add('conversation-open');

    // Mettre à jour l'input caché receiver_id
    document.getElementById('inputReceiverId').value = userId;
    SG_CHAT.activeUserId = userId;

    // Appel AJAX pour charger les messages et les détails
    const url = `${SG_CHAT.baseUrl}/chat/conversation/${userId}?ajax=1`;
    fetch(url, {
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(async res => {
        const text = await res.text();
        try {
            return JSON.parse(text);
        } catch(e) {
            throw new Error('Réponse invalide du serveur');
        }
    })
    .then(data => {
        loader.style.display = 'none';
        if (data.success) {
            renderActiveConversation(data.receiver, data.messages);
            
            if (pushState) {
                history.pushState({ userId: userId }, '', `${SG_CHAT.baseUrl}/chat/conversation/${userId}`);
            }

            startMessagesPolling();
        } else {
            alert(data.error || 'Impossible de charger la conversation.');
        }
    })
    .catch(err => {
        loader.style.display = 'none';
        console.error('Erreur chargement conversation:', err);
    });
}

/* --- Rendu dynamique de l'en-tête et des messages --- */
function renderActiveConversation(receiver, messages) {
    if (!receiver) return;

    // Mise à jour de l'en-tête
    const initial = (receiver.name || 'U').charAt(0).toUpperCase();
    const color   = getAvatarColor(receiver.name);
    const roleKey = receiver.role || 'etudiant';
    const roleInf = SG_CHAT.roleConfig[roleKey] || { label: roleKey, badge: 'role-default', icon: 'fa-user' };
    const isOnline = Boolean(parseInt(receiver.is_online));

    const avatarEl = document.getElementById('headerAvatar');
    avatarEl.textContent = initial;
    avatarEl.style.background = color;

    const presenceEl = document.getElementById('headerPresence');
    presenceEl.className = 'sg-presence-indicator ' + (isOnline ? 'online' : 'offline');

    document.getElementById('headerUserName').textContent = receiver.name || 'Utilisateur';

    const roleBadge = document.getElementById('headerRoleBadge');
    roleBadge.className = 'sg-role-pill ' + roleInf.badge;
    document.getElementById('headerRoleIcon').className = 'fa-solid ' + roleInf.icon;
    document.getElementById('headerRoleLabel').textContent = roleInf.label;

    const statusEl = document.getElementById('headerUserStatus');
    statusEl.textContent = isOnline ? 'En ligne' : 'Hors ligne';
    statusEl.className = 'sg-conv-header-status ' + (isOnline ? 'online' : '');

    // Rendu des messages
    const feed = document.getElementById('messagesFeed');
    feed.innerHTML = '';

    if (!messages || messages.length === 0) {
        feed.innerHTML = `
            <div class="sg-conversation-empty">
                <div class="sg-empty-wave">👋</div>
                <h4>Commencez la discussion !</h4>
                <p>Envoyez un message pour démarrer la conversation avec <strong>${escapeHtml(receiver.name)}</strong>.</p>
            </div>
            <div id="messagesAnchor"></div>
        `;
        SG_CHAT.lastMessageId = 0;
        return;
    }

    let lastDay = null;
    let maxId = 0;

    messages.forEach(msg => {
        if (msg.id > maxId) maxId = msg.id;
        const msgDate = new Date((msg.created_at || '').replace(' ', 'T'));
        const dayStr  = !isNaN(msgDate.getTime()) ? msgDate.toISOString().slice(0, 10) : '';

        if (dayStr !== lastDay) {
            lastDay = dayStr;
            const divider = document.createElement('div');
            divider.className = 'sg-date-divider';
            divider.innerHTML = `<span>${formatDateLabel(msgDate)}</span>`;
            feed.appendChild(divider);
        }

        const msgRow = createMessageElement(msg, receiver);
        feed.appendChild(msgRow);
    });

    SG_CHAT.lastMessageId = maxId;

    const anchor = document.createElement('div');
    anchor.id = 'messagesAnchor';
    feed.appendChild(anchor);

    initScrollToBottom(false);
}

/* --- Création d'une ligne de message HTML --- */
function createMessageElement(msg, receiver) {
    const isMine = (parseInt(msg.sender_id) === SG_CHAT.currentUserId);
    const row = document.createElement('div');
    row.className = `sg-msg-row ${isMine ? 'outgoing' : 'incoming'}`;
    row.id = `msg-${msg.id}`;

    const dateInput = (msg.created_at || '').replace(' ', 'T');
    const msgDate   = dateInput ? new Date(dateInput) : new Date();
    const timeStr   = !isNaN(msgDate.getTime()) 
        ? msgDate.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) 
        : new Date().toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });

    let avatarHtml = '';
    if (!isMine) {
        const recName = (receiver && receiver.name) ? receiver.name : 'Utilisateur';
        const initial = recName.charAt(0).toUpperCase();
        const color   = getAvatarColor(recName);
        avatarHtml = `<div class="sg-msg-avatar" style="background: ${color}">${initial}</div>`;
    }

    let textHtml = '';
    if (msg.message && msg.message.trim()) {
        textHtml = `<div class="sg-bubble-text">${escapeHtml(msg.message).replace(/\n/g, '<br>')}</div>`;
    }

    let fileHtml = '';
    if (msg.file_path || msg.file_name) {
        const fname = msg.file_name || 'Fichier';
        const furl  = msg.file_url || `${SG_CHAT.baseUrl}/storage/uploads/chat/${fname}`;
        const isImg = /\.(jpg|jpeg|png|gif|webp)$/i.test(fname);

        if (isImg) {
            fileHtml = `
                <div class="sg-msg-attachment img">
                    <img src="${furl}" alt="${escapeHtml(fname)}" onclick="openLightbox('${furl}')">
                </div>
            `;
        } else {
            fileHtml = `
                <a href="${furl}" download="${escapeHtml(fname)}" class="sg-msg-attachment file">
                    <div class="sg-file-icon"><i class="fa-solid fa-file-lines"></i></div>
                    <div class="sg-file-meta">
                        <span class="sg-file-name">${escapeHtml(fname)}</span>
                        <span class="sg-file-action">Télécharger <i class="fa-solid fa-arrow-down"></i></span>
                    </div>
                </a>
            `;
        }
    }

    let statusHtml = '';
    if (isMine) {
        statusHtml = `
            <span class="sg-msg-status ${msg.is_read ? 'read' : ''}">
                <i class="fa-solid fa-check-double"></i>
            </span>
        `;
    }

    let menuHtml = '';
    if (isMine) {
        menuHtml = `
            <button class="sg-msg-action-btn" title="Options" onclick="toggleMsgMenu(event, ${msg.id})">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
            <div class="sg-msg-menu" id="menu-msg-${msg.id}">
                <button onclick="deleteSingleMessage(${msg.id})">
                    <i class="fa-solid fa-trash"></i> Supprimer
                </button>
            </div>
        `;
    }

    row.innerHTML = `
        ${avatarHtml}
        <div class="sg-bubble ${isMine ? 'outgoing' : 'incoming'}">
            ${textHtml}
            ${fileHtml}
            <div class="sg-bubble-footer">
                <span class="sg-msg-time">${timeStr}</span>
                ${statusHtml}
            </div>
            ${menuHtml}
        </div>
    `;

    return row;
}

/* --- Configuration du formulaire d'envoi --- */
function setupFormSubmit() {
    const form = document.getElementById('chatSendForm');
    if (form) {
        form.addEventListener('submit', handleMessageSubmit);
    }
}

/* --- Soumission d'un message --- */
function handleMessageSubmit(event) {
    if (event) event.preventDefault();

    const form      = document.getElementById('chatSendForm');
    const textarea  = document.getElementById('chatTextarea');
    const fileInput = document.getElementById('chatFileInput');
    const sendBtn   = document.getElementById('btnSendMessage');
    const text      = textarea.value.trim();
    const hasFile   = fileInput.files && fileInput.files.length > 0;

    // Destinataire
    const receiverId = parseInt(document.getElementById('inputReceiverId').value) || SG_CHAT.activeUserId;

    if (!receiverId) {
        alert('Veuillez sélectionner une conversation.');
        return;
    }

    if (!text && !hasFile) {
        textarea.focus();
        return;
    }

    const fd = new FormData(form);
    fd.set('receiver_id', receiverId);
    fd.set('csrf_token', SG_CHAT.csrfToken);

    sendBtn.disabled = true;

    fetch(`${SG_CHAT.baseUrl}/chat/send-message`, {
        method: 'POST',
        body: fd,
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': SG_CHAT.csrfToken
        }
    })
    .then(async res => {
        const rawText = await res.text();
        try {
            return JSON.parse(rawText);
        } catch(e) {
            console.error('Réponse non-JSON du serveur:', rawText);
            throw new Error('Erreur de communication avec le serveur');
        }
    })
    .then(data => {
        sendBtn.disabled = false;
        if (data.success && data.message) {
            // Réinitialiser le formulaire
            textarea.value = '';
            textarea.style.height = 'auto';
            removeSelectedFile();

            // Retirer le message de conversation vide si présent
            const emptyNotice = document.querySelector('.sg-conversation-empty');
            if (emptyNotice) emptyNotice.remove();

            // Ajouter le message envoyé
            const feed   = document.getElementById('messagesFeed');
            const anchor = document.getElementById('messagesAnchor');
            const newEl  = createMessageElement(data.message, {});
            
            feed.insertBefore(newEl, anchor);
            if (data.message.id > SG_CHAT.lastMessageId) {
                SG_CHAT.lastMessageId = data.message.id;
            }

            initScrollToBottom(true);
            textarea.focus();
        } else {
            alert(data.error || "Erreur lors de l'envoi du message.");
        }
    })
    .catch(err => {
        sendBtn.disabled = false;
        console.error('Erreur envoi message:', err);
        alert(err.message || "Erreur lors de l'envoi.");
    });
}

/* --- Polling automatique des messages --- */
function startMessagesPolling() {
    if (SG_CHAT.pollInterval) clearInterval(SG_CHAT.pollInterval);

    SG_CHAT.pollInterval = setInterval(() => {
        if (!SG_CHAT.activeUserId) return;

        const url = `${SG_CHAT.baseUrl}/chat/get-messages?other_user_id=${SG_CHAT.activeUserId}&last_id=${SG_CHAT.lastMessageId}`;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.messages && data.messages.length > 0) {
                    const feed = document.getElementById('messagesFeed');
                    const anchor = document.getElementById('messagesAnchor');
                    let hasNew = false;

                    data.messages.forEach(msg => {
                        if (!document.getElementById(`msg-${msg.id}`)) {
                            const newEl = createMessageElement(msg, data.receiver);
                            feed.insertBefore(newEl, anchor);
                            hasNew = true;
                        }
                        if (msg.id > SG_CHAT.lastMessageId) {
                            SG_CHAT.lastMessageId = msg.id;
                        }
                    });

                    if (hasNew) {
                        initScrollToBottom(true);
                    }
                }
            })
            .catch(() => {});
    }, 3000);
}

/* --- Suppression de message --- */
function deleteSingleMessage(msgId) {
    if (!confirm('Voulez-vous vraiment supprimer ce message ?')) return;

    const fd = new FormData();
    fd.append('message_id', msgId);
    fd.append('csrf_token', SG_CHAT.csrfToken);

    fetch(`${SG_CHAT.baseUrl}/chat/delete-message`, {
        method: 'POST',
        body: fd,
        headers: { 
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': SG_CHAT.csrfToken
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById(`msg-${msgId}`);
            if (el) {
                el.style.opacity = '0';
                el.style.transform = 'scale(0.9)';
                setTimeout(() => el.remove(), 200);
            }
        } else {
            alert(data.error || "Impossible de supprimer ce message.");
        }
    })
    .catch(err => console.error('Erreur suppression:', err));
}

/* --- Rechargement forcé de la conversation --- */
function reloadActiveMessages() {
    if (!SG_CHAT.activeUserId) return;
    const icon = document.getElementById('refreshIcon');
    if (icon) icon.classList.add('fa-spin');
    
    selectUserChat(SG_CHAT.activeUserId, false);
    setTimeout(() => { if (icon) icon.classList.remove('fa-spin'); }, 600);
}

/* --- Fermeture mobile --- */
function closeConversationMobile() {
    document.getElementById('chatShell').classList.remove('conversation-open');
}

/* --- Navigation Popstate (Bouton retour navigateur) --- */
function setupWindowPopState() {
    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.userId) {
            selectUserChat(e.state.userId, false);
        } else {
            SG_CHAT.activeUserId = 0;
            document.getElementById('welcomeView').style.display = 'flex';
            document.getElementById('conversationView').style.display = 'none';
            document.getElementById('chatShell').classList.remove('conversation-open');
            document.querySelectorAll('.sg-contact-card').forEach(c => c.classList.remove('active'));
        }
    });
}

/* --- Gestion de la recherche et des filtres de rôles --- */
function setupSearchFilters() {
    const searchInput = document.getElementById('contactSearchInput');
    const clearBtn    = document.getElementById('btnSearchClear');
    const tabBtns     = document.querySelectorAll('.sg-tab-btn');
    let currentRole   = 'all';

    function filterContacts() {
        const query = (searchInput.value || '').trim().toLowerCase();
        clearBtn.style.display = query ? 'block' : 'none';

        document.querySelectorAll('.sg-contact-card').forEach(card => {
            const name = card.dataset.name || '';
            const role = card.dataset.role || '';

            const matchQuery = !query || name.includes(query);
            const matchRole  = (currentRole === 'all') || (role === currentRole);

            card.style.display = (matchQuery && matchRole) ? 'flex' : 'none';
        });
    }

    searchInput?.addEventListener('input', filterContacts);
    clearBtn?.addEventListener('click', () => {
        searchInput.value = '';
        filterContacts();
        searchInput.focus();
    });

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentRole = btn.dataset.filter;
            filterContacts();
        });
    });

    // Recherche dans la modal
    document.getElementById('modalSearchInput')?.addEventListener('input', function() {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('.sg-modal-user-item').forEach(item => {
            const name = item.dataset.name || '';
            const role = item.dataset.role || '';
            item.style.display = (!q || name.includes(q) || role.includes(q)) ? 'flex' : 'none';
        });
    });
}

/* --- Touche Entrée / Agrandissement automatique du Textarea --- */
function setupTextareaKeydown() {
    const textarea = document.getElementById('chatTextarea');
    const form     = document.getElementById('chatSendForm');

    textarea?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleMessageSubmit(e);
        }
    });
}

function adjustTextareaHeight(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
}

/* --- Pièce jointe --- */
function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        document.getElementById('previewFileName').textContent = input.files[0].name;
        document.getElementById('filePreviewCard').style.display = 'flex';
    }
}

function removeSelectedFile() {
    const input = document.getElementById('chatFileInput');
    if (input) input.value = '';
    document.getElementById('filePreviewCard').style.display = 'none';
}

/* --- Recherche dans les messages d'une conversation --- */
function toggleMessageSearch() {
    const bar = document.getElementById('convSearchBar');
    const isVisible = (bar.style.display === 'flex');
    bar.style.display = isVisible ? 'none' : 'flex';
    if (!isVisible) {
        document.getElementById('inConvSearchInput').focus();
    } else {
        document.querySelectorAll('.sg-msg-row').forEach(row => row.style.opacity = '1');
    }
}

document.getElementById('inConvSearchInput')?.addEventListener('input', function() {
    const q = this.value.trim().toLowerCase();
    document.querySelectorAll('.sg-msg-row').forEach(row => {
        const text = (row.querySelector('.sg-bubble-text')?.textContent || '').toLowerCase();
        row.style.opacity = (!q || text.includes(q)) ? '1' : '0.25';
    });
});

/* --- Menu contextuel d'un message --- */
function toggleMsgMenu(event, msgId) {
    event.stopPropagation();
    document.querySelectorAll('.sg-msg-menu.show').forEach(m => {
        if (m.id !== `menu-msg-${msgId}`) m.classList.remove('show');
    });
    const menu = document.getElementById(`menu-msg-${msgId}`);
    if (menu) menu.classList.toggle('show');
}
document.addEventListener('click', () => {
    document.querySelectorAll('.sg-msg-menu.show').forEach(m => m.classList.remove('show'));
});

/* --- Modal Nouvelle conversation --- */
function setupModalListeners() {
    document.getElementById('btnOpenNewModal')?.addEventListener('click', openNewModal);
    const modal = document.getElementById('newConvModal');
    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeNewModal();
    });
}
function openNewModal() {
    document.getElementById('newConvModal').classList.add('open');
    document.getElementById('modalSearchInput')?.focus();
}
function closeNewModal() {
    document.getElementById('newConvModal').classList.remove('open');
}

/* --- Lightbox --- */
function openLightbox(url) {
    document.getElementById('lightboxImg').src = url;
    document.getElementById('sgLightbox').classList.add('open');
}
function closeLightbox() {
    document.getElementById('sgLightbox').classList.remove('open');
}

/* --- Statut En Ligne Pinger --- */
function startOnlinePresencePinger() {
    function refreshCount() {
        fetch(`${SG_CHAT.baseUrl}/api/chat/online-count`)
            .then(r => r.json())
            .then(d => {
                if (d.success && d.count !== undefined) {
                    const el = document.getElementById('sidebarOnlineCount');
                    if (el) el.textContent = d.count;
                }
            }).catch(() => {});
    }

    function pingOnline(isOnline = true) {
        const fd = new FormData();
        fd.append('online', isOnline ? '1' : '0');
        fd.append('csrf_token', SG_CHAT.csrfToken);
        navigator.sendBeacon(`${SG_CHAT.baseUrl}/api/chat/update-online-status`, fd);
    }

    refreshCount();
    setInterval(refreshCount, 30000);
    setInterval(() => pingOnline(true), 45000);
    window.addEventListener('beforeunload', () => pingOnline(false));
}

/* --- Utilitaires --- */
function getAvatarColor(name) {
    if (!name) return SG_CHAT.avatarPalette[0];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return SG_CHAT.avatarPalette[Math.abs(hash) % SG_CHAT.avatarPalette.length];
}

function formatDateLabel(d) {
    const today = new Date();
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);

    if (d.toDateString() === today.toDateString()) return "Aujourd'hui";
    if (d.toDateString() === yesterday.toDateString()) return "Hier";
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>
