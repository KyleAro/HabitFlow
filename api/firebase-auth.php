<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
habitflow_require('auth.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

$action = $input['action'] ?? '';

if ($action === 'login' || $action === 'register') {
    $uid = $input['uid'] ?? '';
    $email = $input['email'] ?? '';
    $username = $input['username'] ?? explode('@', $email)[0];

    if (empty($uid) || empty($email)) {
        echo json_encode(['success' => false, 'error' => 'Missing user data']);
        exit;
    }

    $_SESSION['user_id'] = $uid;
    $_SESSION['email'] = $email;
    $_SESSION['username'] = $username;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    echo json_encode([
        'success' => true,
        'message' => 'Session created successfully',
        'redirect' => habitflow_url('public/dashboard.php')
    ]);
    exit;
}

if ($action === 'logout') {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out']);
    exit;
}

if ($action === 'check') {
    echo json_encode([
        'success' => true,
        'logged_in' => AuthHandler::isLoggedIn(),
        'user_id' => AuthHandler::getUserId()
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
