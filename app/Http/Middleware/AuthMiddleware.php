<?php

/**
 * Middleware d'authentification
 * Verifie que l'utilisateur est connecte avant d'acceder a une ressource protegee
 */
class AuthMiddleware {

    /**
     * Exige que l'utilisateur soit authentifie.
     * Redirige vers /login si la session est absente.
     */
    public static function requireAuth($redirectPath = '/login') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user'])) {
            redirect($redirectPath);
        }
    }

    /**
     * Exige que l'utilisateur ne soit PAS authentifie (pages invites).
     * Redirige vers /dashboard si une session existe deja.
     */
    public static function guestOnly($redirectPath = '/dashboard') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!empty($_SESSION['user'])) {
            redirect($redirectPath);
        }
    }

    /**
     * Verifie si l'utilisateur est authentifie sans redirection.
     *
     * @return bool
     */
    public static function check() {
        return !empty($_SESSION['user']);
    }
}