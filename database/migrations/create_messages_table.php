<?php

class CreateMessagesTable
{
    public function up()
    {
        $db = new PDO(
            'mysql:host=127.0.0.1;dbname=gestion_academique;charset=utf8mb4',
            'root',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $sql = "CREATE TABLE IF NOT EXISTS messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT NOT NULL,
            file_path VARCHAR(255) NULL,
            file_name VARCHAR(255) NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
            INDEX (sender_id),
            INDEX (receiver_id),
            INDEX (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        try {
            $db->exec($sql);
            echo "✓ Table 'messages' créée avec succès!\n";
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
            $db->exec("DROP TABLE IF EXISTS messages;");
            echo "✓ Table 'messages' supprimée avec succès!\n";
        } catch (PDOException $e) {
            echo "✗ Erreur: " . $e->getMessage() . "\n";
        }
    }
}

// Exécute la migration si le script est lancé directement
if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    (new CreateMessagesTable())->up();
}
