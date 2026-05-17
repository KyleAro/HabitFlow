<?php
session_start();
require_once 'auth.php';
require_once 'config.php';

header('Content-Type: application/json');

if (!AuthHandler::isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['message'])) {
    echo json_encode(['success' => false, 'error' => 'Missing message']);
    exit;
}

$userMessage = trim($input['message']);

if (empty($userMessage)) {
    echo json_encode(['success' => false, 'error' => 'Empty message']);
    exit;
}

if (strlen($userMessage) > 500) {
    echo json_encode(['success' => false, 'error' => 'Message too long (max 500 characters)']);
    exit;
}

$habitContext = isset($input['habits']) ? $input['habits'] : [];

$systemPrompt  = "You are hAbIt's AI Coach - a supportive, encouraging habit-building assistant. ";
$systemPrompt .= "You help users build better habits, overcome challenges, and stay motivated. ";
$systemPrompt .= "You're friendly, concise (max 150 words), and use 1-2 emojis. ";
$systemPrompt .= "Be positive but realistic. ";

if (!empty($habitContext)) {
    $habitList     = implode(', ', array_slice($habitContext, 0, 5));
    $systemPrompt .= "The user's current habits are: {$habitList}. ";
}

$systemPrompt .= "Respond naturally and helpfully to their questions about habits.";

$response = callQwenAPI($userMessage, $systemPrompt);

echo json_encode([
    'success'   => true,
    'message'   => $response,
    'timestamp' => date('Y-m-d H:i:s')
]);

function callQwenAPI($userMessage, $systemPrompt) {
    $apiKey = defined('QWEN_API_KEY') ? QWEN_API_KEY : '';

    if (empty($apiKey) || $apiKey === 'paste-your-openrouter-key-here') {
        error_log('OpenRouter API key not set in Config.php');
        return getFallbackResponse();
    }

    $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    $model = 'qwen/qwen-2.5-7b-instruct:free';

    $data = [
        "model"       => $model,
        "messages"    => [
            ["role" => "system", "content" => $systemPrompt],
            ["role" => "user",   "content" => $userMessage]
        ],
        "max_tokens"  => 500,
        "temperature" => 0.7
    ];

    $curl = curl_init($apiUrl);
    curl_setopt_array($curl, [
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $apiKey,
            "Content-Type: application/json",
            "HTTP-Referer: http://localhost",
            "X-Title: hAbIt Habit Coach"
        ],
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response  = curl_exec($curl);
    $httpCode  = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curlError = curl_error($curl);
    curl_close($curl);

    if ($curlError) {
        error_log("OpenRouter cURL error: " . $curlError);
        return getFallbackResponse();
    }

    if ($httpCode !== 200) {
        error_log("OpenRouter HTTP {$httpCode}: " . $response);
        return getFallbackResponse();
    }

    $responseData = json_decode($response, true);
    if (isset($responseData['choices'][0]['message']['content'])) {
        return $responseData['choices'][0]['message']['content'];
    }

    return getFallbackResponse();
}

function getFallbackResponse() {
    $responses = [
        "I'm having trouble connecting right now 🧠 Try again in a moment!",
        "Oops! Connection issue. But remember — every day is a chance to build better habits! 💪",
        "My AI circuits are a bit fuzzy. Keep going with your habits though — you're doing great! 🚀",
        "Temporary glitch on my end. In the meantime, focus on one habit at a time! ⭐",
        "Can't respond right now, but I believe in you! 🌟 Keep pushing forward!"
    ];
    return $responses[array_rand($responses)];
}
?>