<?php

class AddOnlineStatus
{
    public function up()
    {
        $db = new PDO(
            'mysql:host=127.0.0.1;dbname=gestion_academique;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $sql = "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_online TINYINT(1) DEFAULT 0;
                ALTER TABLE users ADD COLUMN IF NOT EXISTS last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;
                ALTER TABLE users ADD INDEX IF NOT EXISTS idx_is_online (is_online);
                ALTER TABLE users ADD INDEX IF NOT EXISTS idx_last_activity (last_activity);";

        try {
            $db->exec($sql);
            echo "✓ Colonnes de statut en ligne ajoutées avec succès!\n";
        } catch (PDOException $e) {
            echo "✗ Erreur: " . $e->getMessage() . "\n";
        }
    }

    public function down()
    {
        $db = new PDO(
            'mysql:host=127.0.0.1;dbname=gestion_academique;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        try {
            $db->exec("ALTER TABLE users DROP COLUMN IF EXISTS is_online;
                       ALTER TABLE users DROP COLUMN IF EXISTS last_activity;");
            echo "✓ Colonnes supprimées avec succès!\n";
        } catch (PDOException $e) {
            echo "✗ Erreur: " . $e->getMessage() . "\n";
        }
    }
}

// Exécute la migration si le script est lancé directement
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    (new AddOnlineStatus())->up();
}
?>
