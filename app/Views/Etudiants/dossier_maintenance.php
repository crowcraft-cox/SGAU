<div class="maintenance-container">
    <div class="maintenance-card">
        <div class="icon-wrapper">
            <i class="fa-solid fa-folder-open main-icon"></i>
        </div>
        
        <div class="status-badge">
            <i class="fa-solid fa-clock"></i> En cours de configuration
        </div>

        <h1 class="page-title-text">Module Dossiers Étudiant</h1>

        <p class="maintenance-message">
            Ce module est temporairement désactivé. Il sera configuré très prochainement pour permettre la gestion avancée des dossiers administratifs étudiants.
        </p>

        <div class="action-section">
            <a href="<?= base_url('/dashboard') ?>" class="btn-dashboard">
                <i class="fa-solid fa-house"></i> Retour au tableau de bord
            </a>
        </div>
    </div>
</div>

<style>
.maintenance-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 65vh;
    padding: 24px;
}

.maintenance-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 48px 40px;
    max-width: 560px;
    width: 100%;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.icon-wrapper {
    width: 64px;
    height: 64px;
    border-radius: 8px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
}

.main-icon {
    font-size: 28px;
    color: #001A72;
}

.page-title-text {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 12px;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    color: #475569;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin-bottom: 16px;
    border: 1px solid #e2e8f0;
}

.maintenance-message {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
    margin: 0 auto 32px;
}

.btn-dashboard {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #001A72;
    color: #ffffff !important;
    font-weight: 600;
    font-size: 14px;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none !important;
    transition: background 0.15s ease;
}

.btn-dashboard:hover {
    background: #00145a;
}
</style>
