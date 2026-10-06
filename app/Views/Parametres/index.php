<div class="desktop-settings-app">
    
    <!-- DESKTOP WINDOW TITLEBAR & TOOLBAR -->
    <div class="desktop-titlebar">
        <div class="titlebar-left">
            <div class="window-decorations">
                <span class="window-dot dot-close"></span>
                <span class="window-dot dot-min"></span>
                <span class="window-dot dot-max"></span>
            </div>
            <div class="titlebar-heading">
                <div class="app-icon-badge">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div>
                    <h2>Configuration & Paramètres Système</h2>
                    <span class="version-tag">SGAU-OPENLU Studio Suite • v2.4.0 (Système LMD)</span>
                </div>
            </div>
        </div>

        <div class="titlebar-right">
            <div class="system-status-indicator">
                <span class="status-pulse pulse-green"></span>
                <span class="status-text">Système en ligne • Base connectée</span>
            </div>
            <button type="button" class="btn-desktop-primary" onclick="saveAllSettings(event)">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer tout <kbd>Ctrl+S</kbd>
            </button>
        </div>
    </div>

    <!-- MAIN TWO-COLUMN DESKTOP WORKSPACE -->
    <div class="desktop-workspace">
        
        <!-- LEFT SIDEBAR / MASTER PANE -->
        <aside class="desktop-settings-sidebar">
            <div class="sidebar-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="settingsSearchInput" placeholder="Rechercher un réglage..." autocomplete="off">
            </div>

            <nav class="settings-nav-list" id="settingsNavList">
                
                <!-- GROUPE 1 : IDENTITÉ -->
                <div class="nav-group-title">ÉTABLISSEMENT & IDENTITÉ</div>
                <button type="button" class="nav-item active" data-target="pane-general">
                    <div class="nav-icon"><i class="fa-solid fa-building-columns"></i></div>
                    <div class="nav-text">
                        <span class="title">Identité & Université</span>
                        <span class="sub">Logo, intitulé, année académique</span>
                    </div>
                </button>
                <button type="button" class="nav-item" data-target="pane-contact">
                    <div class="nav-icon"><i class="fa-solid fa-address-book"></i></div>
                    <div class="nav-text">
                        <span class="title">Coordonnées & Contact</span>
                        <span class="sub">Email, tél, adresse, campus</span>
                    </div>
                </button>

                <!-- GROUPE 2 : SÉCURITÉ & ACCÈS -->
                <div class="nav-group-title">SÉCURITÉ & COMPTES</div>
                <button type="button" class="nav-item" data-target="pane-users">
                    <div class="nav-icon"><i class="fa-solid fa-users-gear"></i></div>
                    <div class="nav-text">
                        <span class="title">Utilisateurs & Accès</span>
                        <span class="sub">Gestion des comptes & mots de passe</span>
                    </div>
                    <span class="nav-badge"><?= count($users) ?></span>
                </button>
                <button type="button" class="nav-item" data-target="pane-roles">
                    <div class="nav-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="nav-text">
                        <span class="title">Rôles & Permissions</span>
                        <span class="sub">Matrice des droits d'accès</span>
                    </div>
                </button>

                <!-- GROUPE 3 : ACADÉMIQUE -->
                <div class="nav-group-title">ACADÉMIQUE & COTATION</div>
                <button type="button" class="nav-item" data-target="pane-academique">
                    <div class="nav-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                    <div class="nav-text">
                        <span class="title">Règles de Cotation</span>
                        <span class="sub">Barème 200/20, dérogations SGA</span>
                    </div>
                </button>

                <!-- GROUPE 4 : FINANCES -->
                <div class="nav-group-title">FINANCES & COMPTABILITÉ</div>
                <button type="button" class="nav-item" data-target="pane-finance">
                    <div class="nav-icon"><i class="fa-solid fa-money-bill-transfer"></i></div>
                    <div class="nav-text">
                        <span class="title">Tarifs & Devises</span>
                        <span class="sub">Frais, devise USD/CDF</span>
                    </div>
                </button>
                <button type="button" class="nav-item" data-target="pane-comptable">
                    <div class="nav-icon"><i class="fa-solid fa-book-bookmark"></i></div>
                    <div class="nav-text">
                        <span class="title">Plan Comptable (SYSCOHADA)</span>
                        <span class="sub">Structure des comptes financiers</span>
                    </div>
                </button>

                <!-- GROUPE 5 : SYSTÈME -->
                <div class="nav-group-title">ENVIRONNEMENT & SYSTÈME</div>
                <button type="button" class="nav-item" data-target="pane-system">
                    <div class="nav-icon"><i class="fa-solid fa-server"></i></div>
                    <div class="nav-text">
                        <span class="title">Diagnostic & Système</span>
                        <span class="sub">PHP, MySQL, Sauvegardes</span>
                    </div>
                </button>

            </nav>
        </aside>

        <!-- RIGHT DETAIL VIEWPORT / ACTIVE PANE -->
        <main class="desktop-settings-viewport">
            <form id="globalSettingsForm" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <!-- ============================================================= -->
                <!-- PANE 1 : IDENTITÉ & GÉNÉRAL                                   -->
                <!-- ============================================================= -->
                <section class="settings-pane active" id="pane-general">
                    <div class="pane-header">
                        <div>
                            <h3><i class="fa-solid fa-building-columns text-primary"></i> Identité Institutionnelle & Université</h3>
                            <p class="pane-desc">Configurez le nom officiel, le logo institutionnel et les paramètres généraux de l'université.</p>
                        </div>
                    </div>

                    <div class="settings-card-desktop">
                        <div class="card-section-title">
                            <i class="fa-solid fa-image"></i> Logo & Identité Visuelle
                        </div>
                        <div class="logo-customizer-row">
                            <div class="logo-preview-box">
                                <?php 
                                    $currentLogo = !empty($settings['university_logo']) ? $settings['university_logo'] : 'openlu v1.jpg';
                                ?>
                                <img src="<?= base_url('/assets/images/' . $currentLogo) ?>" id="logoPreviewImg" alt="Logo de l'université">
                            </div>
                            <div class="logo-controls">
                                <label class="btn-desktop-secondary btn-file-picker">
                                    <i class="fa-solid fa-cloud-arrow-up"></i> Choisir une nouvelle image
                                    <input type="file" name="university_logo" id="logoFileInput" accept="image/*" style="display: none;">
                                </label>
                                <span class="file-name-info" id="selectedLogoName">Logo actuel : <?= htmlspecialchars($currentLogo) ?></span>
                                <small class="text-muted d-block mt-1">Formats acceptés : PNG, JPG, JPEG, SVG. Format carré ou rectangulaire recommandé.</small>
                            </div>
                        </div>
                    </div>

                    <div class="settings-card-desktop">
                        <div class="card-section-title">
                            <i class="fa-solid fa-landmark"></i> Informations Officielles
                        </div>

                        <div class="desktop-form-grid">
                            <div class="form-field-group col-span-2">
                                <label for="university_name">Nom Complet de l'Établissement <span class="req">*</span></label>
                                <input type="text" id="university_name" name="university_name" class="desktop-input" value="<?= htmlspecialchars($settings['university_name'] ?? 'OPEN LEARNING UNIVERSITY') ?>" required>
                                <span class="field-hint">Ce nom apparaîtra sur tous les relevés de notes, fiches de cotes et en-têtes officiels.</span>
                            </div>

                            <div class="form-field-group">
                                <label for="academic_year">Année Académique Active <span class="req">*</span></label>
                                <select id="academic_year" name="academic_year" class="desktop-select">
                                    <?php
                                        $currentY = $settings['academic_year'] ?? '2025-2026';
                                        $years = ['2023-2024', '2024-2025', '2025-2026', '2026-2027', '2027-2028'];
                                        foreach ($years as $y) {
                                            $selected = ($currentY === $y) ? 'selected' : '';
                                            echo "<option value=\"$y\" $selected>$y</option>";
                                        }
                                    ?>
                                </select>
                                <span class="field-hint">Définit la session académique par défaut pour les cours et notes.</span>
                            </div>

                            <div class="form-field-group">
                                <label for="system_status">Statut de Fonctionnement</label>
                                <select id="system_status" name="system_status" class="desktop-select">
                                    <option value="Prêt" <?= ($settings['system_status'] ?? '') === 'Prêt' ? 'selected' : '' ?>>🟢 En ligne / Prêt (Normal)</option>
                                    <option value="Maintenance" <?= ($settings['system_status'] ?? '') === 'Maintenance' ? 'selected' : '' ?>>🟡 Maintenance (Accès restreint)</option>
                                    <option value="Fermé" <?= ($settings['system_status'] ?? '') === 'Fermé' ? 'selected' : '' ?>>🔴 Session Fermée</option>
                                </select>
                                <span class="field-hint">Contrôle l'accès global pour les enseignants et étudiants.</span>
                            </div>

                            <div class="form-field-group col-span-2">
                                <label for="sga_name">Secrétaire Général Académique (SGA)</label>
                                <input type="text" id="sga_name" name="sga_name" class="desktop-input" value="<?= htmlspecialchars($settings['sga_name'] ?? 'Prof. Dr. SGA') ?>" placeholder="ex: Prof. Dr. Jean-Baptiste KABILA, PhD">
                                <span class="field-hint">Mentionné sur les fiches de cotes officielles et actes académiques.</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ============================================================= -->
                <!-- PANE 2 : COORDONNÉES & CONTACT                                -->
                <!-- ============================================================= -->
                <section class="settings-pane" id="pane-contact">
                    <div class="pane-header">
                        <div>
                            <h3><i class="fa-solid fa-address-book text-primary"></i> Coordonnées & Siège de l'Université</h3>
                            <p class="pane-desc">Renseignez les informations de localisation et de communication de l'université.</p>
                        </div>
                    </div>

                    <div class="settings-card-desktop">
                        <div class="card-section-title">
                            <i class="fa-solid fa-map-location-dot"></i> Adresse & Localisation
                        </div>

                        <div class="desktop-form-grid">
                            <div class="form-field-group col-span-2">
                                <label for="university_address">Adresse Physique du Campus Principal</label>
                                <input type="text" id="university_address" name="university_address" class="desktop-input" value="<?= htmlspecialchars($settings['university_address'] ?? 'Boulevard du 30 Juin, RDC') ?>">
                            </div>

                            <div class="form-field-group">
                                <label for="university_bp">Boîte Postale (B.P)</label>
                                <input type="text" id="university_bp" name="university_bp" class="desktop-input" value="<?= htmlspecialchars($settings['university_bp'] ?? '218 BENI') ?>" placeholder="ex: B.P 218 BENI">
                            </div>

                            <div class="form-field-group">
                                <label for="university_phone">Numéro de Téléphone Officiel</label>
                                <input type="text" id="university_phone" name="university_phone" class="desktop-input" value="<?= htmlspecialchars($settings['university_phone'] ?? '+243 000 000 000') ?>">
                            </div>

                            <div class="form-field-group">
                                <label for="university_email">Email Institutionnel</label>
                                <input type="email" id="university_email" name="university_email" class="desktop-input" value="<?= htmlspecialchars($settings['university_email'] ?? 'contact@openlu.org') ?>">
                            </div>

                            <div class="form-field-group">
                                <label for="university_website">Site Web Officiel</label>
                                <input type="text" id="university_website" name="university_website" class="desktop-input" value="<?= htmlspecialchars($settings['university_website'] ?? 'https://www.openlu.org') ?>">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ============================================================= -->
                <!-- PANE 3 : UTILISATEURS & ACCÈS                                 -->
                <!-- ============================================================= -->
                <section class="settings-pane" id="pane-users">
                    <div class="pane-header">
                        <div>
                            <h3><i class="fa-solid fa-users-gear text-primary"></i> Gestionnaire des Comptes Utilisateurs</h3>
                            <p class="pane-desc">Créez, modifiez, réinitialisez les mots de passe et gérez les rôles d'accès au système.</p>
                        </div>
                        <button type="button" class="btn-desktop-primary" onclick="openCreateUserModal()">
                            <i class="fa-solid fa-user-plus"></i> Nouvel Utilisateur
                        </button>
                    </div>

                    <!-- Toolbar filtre utilisateurs -->
                    <div class="users-desktop-toolbar">
                        <div class="users-search-box">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" id="userFilterSearch" placeholder="Rechercher par nom, email..." autocomplete="off">
                        </div>
                        <div class="users-role-filters">
                            <button type="button" class="role-filter-btn active" data-filter="all">Tous (<?= count($users) ?>)</button>
                            <button type="button" class="role-filter-btn" data-filter="admin">Admins (<?= $stats['admin'] ?>)</button>
                            <button type="button" class="role-filter-btn" data-filter="enseignant">Enseignants (<?= $stats['enseignant'] ?>)</button>
                            <button type="button" class="role-filter-btn" data-filter="etudiant">Étudiants (<?= $stats['etudiant'] ?>)</button>
                            <button type="button" class="role-filter-btn" data-filter="finance">Finance (<?= $stats['finance'] ?>)</button>
                        </div>
                    </div>

                    <!-- Table des utilisateurs desktop -->
                    <div class="desktop-table-container">
                        <table class="desktop-data-table" id="usersDataTable">
                            <thead>
                                <tr>
                                    <th>Utilisateur</th>
                                    <th>Email de connexion</th>
                                    <th width="150">Rôle attribué</th>
                                    <th width="120">Créé le</th>
                                    <th width="140" class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                    <tr class="user-row" data-role="<?= htmlspecialchars(strtolower($u['role'])) ?>" data-name="<?= strtolower(htmlspecialchars($u['name'])) ?>" data-email="<?= strtolower(htmlspecialchars($u['email'])) ?>">
                                        <td>
                                            <div class="user-avatar-cell">
                                                <div class="user-avatar-circle avatar-<?= strtolower($u['role']) ?>">
                                                    <?= strtoupper(substr($u['name'], 0, 2)) ?>
                                                </div>
                                                <div class="user-name-meta">
                                                    <strong><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                    <?php if ($u['id'] == ($_SESSION['user']['id'] ?? 0)): ?>
                                                        <span class="badge-current-user">Vous</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code class="user-email-tag"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></code>
                                        </td>
                                        <td>
                                            <span class="badge-role badge-role-<?= strtolower($u['role']) ?>">
                                                <?= ucfirst(htmlspecialchars($u['role'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($u['created_at'])) ?></small>
                                        </td>
                                        <td class="text-right">
                                            <div class="table-actions-inline">
                                                <button type="button" class="btn-table-action" title="Modifier l'utilisateur" onclick="openEditUserModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <?php if ($u['id'] != ($_SESSION['user']['id'] ?? 0)): ?>
                                                    <button type="button" class="btn-table-action btn-action-delete" title="Supprimer le compte" onclick="deleteUserAccount(<?= $u['id'] ?>, '<?= addslashes(htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8')) ?>')">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- ============================================================= -->
                <!-- PANE 4 : RÔLES & PERMISSIONS                                  -->
                <!-- ============================================================= -->
                <section class="settings-pane" id="pane-roles">
                    <div class="pane-header">
                        <div>
                            <h3><i class="fa-solid fa-shield-halved text-primary"></i> Matrice des Rôles & Permissions</h3>
                            <p class="pane-desc">Architecture de sécurité et séparation des privilèges selon le cadre LMD.</p>
                        </div>
                    </div>

                    <div class="roles-cards-grid">
                        <?php foreach ($roleDescriptions as $rKey => $rData): ?>
                            <div class="role-desktop-card card-role-<?= $rKey ?>">
                                <div class="role-card-header">
                                    <div class="role-title-box">
                                        <span class="badge-role badge-role-<?= $rKey ?>"><?= $rData['name'] ?></span>
                                        <span class="role-count-pill"><?= $stats[$rKey] ?? 0 ?> membre(s)</span>
                                    </div>
                                    <p class="role-desc"><?= $rData['description'] ?></p>
                                </div>
                                <div class="role-card-body">
                                    <div class="perm-section">
                                        <div class="perm-title"><i class="fa-solid fa-circle-check text-success"></i> Privilèges autorisés :</div>
                                        <ul class="perm-list">
                                            <?php foreach ($rData['permissions'] as $p): ?>
                                                <li><?= $p ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <?php if (!empty($rData['restrictions'])): ?>
                                        <div class="perm-section mt-2">
                                            <div class="perm-title"><i class="fa-solid fa-circle-xmark text-danger"></i> Restrictions strictes :</div>
                                            <ul class="perm-list text-muted">
                                                <?php foreach ($rData['restrictions'] as $res): ?>
                                                    <li><?= $res ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- ============================================================= -->
                <!-- PANE 5 : ACADÉMIQUE & COTATION                                -->
                <!-- ============================================================= -->
                <section class="settings-pane" id="pane-academique">
                    <div class="pane-header">
                        <div>
                            <h3><i class="fa-solid fa-graduation-cap text-primary"></i> Règles de Cotation & Dérogations SGA</h3>
                            <p class="pane-desc">Paramètres académiques régissant les Fiches de Cotes officielles et les flux de dérogation.</p>
                        </div>
                    </div>

                    <div class="settings-card-desktop">
                        <div class="card-section-title">
                            <i class="fa-solid fa-calculator"></i> Barème Officiel OPENLU (Total / 200 pts • Moyenne / 20 pts)
                        </div>
                        <div class="bareme-preview-box">
                            <div class="bareme-item">
                                <span class="lbl">Travaux Journaliers (TJ)</span>
                                <strong class="val">100 Pts</strong>
                                <small class="text-muted">Interro (30) + TP (40) + TD (30)</small>
                            </div>
                            <div class="bareme-sep">+</div>
                            <div class="bareme-item">
                                <span class="lbl">Mi-Session</span>
                                <strong class="val">50 Pts</strong>
                                <small class="text-muted">Évaluation mi-parcours</small>
                            </div>
                            <div class="bareme-sep">+</div>
                            <div class="bareme-item">
                                <span class="lbl">Examen Final</span>
                                <strong class="val">50 Pts</strong>
                                <small class="text-muted">Épreuve terminale</small>
                            </div>
                            <div class="bareme-sep">=</div>
                            <div class="bareme-item highlight-total">
                                <span class="lbl">Total Général</span>
                                <strong class="val">200 Pts</strong>
                                <small class="text-primary font-weight-bold">Moyenne = Total / 10 (/ 20 pts)</small>
                            </div>
                        </div>
                    </div>

                    <div class="settings-card-desktop">
                        <div class="card-section-title">
                            <i class="fa-solid fa-lock"></i> Règle de Verrouillage & Dérogations du SGA
                        </div>
                        <p class="text-muted" style="font-size: 13.5px; line-height: 1.6;">
                            Le système applique la politique stricte de sécurité académique :
                        </p>
                        <ul class="academic-rules-list">
                            <li><i class="fa-solid fa-check-double text-success"></i> <strong>Saisie initiale & 1ère modification :</strong> L'enseignant peut modifier librement les notes une seule fois.</li>
                            <li><i class="fa-solid fa-shield-halved text-warning"></i> <strong>Verrouillage automatique :</strong> Dès la première modification effectuée, les cotes sont scellées.</li>
                            <li><i class="fa-solid fa-file-signature text-primary"></i> <strong>Dérogation SGA :</strong> Toute modification ultérieure requiert une demande motivée adressée au Secrétariat Général Académique.</li>
                        </ul>
                    </div>
                </section>

                <!-- ============================================================= -->
                <!-- PANE 6 : FINANCES & COMPTABILITÉ                              -->
                <!-- ============================================================= -->
                <section class="settings-pane" id="pane-finance">
                    <div class="pane-header">
                        <div>
                            <h3><i class="fa-solid fa-money-bill-transfer text-primary"></i> Paramètres Financiers & Tarification</h3>
                            <p class="pane-desc">Configuration des frais généraux et de la monnaie de tenue de compte.</p>
                        </div>
                    </div>

                    <div class="settings-card-desktop">
                        <div class="card-section-title">
                            <i class="fa-solid fa-wallet"></i> Frais & Monnaie
                        </div>

                        <div class="desktop-form-grid">
                            <div class="form-field-group">
                                <label for="registration_fee">Frais d'Inscription par défaut</label>
                                <input type="number" id="registration_fee" name="registration_fee" class="desktop-input" value="<?= htmlspecialchars($settings['registration_fee'] ?? '20') ?>" min="0" step="0.5">
                                <span class="field-hint">Montant standard appliqué lors de l'enregistrement d'un étudiant.</span>
                            </div>

                            <div class="form-field-group">
                                <label for="default_currency">Devise Principale du Système</label>
                                <select id="default_currency" name="default_currency" class="desktop-select">
                                    <option value="USD" <?= ($settings['default_currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD - Dollar Américain ($)</option>
                                    <option value="CDF" <?= ($settings['default_currency'] ?? '') === 'CDF' ? 'selected' : '' ?>>CDF - Franc Congolais (FC)</option>
                                </select>
                                <span class="field-hint">Devise de référence pour les factures et états financiers.</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ============================================================= -->
                <!-- PANE 6B : PLAN COMPTABLE (SYSCOHADA)                          -->
                <!-- ============================================================= -->
                <section class="settings-pane" id="pane-comptable">
                    <div class="pane-header">
                        <div>
                            <h3><i class="fa-solid fa-book-bookmark text-primary"></i> Plan Comptable Général (SYSCOHADA)</h3>
                            <p class="pane-desc">Structure des comptes financiers pour la comptabilité analytique et générale.</p>
                        </div>
                        <button type="button" class="btn-desktop-primary" onclick="showAddCompteModal()">
                            <i class="fa-solid fa-plus"></i> Nouveau Compte
                        </button>
                    </div>

                    <div class="settings-card-desktop">
                        <div class="comptes-tree-container">
                            <table class="desktop-data-table" id="comptesTreeTable">
                                <thead>
                                    <tr>
                                        <th width="140">Numéro Compte</th>
                                        <th>Intitulé du Compte</th>
                                        <th width="130">Type de Compte</th>
                                        <th width="90" class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="comptes_table_body">
                                    <tr><td colspan="4" class="text-center py-4 text-muted"><i class="fa-solid fa-spinner fa-spin"></i> Chargement du plan comptable...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- ============================================================= -->
                <!-- PANE 7 : DIAGNOSTIC & SYSTÈME                                 -->
                <!-- ============================================================= -->
                <section class="settings-pane" id="pane-system">
                    <div class="pane-header">
                        <div>
                            <h3><i class="fa-solid fa-server text-primary"></i> Diagnostic Serveur & Environnement</h3>
                            <p class="pane-desc">Informations techniques sur l'état de l'application et de l'hébergement.</p>
                        </div>
                    </div>

                    <div class="diagnostic-grid">
                        <div class="diag-card">
                            <div class="diag-icon"><i class="fa-brands fa-php"></i></div>
                            <div class="diag-info">
                                <span class="lbl">Version PHP</span>
                                <strong><?= phpversion() ?></strong>
                            </div>
                        </div>
                        <div class="diag-card">
                            <div class="diag-icon"><i class="fa-solid fa-database"></i></div>
                            <div class="diag-info">
                                <span class="lbl">Moteur Base de Données</span>
                                <strong>MySQL / MariaDB (PDO)</strong>
                            </div>
                        </div>
                        <div class="diag-card">
                            <div class="diag-icon"><i class="fa-solid fa-memory"></i></div>
                            <div class="diag-info">
                                <span class="lbl">Limite Mémoire PHP</span>
                                <strong><?= ini_get('memory_limit') ?></strong>
                            </div>
                        </div>
                        <div class="diag-card">
                            <div class="diag-icon"><i class="fa-solid fa-clock"></i></div>
                            <div class="diag-info">
                                <span class="lbl">Fuseau Horaire Serveur</span>
                                <strong><?= date_default_timezone_get() ?> (<?= date('H:i:s') ?>)</strong>
                            </div>
                        </div>
                    </div>

                    <div class="settings-card-desktop mt-3">
                        <div class="card-section-title">
                            <i class="fa-solid fa-user-shield"></i> Compte Administrateur Connecté
                        </div>
                        <div class="admin-profile-box">
                            <div class="user-avatar-circle avatar-admin avatar-lg">
                                <?= strtoupper(substr($user['name'] ?? 'AD', 0, 2)) ?>
                            </div>
                            <div class="admin-meta">
                                <h4><?= htmlspecialchars($user['name'] ?? 'Administrateur', ENT_QUOTES, 'UTF-8') ?></h4>
                                <p class="text-muted mb-1"><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                                <span class="badge-role badge-role-admin">Super Administrateur SGA</span>
                            </div>
                        </div>
                    </div>
                </section>

            </form>
        </main>
    </div>

    <!-- BOTTOM FLOATING ACTION BAR -->
    <div class="desktop-save-bar">
        <div class="save-bar-left">
            <span class="save-status-indicator" id="saveStatusIndicator">
                <i class="fa-solid fa-check-circle text-success"></i> Tous les paramètres sont synchronisés
            </span>
        </div>
        <div class="save-bar-right">
            <button type="button" class="btn-desktop-secondary" onclick="location.reload()">
                <i class="fa-solid fa-rotate-left"></i> Annuler
            </button>
            <button type="button" class="btn-desktop-primary" id="btnSaveBottom" onclick="saveAllSettings(event)">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer les modifications
            </button>
        </div>
    </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: NOUVEL UTILISATEUR                                     -->
<!-- ============================================================= -->
<div id="modalUser" class="desktop-modal-backdrop" style="display: none;">
    <div class="desktop-modal-card">
        <div class="desktop-modal-header">
            <h3 id="modalUserTitle"><i class="fa-solid fa-user-plus text-primary"></i> Nouvel Utilisateur</h3>
            <button type="button" class="btn-close-modal" onclick="closeUserModal()">&times;</button>
        </div>
        <form id="userForm" onsubmit="submitUserForm(event)">
            <input type="hidden" id="user_id" name="id">
            <div class="desktop-modal-body">
                <div class="form-field-group mb-3">
                    <label for="user_name">Nom & Prénom <span class="req">*</span></label>
                    <input type="text" id="user_name" name="name" class="desktop-input" required placeholder="ex: Jean-Paul MBIYA">
                </div>
                <div class="form-field-group mb-3">
                    <label for="user_email">Email de connexion <span class="req">*</span></label>
                    <input type="email" id="user_email" name="email" class="desktop-input" required placeholder="ex: j.mbiya@openlu.org">
                </div>
                <div class="form-field-group mb-3">
                    <label for="user_role">Rôle du compte <span class="req">*</span></label>
                    <select id="user_role" name="role" class="desktop-select" required>
                        <option value="admin">Administrateur</option>
                        <option value="enseignant">Enseignant</option>
                        <option value="etudiant">Étudiant</option>
                        <option value="finance">Service Financier</option>
                    </select>
                </div>
                <div class="form-field-group mb-3">
                    <label for="user_password" id="lblUserPassword">Mot de passe <span class="req">*</span></label>
                    <input type="password" id="user_password" name="password" class="desktop-input" placeholder="••••••••">
                    <small class="text-muted" id="userPasswordHelp">Minimum 6 caractères.</small>
                </div>
            </div>
            <div class="desktop-modal-footer">
                <button type="button" class="btn-desktop-secondary" onclick="closeUserModal()">Annuler</button>
                <button type="submit" class="btn-desktop-primary" id="btnSubmitUser">
                    <i class="fa-solid fa-check"></i> Enregistrer l'utilisateur
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: NOUVEAU COMPTE COMPTABLE                               -->
<!-- ============================================================= -->
<div id="modalCompte" class="desktop-modal-backdrop" style="display: none;">
    <div class="desktop-modal-card">
        <div class="desktop-modal-header">
            <h3><i class="fa-solid fa-plus-circle text-primary"></i> Ajouter un compte comptable</h3>
            <button type="button" class="btn-close-modal" onclick="closeAddCompteModal()">&times;</button>
        </div>
        <div class="desktop-modal-body">
            <div class="form-field-group mb-3">
                <label>Numéro de Compte (SYSCOHADA) <span class="req">*</span></label>
                <input type="text" id="new_compte_numero" class="desktop-input" placeholder="ex: 5713">
            </div>
            <div class="form-field-group mb-3">
                <label>Intitulé du Compte <span class="req">*</span></label>
                <input type="text" id="new_compte_libelle" class="desktop-input" placeholder="ex: Caisse Principale">
            </div>
            <div class="form-field-group mb-3">
                <label>Type de Compte</label>
                <select id="new_compte_type" class="desktop-select">
                    <option value="Actif">Actif</option>
                    <option value="Passif">Passif</option>
                    <option value="Charge">Charge</option>
                    <option value="Produit">Produit</option>
                </select>
            </div>
            <div class="form-field-group mb-3">
                <label>Compte Parent (Numéro)</label>
                <input type="text" id="new_compte_parent" class="desktop-input" placeholder="ex: 57 (laisser vide si racine)">
            </div>
            <div class="form-field-group">
                <label class="checkbox-desktop-label">
                    <input type="checkbox" id="new_compte_is_groupe"> 
                    <span>C'est un compte de groupe (non mouvementable directement)</span>
                </label>
            </div>
        </div>
        <div class="desktop-modal-footer">
            <button type="button" class="btn-desktop-secondary" onclick="closeAddCompteModal()">Annuler</button>
            <button type="button" class="btn-desktop-primary" onclick="saveNewCompte()">
                <i class="fa-solid fa-check"></i> Créer le compte
            </button>
        </div>
    </div>
</div>

<!-- TOAST NOTIFICATION CONTAINER -->
<div id="desktopToast" class="desktop-toast" style="display: none;">
    <i class="fa-solid fa-circle-check toast-icon"></i>
    <div class="toast-content">
        <strong id="toastTitle">Succès</strong>
        <p id="toastMessage">Modifications enregistrées.</p>
    </div>
</div>

<!-- ============================================================= -->
<!-- JAVASCRIPT LOGIC                                              -->
<!-- ============================================================= -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. Navigation entre les onglets ---
    const navItems = document.querySelectorAll('.settings-nav-list .nav-item');
    const panes = document.querySelectorAll('.settings-pane');

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            
            navItems.forEach(n => n.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            const targetPane = document.getElementById(targetId);
            if (targetPane) {
                targetPane.classList.add('active');
            }

            // Si onglet plan comptable, charger la liste
            if (targetId === 'pane-comptable') {
                loadComptes();
            }
        });
    });

    // --- 2. Recherche rapide dans les paramètres ---
    const searchInput = document.getElementById('settingsSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            navItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (q === '' || text.includes(q)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // --- 3. Aperçu en direct du logo uploadé ---
    const logoInput = document.getElementById('logoFileInput');
    const logoPreview = document.getElementById('logoPreviewImg');
    const logoNameInfo = document.getElementById('selectedLogoName');

    if (logoInput) {
        logoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                logoNameInfo.textContent = "Sélectionné : " + file.name;
                const reader = new FileReader();
                reader.onload = function(e) {
                    logoPreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
                showUnsavedState();
            }
        });
    }

    // Détecter les modifications dans le formulaire
    document.querySelectorAll('#globalSettingsForm input, #globalSettingsForm select, #globalSettingsForm textarea').forEach(el => {
        el.addEventListener('input', showUnsavedState);
        el.addEventListener('change', showUnsavedState);
    });

    // Raccourci clavier Ctrl+S / Cmd+S
    window.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            saveAllSettings(e);
        }
    });

    // --- 4. Filtre dynamique de la liste des utilisateurs ---
    const userSearch = document.getElementById('userFilterSearch');
    const roleFilterBtns = document.querySelectorAll('.role-filter-btn');
    const userRows = document.querySelectorAll('.user-row');

    let currentRoleFilter = 'all';

    function filterUsers() {
        const q = userSearch ? userSearch.value.toLowerCase().trim() : '';
        userRows.forEach(row => {
            const role = row.getAttribute('data-role');
            const name = row.getAttribute('data-name');
            const email = row.getAttribute('data-email');

            const matchesRole = (currentRoleFilter === 'all' || role === currentRoleFilter);
            const matchesText = (q === '' || name.includes(q) || email.includes(q));

            row.style.display = (matchesRole && matchesText) ? '' : 'none';
        });
    }

    if (userSearch) {
        userSearch.addEventListener('input', filterUsers);
    }

    roleFilterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            roleFilterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentRoleFilter = this.getAttribute('data-filter');
            filterUsers();
        });
    });
});

function showUnsavedState() {
    const indicator = document.getElementById('saveStatusIndicator');
    if (indicator) {
        indicator.innerHTML = '<i class="fa-solid fa-circle-dot text-warning"></i> Modifications non enregistrées (Ctrl+S)';
    }
}

// --- Sauvegarde Globale ---
async function saveAllSettings(e) {
    if (e) e.preventDefault();

    const btn = document.getElementById('btnSaveBottom');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Enregistrement...';

    const form = document.getElementById('globalSettingsForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('<?= base_url('/parametres/update-settings') ?>', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (response.ok && data.success) {
            showToast("Succès", "Tous les paramètres ont été enregistrés avec succès !");
            const indicator = document.getElementById('saveStatusIndicator');
            if (indicator) {
                indicator.innerHTML = '<i class="fa-solid fa-check-circle text-success"></i> Tous les paramètres sont synchronisés';
            }
        } else {
            showToast("Erreur", data.error || "Impossible d'enregistrer les paramètres", "error");
        }
    } catch (err) {
        showToast("Erreur réseau", "La communication avec le serveur a échoué.", "error");
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// --- GESTION DES UTILISATEURS (MODAL & ACTIONS) ---
function openCreateUserModal() {
    document.getElementById('modalUserTitle').innerHTML = '<i class="fa-solid fa-user-plus text-primary"></i> Nouvel Utilisateur';
    document.getElementById('user_id').value = '';
    document.getElementById('user_name').value = '';
    document.getElementById('user_email').value = '';
    document.getElementById('user_role').value = 'enseignant';
    document.getElementById('user_password').value = '';
    document.getElementById('user_password').required = true;
    document.getElementById('userPasswordHelp').textContent = 'Minimum 6 caractères.';
    document.getElementById('modalUser').style.display = 'flex';
}

function openEditUserModal(user) {
    document.getElementById('modalUserTitle').innerHTML = '<i class="fa-solid fa-user-pen text-primary"></i> Modifier l\'Utilisateur';
    document.getElementById('user_id').value = user.id;
    document.getElementById('user_name').value = user.name;
    document.getElementById('user_email').value = user.email;
    document.getElementById('user_role').value = user.role;
    document.getElementById('user_password').value = '';
    document.getElementById('user_password').required = false;
    document.getElementById('userPasswordHelp').textContent = 'Laisser vide pour conserver le mot de passe actuel.';
    document.getElementById('modalUser').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('modalUser').style.display = 'none';
}

async function submitUserForm(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitUser');
    btn.disabled = true;

    const id = document.getElementById('user_id').value;
    const name = document.getElementById('user_name').value.trim();
    const email = document.getElementById('user_email').value.trim();
    const role = document.getElementById('user_role').value;
    const password = document.getElementById('user_password').value;

    const isEdit = !!id;
    const url = isEdit ? '<?= base_url('/parametres/users/update') ?>' : '<?= base_url('/parametres/users/store') ?>';

    const payload = { id, name, email, role, password };

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const res = await response.json();

        if (response.ok && res.success) {
            closeUserModal();
            showToast("Succès", res.message || "Opération réussie !");
            setTimeout(() => location.reload(), 800);
        } else {
            alert(res.error || "Une erreur est survenue.");
        }
    } catch (err) {
        alert("Erreur de communication avec le serveur.");
    } finally {
        btn.disabled = false;
    }
}

async function deleteUserAccount(id, name) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer définitivement le compte de ${name} ?`)) {
        return;
    }

    try {
        const response = await fetch(`<?= base_url('/parametres/users/delete/') ?>${id}`, {
            method: 'POST'
        });
        const res = await response.json();
        if (response.ok && res.success) {
            showToast("Supprimé", "L'utilisateur a été supprimé.");
            setTimeout(() => location.reload(), 700);
        } else {
            alert(res.error || "Impossible de supprimer cet utilisateur.");
        }
    } catch (err) {
        alert("Erreur lors de la suppression.");
    }
}

// --- PLAN COMPTABLE SYSCOHADA ---
function loadComptes() {
    fetch('<?= base_url('/api/accounting/comptes-all') ?>')
        .then(r => r.json())
        .then(resp => {
            const tbody = document.getElementById('comptes_table_body');
            if (!resp.success || resp.data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Aucun compte trouvé dans le plan comptable.</td></tr>';
                return;
            }
            tbody.innerHTML = resp.data.map(c => {
                const indent = '&nbsp;&nbsp;'.repeat(Math.max(0, c.numero.length - 2));
                const icon = c.is_groupe ? '<i class="fa-solid fa-folder text-warning"></i>' : '<i class="fa-solid fa-file-invoice text-muted"></i>';
                const badgeClass = {Actif:'badge-actif', Passif:'badge-passif', Charge:'badge-charge', Produit:'badge-produit'}[c.type_compte] || 'badge-neutral';
                return `<tr>
                    <td class="font-mono">${indent}${icon} <strong>${c.numero}</strong></td>
                    <td><strong>${c.libelle}</strong></td>
                    <td><span class="badge-compte ${badgeClass}">${c.type_compte}</span></td>
                    <td class="text-right">
                        ${!c.is_groupe ? `<button type="button" onclick="deleteCompte('${c.numero}')" class="btn-table-action btn-action-delete" title="Supprimer"><i class="fa-solid fa-trash"></i></button>` : ''}
                    </td>
                </tr>`;
            }).join('');
        })
        .catch(() => {
            document.getElementById('comptes_table_body').innerHTML = '<tr><td colspan="4" class="text-center text-danger py-3">Erreur de chargement du plan comptable</td></tr>';
        });
}

function showAddCompteModal() {
    document.getElementById('new_compte_numero').value = '';
    document.getElementById('new_compte_libelle').value = '';
    document.getElementById('new_compte_parent').value = '';
    document.getElementById('new_compte_is_groupe').checked = false;
    document.getElementById('modalCompte').style.display = 'flex';
}

function closeAddCompteModal() {
    document.getElementById('modalCompte').style.display = 'none';
}

function saveNewCompte() {
    const data = {
        numero: document.getElementById('new_compte_numero').value.trim(),
        libelle: document.getElementById('new_compte_libelle').value.trim(),
        type_compte: document.getElementById('new_compte_type').value,
        parent_numero: document.getElementById('new_compte_parent').value.trim() || null,
        is_groupe: document.getElementById('new_compte_is_groupe').checked ? 1 : 0
    };
    if (!data.numero || !data.libelle) {
        alert('Le numéro et l\'intitulé sont obligatoires.');
        return;
    }

    fetch('<?= base_url('/api/accounting/compte/add') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    }).then(r => r.json()).then(resp => {
        if (resp.success) {
            closeAddCompteModal();
            loadComptes();
            showToast("Compte Ajouté", "Nouveau compte enregistré dans le plan comptable.");
        } else {
            alert('Erreur : ' + resp.message);
        }
    });
}

function deleteCompte(numero) {
    if (!confirm(`Supprimer le compte N° ${numero} ?`)) return;
    fetch('<?= base_url('/api/accounting/compte/delete') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({numero})
    }).then(r => r.json()).then(resp => {
        if (resp.success) {
            loadComptes();
            showToast("Supprimé", `Le compte ${numero} a été retiré.`);
        } else {
            alert('Erreur : ' + resp.message);
        }
    });
}

// --- Toast Notification Desktop ---
function showToast(title, message, type = 'success') {
    const toast = document.getElementById('desktopToast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMsg = document.getElementById('toastMessage');
    const icon = toast.querySelector('.toast-icon');

    toastTitle.textContent = title;
    toastMsg.textContent = message;

    if (type === 'error') {
        toast.className = 'desktop-toast toast-error';
        icon.className = 'fa-solid fa-circle-exclamation toast-icon';
    } else {
        toast.className = 'desktop-toast toast-success';
        icon.className = 'fa-solid fa-circle-check toast-icon';
    }

    toast.style.display = 'flex';
    setTimeout(() => {
        toast.style.display = 'none';
    }, 3500);
}
</script>

<!-- ============================================================= -->
<!-- DESKTOP SUITE STYLES (PURE LUXURY DESIGN)                    -->
<!-- ============================================================= -->
<style>
/* === CONTAINER PRINCIPAL DESKTOP === */
.desktop-settings-app {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 14px;
    box-shadow: 0 12px 36px rgba(15, 23, 42, 0.08), 0 2px 6px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    margin-bottom: 50px;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    color: #1e293b;
}

/* === WINDOW TITLEBAR === */
.desktop-titlebar {
    background: linear-gradient(180deg, #ffffff 0%, #f1f5f9 100%);
    border-bottom: 1px solid #cbd5e1;
    padding: 14px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    user-select: none;
    flex-wrap: wrap;
    gap: 15px;
}

.titlebar-left {
    display: flex;
    align-items: center;
    gap: 18px;
}

.window-decorations {
    display: flex;
    gap: 7px;
}

.window-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.dot-close { background: #ef4444; }
.dot-min   { background: #f59e0b; }
.dot-max   { background: #10b981; }

.titlebar-heading {
    display: flex;
    align-items: center;
    gap: 12px;
}

.app-icon-badge {
    background: #001a72;
    color: white;
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 17px;
    box-shadow: 0 2px 6px rgba(0, 26, 114, 0.25);
}

.titlebar-heading h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.2px;
}

.version-tag {
    font-size: 11.5px;
    color: #64748b;
    font-weight: 500;
}

.titlebar-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.system-status-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    border: 1px solid #e2e8f0;
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
}

.status-pulse {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}
.pulse-green {
    background: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

/* === BOUTONS DESKTOP === */
.btn-desktop-primary {
    background: #001a72;
    color: white;
    border: 1px solid #001252;
    padding: 8px 18px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.15s;
    box-shadow: 0 2px 4px rgba(0, 26, 114, 0.15);
}

.btn-desktop-primary:hover {
    background: #00145a;
}

.btn-desktop-primary kbd {
    background: rgba(255,255,255,0.2);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    font-family: monospace;
}

.btn-desktop-secondary {
    background: white;
    color: #334155;
    border: 1px solid #cbd5e1;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: all 0.15s;
}

.btn-desktop-secondary:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
    color: #0f172a;
}

/* === WORKSPACE 2 COLONNES === */
.desktop-workspace {
    display: flex;
    min-height: 680px;
    background: white;
}

/* === LEFT SIDEBAR / MASTER PANE === */
.desktop-settings-sidebar {
    width: 280px;
    background: #f8fafc;
    border-right: 1px solid #e2e8f0;
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    flex-shrink: 0;
}

.sidebar-search-box {
    position: relative;
    margin-bottom: 6px;
}

.sidebar-search-box input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    background: white;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12.5px;
    color: #1e293b;
    outline: none;
    transition: border 0.15s;
}

.sidebar-search-box input:focus {
    border-color: #001a72;
    box-shadow: 0 0 0 3px rgba(0, 26, 114, 0.08);
}

.sidebar-search-box i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 12px;
}

.settings-nav-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
    overflow-y: auto;
}

.nav-group-title {
    font-size: 10px;
    font-weight: 800;
    color: #94a3b8;
    letter-spacing: 0.8px;
    padding: 10px 10px 4px 10px;
    text-transform: uppercase;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 9px 12px;
    border-radius: 8px;
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    transition: all 0.15s;
    position: relative;
}

.nav-item:hover {
    background: #e2e8f0;
}

.nav-item.active {
    background: #001a72;
    color: white;
    box-shadow: 0 2px 6px rgba(0, 26, 114, 0.18);
}

.nav-item.active .nav-icon {
    background: rgba(255,255,255,0.2);
    color: white;
}

.nav-item.active .nav-text .title { color: white; }
.nav-item.active .nav-text .sub { color: #cbd5e1; }

.nav-icon {
    width: 32px;
    height: 32px;
    border-radius: 7px;
    background: #e2e8f0;
    color: #475569;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.nav-text {
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
}

.nav-text .title {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.nav-text .sub {
    font-size: 10.5px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.nav-badge {
    background: #cbd5e1;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 12px;
}

.nav-item.active .nav-badge {
    background: rgba(255,255,255,0.25);
    color: white;
}

/* === RIGHT DETAIL VIEWPORT === */
.desktop-settings-viewport {
    flex: 1;
    padding: 28px 36px 80px 36px;
    background: #ffffff;
    overflow-y: auto;
    max-height: calc(100vh - 180px);
}

.settings-pane {
    display: none;
    animation: fadeInPane 0.2s ease-out;
}

.settings-pane.active {
    display: block;
}

@keyframes fadeInPane {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.pane-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 16px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 15px;
}

.pane-header h3 {
    margin: 0 0 4px 0;
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}

.pane-desc {
    margin: 0;
    font-size: 13.5px;
    color: #64748b;
}

/* === CARTES ET SECTIONS DE PARAMÈTRES === */
.settings-card-desktop {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 22px 26px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

.card-section-title {
    font-size: 14.5px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 9px;
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 10px;
}

/* === LOGO CUSTOMIZER === */
.logo-customizer-row {
    display: flex;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
}

.logo-preview-box {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    padding: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.logo-preview-box img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.logo-controls {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.file-name-info {
    font-size: 12px;
    color: #475569;
    font-weight: 600;
}

/* === FORMULAIRES DESKTOP === */
.desktop-form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px 24px;
}

.col-span-2 {
    grid-column: span 2;
}

.form-field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-field-group label {
    font-size: 13px;
    font-weight: 700;
    color: #334155;
}

.form-field-group label .req {
    color: #ef4444;
}

.desktop-input, .desktop-select {
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 13.5px;
    color: #0f172a;
    background: #fdfdfd;
    outline: none;
    transition: all 0.15s;
    width: 100%;
}

.desktop-input:focus, .desktop-select:focus {
    border-color: #001a72;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(0, 26, 114, 0.1);
}

.field-hint {
    font-size: 11.5px;
    color: #64748b;
    margin-top: 2px;
}

/* === TOOLBAR & DATA-TABLE DESKTOP === */
.users-desktop-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
}

.users-search-box {
    position: relative;
    width: 260px;
}

.users-search-box input {
    width: 100%;
    padding: 7px 12px 7px 32px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12.5px;
}

.users-search-box i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 12px;
}

.users-role-filters {
    display: flex;
    gap: 6px;
}

.role-filter-btn {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    color: #475569;
    padding: 5px 12px;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
}

.role-filter-btn:hover { background: #e2e8f0; }

.role-filter-btn.active {
    background: #001a72;
    color: white;
    border-color: #001a72;
}

.desktop-table-container {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow-x: auto;
}

.desktop-data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.desktop-data-table thead th {
    background: #f8fafc;
    color: #475569;
    font-weight: 700;
    padding: 12px 16px;
    border-bottom: 2px solid #e2e8f0;
    text-align: left;
}

.desktop-data-table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}

.user-avatar-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar-circle {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 12px;
    color: white;
    flex-shrink: 0;
}

.avatar-lg {
    width: 50px;
    height: 50px;
    font-size: 18px;
}

.avatar-admin      { background: #dc2626; }
.avatar-enseignant { background: #0284c7; }
.avatar-etudiant   { background: #16a34a; }
.avatar-finance    { background: #d97706; }

.badge-current-user {
    color: #1d4ed8;
    font-size: 10px;
    font-weight: 800;
    margin-left: 6px;
}

.user-email-tag {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    color: #334155;
}

/* === BADGES DE RÔLE === */
.badge-role {
    display: inline-block;
    font-size: 12px;
    font-weight: 700;
}

.badge-role-admin      { color: #b91c1c; }
.badge-role-enseignant { color: #0369a1; }
.badge-role-etudiant   { color: #15803d; }
.badge-role-finance    { color: #b45309; }

.table-actions-inline {
    display: flex;
    gap: 6px;
    justify-content: flex-end;
}

.btn-table-action {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #475569;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.15s;
}

.btn-table-action:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.btn-action-delete:hover {
    background: #fee2e2;
    border-color: #fca5a5;
    color: #b91c1c;
}

/* === RÔLES & PERMISSIONS GRID === */
.roles-cards-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.role-desktop-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}

.role-card-header {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 12px;
    margin-bottom: 12px;
}

.role-title-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.role-count-pill {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    padding: 2px 8px;
    border-radius: 12px;
}

.role-desc {
    margin: 0;
    font-size: 12.5px;
    color: #64748b;
}

.perm-title {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.perm-list {
    margin: 0;
    padding-left: 18px;
    font-size: 12px;
    line-height: 1.6;
    color: #334155;
}

/* === BARÈME BOX === */
.bareme-preview-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 16px 20px;
    flex-wrap: wrap;
    gap: 12px;
}

.bareme-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.bareme-item .lbl { font-size: 12px; font-weight: 700; color: #475569; }
.bareme-item .val { font-size: 18px; font-weight: 900; color: #0f172a; margin: 2px 0; }
.bareme-sep { font-size: 20px; font-weight: 900; color: #94a3b8; }

.highlight-total {
    background: #e0f2fe;
    padding: 10px 18px;
    border-radius: 8px;
    border: 1px solid #bae6fd;
}
.highlight-total .val { color: #0369a1; }

.academic-rules-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
    font-size: 13px;
}

.academic-rules-list li {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    padding: 10px 14px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

/* === DIAGNOSTIC GRID === */
.diagnostic-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.diag-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
}

.diag-icon {
    font-size: 24px;
    color: #001a72;
    width: 44px;
    height: 44px;
    border-radius: 8px;
    background: #e0f2fe;
    display: flex;
    align-items: center;
    justify-content: center;
}

.diag-info .lbl { font-size: 11px; color: #64748b; font-weight: 600; display: block; }
.diag-info strong { font-size: 14px; color: #0f172a; font-weight: 700; }

.admin-profile-box {
    display: flex;
    align-items: center;
    gap: 18px;
}

.admin-meta h4 { margin: 0 0 2px 0; font-size: 16px; font-weight: 800; }

/* === PLAN COMPTABLE TYPES === */
.badge-compte {
    font-size: 11px;
    font-weight: 700;
}
.badge-actif   { color: #0284c7; }
.badge-passif  { color: #dc2626; }
.badge-charge  { color: #d97706; }
.badge-produit { color: #16a34a; }

/* === BOTTOM FLOATING SAVE BAR === */
.desktop-save-bar {
    position: sticky;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    border-top: 1px solid #cbd5e1;
    padding: 14px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 -4px 16px rgba(0,0,0,0.06);
    z-index: 100;
}

.save-status-indicator {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.save-bar-right {
    display: flex;
    gap: 12px;
}

/* === MODALES DESKTOP === */
.desktop-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.6);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(3px);
}

.desktop-modal-card {
    background: white;
    width: 90%;
    max-width: 520px;
    border-radius: 14px;
    box-shadow: 0 20px 30px -5px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: modalScale 0.2s ease-out;
}

.desktop-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

.desktop-modal-header h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 800;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-close-modal {
    background: none;
    border: none;
    font-size: 24px;
    color: #94a3b8;
    cursor: pointer;
}

.desktop-modal-body {
    padding: 22px 24px;
}

.desktop-modal-footer {
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

@keyframes modalScale {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}

/* === TOAST DESKTOP NOTIFICATION === */
.desktop-toast {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: white;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    z-index: 20000;
    animation: toastSlide 0.25s ease-out;
}

.toast-success { border-left: 5px solid #10b981; }
.toast-success .toast-icon { color: #10b981; font-size: 22px; }

.toast-error { border-left: 5px solid #ef4444; }
.toast-error .toast-icon { color: #ef4444; font-size: 22px; }

.toast-content strong { display: block; font-size: 13.5px; color: #0f172a; }
.toast-content p { margin: 2px 0 0 0; font-size: 12px; color: #64748b; }

@keyframes toastSlide {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
