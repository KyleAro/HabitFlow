<?php

require_once __DIR__ . '/bootstrap.php';

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

    /** For API routes on Vercel: session, cookie, or Firebase Bearer token. */
    public static function authenticateApiRequest(): bool
    {
        if (self::isLoggedIn()) {
            return true;
        }
        return self::authenticateFromBearer();
    }

    private static function authenticateFromBearer(): bool
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (!preg_match('/Bearer\s+(\S+)/i', $header, $matches)) {
            return false;
        }

        $user = self::verifyFirebaseIdToken($matches[1]);
        if (!$user) {
            return false;
        }

        $_SESSION['user_id'] = $user['uid'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['username'] = $user['displayName'] ?? explode('@', $user['email'])[0];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        return true;
    }

    private static function verifyFirebaseIdToken(string $idToken): ?array
    {
        $apiKey = habitflow_env('FIREBASE_API_KEY');
        if ($apiKey === '') {
            return null;
        }

        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . urlencode($apiKey);
        $body = json_encode(['idToken' => $idToken]);

        $response = self::httpPost($url, $body, ['Content-Type: application/json']);
        if ($response === null) {
            return null;
        }

        $data = json_decode($response, true);
        if (!isset($data['users'][0])) {
            return null;
        }

        $user = $data['users'][0];
        return [
            'uid'         => $user['localId'] ?? '',
            'email'       => $user['email'] ?? '',
            'displayName' => $user['displayName'] ?? null,
        ];
    }

    private static function httpPost(string $url, string $body, array $headers): ?string
    {
        if (function_exists('curl_init')) {
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
            ]);
            $response = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            return ($response !== false && $code === 200) ? $response : null;
        }

        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => implode("\r\n", $headers),
                'content' => $body,
                'timeout' => 10,
            ],
        ];
        $response = @file_get_contents($url, false, stream_context_create($opts));
        return $response !== false ? $response : null;
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
        self::clearAuthCookie();
        self::clearSessionCookie();

        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        return ['success' => true, 'message' => 'Logged out successfully'];
    }

    private static function clearSessionCookie(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $params['path'] ?: '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    private static function cookieSecret(): string
    {
        $secret = habitflow_env('AUTH_COOKIE_SECRET');
        return $secret !== '' ? $secret : 'habitflow-dev-secret-change-in-env';
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
        $expired = time() - 3600;
        $opts = [
            'expires'  => $expired,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ];
        // Clear both secure and non-secure variants (dev vs Vercel)
        setcookie(self::COOKIE_NAME, '', array_merge($opts, ['secure' => true]));
        setcookie(self::COOKIE_NAME, '', array_merge($opts, ['secure' => false]));
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
