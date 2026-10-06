<?php
/**
 * Migration: Création de la table cours_etudiants et mise à jour de la table notes
 */

$config = require __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== 1. Création de la table cours_etudiants ===\n";
    $sqlTable = "
        CREATE TABLE IF NOT EXISTS cours_etudiants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cours_id INT NOT NULL,
            etudiant_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_cours_etudiant (cours_id, etudiant_id),
            INDEX idx_cours (cours_id),
            INDEX idx_etudiant (etudiant_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlTable);
    echo "✓ Table cours_etudiants prête\n\n";

    echo "=== 2. Vérification / Ajout des colonnes td, mi_session, total dans notes ===\n";
    $columns = [
        'td' => "DECIMAL(5,2) NULL DEFAULT 0.00 AFTER tp",
        'mi_session' => "DECIMAL(5,2) NULL DEFAULT 0.00 AFTER td",
        'total' => "DECIMAL(5,2) NULL DEFAULT 0.00 AFTER examen"
    ];

    foreach ($columns as $col => $definition) {
        $stmt = $pdo->query("SHOW COLUMNS FROM notes LIKE '$col'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE notes ADD COLUMN $col $definition");
            echo "✓ Colonne '$col' ajoutée à la table notes\n";
        } else {
            echo "ℹ Colonne '$col' existe déjà dans notes\n";
        }
    }

    echo "\n=== 3. Migration automatique des enrôlements depuis les notes existantes ===\n";
    $stmtNotes = $pdo->query("SELECT DISTINCT cours_id, etudiant_id FROM notes WHERE cours_id IS NOT NULL AND etudiant_id IS NOT NULL");
    $existingNotes = $stmtNotes->fetchAll(PDO::FETCH_ASSOC);

    $inserted = 0;
    $stmtInsert = $pdo->prepare("INSERT IGNORE INTO cours_etudiants (cours_id, etudiant_id) VALUES (?, ?)");
    foreach ($existingNotes as $row) {
        $stmtInsert->execute([$row['cours_id'], $row['etudiant_id']]);
        if ($stmtInsert->rowCount() > 0) {
            $inserted++;
        }
    }
    echo "✓ $inserted enrôlements automatiquement créés à partir des notes existantes\n";

    echo "\n=== Migration terminée avec succès ! ===\n";

} catch (Exception $e) {
    echo "✗ Erreur lors de la migration: " . $e->getMessage() . "\n";
}
