<?php

function auth() {
    return $_SESSION['user'] ?? null;
}

function check_auth() {
    return !empty($_SESSION['user']);
}

function require_auth() {
    if (!check_auth()) {
        redirect('/login');
    }
}

function guest_only() {
    if (check_auth()) {
        redirect('/dashboard');
    }
}
