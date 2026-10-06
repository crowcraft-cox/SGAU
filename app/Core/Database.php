<?php

class Database {
    private static $instance = null;
    private $pdo = null;

    /**
     * Singleton pattern
     * Assure qu'une seule instance existe
     */
    public function __construct() {
        if (self::$instance === null) {
            $this->initializeConnection();
            self::$instance = $this;
        }
    }

    /**
     * Getter statique pour le Singleton
     */
    public static function getInstance() {
        if (self::$instance === null) {
            new self();
        }
        return self::$instance;
    }

    /**
     * Initialise la connexion à la base de données
     */
    private function initializeConnection() {
        if ($this->pdo === null) {
            $config = require APP_ROOT . '/config/database.php';
            $dsn = sprintf(
                '%s:host=%s;dbname=%s;charset=%s',
                $config['driver'] ?? 'mysql',
                $config['host'] ?? '127.0.0.1',
                $config['database'] ?? '',
                $config['charset'] ?? 'utf8mb4'
            );

            try {
                $this->pdo = new PDO(
                    $dsn,
                    $config['username'] ?? 'root',
                    $config['password'] ?? ''
                );
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_TIMEOUT, 30);
            } catch (PDOException $e) {
                // Log l'erreur complète discrètement (visible dans les logs serveur uniquement)
                error_log('Erreur connexion DB: ' . $e->getMessage());
                // Afficher un message générique sans exposer les détails système
                http_response_code(503);
                die('Service temporairement indisponible. Veuillez réessayer dans quelques instants.');
            }
        }
    }

    /**
     * Retourne la connexion PDO
     * Compatible avec: (new Database())->connect()
     */
    public function connect() {
        if (self::$instance === null) {
            return $this->pdo;
        }
        return self::$instance->pdo;
    }
}

