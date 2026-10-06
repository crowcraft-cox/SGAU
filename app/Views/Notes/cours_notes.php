<div class="fiche-cotes-container">

    <!-- Fil d'ariane et Actions Haut -->
    <div class="fiche-top-bar">
        <div class="top-left">
            <a href="<?= base_url('/notes') ?>" class="btn-back-link">
                <i class="fas fa-arrow-left"></i> Retour aux cours
            </a>
        </div>
        <div class="top-right">
            <a href="<?= base_url('/cours/' . $cours['id'] . '/enrollement') ?>" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-user-plus"></i> Enrôler des étudiants (<?= count($etudiantsNotes) ?>)
            </a>
            <a href="<?= base_url('/notes/cours/' . $cours['id'] . '/print') ?>" target="_blank" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-print"></i> Imprimer la Fiche de Cotes (A4)
            </a>
            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('ficheCotesForm').submit()">
                <i class="fas fa-save"></i> Enregistrer toutes les notes
            </button>
        </div>
    </div>

    <!-- Messages Flash -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- En-tête officiel de la Fiche de Cotes (Conforme au document physique OPENLU) -->
    <div class="fiche-header-card">
        <div class="institution-header">
            <div class="univ-brand">
                <?php $logo = !empty($settings['university_logo']) ? $settings['university_logo'] : 'openlu v1.jpg'; ?>
                <img src="<?= base_url('/assets/images/' . $logo) ?>" alt="Logo" class="univ-logo-img">
                <div class="univ-titles">
                    <h4 class="country-title">RÉPUBLIQUE DÉMOCRATIQUE DU CONGO</h4>
                    <p class="ministry-title">ENSEIGNEMENT SUPÉRIEUR, UNIVERSITAIRE, RECHERCHE SCIENTIFIQUE ET INNOVATIONS</p>
                    <h2 class="univ-name"><?= htmlspecialchars($settings['university_name'] ?? 'OPEN LEARNING UNIVERSITY', ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="univ-sub">SECRÉTARIAT GÉNÉRAL ACADÉMIQUE</p>
                </div>
            </div>
            <div class="doc-badge">
                <h1>FICHE DE COTES</h1>
            </div>
        </div>

        <div class="fiche-meta-grid">
            <div class="meta-row">
                <div class="meta-field">
                    <span class="meta-label">Année Académique :</span>
                    <span class="meta-val"><?= htmlspecialchars($settings['academic_year'] ?? (date('Y') - 1 . '-' . date('Y')), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="meta-field">
                    <span class="meta-label">Auditoire / Promotion :</span>
                    <span class="meta-val"><?= htmlspecialchars($cours['orientation_nom'] ?? ($cours['filiere_nom'] ?? 'Tous auditoires'), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="meta-field">
                    <span class="meta-label">Option / Filière :</span>
                    <span class="meta-val"><?= htmlspecialchars($cours['filiere_nom'] ?? ($cours['domaine_nom'] ?? 'Générale'), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <div class="meta-row">
                <div class="meta-field">
                    <span class="meta-label">Intitulé du Cours :</span>
                    <span class="meta-val highlight-course"><?= htmlspecialchars($cours['nom'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($cours['code'], ENT_QUOTES, 'UTF-8') ?>)</span>
                </div>
                <div class="meta-field">
                    <span class="meta-label">Crédits / Volume Horaire :</span>
                    <span class="meta-val"><?= htmlspecialchars($cours['credit'] ?? '1', ENT_QUOTES, 'UTF-8') ?> Crédit(s) (<?= (int)($cours['credit'] ?? 1) * 25 ?> Heures)</span>
                </div>
                <div class="meta-field">
                    <span class="meta-label">Titulaire du cours :</span>
                    <span class="meta-val teacher-name"><?= htmlspecialchars(trim(($cours['enseignant_nom'] ?? '') . ' ' . ($cours['enseignant_prenom'] ?? 'Non assigné')), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Bannière d'information sur la règle de verrouillage -->
    <?php 
        $currentUser = auth();
        $userRole = $currentUser['role'] ?? '';
        $isTeacher = ($userRole === 'enseignant');
        $isAdmin = ($userRole === 'admin');
        $canRequest = $isTeacher || $isAdmin;
    ?>
    <?php if ($isTeacher): ?>
        <div class="rule-notice-bar">
            <i class="fas fa-shield-halved text-info"></i>
            <span>
                <strong>Règle Académique :</strong> Vous avez droit à <strong>une seule modification</strong> directe des cotes d'un étudiant. Au-delà, une demande motivée adressée au <strong>Secrétariat Général Académique (SGA)</strong> est requise pour débloquer la saisie.
            </span>
        </div>
    <?php endif; ?>

    <!-- Barre de Recherche et Filtre de Tableau -->
    <div class="table-toolbar">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="filterStudentInput" placeholder="Filtrer un étudiant par nom ou matricule..." autocomplete="off">
        </div>
        <div class="toolbar-stats" id="classStatsSummary">
            <span class="stat-pill"><i class="fas fa-users"></i> <strong><?= count($etudiantsNotes) ?></strong> étudiant(s) enrôlé(s)</span>
            <span class="stat-pill"><i class="fas fa-calculator"></i> Total max : <strong>200 Points</strong></span>
            <span class="stat-pill"><i class="fas fa-star text-warning"></i> Moyenne max : <strong>20 Points</strong></span>
        </div>
    </div>

    <!-- Formulaire Global de Saisie des Notes -->
    <form method="POST" action="<?= base_url('/notes/cours/' . $cours['id'] . '/bulk-save') ?>" id="ficheCotesForm">
        <?= csrf_field() ?>

        <?php if (empty($etudiantsNotes)): ?>
            <div class="empty-enrolled-card">
                <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                <h3>Aucun étudiant enrôlé dans ce cours</h3>
                <p class="text-muted">Pour pouvoir saisir les notes, vous devez d'abord enrôler les étudiants à ce cours.</p>
                <a href="<?= base_url('/cours/' . $cours['id'] . '/enrollement') ?>" class="btn btn-primary mt-2">
                    <i class="fas fa-user-plus"></i> Enrôler des étudiants maintenant
                </a>
            </div>
        <?php else: ?>
            <div class="fiche-table-wrapper">
                <table class="fiche-table" id="ficheCotesTable">
                    <thead>
                        <!-- Ligne 1 d'en-tête -->
                        <tr class="header-main-row">
                            <th rowspan="2" class="col-num text-center">N°</th>
                            <th rowspan="2" class="col-name">NOMS</th>
                            <th rowspan="2" class="col-sem text-center" width="70">Semestre</th>
                            <th colspan="3" class="col-tj text-center">TRAVAUX JOURNALIERS (100 Pts)</th>
                            <th rowspan="2" class="col-exam text-center">
                                MI-SESSION
                                <span class="pts-badge">50 Points</span>
                            </th>
                            <th rowspan="2" class="col-exam text-center">
                                EXAMEN FINAL
                                <span class="pts-badge">50 Points</span>
                            </th>
                            <th rowspan="2" class="col-total text-center">
                                TOTAL
                                <span class="pts-badge pts-total">200 Points</span>
                            </th>
                            <th rowspan="2" class="col-moyenne text-center">
                                MOYENNE
                                <span class="pts-badge pts-moy">20 Points</span>
                            </th>
                            <th rowspan="2" class="col-mention text-center" width="130">Statut & Actions</th>
                        </tr>
                        <!-- Ligne 2 d'en-tête (Sous-colonnes Travaux Journaliers) -->
                        <tr class="header-sub-row">
                            <th class="col-sub text-center">
                                Interro
                                <span class="sub-pts">30 Points</span>
                            </th>
                            <th class="col-sub text-center">
                                TP
                                <span class="sub-pts">40 Points</span>
                            </th>
                            <th class="col-sub text-center">
                                TD
                                <span class="sub-pts">30 Points</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $index = 1; ?>
                        <?php foreach ($etudiantsNotes as $row): ?>
                            <?php 
                                $etudiantId = $row['etudiant_id'];
                                $fullName = trim(($row['etudiant_nom'] ?? '') . ' ' . ($row['etudiant_prenom'] ?? ''));
                                $interroVal = $row['interro'] !== null ? floatval($row['interro']) : '';
                                $tpVal = $row['tp'] !== null ? floatval($row['tp']) : '';
                                $tdVal = $row['td'] !== null ? floatval($row['td']) : '';
                                $miSessionVal = $row['mi_session'] !== null ? floatval($row['mi_session']) : '';
                                $examenVal = $row['examen'] !== null ? floatval($row['examen']) : '';
                                $semestreVal = $row['semestre'] ?? 1;

                                $modCount = (int)($row['modifications_count'] ?? 0);
                                $hasDerogation = (int)($row['derogation_accordee'] ?? 0) === 1;
                                $hasPendingRequest = !empty($row['demande_en_attente_id']);

                                // La ligne est verrouillée pour l'enseignant si modCount >= 1 et pas de dérogation active
                                $isLocked = ($isTeacher && $modCount >= 1 && !$hasDerogation);
                                $readOnlyAttr = $isLocked ? 'readonly tabindex="-1"' : '';
                                $inputClass = $isLocked ? 'grade-input input-locked' : 'grade-input';
                            ?>
                            <tr class="fiche-row <?= $isLocked ? 'row-locked' : ($hasDerogation ? 'row-derogation-active' : '') ?>" 
                                data-name="<?= strtolower(htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8')) ?>" 
                                data-matricule="<?= strtolower(htmlspecialchars($row['etudiant_matricule'] ?? '', ENT_QUOTES, 'UTF-8')) ?>">
                                
                                <!-- N° -->
                                <td class="text-center row-index"><?= $index++ ?></td>

                                <!-- Noms & Matricule -->
                                <td class="cell-student">
                                    <div class="student-cell-content">
                                        <strong class="student-full-name"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span class="student-mat"><?= htmlspecialchars($row['etudiant_matricule'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </td>

                                <!-- Semestre -->
                                <td class="text-center">
                                    <select name="notes[<?= $etudiantId ?>][semestre]" class="form-control-cell input-sem" <?= $isLocked ? 'disabled' : '' ?>>
                                        <option value="1" <?= $semestreVal == 1 ? 'selected' : '' ?>>S1</option>
                                        <option value="2" <?= $semestreVal == 2 ? 'selected' : '' ?>>S2</option>
                                        <option value="3" <?= $semestreVal == 3 ? 'selected' : '' ?>>S3</option>
                                        <option value="4" <?= $semestreVal == 4 ? 'selected' : '' ?>>S4</option>
                                        <option value="5" <?= $semestreVal == 5 ? 'selected' : '' ?>>S5</option>
                                        <option value="6" <?= $semestreVal == 6 ? 'selected' : '' ?>>S6</option>
                                    </select>
                                </td>

                                <!-- Interro (30 Pts) -->
                                <td class="text-center">
                                    <input type="number" 
                                           name="notes[<?= $etudiantId ?>][interro]" 
                                           value="<?= $interroVal ?>" 
                                           min="0" max="30" step="0.25" 
                                           placeholder="0" 
                                           class="form-control-cell <?= $inputClass ?> input-interro" 
                                           data-max="30"
                                           autocomplete="off"
                                           <?= $readOnlyAttr ?>>
                                </td>

                                <!-- TP (40 Pts) -->
                                <td class="text-center">
                                    <input type="number" 
                                           name="notes[<?= $etudiantId ?>][tp]" 
                                           value="<?= $tpVal ?>" 
                                           min="0" max="40" step="0.25" 
                                           placeholder="0" 
                                           class="form-control-cell <?= $inputClass ?> input-tp" 
                                           data-max="40"
                                           autocomplete="off"
                                           <?= $readOnlyAttr ?>>
                                </td>

                                <!-- TD (30 Pts) -->
                                <td class="text-center">
                                    <input type="number" 
                                           name="notes[<?= $etudiantId ?>][td]" 
                                           value="<?= $tdVal ?>" 
                                           min="0" max="30" step="0.25" 
                                           placeholder="0" 
                                           class="form-control-cell <?= $inputClass ?> input-td" 
                                           data-max="30"
                                           autocomplete="off"
                                           <?= $readOnlyAttr ?>>
                                </td>

                                <!-- Mi-Session (50 Pts) -->
                                <td class="text-center">
                                    <input type="number" 
                                           name="notes[<?= $etudiantId ?>][mi_session]" 
                                           value="<?= $miSessionVal ?>" 
                                           min="0" max="50" step="0.25" 
                                           placeholder="0" 
                                           class="form-control-cell <?= $inputClass ?> input-misession" 
                                           data-max="50"
                                           autocomplete="off"
                                           <?= $readOnlyAttr ?>>
                                </td>

                                <!-- Examen Final (50 Pts) -->
                                <td class="text-center">
                                    <input type="number" 
                                           name="notes[<?= $etudiantId ?>][examen]" 
                                           value="<?= $examenVal ?>" 
                                           min="0" max="50" step="0.25" 
                                           placeholder="0" 
                                           class="form-control-cell <?= $inputClass ?> input-examen" 
                                           data-max="50"
                                           autocomplete="off"
                                           <?= $readOnlyAttr ?>>
                                </td>

                                <!-- Total (200 Pts) -->
                                <td class="text-center cell-total">
                                    <span class="total-display">-</span>
                                </td>

                                <!-- Moyenne (20 Pts) -->
                                <td class="text-center cell-moyenne">
                                    <span class="moyenne-display">-</span>
                                </td>

                                <!-- Statut & Actions de Dérogation -->
                                <td class="text-center cell-statut">
                                    <div class="statut-action-stack">
                                        <span class="badge-status badge-neutral">-</span>
                                        
                                        <?php if ($hasPendingRequest): ?>
                                            <span class="badge-derogation-pending" title="Demande de dérogation en cours d'examen par le SGA">
                                                <i class="fas fa-hourglass-half"></i> En attente SGA
                                            </span>
                                        <?php elseif ($hasDerogation): ?>
                                            <span class="badge-derogation-active" title="Dérogation accordée par le SGA : modification autorisée">
                                                <i class="fas fa-unlock"></i> Dérogation active
                                            </span>
                                        <?php elseif ($isLocked): ?>
                                            <button type="button" 
                                                    class="btn btn-warning-custom btn-xs mt-1 btn-demande-sga" 
                                                    data-cours-id="<?= (int)$cours['id'] ?>"
                                                    data-etudiant-id="<?= (int)$etudiantId ?>"
                                                    data-student-name="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-student-mat="<?= htmlspecialchars($row['etudiant_matricule'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-cours-name="<?= htmlspecialchars($cours['nom'] . ' (' . $cours['code'] . ')', ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick="openRequestModalFromBtn(this)"
                                                    title="Déposer une demande de dérogation au SGA pour modifier cette cote">
                                                <i class="fas fa-lock"></i> Demande SGA
                                            </button>
                                        <?php elseif ($canRequest && !empty($row['note_id'])): ?>
                                            <button type="button" 
                                                    class="btn btn-outline-warning btn-xs mt-1 btn-demande-sga" 
                                                    data-cours-id="<?= (int)$cours['id'] ?>"
                                                    data-etudiant-id="<?= (int)$etudiantId ?>"
                                                    data-student-name="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"
                                                    data-student-mat="<?= htmlspecialchars($row['etudiant_matricule'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                                    data-cours-name="<?= htmlspecialchars($cours['nom'] . ' (' . $cours['code'] . ')', ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick="openRequestModalFromBtn(this)"
                                                    title="Transmettre une demande de modification au SGA">
                                                <i class="fas fa-file-signature"></i> Demande SGA
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Barre d'Actions Inférieure & Signature -->
            <div class="fiche-footer-section">
                <div class="footer-stats-box">
                    <div class="stat-summary-item">
                        <span class="lbl">Moyenne Promotion :</span>
                        <strong class="val" id="promoAvgVal">-</strong>
                    </div>
                    <div class="stat-summary-item">
                        <span class="lbl">Taux de Réussite :</span>
                        <strong class="val text-success" id="successRateVal">-</strong>
                    </div>
                </div>

                <div class="footer-save-cta">
                    <a href="<?= base_url('/notes/cours/' . $cours['id'] . '/print') ?>" target="_blank" class="btn btn-outline-secondary">
                        <i class="fas fa-print"></i> Aperçu Impression
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Enregistrer toutes les notes
                    </button>
                </div>
            </div>

            <!-- Bloc Signature officiel en bas -->
            <div class="signature-notice">
                <p>Noms et signature du titulaire du cours : <strong><?= htmlspecialchars(trim(($cours['enseignant_nom'] ?? '') . ' ' . ($cours['enseignant_prenom'] ?? '...................................................')), ENT_QUOTES, 'UTF-8') ?></strong></p>
            </div>
        <?php endif; ?>
    </form>
</div>

<!-- MODAL DEMANDE DE DÉROGATION AU SGA -->
<div id="requestModal" class="custom-modal-backdrop" style="display: none;">
    <div class="custom-modal-card">
        <div class="modal-header-box">
            <h3><i class="fas fa-file-signature text-warning"></i> Demande de Modification de Cotes (SGA)</h3>
            <button type="button" class="btn-close-modal" onclick="closeRequestModal()">&times;</button>
        </div>
        <form method="POST" action="<?= base_url('/demandes-modification-notes/store') ?>" id="requestDerogationForm">
            <?= csrf_field() ?>
            <input type="hidden" name="cours_id" id="reqCoursId" value="<?= $cours['id'] ?>">
            <input type="hidden" name="etudiant_id" id="reqEtudiantId">

            <div class="modal-body-box">
                <div class="alert alert-info py-2" style="font-size: 13px;">
                    <i class="fas fa-info-circle"></i> Cette demande sera directement transmise au <strong>Secrétariat Général Académique (SGA)</strong> pour validation.
                </div>

                <div id="reqAlertBox" style="display: none; padding: 10px 14px; border-radius: 6px; font-size: 13px; margin-bottom: 12px;"></div>

                <div class="form-group mb-2">
                    <label class="font-weight-600">Étudiant concerné :</label>
                    <div id="reqStudentNameDisplay" class="p-2 bg-light rounded font-weight-bold text-dark"></div>
                </div>

                <div class="form-group mb-2">
                    <label class="font-weight-600">Cours :</label>
                    <div id="reqCourseNameDisplay" class="p-2 bg-light rounded text-dark"><?= htmlspecialchars($cours['nom'] . ' (' . $cours['code'] . ')', ENT_QUOTES, 'UTF-8') ?></div>
                </div>

                <div class="form-group">
                    <label for="reqMotif" class="font-weight-600">Motif explicatif de la modification <span class="text-danger">*</span> :</label>
                    <textarea name="motif" id="reqMotif" rows="4" required class="form-control" placeholder="Expliquez clairement la raison de cette modification (ex: erreur matérielle de saisie, rectification suite à réclamation justifiée de l'étudiant avec copie, omission de point de TP...)"></textarea>
                </div>
            </div>
            <div class="modal-footer-box">
                <button type="button" class="btn btn-secondary" onclick="closeRequestModal()">Annuler</button>
                <button type="submit" id="reqSubmitBtn" class="btn btn-warning font-weight-700">
                    <i class="fas fa-paper-plane"></i> Transmettre la demande au SGA
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.fiche-cotes-container {
    padding: 10px 0 50px 0;
}

/* Règle académique */
.rule-notice-bar {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 12px 18px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 13.5px;
    color: #1e3a8a;
}

/* Barre supérieure */
.fiche-top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.btn-back-link {
    color: #475569;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-back-link:hover { color: #0056b3; }

.top-right {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

/* En-tête officiel */
.fiche-header-card {
    background: #fafbfc;
    border-radius: 8px;
    border: 1px solid #cbd5e1;
    box-shadow: none;
    padding: 24px 30px;
    margin-bottom: 20px;
}

.institution-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #0f172a;
    padding-bottom: 16px;
    margin-bottom: 18px;
    flex-wrap: wrap;
    gap: 20px;
}

.univ-brand {
    display: flex;
    align-items: center;
    gap: 16px;
}

.univ-logo-img {
    height: 70px;
    width: auto;
    object-fit: contain;
}

.country-title {
    margin: 0;
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: 1px;
}

.ministry-title {
    margin: 2px 0 4px 0;
    font-size: 10px;
    font-weight: 600;
    color: #475569;
    letter-spacing: 0.5px;
}

.univ-name {
    margin: 0;
    font-size: 20px;
    font-weight: 900;
    color: #1e3a8a;
    letter-spacing: 0.5px;
}

.univ-sub {
    margin: 2px 0 0 0;
    font-size: 13px;
    font-weight: 700;
    color: #047857;
    letter-spacing: 0.8px;
}

.doc-badge h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 900;
    color: white;
    background: #0f172a;
    padding: 8px 24px;
    border-radius: 8px;
    letter-spacing: 1.5px;
}

.fiche-meta-grid {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.meta-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

.meta-field {
    display: flex;
    align-items: baseline;
    gap: 8px;
    font-size: 13px;
}

.meta-label {
    font-weight: 600;
    color: #64748b;
}

.meta-val {
    font-weight: 700;
    color: #0f172a;
    border-bottom: 1px dotted #94a3b8;
    flex: 1;
    padding-bottom: 1px;
}

.highlight-course {
    color: #1e40af;
    font-size: 14px;
}

.teacher-name {
    color: #047857;
}

/* Barre d'outils */
.table-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 12px 20px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 15px;
}

.search-wrap {
    position: relative;
    width: 320px;
}

.search-wrap input {
    width: 100%;
    padding: 8px 12px 8px 34px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
}

.search-wrap i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}

.toolbar-stats {
    display: flex;
    gap: 12px;
}

.stat-pill {
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    color: #334155;
    border: 1px solid #e2e8f0;
}

/* Table Fiche de Cotes */
.fiche-table-wrapper {
    background: white;
    border-radius: 8px;
    border: 1px solid #0f172a;
    box-shadow: none;
    overflow-x: auto;
    margin-bottom: 25px;
}

.fiche-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.fiche-table th, .fiche-table td {
    border: 1px solid #94a3b8;
    padding: 8px 10px;
    vertical-align: middle;
}

.fiche-table thead th {
    background: #f1f5f9;
    color: #0f172a;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 11.5px;
    letter-spacing: 0.3px;
}

.col-tj {
    background: #e0f2fe !important;
    color: #0369a1 !important;
}

.header-sub-row th {
    background: #f8fafc;
    font-size: 11px;
}

.pts-badge {
    display: block;
    font-size: 10px;
    font-weight: 600;
    color: #475569;
    text-transform: none;
    margin-top: 2px;
}

.pts-total { color: #1e40af; font-weight: 700; }
.pts-moy { color: #047857; font-weight: 700; }
.sub-pts { display: block; font-size: 9.5px; color: #64748b; font-weight: 500; }

.col-num { width: 45px; }
.col-name { min-width: 220px; }

.student-cell-content {
    display: flex;
    flex-direction: column;
}

.student-full-name {
    color: #0f172a;
    font-size: 13.5px;
}

.student-mat {
    font-size: 11px;
    color: #64748b;
    font-family: monospace;
}

.form-control-cell {
    width: 100%;
    max-width: 75px;
    text-align: center;
    padding: 6px 4px;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    font-weight: 600;
    font-size: 13px;
    background: #fdfdfd;
    transition: all 0.2s;
}

.form-control-cell:focus {
    outline: none;
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.input-sem {
    max-width: 60px;
    padding: 4px 2px;
}

/* Lignes et inputs verrouillés */
.input-locked {
    background-color: #f1f5f9 !important;
    color: #64748b !important;
    border-color: #e2e8f0 !important;
    cursor: not-allowed;
}

.row-locked {
    background-color: #fafbfc;
}

.row-derogation-active {
    background-color: #f0fdf4 !important;
}

.grade-input.input-invalid {
    border-color: #ef4444 !important;
    background-color: #fef2f2 !important;
    color: #b91c1c !important;
}

.cell-total {
    background: #f8fafc;
    font-weight: 800;
    font-size: 14px;
    color: #1e40af;
}

.cell-moyenne {
    background: #f8fafc;
    font-weight: 800;
    font-size: 14px;
}

.statut-action-stack {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.badge-status {
    font-weight: 700;
    font-size: 11px;
    display: inline-block;
}

.badge-valid   { color: #15803d; }
.badge-invalid { color: #b91c1c; }
.badge-neutral { color: #64748b; }

.badge-derogation-pending {
    color: #b45309;
    font-size: 10px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.badge-derogation-active {
    color: #15803d;
    font-size: 10px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 3px;
}

.btn-warning-custom {
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #fcd34d;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-warning-custom:hover {
    background: #f59e0b;
    color: white;
}

.fiche-row:hover {
    background-color: #f8fafc;
}

/* Pied de page et stats */
.fiche-footer-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 18px 24px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.footer-stats-box {
    display: flex;
    gap: 25px;
}

.stat-summary-item {
    font-size: 14px;
}

.stat-summary-item .lbl { color: #64748b; }
.stat-summary-item .val { font-size: 16px; margin-left: 6px; }

.footer-save-cta {
    display: flex;
    gap: 12px;
}

.signature-notice {
    text-align: right;
    padding: 15px 30px;
    color: #334155;
    font-size: 14px;
}

.empty-enrolled-card {
    background: white;
    padding: 60px 20px;
    border-radius: 12px;
    text-align: center;
    border: 1px dashed #cbd5e1;
}

/* Modale */
.custom-modal-backdrop {
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
    backdrop-filter: blur(2px);
}

.custom-modal-card {
    background: white;
    width: 90%;
    max-width: 520px;
    border-radius: 12px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    animation: modalIn 0.2s ease-out;
}

.modal-header-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 24px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.modal-header-box h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
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

.modal-body-box {
    padding: 20px 24px;
}

.modal-footer-box {
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.fiche-row');
    const filterInput = document.getElementById('filterStudentInput');

    function calculateRow(row) {
        const inputInterro = row.querySelector('.input-interro');
        const inputTp = row.querySelector('.input-tp');
        const inputTd = row.querySelector('.input-td');
        const inputMiSession = row.querySelector('.input-misession');
        const inputExamen = row.querySelector('.input-examen');

        const totalCell = row.querySelector('.total-display');
        const moyenneCell = row.querySelector('.moyenne-display');
        const statutBadge = row.querySelector('.cell-statut .badge-status');

        const vInterro = parseFloat(inputInterro.value);
        const vTp = parseFloat(inputTp.value);
        const vTd = parseFloat(inputTd.value);
        const vMiSession = parseFloat(inputMiSession.value);
        const vExamen = parseFloat(inputExamen.value);

        // Validation des maxima
        validateInput(inputInterro, 30);
        validateInput(inputTp, 40);
        validateInput(inputTd, 30);
        validateInput(inputMiSession, 50);
        validateInput(inputExamen, 50);

        const hasAny = (!isNaN(vInterro) || !isNaN(vTp) || !isNaN(vTd) || !isNaN(vMiSession) || !isNaN(vExamen));

        if (!hasAny) {
            totalCell.textContent = '-';
            moyenneCell.textContent = '-';
            moyenneCell.className = 'moyenne-display';
            if (statutBadge) statutBadge.className = 'badge-status badge-neutral';
            if (statutBadge) statutBadge.textContent = '-';
            return null;
        }

        const sum = (isNaN(vInterro) ? 0 : vInterro) +
                    (isNaN(vTp) ? 0 : vTp) +
                    (isNaN(vTd) ? 0 : vTd) +
                    (isNaN(vMiSession) ? 0 : vMiSession) +
                    (isNaN(vExamen) ? 0 : vExamen);

        const moyenne = sum / 10;

        totalCell.textContent = sum.toFixed(2) + ' / 200';
        moyenneCell.textContent = moyenne.toFixed(2) + ' / 20';

        if (moyenne >= 10) {
            moyenneCell.className = 'moyenne-display text-success font-weight-bold';
            if (statutBadge) {
                statutBadge.className = 'badge-status badge-valid';
                statutBadge.innerHTML = '<i class="fas fa-check"></i> Validé';
            }
        } else {
            moyenneCell.className = 'moyenne-display text-danger font-weight-bold';
            if (statutBadge) {
                statutBadge.className = 'badge-status badge-invalid';
                statutBadge.innerHTML = '<i class="fas fa-times"></i> Non Validé';
            }
        }

        return moyenne;
    }

    function validateInput(input, max) {
        const val = parseFloat(input.value);
        if (!isNaN(val) && (val < 0 || val > max)) {
            input.classList.add('input-invalid');
            input.title = 'La note maximale autorisée est ' + max;
        } else {
            input.classList.remove('input-invalid');
            input.title = '';
        }
    }

    function updateGlobalStats() {
        let totalMoyenne = 0;
        let countGrades = 0;
        let successCount = 0;

        rows.forEach(row => {
            const moy = calculateRow(row);
            if (moy !== null) {
                totalMoyenne += moy;
                countGrades++;
                if (moy >= 10) {
                    successCount++;
                }
            }
        });

        const promoAvgVal = document.getElementById('promoAvgVal');
        const successRateVal = document.getElementById('successRateVal');

        if (promoAvgVal && successRateVal) {
            if (countGrades > 0) {
                const avg = totalMoyenne / countGrades;
                const rate = (successCount / countGrades) * 100;
                promoAvgVal.textContent = avg.toFixed(2) + ' / 20';
                successRateVal.textContent = rate.toFixed(1) + '% (' + successCount + '/' + countGrades + ')';
            } else {
                promoAvgVal.textContent = '-';
                successRateVal.textContent = '-';
            }
        }
    }

    // Attacher les écouteurs sur tous les inputs non verrouillés
    const gradeInputs = document.querySelectorAll('.grade-input:not(.input-locked)');
    gradeInputs.forEach(input => {
        input.addEventListener('input', function() {
            calculateRow(this.closest('tr'));
            updateGlobalStats();
        });

        // Navigation au clavier (Flèches Haut/Bas, Entrée)
        input.addEventListener('keydown', function(e) {
            const currentCell = this.closest('td');
            const currentRow = this.closest('tr');
            const colIndex = Array.from(currentRow.children).indexOf(currentCell);

            if (e.key === 'Enter' || e.key === 'ArrowDown') {
                e.preventDefault();
                let nextRow = currentRow.nextElementSibling;
                while (nextRow && nextRow.classList.contains('row-locked')) {
                    nextRow = nextRow.nextElementSibling;
                }
                if (nextRow) {
                    const targetInput = nextRow.children[colIndex].querySelector('input');
                    if (targetInput && !targetInput.classList.contains('input-locked')) targetInput.focus();
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                let prevRow = currentRow.previousElementSibling;
                while (prevRow && prevRow.classList.contains('row-locked')) {
                    prevRow = prevRow.previousElementSibling;
                }
                if (prevRow) {
                    const targetInput = prevRow.children[colIndex].querySelector('input');
                    if (targetInput && !targetInput.classList.contains('input-locked')) targetInput.focus();
                }
            }
        });
    });

    // Initialisation
    updateGlobalStats();

    // Filtre de recherche
    if (filterInput) {
        filterInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const matricule = row.getAttribute('data-matricule') || '';
                if (query === '' || name.includes(query) || matricule.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});

function openRequestModalFromBtn(btn) {
    if (!btn) return;
    const coursId = btn.getAttribute('data-cours-id');
    const etudiantId = btn.getAttribute('data-etudiant-id');
    const studentName = btn.getAttribute('data-student-name') || '';
    const studentMat = btn.getAttribute('data-student-mat') || '';
    const coursName = btn.getAttribute('data-cours-name') || '';
    
    openRequestModal(coursId, etudiantId, studentName + (studentMat ? ' (' + studentMat + ')' : ''), coursName);
}

function openRequestModal(coursId, etudiantId, studentName, courseName) {
    const modal = document.getElementById('requestModal');
    if (!modal) return;
    
    const reqCoursId = document.getElementById('reqCoursId');
    const reqEtudiantId = document.getElementById('reqEtudiantId');
    const reqStudentNameDisplay = document.getElementById('reqStudentNameDisplay');
    const reqCourseNameDisplay = document.getElementById('reqCourseNameDisplay');
    const reqMotif = document.getElementById('reqMotif');
    const alertBox = document.getElementById('reqAlertBox');

    if (reqCoursId) reqCoursId.value = coursId || '';
    if (reqEtudiantId) reqEtudiantId.value = etudiantId || '';
    if (reqStudentNameDisplay) reqStudentNameDisplay.textContent = studentName || 'Étudiant';
    if (reqCourseNameDisplay && courseName) reqCourseNameDisplay.textContent = courseName;
    if (reqMotif) reqMotif.value = '';
    if (alertBox) {
        alertBox.style.display = 'none';
        alertBox.textContent = '';
    }

    modal.style.display = 'flex';
    setTimeout(function() {
        if (reqMotif) reqMotif.focus();
    }, 150);
}

function closeRequestModal() {
    const modal = document.getElementById('requestModal');
    if (modal) modal.style.display = 'none';
}

// Fermeture de la modale en cliquant à l'extérieur ou avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRequestModal();
});

document.addEventListener('click', function(e) {
    const modal = document.getElementById('requestModal');
    if (modal && e.target === modal) closeRequestModal();
});

// Soumission AJAX robuste du formulaire de demande SGA
document.addEventListener('DOMContentLoaded', function() {
    const derogationForm = document.getElementById('requestDerogationForm');
    if (!derogationForm) return;

    derogationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('reqSubmitBtn');
        const alertBox = document.getElementById('reqAlertBox');
        const motifInput = document.getElementById('reqMotif');
        const etudiantId = document.getElementById('reqEtudiantId').value;
        const coursId = document.getElementById('reqCoursId').value;

        if (!motifInput.value.trim()) {
            if (alertBox) {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = 'Veuillez rédiger le motif explicatif de la modification.';
                alertBox.style.display = 'block';
            }
            motifInput.focus();
            return;
        }

        const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Transmission en cours...';
        }
        if (alertBox) alertBox.style.display = 'none';

        const formData = new FormData(derogationForm);

        fetch(derogationForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            return response.json().then(data => ({
                status: response.status,
                ok: response.ok,
                body: data
            })).catch(() => ({
                status: response.status,
                ok: response.ok,
                body: null
            }));
        })
        .then(result => {
            if (result.ok && result.body && result.body.success) {
                if (alertBox) {
                    alertBox.className = 'alert alert-success';
                    alertBox.textContent = result.body.message || 'Votre demande a été transmise au SGA avec succès !';
                    alertBox.style.display = 'block';
                }
                if (submitBtn) {
                    submitBtn.innerHTML = '<i class="fas fa-check"></i> Transmise avec succès !';
                }
                setTimeout(function() {
                    window.location.reload();
                }, 900);
            } else {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
                const msg = (result.body && result.body.message) ? result.body.message : 'Une erreur est survenue lors de l\'envoi de la demande au SGA.';
                if (alertBox) {
                    alertBox.className = 'alert alert-danger';
                    alertBox.textContent = msg;
                    alertBox.style.display = 'block';
                } else {
                    alert(msg);
                }
            }
        })
        .catch(err => {
            console.warn('Erreur AJAX, repli vers envoi standard:', err);
            derogationForm.submit();
        });
    });
});
</script>
