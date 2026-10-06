<?php
require_once __DIR__ . '/../app/Core/Database.php';

try {
    $db = (new Database())->connect();
    
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM etudiants LIKE 'releve_bloque'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE etudiants ADD COLUMN releve_bloque TINYINT(1) DEFAULT 0 AFTER niveau;");
        echo "Column 'releve_bloque' added successfully.";
    } else {
        echo "Column 'releve_bloque' already exists.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
