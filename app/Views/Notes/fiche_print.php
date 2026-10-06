<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche de Cotes - <?= htmlspecialchars($cours['nom'] ?? 'Cours', ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 10mm 10mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            color: #000;
        }

        .no-print-bar {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 9999;
            display: flex;
            gap: 10px;
            background: white;
            padding: 8px 12px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }

        .btn-print-action {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-print-now {
            background: #1e40af;
            color: white;
        }

        .btn-close-view {
            background: #e2e8f0;
            color: #334155;
        }

        .print-page {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm;
            margin: 15px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }

        /* En-tête officiel */
        .official-header {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 6px;
        }

        .header-logo {
            width: 70px;
            text-align: center;
        }

        .header-logo img {
            width: 65px;
            height: auto;
        }

        .header-text {
            flex: 1;
            text-align: center;
            line-height: 1.15;
        }

        .rep-title {
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .min-title {
            font-size: 7pt;
            font-weight: bold;
            margin: 1px 0;
        }

        .esursi-title {
            font-size: 6pt;
            font-weight: bold;
            margin: 0;
        }

        .univ-title {
            font-size: 12pt;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin: 1px 0;
        }

        .openlu-sub {
            font-size: 7.5pt;
            font-weight: bold;
            margin: 0;
        }

        .legal-text {
            font-size: 7pt;
            margin: 1px 0;
        }

        .contact-text {
            font-size: 7pt;
            margin: 1px 0;
        }

        .sga-banner {
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            text-align: center;
            font-size: 11pt;
            font-weight: 900;
            letter-spacing: 1px;
            padding: 2px 0;
            margin: 4px 0;
        }

        .doc-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: 4px 0 8px 0;
        }

        /* Métadonnées avec pointillés */
        .meta-lines {
            font-size: 8.5pt;
            line-height: 1.5;
            margin-bottom: 8px;
        }

        .meta-line {
            display: flex;
            justify-content: space-between;
        }

        .meta-item {
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .dots {
            border-bottom: 1px dotted #000;
            font-weight: bold;
            padding: 0 4px;
        }

        /* Tableau des cotes */
        .fiche-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }

        .fiche-table th, .fiche-table td {
            border: 1px solid #000;
            padding: 2px 3px;
            height: 17px;
        }

        .fiche-table thead th {
            text-align: center;
            font-weight: bold;
            background-color: #f8fafc;
        }

        .col-n { width: 22px; text-align: center; }
        .col-noms { width: 175px; text-align: left; }
        .col-tj-sub { width: 44px; text-align: center; }
        .col-mid { width: 50px; text-align: center; }
        .col-ex { width: 50px; text-align: center; }
        .col-tot { width: 48px; text-align: center; }
        .col-moy { width: 44px; text-align: center; }

        .sub-header-pts {
            display: block;
            font-size: 6.5pt;
            font-weight: normal;
        }

        /* Signature bas */
        .official-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
        }

        @media print {
            .no-print-bar { display: none !important; }
            body { background: white; }
            .print-page {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                min-height: auto;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button onclick="window.print()" class="btn-print-action btn-print-now">
            <i class="fas fa-print"></i> Imprimer
        </button>
        <button onclick="window.close()" class="btn-print-action btn-close-view">
            <i class="fas fa-times"></i> Fermer
        </button>
    </div>

    <div class="print-page">
        <!-- En-tête conforme -->
        <div class="official-header">
            <div class="header-logo">
                <?php $logo = !empty($settings['university_logo']) ? $settings['university_logo'] : 'openlu v1.jpg'; ?>
                <img src="<?= base_url('/assets/images/' . $logo) ?>" alt="Logo">
            </div>
            <div class="header-text">
                <div class="rep-title">RÉPUBLIQUE DÉMOCRATIQUE DU CONGO</div>
                <div class="min-title">ENSEIGNEMENT SUPÉRIEUR, UNIVERSITAIRE, RECHERCHE SCIENTIFIQUE ET INNOVATIONS</div>
                <div class="esursi-title">•ESURSI•</div>
                <div class="univ-title"><?= htmlspecialchars($settings['university_name'] ?? 'OPEN LEARNING UNIVERSITY', ENT_QUOTES, 'UTF-8') ?></div>
                <div class="openlu-sub">•OPENLU•</div>
                <div class="legal-text">Arrêté Ministériel N°554/MINESU/CAB.MIN/MNB/MM/2022</div>
                <div class="contact-text">Site Web: <?= htmlspecialchars($settings['university_website'] ?? 'www.openlu.org') ?> / E-mail: <?= htmlspecialchars($settings['university_email'] ?? 'info@openlu.org') ?></div>
                <div class="contact-text">B.P. 218 Beni &nbsp;&nbsp;&nbsp; <?= htmlspecialchars($settings['university_phone'] ?? '+243 828 016 729, +66 959 100 486') ?></div>
            </div>
        </div>

        <div class="sga-banner">SECRÉTARIAT GÉNÉRAL ACADÉMIQUE</div>
        <div class="doc-title">FICHE DE COTES</div>

        <!-- Métadonnées avec pointillés -->
        <div class="meta-lines">
            <div class="meta-line">
                <div class="meta-item" style="flex: 1;">
                    <span>Année Académique :</span>
                    <span class="dots" style="flex: 1;"><?= htmlspecialchars($settings['academic_year'] ?? (date('Y') - 1 . '-' . date('Y')), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <div class="meta-line">
                <div class="meta-item" style="flex: 1.2;">
                    <span>Auditoire :</span>
                    <span class="dots" style="flex: 1;"><?= htmlspecialchars($cours['orientation_nom'] ?? ($cours['filiere_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="meta-item" style="flex: 1; margin-left: 10px;">
                    <span>Option :</span>
                    <span class="dots" style="flex: 1;"><?= htmlspecialchars($cours['filiere_nom'] ?? ($cours['domaine_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <div class="meta-line">
                <div class="meta-item" style="flex: 2;">
                    <span>Intitulé du Cours :</span>
                    <span class="dots" style="flex: 1;"><?= htmlspecialchars($cours['nom'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($cours['code'], ENT_QUOTES, 'UTF-8') ?>)</span>
                </div>
                <div class="meta-item" style="flex: 0.6; margin-left: 10px;">
                    <span>Heures :</span>
                    <span class="dots" style="flex: 1;"><?= (int)($cours['credit'] ?? 1) * 25 ?> H (<?= htmlspecialchars($cours['credit'] ?? '1', ENT_QUOTES, 'UTF-8') ?> Cr)</span>
                </div>
            </div>
            <div class="meta-line">
                <div class="meta-item" style="flex: 1;">
                    <span>Titulaire du cours :</span>
                    <span class="dots" style="flex: 1;"><?= htmlspecialchars(trim(($cours['enseignant_nom'] ?? '') . ' ' . ($cours['enseignant_prenom'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>

        <!-- Tableau des Cotes conforme à l'image -->
        <table class="fiche-table">
            <thead>
                <tr>
                    <th rowspan="2" class="col-n">N°</th>
                    <th rowspan="2" class="col-noms">NOMS</th>
                    <th colspan="3">TRAVAUX JOURNALIERS</th>
                    <th rowspan="2" class="col-mid">
                        MI-SESSION
                        <span class="sub-header-pts">50 Points</span>
                    </th>
                    <th rowspan="2" class="col-ex">
                        EXAMEN FINAL
                        <span class="sub-header-pts">50 Points</span>
                    </th>
                    <th rowspan="2" class="col-tot">
                        TOTAL
                        <span class="sub-header-pts">200 Points</span>
                    </th>
                    <th rowspan="2" class="col-moy">
                        MOYENNE
                        <span class="sub-header-pts">20 Points</span>
                    </th>
                </tr>
                <tr>
                    <th class="col-tj-sub">
                        Interro
                        <span class="sub-header-pts">30 Points</span>
                    </th>
                    <th class="col-tj-sub">
                        TP
                        <span class="sub-header-pts">40 Points</span>
                    </th>
                    <th class="col-tj-sub">
                        TD
                        <span class="sub-header-pts">30 Points</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $totalRows = max(35, count($etudiantsNotes));
                    for ($i = 0; $i < $totalRows; $i++): 
                        $student = $etudiantsNotes[$i] ?? null;
                        if ($student) {
                            $fullName = trim(($student['etudiant_nom'] ?? '') . ' ' . ($student['etudiant_prenom'] ?? ''));
                            $interro = $student['interro'] !== null ? floatval($student['interro']) : null;
                            $tp = $student['tp'] !== null ? floatval($student['tp']) : null;
                            $td = $student['td'] !== null ? floatval($student['td']) : null;
                            $miSession = $student['mi_session'] !== null ? floatval($student['mi_session']) : null;
                            $examen = $student['examen'] !== null ? floatval($student['examen']) : null;

                            $sum = ($interro ?? 0) + ($tp ?? 0) + ($td ?? 0) + ($miSession ?? 0) + ($examen ?? 0);
                            $hasNotes = ($interro !== null || $tp !== null || $td !== null || $miSession !== null || $examen !== null);
                            $total = $hasNotes ? number_format($sum, 1) : '';
                            $moyenne = $hasNotes ? number_format($sum / 10, 1) : '';
                        } else {
                            $fullName = '';
                            $interro = null;
                            $tp = null;
                            $td = null;
                            $miSession = null;
                            $examen = null;
                            $total = '';
                            $moyenne = '';
                        }
                ?>
                    <tr>
                        <td class="col-n"><?= $i + 1 ?></td>
                        <td class="col-noms"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="col-tj-sub"><?= $interro !== null ? number_format($interro, 1) : '' ?></td>
                        <td class="col-tj-sub"><?= $tp !== null ? number_format($tp, 1) : '' ?></td>
                        <td class="col-tj-sub"><?= $td !== null ? number_format($td, 1) : '' ?></td>
                        <td class="col-mid"><?= $miSession !== null ? number_format($miSession, 1) : '' ?></td>
                        <td class="col-ex"><?= $examen !== null ? number_format($examen, 1) : '' ?></td>
                        <td class="col-tot" style="font-weight: bold;"><?= $total ?></td>
                        <td class="col-moy" style="font-weight: bold;"><?= $moyenne ?></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <!-- Signature en bas comme sur le document -->
        <div class="official-footer">
            Noms et signature du titulaire
        </div>
    </div>

</body>
</html>
