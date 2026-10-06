<?php include APP_ROOT . '/app/Views/Layouts/header.php'; ?>
<?php include APP_ROOT . '/app/Views/Layouts/navbar.php'; ?>

<!-- JSpreadsheet / JExcel (Vanilla JS) -->
<script src="https://bossanova.uk/jspreadsheet/v4/jexcel.js"></script>
<script src="https://jsuites.net/v4/jsuites.js"></script>
<link rel="stylesheet" href="https://jsuites.net/v4/jsuites.css" type="text/css" />
<link rel="stylesheet" href="https://bossanova.uk/jspreadsheet/v4/jexcel.css" type="text/css" />

<style>
    .main-content { padding: 20px; }
    #spreadsheet-container {
        width: 100%;
        overflow-x: auto;
        background: #fff;
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 10px;
    }
    .status-bar {
        background: #f8f9fa;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-top: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: monospace;
        font-size: 14px;
    }
    .save-indicator {
        font-size: 12px;
        color: #6c757d;
    }
    .status-item {
        margin-right: 20px;
        font-weight: bold;
    }
    .text-danger { color: #dc3545 !important; }
    .text-success { color: #198754 !important; }
</style>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice-dollar text-primary"></i> Journal des Écritures</h2>
        <div>
            <select id="exercice_select" class="form-select d-inline-block w-auto me-2" onchange="loadGridData()">
                <?php foreach($exercices as $ex): ?>
                    <option value="<?= $ex['id'] ?>"><?= htmlspecialchars($ex['nom']) ?> (<?= $ex['statut'] ?>)</option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" onclick="saveGridData()">
                <i class="fas fa-save"></i> Sauvegarder
            </button>
        </div>
    </div>

    <div id="spreadsheet-container">
        <div id="spreadsheet"></div>
        <div class="status-bar">
            <div>
                <span class="status-item">Total Débit : <span id="total_debit">0.00</span></span>
                <span class="status-item">Total Crédit : <span id="total_credit">0.00</span></span>
                <span class="status-item">Différence : <span id="total_diff" class="text-success">0.00</span></span>
            </div>
            <div class="save-indicator" id="save_status">
                <i class="fas fa-check-circle text-success"></i> Synchronisé
            </div>
        </div>
    </div>
</div>

<script>
    let myTable = null;
    let accountOptions = [];
    let saveTimeout = null;

    // Load accounts for dropdowns
    fetch('/api/accounting/comptes')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                accountOptions = response.data;
                initGrid();
            } else {
                alert("Erreur chargement des comptes");
            }
        });

    function initGrid() {
        const exerciceId = document.getElementById('exercice_select').value;
        
        fetch(`/api/accounting/transactions?exercice_id=${exerciceId}`)
            .then(res => res.json())
            .then(response => {
                if(response.success) {
                    renderGrid(response.data);
                }
            });
    }

    function renderGrid(data) {
        if(myTable) {
            myTable.destroy();
        }

        myTable = jspreadsheet(document.getElementById('spreadsheet'), {
            data: data,
            columns: [
                { type: 'hidden', title: 'ID' },
                { type: 'calendar', title: 'Date', width: 100, options: { format: 'YYYY-MM-DD' } },
                { type: 'text', title: 'Pièce/Doc', width: 100 },
                { type: 'text', title: 'Description', width: 300 },
                { type: 'dropdown', title: 'Compte Débit', width: 250, source: accountOptions, autocomplete: true },
                { type: 'dropdown', title: 'Compte Crédit', width: 250, source: accountOptions, autocomplete: true },
                { type: 'numeric', title: 'Montant', width: 120, mask: '#,##0.00', decimal: '.' },
                { type: 'dropdown', title: 'Devise', width: 80, source: ['FC', 'USD'] },
                { type: 'numeric', title: 'Taux', width: 80 }
            ],
            colHeaders: ['ID', 'Date', 'Pièce/Doc', 'Description', 'Compte Débit', 'Compte Crédit', 'Montant', 'Devise', 'Taux'],
            minDimensions: [9, 10],
            defaultColWidth: 100,
            tableOverflow: true,
            tableHeight: '60vh',
            search: true,
            pagination: 50,
            onchange: function(instance, cell, x, y, value) {
                updateTotals();
                autoSaveTrigger();
            },
            ondeleterow: function() { updateTotals(); autoSaveTrigger(); },
            oninsertrow: function() { updateTotals(); }
        });
        updateTotals();
    }

    function updateTotals() {
        if(!myTable) return;
        let data = myTable.getData();
        let sumDebit = 0;
        let sumCredit = 0;

        // Note: For a flat journal, total debit/credit check per Doc is standard.
        // We calculate global sum here just as an indicator.
        
        // Group by Doc to check balance
        let docBalances = {};

        data.forEach(row => {
            let doc = row[2];
            let c_debit = row[4];
            let c_credit = row[5];
            let montant = parseFloat(row[6]) || 0;
            let taux = parseFloat(row[8]) || 1;
            let base_val = montant * taux;

            if(c_debit) sumDebit += base_val;
            if(c_credit) sumCredit += base_val;
        });

        document.getElementById('total_debit').textContent = sumDebit.toFixed(2);
        document.getElementById('total_credit').textContent = sumCredit.toFixed(2);
        
        let diff = Math.abs(sumDebit - sumCredit);
        let diffSpan = document.getElementById('total_diff');
        diffSpan.textContent = diff.toFixed(2);
        
        if (diff > 0.01) {
            diffSpan.className = 'text-danger';
        } else {
            diffSpan.className = 'text-success';
        }
    }

    function autoSaveTrigger() {
        let status = document.getElementById('save_status');
        status.innerHTML = '<i class="fas fa-spinner fa-spin text-warning"></i> Modifications non sauvegardées...';
        
        if(saveTimeout) clearTimeout(saveTimeout);
        saveTimeout = setTimeout(saveGridData, 3000); // Autosave after 3s of inactivity
    }

    function saveGridData() {
        if(!myTable) return;
        
        let status = document.getElementById('save_status');
        status.innerHTML = '<i class="fas fa-spinner fa-spin text-info"></i> Sauvegarde en cours...';

        const exerciceId = document.getElementById('exercice_select').value;
        const gridData = myTable.getData();

        fetch('/api/accounting/transactions/batch', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                exercice_id: exerciceId,
                data: gridData
            })
        })
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                status.innerHTML = '<i class="fas fa-check-circle text-success"></i> Synchronisé';
                // Reload to get fresh DB IDs
                initGrid(); 
            } else {
                status.innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i> Erreur : ' + response.message;
            }
        })
        .catch(err => {
            status.innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i> Erreur réseau';
        });
    }

    function loadGridData() {
        initGrid();
    }

</script>

<?php include APP_ROOT . '/app/Views/Layouts/footer.php'; ?>
