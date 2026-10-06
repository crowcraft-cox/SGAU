<?php

/**
 * RateLimiter — Protection contre les attaques par force brute
 * Stocke les tentatives en session et bloque temporairement après N échecs
 */
class RateLimiter {

    /**
     * Vérifie si une clé est trop sollicitée et bloque si nécessaire
     *
     * @param string $key       Identifiant unique (ex: 'login_' . $ip)
     * @param int    $maxAttempts Nombre max de tentatives autorisées
     * @param int    $decaySeconds Fenêtre de temps en secondes
     * @return bool  true si la requête est autorisée, false si bloquée
     */
    public static function attempt(string $key, int $maxAttempts = 5, int $decaySeconds = 60): bool {
        $sessionKey = 'rate_limit_' . $key;

        if (!isset($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = ['count' => 0, 'expires_at' => time() + $decaySeconds];
        }

        // Réinitialiser si la fenêtre de temps est expirée
        if ($_SESSION[$sessionKey]['expires_at'] < time()) {
            $_SESSION[$sessionKey] = ['count' => 0, 'expires_at' => time() + $decaySeconds];
        }

        $_SESSION[$sessionKey]['count']++;

        if ($_SESSION[$sessionKey]['count'] > $maxAttempts) {
            // Logguer la tentative de force brute
            error_log(sprintf(
                'Rate limit atteint pour la clé [%s] depuis %s (%d tentatives)',
                $key,
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SESSION[$sessionKey]['count']
            ));
            return false;
        }

        return true;
    }

    /**
     * Remet à zéro le compteur après un succès (ex: connexion réussie)
     *
     * @param string $key Identifiant unique
     */
    public static function clear(string $key): void {
        $sessionKey = 'rate_limit_' . $key;
        unset($_SESSION[$sessionKey]);
    }

    /**
     * Retourne le nombre de tentatives restantes
     *
     * @param string $key
     * @param int    $maxAttempts
     * @return int
     */
    public static function remaining(string $key, int $maxAttempts = 5): int {
        $sessionKey = 'rate_limit_' . $key;
        if (!isset($_SESSION[$sessionKey])) {
            return $maxAttempts;
        }
        if ($_SESSION[$sessionKey]['expires_at'] < time()) {
            return $maxAttempts;
        }
        return max(0, $maxAttempts - $_SESSION[$sessionKey]['count']);
    }

    /**
     * Retourne les secondes restantes avant déblocage
     *
     * @param string $key
     * @return int
     */
    public static function retryAfter(string $key): int {
        $sessionKey = 'rate_limit_' . $key;
        if (!isset($_SESSION[$sessionKey])) {
            return 0;
        }
        $remaining = $_SESSION[$sessionKey]['expires_at'] - time();
        return max(0, $remaining);
    }
}
