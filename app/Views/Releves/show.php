<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

    :root {
        --openlu-blue: #2e3192;
        --openlu-green: #00a651;
        --openlu-dark: #1a1a1a;
        --border-radius: 8px;
    }

    body {
        background-color: #f4f7f6;
        font-family: 'Outfit', sans-serif;
        margin: 0;
        padding: 0;
        color: var(--openlu-dark);
    }

    .releve-container {
        width: 210mm;
        min-height: 297mm;
        margin: 20px auto;
        padding: 15mm;
        background: white;
        position: relative;
        box-sizing: border-box;
        border: 4px solid var(--openlu-blue);
        border-radius: var(--border-radius);
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    /* Watermark */
    .watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 140mm;
        opacity: 0.08;
        pointer-events: none;
        z-index: 0;
    }

    .content-wrapper {
        position: relative;
        z-index: 1;
    }

    /* Header Styling */
    /* Header Styling */
    .header-container {
        margin-bottom: 6px;
        width: 100%;
        text-align: center;
    }
    .header-top {
        position: relative;
        width: 100%;
        display: block;
        text-align: center;
        padding-bottom: 2px;
    }
    .header-logo {
        position: absolute;
        left: calc(50% - 275px);
        top: 50%;
        transform: translateY(-50%);
        width: 95px;
        text-align: left;
    }
    .header-logo img {
        width: 95px;
        height: 95px;
        object-fit: contain;
        display: block;
    }
    .header-text {
        width: 100%;
        text-align: center;
        line-height: 1.25;
        margin: 0 auto;
    }
    .header-text .republique {
        font-size: 13.5px;
        font-weight: 800;
        text-transform: uppercase;
        color: #000;
        letter-spacing: 0.04em;
        margin-bottom: 2px;
        text-align: center;
    }
    .header-text .enseignement {
        font-size: 10.5px;
        font-weight: 700;
        text-transform: uppercase;
        color: #000;
        line-height: 1.25;
        text-align: center;
    }
    .header-text .uni-name-main {
        font-size: 18px;
        font-weight: 900;
        text-transform: uppercase;
        color: #000;
        margin: 3px 0 1px;
        letter-spacing: 0.04em;
        font-family: 'Outfit', 'Inter', sans-serif;
        text-align: center;
    }
    .header-text .uni-sigle {
        font-size: 11px;
        font-weight: 700;
        color: #000;
        margin-bottom: 2px;
        text-align: center;
    }
    .header-text .arrete {
        font-size: 10px;
        color: #000;
        margin: 1px 0;
        text-align: center;
    }
    .header-text .contact-line {
        font-size: 10px;
        color: #000;
        margin: 1px 0;
        text-align: center;
    }
    .header-text .contact-line a {
        color: #0056b3;
        text-decoration: underline;
    }
    .header-text .bp-line {
        font-size: 10px;
        font-weight: 600;
        color: #000;
        margin-top: 2px;
        text-align: center;
    }
    .green-bar {
        height: 5px;
        background-color: #00a651;
        width: 100%;
        margin: 4px 0 6px 0;
        border-radius: 1px;
    }
    /* SECRÉTARIAT GÉNÉRAL ACADÉMIQUE */
    .sga-section {
        text-align: center;
        margin: 4px 0 6px 0;
        width: 100%;
    }
    .sga-section .sga-title {
        font-size: 15px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #000;
        text-align: center;
    }

    /* Document Box */
    .doc-number-wrapper {
        text-align: center;
        margin-bottom: 25px;
    }

    .doc-number-box {
        display: inline-block;
        border: 2px solid var(--openlu-dark);
        border-radius: 12px;
        padding: 8px 30px;
        font-weight: 800;
        font-size: 17px;
        background: white;
    }

    /* Student Info */
    .student-info {
        margin-bottom: 20px;
        font-size: 15px;
        line-height: 1.5;
    }

    .info-row {
        margin-bottom: 2px;
    }
    .info-label {
        font-weight: 400;
        display: inline-block;
        width: 100px;
    }

    .student-name-line {
        font-size: 16px;
        font-weight: 800;
        margin-top: 10px;
        text-transform: uppercase;
    }

    /* Table Styling */
    .transcript-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin-bottom: 0;
    }

    .transcript-table th, .transcript-table td {
        border: 1px solid #888;
        padding: 4px 6px;
    }

    .transcript-table th {
        background-color: white;
        font-weight: 800;
        text-align: left;
        text-transform: uppercase;
    }

    .semester-row {
        background-color: #f9f9f9;
        font-weight: 800;
        text-transform: uppercase;
    }

    .summary-row {
        font-weight: 700;
    }
    .summary-row td:first-child {
        text-align: center;
    }

    .green-separator {
        height: 12px;
        background-color: var(--openlu-green);
        border: 1px solid #888;
        border-top: none;
    }

    /* Final Results */
    .final-results-table {
        width: 60%;
        border-collapse: collapse;
        margin-top: 5px;
        font-size: 13px;
    }
    .final-results-table td {
        border: 1px solid #888;
        padding: 4px 8px;
    }
    .result-header {
        font-weight: 800;
        text-decoration: underline;
    }

    /* Footer */
    .footer-section {
        display: flex;
        justify-content: space-between;
        margin-top: 55px;
        font-size: 14px;
    }

    .signature-block {
        width: 45%;
    }

    .signature-area {
        height: 105px;
        margin-bottom: 12px;
        border-bottom: 1px dotted #888;
    }

    .contact-footer {
        position: absolute;
        bottom: 20px;
        right: 40px;
        text-align: right;
        font-size: 10px;
        color: #444;
        line-height: 1.3;
    }

    .action-bar {
        position: fixed;
        bottom: 30px;
        right: 30px;
        display: flex;
        gap: 12px;
        z-index: 1000;
    }

    .btn-action {
        padding: 10px 20px;
        border-radius: 6px;
        border: none;
        color: white;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: opacity 0.15s ease;
    }
    .btn-action:hover { opacity: 0.9; }
    .btn-blue { background: var(--openlu-blue); }
    .btn-green { background: var(--openlu-green); }
    .btn-orange { background: #b45309; }

    @media print {
        body { background: white; }
        .releve-container { 
            margin: 0; 
            box-shadow: none;
            border: 4px solid var(--openlu-blue);
        }
        .action-bar { display: none !important; }
    }
</style>

<div class="action-bar">
    <a href="<?= base_url('/releves') ?>" class="btn-action btn-blue">
        <i class="fas fa-arrow-left"></i> Retour
    </a>
    <button onclick="telechargerPDF(event)" class="btn-action btn-orange">
        <i class="fas fa-file-pdf"></i> PDF
    </button>
    <button onclick="window.print()" class="btn-action btn-green">
        <i class="fas fa-print"></i> Imprimer
    </button>
</div>

<div class="releve-container" id="releve-to-print">
    <img src="<?= base_url('/assets/images/openlu v1.jpg') ?>" class="watermark" alt="Watermark">

    <div class="content-wrapper">
        <!-- EN-TÊTE OFFICIEL -->
        <div class="header-container">
            <div class="header-top">
                <div class="header-logo">
                    <img src="<?= !empty($settings['university_logo']) ? base_url('/assets/images/' . $settings['university_logo']) : base_url('/assets/images/openlu v1.jpg') ?>" alt="Logo OPENLU">
                </div>
                <div class="header-text">
                    <div class="republique">REPUBLIQUE DEMOCRATIQUE DU CONGO</div>
                    <div class="enseignement">ENSEIGNEMENT SUPERIEUR, UNIVERSITAIRE,<br>RECHERCHE SCIENTIFIQUE ET INNOVATION</div>
                    <div class="uni-name-main">OPEN LEARNING UNIVERSITY</div>
                    <div class="uni-sigle">« OPENLU »</div>
                    <div class="arrete">Arrêté Ministériel N°554/MINESU/CAB.MIN/MNB/MM/2022</div>
                    <div class="contact-line">Site web: <a href="<?= htmlspecialchars($settings['university_website'] ?? 'http://www.openlu.org') ?>"><?= htmlspecialchars(preg_replace('#^https?://#', '', $settings['university_website'] ?? 'www.openlu.org')) ?></a> / E-mail: <a href="mailto:<?= htmlspecialchars($settings['university_email'] ?? 'info@openlu.org') ?>"><?= htmlspecialchars($settings['university_email'] ?? 'info@openlu.org') ?></a></div>
                    <div class="bp-line">B.P: <?= htmlspecialchars($settings['university_bp'] ?? '218 BENI') ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <?= htmlspecialchars(!empty($settings['university_phone']) ? $settings['university_phone'] : '+243 828 016 729, +66 959 100 486') ?></div>
                </div>
            </div>
            <div class="green-bar"></div>
            <!-- SECRÉTARIAT GÉNÉRAL ACADÉMIQUE -->
            <div class="sga-section">
                <span class="sga-title">SECRÉTARIAT GÉNÉRAL ACADÉMIQUE</span>
            </div>
        </div>

        <?php
        $sgaName = $settings['sga_name'] ?? '';
        $sgaInitials = '';
        if (!empty($sgaName)) {
            $cleanedName = preg_replace('/\b(dr|prof|ir|mr|mme|ms|phd|doc)\.?\b/i', '', $sgaName);
            $words = preg_split('/\s+/', trim($cleanedName));
            foreach ($words as $w) {
                if ($w !== '') {
                    $sgaInitials .= mb_strtoupper(mb_substr($w, 0, 1, 'UTF-8'), 'UTF-8');
                }
            }
        }
        $sgaPart = !empty($sgaInitials) ? $sgaInitials . '/' : '';
        $univName = $settings['university_name'] ?? 'SGAU-OPENLU';
        ?>
        <div class="doc-number-wrapper">
            <div class="doc-number-box">
                RELEVE DES COTES N°........../........../........../.........../..........
            </div>
        </div>

        <!-- Student Info -->
        <div class="student-info">
            <div class="student-name-line" style="margin-top: 0; margin-bottom: 4px;">
                <?= htmlspecialchars($etudiant['nom'] . ' ' . ($etudiant['postnom'] ?? '') . ' ' . $etudiant['prenom'], ENT_QUOTES, 'UTF-8') ?> 
                &nbsp;&nbsp;&nbsp;MATRICULE : <?= htmlspecialchars($etudiant['matricule'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>, 
                Né(e) à <?= htmlspecialchars($etudiant['lieu_naissance'] ?? 'BENI', ENT_QUOTES, 'UTF-8') ?> 
                le <?= !empty($etudiant['date_naissance']) ? date('d.m.Y', strtotime($etudiant['date_naissance'])) : '-' ?>
            </div>
            <div class="info-row"><span class="info-label">DOMAINE :</span> <?= htmlspecialchars($etudiant['domaine_nom'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
            <div class="info-row"><span class="info-label">FILIÈRE :</span> <?= htmlspecialchars($etudiant['filiere_nom'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></div>
            <div class="info-row"><span class="info-label">PROMOTION :</span> <?= htmlspecialchars(($etudiant['niveau'] ?? 'L1') . ' ' . ($etudiant['filiere_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="info-row"><span class="info-label" style="width: auto;">ANNÉE ACADÉMIQUE :</span> <?= htmlspecialchars($settings['academic_year'] ?? (date('Y') - 1 . '-' . date('Y')), ENT_QUOTES, 'UTF-8') ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <span class="info-label" style="width: auto;">SEMESTRE :</span> <?= htmlspecialchars($releve['semestre'] ?? '1', ENT_QUOTES, 'UTF-8') ?></div>
        </div>

        <!-- Transcript Table -->
        <table class="transcript-table">
            <thead>
                <tr>
                    <th width="10%">CODE</th>
                    <th width="38%">INTITULE</th>
                    <th width="6%">Cr</th>
                    <th width="14%">VOLUME HORAIRE</th>
                    <th width="10%">COTE/20</th>
                    <th width="10%">JURY</th>
                    <th width="12%">MENTION</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grandTotalCredits = 0;
                $grandTotalPoints = 0;
                $totalCours = 0;

                // Affichage par semestre
                foreach (['1', '2'] as $semNum): 
                    $semNotes = $allNotes[$semNum] ?? [];
                    $semCredits = 0;
                    $semPoints = 0;
                ?>
                <tr class="semester-row">
                    <td colspan="7">SEMESTRE <?= $semNum ?></td>
                </tr>

                <?php if (!empty($semNotes)): ?>
                    <?php foreach ($semNotes as $note): 
                        $cr = floatval($note['credit'] ?: 0);
                        $cote = floatval($note['note'] ?: 0);
                        $pondere = $cr * $cote;
                        $vh = (isset($note['volume_horaire']) && is_numeric($note['volume_horaire'])) ? $note['volume_horaire'] : ($cr > 0 ? ($cr * 25) : '-');
                        
                        $semCredits += $cr;
                        $semPoints += $pondere;
                        $grandTotalCredits += $cr;
                        $grandTotalPoints += $pondere;
                        $totalCours++;
                    ?>
                    <tr>
                        <td style="text-align: center;"><?= htmlspecialchars($note['cours_code']) ?></td>
                        <td><?= htmlspecialchars($note['cours_nom']) ?></td>
                        <td style="text-align: center;"><?= $cr ?></td>
                        <td style="text-align: center;"><?= $vh ?></td>
                        <td style="text-align: center;"><?= number_format($cote, 1) ?></td>
                        <td style="text-align: center; font-weight: bold;"><?= $cote >= 10 ? 'Validé' : 'Non Validé' ?></td>
                        <td style="text-align: center;"><?= getMentionLettreOPENLU($cote) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <!-- Lignes vides si nécessaire pour le look -->
                    <?php for($i=count($semNotes); $i<8; $i++): ?>
                    <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                    <?php endfor; ?>

                    <tr class="summary-row">
                        <td>&nbsp;</td>
                        <td>Moyenne</td>
                        <td colspan="5" style="text-align: center;"><?= $semCredits > 0 ? number_format($semPoints / $semCredits, 2) : '0.00' ?> / 20</td>
                    </tr>
                    <tr class="summary-row">
                        <td>&nbsp;</td>
                        <td>Total Points Obtenus</td>
                        <td colspan="5" style="text-align: center;"><?= number_format($semPoints, 1) ?></td>
                    </tr>
                    <tr class="summary-row">
                        <td>&nbsp;</td>
                        <td>Crédits Capitalisés</td>
                        <td colspan="5" style="text-align: center;"><?= $semCredits ?></td>
                    </tr>
                <?php else: ?>
                    <!-- Si pas de notes pour ce semestre -->
                    <?php for($i=0; $i<8; $i++): ?>
                    <tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>
                    <?php endfor; ?>
                <?php endif; ?>

                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="green-separator"></div>

        <!-- Final Summary -->
        <table class="final-results-table">
            <tr>
                <td class="result-header" colspan="2">RESULTAT ANNUEL</td>
            </tr>
            <tr>
                <td width="50%">Moyenne Annuelle</td>
                <td style="font-weight: 800;"><?= $grandTotalCredits > 0 ? number_format($grandTotalPoints / $grandTotalCredits, 2) : '0.00' ?> / 20</td>
            </tr>
            <tr>
                <td>Total Points Obtenus</td>
                <td style="font-weight: 800;"><?= number_format($grandTotalPoints, 1) ?></td>
            </tr>
            <tr>
                <td>Pourcentages</td>
                <td style="font-weight: 800;"><?= $grandTotalCredits > 0 ? number_format(($grandTotalPoints / ($grandTotalCredits * 20)) * 100, 1) : '0.0' ?> %</td>
            </tr>
            <tr>
                <td>Nombre des Crédits Capitalisés S1+S2</td>
                <td style="font-weight: 800;"><?= $grandTotalCredits ?></td>
            </tr>
            <tr>
                <td>Décision du jury</td>
                <td style="font-weight: 800;"><?= ($grandTotalCredits > 0 && ($grandTotalPoints / $grandTotalCredits) >= 10) ? 'Réussi' : 'Ajourné' ?></td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer-section" style="justify-content: flex-end;">
            <div class="signature-block" style="text-align: right; width: 250px;">
                <div style="font-weight: 400;">Fait à <?= htmlspecialchars($settings['university_city'] ?? 'Beni') ?>, le <?= date('d/m/20') ?>...</div>
                <div style="font-weight: 800; margin-top: 5px;">Le Secrétaire Général Académique</div>
                <div class="signature-area" style="height: 75px;"></div>
                <div style="font-size: 11px; border-top: 1px solid #444; width: 100%; padding-top: 2px;">....................................................................</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    function telechargerPDF(event) {
        const element = document.getElementById('releve-to-print');
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>...';
        btn.disabled = true;

        const opt = {
            margin: 0,
            filename: 'Releve_<?= $etudiant['nom'] ?>_<?= $etudiant['matricule'] ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, scrollY: 0, scrollX: 0 },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            btn.innerHTML = originalContent;
            btn.disabled = false;
        });
    }
</script>

<?php
function getMentionLettreOPENLU($note) {
    if ($note >= 18) return 'A';
    if ($note >= 16) return 'B';
    if ($note >= 14) return 'C';
    if ($note >= 12) return 'D';
    if ($note >= 10) return 'E';
    if ($note >= 8)  return 'F';
    return 'G';
}
?>
