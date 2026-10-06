<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=gestion_academique;charset=utf8mb4', 'root', '');

try {
    // Vérifier si la colonne photo existe déjà
    $result = $db->query("SHOW COLUMNS FROM enseignants LIKE 'photo'");
    if ($result->rowCount() == 0) {
        // Ajouter la colonne photo après departement
        $db->exec("ALTER TABLE enseignants ADD COLUMN photo VARCHAR(255) AFTER departement");
        // Renommer departement en specialite (optionnel, ou on peut juste utiliser "departement" comme "specialité")
        echo "✓ Colonne 'photo' ajoutée avec succès\n";
    } else {
        echo "✓ Colonne 'photo' existe déjà\n";
    }
    
    // Vérifier les colonnes finales
    echo "\n=== Structure mise à jour ===\n";
    $result = $db->query('SHOW COLUMNS FROM enseignants');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
