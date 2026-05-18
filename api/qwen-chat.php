<?php
require_once __DIR__ . '/init.php';
habitflow_require('auth.php');
habitflow_require('qwen.php');

if (!AuthHandler::authenticateApiRequest()) {
    habitflow_json_response(['success' => false, 'error' => 'Not authenticated. Log out and log back in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    habitflow_json_response(['success' => false, 'error' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['message'])) {
    habitflow_json_response(['success' => false, 'error' => 'Missing message'], 400);
}

$userMessage = trim($input['message']);

if ($userMessage === '') {
    habitflow_json_response(['success' => false, 'error' => 'Empty message'], 400);
}

if (strlen($userMessage) > 500) {
    habitflow_json_response(['success' => false, 'error' => 'Message too long (max 500 characters)'], 400);
}

if (!QwenHandler::isConfigured()) {
    habitflow_json_response([
        'success' => false,
        'error'   => 'AI is not configured. Add QWEN_API_KEY in Vercel environment variables.',
    ], 503);
}

$habitContext = $input['habits'] ?? [];
$userHabits = [];
foreach ($habitContext as $name) {
    if (is_string($name) && $name !== '') {
        $userHabits[] = ['name' => $name, 'category' => 'other', 'streak' => 0];
    }
}

$reply = QwenHandler::chatWithCoach($userMessage, $userHabits, []);

if (!$reply || strpos($reply, "AI isn't connected") !== false) {
    habitflow_json_response([
        'success' => false,
        'error'   => QwenHandler::getLastError() ?: 'Could not reach the AI service. Check your API key and try again.',
    ], 502);
}

habitflow_json_response([
    'success'   => true,
    'message'   => $reply,
    'timestamp' => date('Y-m-d H:i:s'),
]);
