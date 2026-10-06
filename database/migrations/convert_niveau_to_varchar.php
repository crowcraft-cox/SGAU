<?php
/**
 * Migration: Convertir la colonne niveau de INT à VARCHAR
 * Pour supporter les valeurs L1, L2, L3, M1, M2
 */

require_once __DIR__ . '/../../config/database.php';
$config = require __DIR__ . '/../../config/database.php';

$pdo = new PDO(
    'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
    $config['username'],
    $config['password']
);

echo "Migration: Convertir colonne niveau INT -> VARCHAR\n";
echo "════════════════════════════════════════════════\n\n";

try {
    // Vérifier le type actuel
    $result = $pdo->query('SHOW COLUMNS FROM etudiants WHERE Field = "niveau"')->fetch(PDO::FETCH_ASSOC);
    echo "Type actuel: {$result['Type']}\n";
    
    // Si c'est déjà VARCHAR, pas besoin de migrer
    if (strpos($result['Type'], 'varchar') !== false) {
        echo "✅ La colonne est déjà en VARCHAR, rien à faire!\n";
        exit(0);
    }

    // Migrer
    echo "\n⏳ Migration en cours...\n";
    
    $pdo->exec("ALTER TABLE etudiants MODIFY COLUMN niveau VARCHAR(10) NOT NULL");
    
    echo "✅ Migration réussie!\n";
    echo "   La colonne 'niveau' est maintenant VARCHAR(10)\n";
    echo "   Elle accepte les valeurs: L1, L2, L3, M1, M2\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
    exit(1);
}
?>
