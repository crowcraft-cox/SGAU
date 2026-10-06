<?php

/**
 * Middleware de vérification des rôles
 * Vérifie que l'utilisateur connecté a l'un des rôles requis
 */
class RoleMiddleware {
    /**
     * Vérifie si l'utilisateur a le rôle requis
     * @param array $requiredRoles - Rôles autorisés
     * @return bool
     */
    public static function check($requiredRoles) {
        if (!isset($_SESSION['user'])) {
            return false;
        }

        $userRole = $_SESSION['user']['role'] ?? null;
        
        if (!is_array($requiredRoles)) {
            $requiredRoles = [$requiredRoles];
        }

        return in_array($userRole, $requiredRoles);
    }

    /**
     * Redirige si l'utilisateur n'a pas le rôle requis
     * @param array $requiredRoles - Rôles autorisés
     * @param string $redirectPath - Chemin de redirection
     */
    public static function requireRole($requiredRoles, $redirectPath = '/dashboard') {
        if (!self::check($requiredRoles)) {
            redirect($redirectPath);
        }
    }

    /**
     * Vérifie si l'utilisateur est administrateur
     */
    public static function isAdmin() {
        return self::check(['admin']);
    }

    /**
     * Vérifie si l'utilisateur est enseignant
     */
    public static function isEnseignant() {
        return self::check(['enseignant']);
    }

    /**
     * Vérifie si l'utilisateur est étudiant
     */
    public static function isEtudiant() {
        return self::check(['etudiant']);
    }

    /**
     * Vérifie si l'utilisateur est du service financier
     */
    public static function isFinance() {
        return self::check(['finance']);
    }
}
