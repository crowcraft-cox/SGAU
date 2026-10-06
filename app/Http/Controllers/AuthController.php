<?php

require_once APP_ROOT . '/app/Services/AuthService.php';
require_once APP_ROOT . '/app/Services/RateLimiter.php';

class AuthController {
    public function showLogin() {
        guest_only();
        $error = $_SESSION['error'] ?? null;
        $rateLimited = $_SESSION['rate_limited'] ?? false;
        unset($_SESSION['error'], $_SESSION['rate_limited']);
        require APP_ROOT . '/app/Views/Auth/login.php';
    }

    public function login() {
        guest_only();

        // ---- Rate Limiting : 5 tentatives max par IP sur 2 minutes ----
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $rateLimitKey = 'login_' . md5($ip);

        if (!RateLimiter::attempt($rateLimitKey, 5, 120)) {
            $retryAfter = RateLimiter::retryAfter($rateLimitKey);
            $_SESSION['error'] = 'Trop de tentatives de connexion. Veuillez réessayer dans ' . ceil($retryAfter / 60) . ' minute(s).';
            $_SESSION['rate_limited'] = true;
            redirect('/login');
            return;
        }

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation basique des champs
        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs.';
            redirect('/login');
            return;
        }

        $auth = new AuthService();

        if ($auth->login($email, $password)) {
            // Connexion réussie : réinitialiser le compteur
            RateLimiter::clear($rateLimitKey);
            // Régénérer l'ID de session pour prévenir la fixation de session
            session_regenerate_id(true);
            redirect('/dashboard');
            return;
        }

        // Message d'erreur générique (ne pas révéler si l'email existe)
        $_SESSION['error'] = 'Identifiants incorrects.';
        redirect('/login');
    }

    public function logout() {
        // Réinitialiser complètement la session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        redirect('/login');
    }
}
