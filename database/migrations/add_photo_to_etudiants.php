<?php

/**
 * Migration: Ajouter la colonne 'photo' à la table 'etudiants'
 * 
 * Cette migration ajoute une colonne pour stocker le chemin de la photo de profil
 */

require_once __DIR__ . '/../../config/database.php';

try {
    $config = require __DIR__ . '/../../config/database.php';
    
    $mysqli = new mysqli(
        $config['host'],

         $config['username'],
        $config['password'],
        $config['database']
    );

    if ($mysqli->connect_error) {
        die('Erreur de connexion: ' . $mysqli->connect_error);
    }

    // Vérifier si la colonne existe déjà
    $result = $mysqli->query("SHOW COLUMNS FROM etudiants LIKE 'photo'");
    
    if ($result->num_rows === 0) {
        // Ajouter la colonne
        $sql = "ALTER TABLE etudiants ADD COLUMN photo VARCHAR(255) NULL DEFAULT NULL AFTER niveau";
        
        if ($mysqli->query($sql)) {
            echo "✓ Colonne 'photo' ajoutée avec succès à la table 'etudiants'.\n";
        } else {
            echo "✗ Erreur lors de l'ajout de la colonne: " . $mysqli->error . "\n";
        }
    } else {
        echo "ℹ La colonne 'photo' existe déjà dans la table 'etudiants'.\n";
    }

    $mysqli->close();

} catch (Exception $e) {
    echo "✗ Erreur: " . $e->getMessage() . "\n";
}
