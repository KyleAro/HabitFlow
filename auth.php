<?php

class AuthHandler {

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']) &&
               isset($_SESSION['logged_in']) &&
               $_SESSION['logged_in'] === true;
    }

    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUsername() {
        return $_SESSION['username'] ?? 'User';
    }

    public static function getEmail() {
        return $_SESSION['email'] ?? '';
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    public static function logout() {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
}
?>