<?php
/**
 * Migration: Table demandes_modification_notes et colonnes de verrouillage dans notes
 */

$config = require __DIR__ . '/../../config/database.php';

try {
    $pdo = new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
        $config['username'],
        $config['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "=== 1. Ajout des colonnes de verrouillage dans la table notes ===\n";
    $columns = [
        'modifications_count' => "INT NOT NULL DEFAULT 0 AFTER total",
        'derogation_accordee' => "TINYINT(1) NOT NULL DEFAULT 0 AFTER modifications_count"
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

    echo "\n=== 2. Création de la table demandes_modification_notes ===\n";
    $sqlTable = "
        CREATE TABLE IF NOT EXISTS demandes_modification_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            enseignant_id INT NOT NULL,
            cours_id INT NOT NULL,
            etudiant_id INT NOT NULL,
            note_id INT NULL,
            motif TEXT NOT NULL,
            anciennes_cotes TEXT NULL,
            statut ENUM('en_attente', 'approuvee', 'rejetee') NOT NULL DEFAULT 'en_attente',
            reponse_sga TEXT NULL,
            traite_par INT NULL,
            traite_le DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_enseignant (enseignant_id),
            INDEX idx_cours (cours_id),
            INDEX idx_etudiant (etudiant_id),
            INDEX idx_statut (statut)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($sqlTable);
    echo "✓ Table demandes_modification_notes créée avec succès\n";

    echo "\n=== Migration terminée avec succès ! ===\n";

} catch (Exception $e) {
    echo "✗ Erreur lors de la migration: " . $e->getMessage() . "\n";
}
