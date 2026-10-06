<?php
require_once __DIR__ . '/../../bootstrap/app.php';

try {
    $db = (new Database())->connect();
    
    $sql = "CREATE TABLE IF NOT EXISTS transactions_en_ligne (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reference VARCHAR(100) UNIQUE NOT NULL,
        etudiant_id INT NOT NULL,
        montant DECIMAL(10,2) NOT NULL,
        moyen_paiement VARCHAR(50) NOT NULL, 
        numero_telephone VARCHAR(20) NOT NULL,
        statut VARCHAR(20) DEFAULT 'En attente', 
        paiement_id INT NULL, 
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
        FOREIGN KEY (paiement_id) REFERENCES paiements(id) ON DELETE SET NULL
    )";
    
    $db->exec($sql);
    echo "Table 'transactions_en_ligne' created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
