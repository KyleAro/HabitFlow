<?php
require_once __DIR__ . '/init.php';
habitflow_require('auth.php');
habitflow_require('qwen.php');

$auth = AuthHandler::authenticateApiRequest();

$health = [
    'auth'             => $auth,
    'qwen_configured'  => QwenHandler::isConfigured(),
    'qwen_key_length'  => strlen(QWEN_API_KEY),
    'firebase_key_set' => habitflow_env('FIREBASE_API_KEY') !== '',
    'vercel'           => (bool) getenv('VERCEL'),
    'curl'             => function_exists('curl_init'),
];

if ($auth && QwenHandler::isConfigured()) {
    $test = QwenHandler::generateMotivationMessage('test', 1);
    $health['api_test'] = $test ? 'ok' : 'fail';
    $health['api_error'] = QwenHandler::getLastError();
} else {
    $health['api_test'] = 'skipped';
}

habitflow_json_response($health);
