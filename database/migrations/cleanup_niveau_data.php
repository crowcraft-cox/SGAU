<?php
/**
 * Nettoyage: Supprimer les valeurs zéro de la colonne niveau
 * Pour éviter les doublets avec les nouvelles valeurs
 */

require_once __DIR__ . '/../../config/database.php';
$config = require __DIR__ . '/../../config/database.php';

$pdo = new PDO(
    'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
    $config['username'],
    $config['password']
);

echo "Nettoyage des données: Supprimer les niveles invalides\n";
echo "════════════════════════════════════════════════════════\n\n";

try {
    // Vérifier combien de records ont des valeurs invalides
    $result = $pdo->query("SELECT COUNT(*) as count FROM etudiants WHERE niveau NOT IN ('L1', 'L2', 'L3', 'M1', 'M2', '')")->fetch(PDO::FETCH_ASSOC);
    $invalidCount = $result['count'];
    
    echo "Records avec des valeurs invalides: {$invalidCount}\n";
    
    if ($invalidCount > 0) {
        $pdo->exec("UPDATE etudiants SET niveau = 'L1' WHERE niveau NOT IN ('L1', 'L2', 'L3', 'M1', 'M2')");
        echo "✅ Nettoyage effectué - tous les valeurs invalides sont passées à 'L1'\n";
    } else {
        echo "✅ Aucune valeur invalide trouvée\n";
    }

    // Afficher la distribution
    $distribution = $pdo->query("SELECT niveau, COUNT(*) as count FROM etudiants GROUP BY niveau")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nDistribution des niveaux:\n";
    foreach ($distribution as $row) {
        echo "  {$row['niveau']}: {$row['count']} étudiant(s)\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage();
    exit(1);
}
?>
