<?php
// Test Alibaba Model Studio API connection
require_once __DIR__ . '/../includes/bootstrap.php';
habitflow_require('qwen.php');

echo "<h1>AI Connection Test (Alibaba Model Studio)</h1>";
echo "<pre>";

$reflection = new ReflectionClass('QwenHandler');

$keyProp = $reflection->getProperty('apiKey');
$keyProp->setAccessible(true);
$apiKey = $keyProp->getValue();

$urlProp = $reflection->getProperty('apiUrl');
$urlProp->setAccessible(true);
$apiUrl = $urlProp->getValue();

$modelProp = $reflection->getProperty('model');
$modelProp->setAccessible(true);
$model = $modelProp->getValue();

echo "TEST 1: Checking API Key...\n";
if (empty($apiKey) || strpos($apiKey, 'YOUR_') === 0) {
    echo "FAIL: API key not configured\n";
    echo "  Open qwen.php and replace 'YOUR_ALIBABA_API_KEY_HERE' with your real key\n";
    exit;
}
echo "PASS: API key is set\n";
echo "  Starts with: " . substr($apiKey, 0, 8) . "...\n";
echo "  Length: " . strlen($apiKey) . " chars\n";

echo "\nTEST 2: Configuration\n";
echo "  Provider: Alibaba Cloud Model Studio (Singapore)\n";
echo "  URL: $apiUrl\n";
echo "  Model: $model\n";

echo "\nTEST 3: Making API call...\n";

$data = [
    "model" => $model,
    "messages" => [
        ["role" => "user", "content" => "Say hello in exactly 5 words"]
    ]
];

$curl = curl_init($apiUrl);
curl_setopt_array($curl, [
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $apiKey,
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => true
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

echo "HTTP Code: $httpCode\n";

if ($curlError) {
    echo "FAIL: cURL error: $curlError\n";
} else if ($httpCode === 200) {
    echo "PASS: API responded!\n";
    $parsed = json_decode($response, true);
    if (isset($parsed['choices'][0]['message']['content'])) {
        echo "AI said: " . $parsed['choices'][0]['message']['content'] . "\n";
    }
    if (isset($parsed['usage'])) {
        echo "Tokens used: " . ($parsed['usage']['total_tokens'] ?? '?') . "\n";
    }
} else if ($httpCode === 401) {
    echo "FAIL: 401 Unauthorized - API key is wrong\n";
    echo "Response: $response\n";
} else if ($httpCode === 404) {
    echo "FAIL: 404 - Model '$model' not found\n";
    echo "Response: $response\n";
} else if ($httpCode === 429) {
    echo "FAIL: 429 Rate Limited\n";
} else {
    echo "FAIL: HTTP $httpCode\n";
    echo "Response: $response\n";
}

echo "\nTEST 4: Test via QwenHandler chat...\n";
$result = QwenHandler::chatWithCoach("Hi, are you working?", [], []);
if ($result) {
    echo "PASS: " . $result . "\n";
} else {
    echo "FAIL: QwenHandler returned null\n";
}

echo "</pre>";
?>