<?php
/**
 * Vue : Module Finance - En développement
 * Affiche une page temporaire indiquant que le module sera disponible prochainement
 */
?>

<style>
.finance-coming-soon {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 65vh;
    padding: 24px;
}

.coming-soon-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 48px 40px;
    max-width: 600px;
    width: 100%;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.cs-icon-wrapper {
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

.cs-icon-wrapper i {
    font-size: 28px;
    color: #001A72;
}

.cs-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 4px;
    margin-bottom: 16px;
}

.cs-badge .badge-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #0284c7;
}

.cs-title {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 12px;
}

.cs-subtitle {
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 32px;
}

.cs-features {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 32px;
    text-align: left;
}

.cs-feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 12px 16px;
}

.cs-feature-item i {
    font-size: 16px;
    color: #001A72;
    width: 20px;
    text-align: center;
    flex-shrink: 0;
}

.cs-feature-item .feat-label {
    font-size: 13px;
    color: #334155;
    font-weight: 500;
}

.cs-back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #001A72;
    color: #ffffff !important;
    font-size: 14px;
    font-weight: 600;
    padding: 10px 20px;
    border-radius: 6px;
    text-decoration: none !important;
    transition: background 0.15s ease;
}

.cs-back-btn:hover {
    background: #00145a;
}
</style>

<div class="finance-coming-soon">
    <div class="coming-soon-card">

        <div class="cs-icon-wrapper">
            <i class="fa-solid fa-wallet"></i>
        </div>

        <div class="cs-badge">
            <span class="badge-dot"></span>
            En cours de développement
        </div>

        <h1 class="cs-title">Module Finance & Recettes</h1>

        <p class="cs-subtitle">
            Ce module est actuellement en cours de développement.
            Il permettra la gestion centralisée des recettes et des opérations financières de l'université.
        </p>

        <div class="cs-features">
            <div class="cs-feature-item">
                <i class="fa-solid fa-credit-card"></i>
                <span class="feat-label">Gestion des paiements</span>
            </div>
            <div class="cs-feature-item">
                <i class="fa-solid fa-chart-simple"></i>
                <span class="feat-label">Tableaux de bord financiers</span>
            </div>
            <div class="cs-feature-item">
                <i class="fa-solid fa-file-lines"></i>
                <span class="feat-label">Journal des opérations</span>
            </div>
            <div class="cs-feature-item">
                <i class="fa-solid fa-download"></i>
                <span class="feat-label">Rapports et exportations</span>
            </div>
        </div>

        <a href="<?= base_url('/dashboard') ?>" class="cs-back-btn">
            <i class="fa-solid fa-arrow-left"></i>
            Retour au tableau de bord
        </a>

    </div>
</div>