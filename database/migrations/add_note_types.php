<?php
/**
 * Migration: Ajouter les colonnes interro, tp, examen à la table notes
 */

require_once __DIR__ . '/../../config/database.php';
$config = require __DIR__ . '/../../config/database.php';

$pdo = new PDO(
    'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
    $config['username'],
    $config['password']
);

echo "=== Migration: Ajouter colonnes interro, tp, examen ===\n\n";

$columns = ['interro', 'tp', 'examen'];

foreach ($columns as $col) {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM notes LIKE '$col'");
        
        if ($stmt->rowCount() === 0) {
            $sql = "ALTER TABLE notes ADD COLUMN $col DECIMAL(5,2) NULL DEFAULT NULL AFTER note";
            $pdo->exec($sql);
            echo "✓ Colonne '$col' ajoutée\n";
        } else {
            echo "ℹ Colonne '$col' existe déjà\n";
        }
    } catch (Exception $e) {
        echo "✗ Erreur pour '$col': " . $e->getMessage() . "\n";
    }
}

echo "\n✓ Migration terminée!\n";
