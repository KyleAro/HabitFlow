<?php
/**
 * Vercel catch-all — routes JSON API actions through the API coordinator.
 */
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
habitflow_require('api.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'POST required']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? ($_GET['action'] ?? '');

if ($action === '') {
    echo json_encode(['success' => false, 'error' => 'Missing action']);
    exit;
}

echo json_encode(API::handleRequest($action, $input));
