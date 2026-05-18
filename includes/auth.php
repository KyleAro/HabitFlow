<?php

class AuthHandler {

    private const COOKIE_NAME = 'hf_auth';

    public static function isLoggedIn() {
        if (isset($_SESSION['user_id']) &&
            isset($_SESSION['logged_in']) &&
            $_SESSION['logged_in'] === true) {
            return true;
        }
        return self::restoreFromCookie();
    }

    public static function getUserId() {
        self::isLoggedIn();
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUsername() {
        self::isLoggedIn();
        return $_SESSION['username'] ?? 'User';
    }

    public static function getEmail() {
        self::isLoggedIn();
        return $_SESSION['email'] ?? '';
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            require_once __DIR__ . '/bootstrap.php';
            header('Location: ' . habitflow_page('login'));
            exit;
        }
    }

    public static function login(string $uid, string $email, string $username): void
    {
        $_SESSION['user_id'] = $uid;
        $_SESSION['email'] = $email;
        $_SESSION['username'] = $username;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        self::setAuthCookie($uid, $email, $username);
    }

    public static function logout() {
        session_unset();
        session_destroy();
        self::clearAuthCookie();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    private static function cookieSecret(): string
    {
        $secret = getenv('AUTH_COOKIE_SECRET');
        return ($secret !== false && $secret !== '') ? $secret : 'habitflow-dev-secret-change-in-env';
    }

    private static function setAuthCookie(string $uid, string $email, string $username): void
    {
        $payload = json_encode([
            'uid'      => $uid,
            'email'    => $email,
            'username' => $username,
            't'        => time(),
        ], JSON_THROW_ON_ERROR);
        $sig = hash_hmac('sha256', $payload, self::cookieSecret());
        $value = base64_encode($payload) . '.' . $sig;
        $secure = !empty($_SERVER['HTTPS']) || (bool) getenv('VERCEL');

        setcookie(self::COOKIE_NAME, $value, [
            'expires'  => time() + 60 * 60 * 24 * 7,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    private static function clearAuthCookie(): void
    {
        $secure = !empty($_SERVER['HTTPS']) || (bool) getenv('VERCEL');
        setcookie(self::COOKIE_NAME, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        unset($_COOKIE[self::COOKIE_NAME]);
    }

    private static function restoreFromCookie(): bool
    {
        if (empty($_COOKIE[self::COOKIE_NAME])) {
            return false;
        }

        $parts = explode('.', $_COOKIE[self::COOKIE_NAME], 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$encoded, $sig] = $parts;
        $payload = base64_decode($encoded, true);
        if ($payload === false) {
            return false;
        }

        if (!hash_equals(hash_hmac('sha256', $payload, self::cookieSecret()), $sig)) {
            return false;
        }

        $data = json_decode($payload, true);
        if (!is_array($data) || empty($data['uid']) || empty($data['email'])) {
            return false;
        }

        if (isset($data['t']) && (time() - (int) $data['t']) > 60 * 60 * 24 * 7) {
            self::clearAuthCookie();
            return false;
        }

        $_SESSION['user_id'] = $data['uid'];
        $_SESSION['email'] = $data['email'];
        $_SESSION['username'] = $data['username'] ?? explode('@', $data['email'])[0];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = $data['t'] ?? time();

        return true;
    }
}
