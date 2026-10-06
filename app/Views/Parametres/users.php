<div class="page-header">
    <div class="header-content">
        <h1>Gestion des Utilisateurs</h1>
        <p class="subtitle">Créez et gérez les comptes d'accès au système</p>
    </div>
    <button class="btn btn-primary btn-add" onclick="openAddModal()">
        <i class="fa-solid fa-user-plus"></i> Ajouter un utilisateur
    </button>
</div>

<div class="users-container">
    <div class="users-table-wrapper">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Créé le</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $u): ?>
                        <tr class="user-row" id="user-row-<?= $u['id'] ?>">
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                                    <span class="user-name"><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower($u['role']) ?>">
                                    <?= htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= isset($u['created_at']) ? date('d/m/Y', strtotime($u['created_at'])) : '-' ?></td>
                            <td class="text-right">
                                <div class="action-group">
                                    <button class="btn-icon btn-edit" title="Modifier" onclick='editUser(<?= json_encode($u) ?>)'>
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <?php if ($u['id'] != $_SESSION['user']['id']): ?>
                                        <button class="btn-icon btn-delete" title="Supprimer" onclick="deleteUser(<?= $u['id'] ?>, '<?= addslashes($u['name']) ?>')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Aucun utilisateur trouvé</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal pour Ajouter/Modifier un utilisateur -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Ajouter un utilisateur</h2>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="userForm">
                <?= csrf_field() ?>
                <input type="hidden" id="userId" name="id">
                
                <div class="form-group">
                    <label for="userName">Nom complet:</label>
                    <input type="text" id="userName" name="name" required placeholder="Ex: Jean Dupont">
                </div>

                <div class="form-group">
                    <label for="userEmail">Adresse Email:</label>
                    <input type="email" id="userEmail" name="email" required placeholder="jean.dupont@exemple.com">
                </div>

                <div class="form-group">
                    <label for="userPassword">Mot de passe:</label>
                    <input type="password" id="userPassword" name="password" placeholder="Laissez vide pour ne pas changer">
                    <small id="passwordHelp" class="form-text text-muted">Requis pour les nouveaux comptes.</small>
                </div>

                <div class="form-group">
                    <label for="userRole">Rôle système:</label>
                    <select id="userRole" name="role" required>
                        <option value="admin">Administrateur</option>
                        <option value="enseignant">Enseignant</option>
                        <option value="etudiant">Étudiant</option>
                        <option value="finance">Service Financier</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="btnSubmit">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-header h1 {
    margin: 0;
    font-size: 1.8rem;
    color: #0f172a;
}

.subtitle {
    margin: 0.2rem 0 0 0;
    color: #64748b;
}

.users-container {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table th {
    padding: 1rem 1.5rem;
    text-align: left;
    background: #f8fafc;
    color: #475569;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    border-bottom: 1px solid #e2e8f0;
}

.users-table td {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.user-avatar {
    width: 32px;
    height: 32px;
    background: #1e40af;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 0.85rem;
}

.badge {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: capitalize;
}

.badge-admin      { color: #b91c1c; }
.badge-enseignant { color: #0369a1; }
.badge-etudiant   { color: #15803d; }
.badge-finance    { color: #b45309; }

.action-group {
    display: flex;
    gap: 0.5rem;
    justify-content: flex-end;
}

.btn-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e2e8f0;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    color: #64748b;
}

.btn-edit:hover { color: #1e40af; border-color: #1e40af; background: #eff6ff; }
.btn-delete:hover { color: #ef4444; border-color: #ef4444; background: #fef2f2; }

.modal {
    display: none;
    position: fixed;
    z-index: 2000;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 450px;
    margin: 5rem auto;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 { margin: 0; font-size: 1.25rem; }

.modal-body { padding: 1.5rem; }

.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; margin-bottom: 0.4rem; font-weight: 500; color: #1e293b; }
.form-group input, .form-group select {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 0.95rem;
}

.modal-footer {
    margin-top: 1.5rem;
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
}

.btn {
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s;
}

.btn-primary { background: #1e40af; color: white; }
.btn-primary:hover { background: #1e3a8a; }
.btn-secondary { background: #f1f5f9; color: #475569; }
.btn-secondary:hover { background: #e2e8f0; }

.text-right { text-align: right; }
.text-muted { color: #64748b; font-size: 0.8rem; }
</style>

<script>
let currentUserId = null;

function openAddModal() {
    currentUserId = null;
    document.getElementById('modalTitle').textContent = "Ajouter un utilisateur";
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = "";
    document.getElementById('userPassword').required = true;
    document.getElementById('passwordHelp').textContent = "Mot de passe requis pour le nouveau compte.";
    document.getElementById('userModal').style.display = 'block';
}

function editUser(user) {
    currentUserId = user.id;
    document.getElementById('modalTitle').textContent = "Modifier l'utilisateur";
    document.getElementById('userId').value = user.id;
    document.getElementById('userName').value = user.name;
    document.getElementById('userEmail').value = user.email;
    document.getElementById('userRole').value = user.role;
    document.getElementById('userPassword').value = "";
    document.getElementById('userPassword').required = false;
    document.getElementById('passwordHelp').textContent = "Laissez vide pour conserver le mot de passe actuel.";
    document.getElementById('userModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('userModal').style.display = 'none';
}

document.getElementById('userForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true;
    btn.textContent = "Traitement...";

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    const url = currentUserId ? '<?= base_url('/parametres/users/update') ?>' : '<?= base_url('/parametres/users/store') ?>';
    
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        if (response.ok) {
            location.reload();
        } else {
            alert(result.error || "Une erreur est survenue");
        }
    } catch (err) {
        alert("Erreur de connexion au serveur");
    } finally {
        btn.disabled = false;
        btn.textContent = "Enregistrer";
    }
});

async function deleteUser(id, name) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur "${name}" ?`)) return;
    
    try {
        const response = await fetch('<?= base_url('/parametres/users/delete/') ?>' + id, { 
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
            }
        });
        const result = await response.json();
        
        if (response.ok) {
            location.reload();
        } else {
            alert(result.error || "Impossible de supprimer l'utilisateur");
        }
    } catch (err) {
        alert("Erreur de connexion");
    }
}

window.onclick = (e) => {
    if (e.target.classList.contains('modal')) closeModal();
}
</script>
