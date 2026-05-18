<?php
require_once __DIR__ . '/init.php';
habitflow_require('auth.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    habitflow_json_response(['success' => false, 'error' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    habitflow_json_response(['success' => false, 'error' => 'Invalid input data'], 400);
}

$action = $input['action'] ?? '';

if ($action === 'login' || $action === 'register') {
    $uid = $input['uid'] ?? '';
    $email = $input['email'] ?? '';
    $username = $input['username'] ?? explode('@', $email)[0];

    if (empty($uid) || empty($email)) {
        habitflow_json_response(['success' => false, 'error' => 'Missing user data'], 400);
    }

    AuthHandler::login($uid, $email, $username);

    habitflow_json_response([
        'success'  => true,
        'message'  => 'Session created successfully',
        'redirect' => habitflow_page('dashboard'),
    ]);
}

if ($action === 'logout') {
    AuthHandler::logout();
    habitflow_json_response(['success' => true, 'message' => 'Logged out']);
}

if ($action === 'check') {
    habitflow_json_response([
        'success'   => true,
        'logged_in' => AuthHandler::isLoggedIn(),
        'user_id'   => AuthHandler::getUserId(),
    ]);
}

habitflow_json_response(['success' => false, 'error' => 'Unknown action'], 400);
