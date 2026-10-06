<?php

class AuthService {

    public function login($email, $password) {

        $db = (new Database())->connect();

        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            return true;
        }

        // Délai de sécurité contre le brute force
        usleep(500000);
        return false;
    }
}