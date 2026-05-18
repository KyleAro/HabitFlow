<?php
/**
 * HabitFlow path helpers — works from public/ and api/ (XAMPP + Vercel).
 */
if (!defined('HABITFLOW_ROOT')) {
    define('HABITFLOW_ROOT', dirname(__DIR__));
}

function habitflow_load_env(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $envFile = HABITFLOW_ROOT . '/.env';
    if (!is_readable($envFile)) {
        $loaded = true;
        return;
    }
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if ($key === '' || getenv($key) !== false) {
            continue;
        }
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
    }
    $loaded = true;
}

function habitflow_base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $dir = rtrim($dir, '/');
    if ($dir === '' || $dir === '.') {
        $base = '';
    } elseif (basename($dir) === 'public' || str_ends_with($dir, '/public')) {
        $parent = dirname($dir);
        $base = ($parent === '/' || $parent === '.') ? '' : $parent;
    } elseif (basename($dir) === 'api' || str_ends_with($dir, '/api')) {
        $parent = dirname($dir);
        $base = ($parent === '/' || $parent === '.') ? '' : $parent;
    } else {
        $base = $dir;
    }
    return $base;
}

function habitflow_url(string $path): string
{
    $base = habitflow_base_path();
    $path = ltrim(str_replace('\\', '/', $path), '/');
    return ($base === '' ? '' : $base) . '/' . $path;
}

function habitflow_asset(string $path): string
{
    $prefix = getenv('VERCEL') ? 'assets/' : 'public/assets/';
    return habitflow_url($prefix . ltrim($path, '/'));
}

/** Clean URLs on Vercel; public/*.php paths locally (XAMPP). */
function habitflow_page(string $page): string
{
    if (getenv('VERCEL')) {
        $routes = [
            'index'     => '/',
            'login'     => '/login',
            'dashboard' => '/dashboard',
            'register'  => '/register',
            'logout'    => '/logout',
        ];
        return $routes[$page] ?? '/' . $page;
    }
    $files = [
        'index'     => 'public/index.php',
        'login'     => 'public/login.php',
        'dashboard' => 'public/dashboard.php',
        'register'  => 'public/register.php',
        'logout'    => 'public/logout.php',
    ];
    return habitflow_url($files[$page] ?? 'public/' . $page . '.php');
}

function habitflow_api(string $endpoint): string
{
    return habitflow_url('api/' . ltrim($endpoint, '/'));
}

function habitflow_require(string $file): void
{
    habitflow_load_env();
    $path = HABITFLOW_ROOT . '/includes/' . ltrim($file, '/');
    if (!is_readable($path)) {
        throw new RuntimeException('Include not found: ' . $file);
    }
    require_once $path;
}
