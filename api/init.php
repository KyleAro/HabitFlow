<?php
/**
 * Shared bootstrap for JSON API endpoints — prevents HTML PHP errors breaking JSON.
 */
ini_set('display_errors', '0');
ini_set('html_errors', '0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ob_start();

require_once __DIR__ . '/../includes/bootstrap.php';

function habitflow_json_response(array $data, int $code = 200): void
{
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}
