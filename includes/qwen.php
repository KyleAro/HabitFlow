<?php
// Qwen AI Integration via Alibaba Cloud Model Studio (Singapore)
// Get API key from: https://modelstudio.console.alibabacloud.com/

require_once __DIR__ . '/config.php';

class QwenHandler {

    private static $apiKey = null;
    private static $apiUrl = 'https://dashscope-intl.aliyuncs.com/compatible-mode/v1/chat/completions';
    private static $model = 'qwen-plus';
    private static $lastError = '';

    public static function getLastError(): string
    {
        return self::$lastError;
    }

    private static function getApiKey(): string
    {
        if (self::$apiKey === null) {
            self::$apiKey = defined('QWEN_API_KEY') ? (string) QWEN_API_KEY : '';
        }
        return self::$apiKey;
    }

    public static function isConfigured(): bool
    {
        $key = self::getApiKey();
        return $key !== '' && strpos($key, 'YOUR_') !== 0 && strpos($key, 'paste-your') !== 0;
    }

    private static function hasValidKey() {
        return self::isConfigured();
    }

    public static function setApiKey($key) {
        self::$apiKey = $key;
    }

    public static function generateMotivationMessage($habitName, $currentStreak, $userId = null) {
        $prompt = "Generate a short, enthusiastic motivational message (max 40 words) for someone who just completed " .
                  "the habit '{$habitName}'. They have a {$currentStreak}-day streak. " .
                  "Use 1-2 emojis and be encouraging. Only return the message, no extra text.";
        return self::callQwenAPI($prompt);
    }

    public static function generateWeeklyInsight($habitName, $completionRate, $bestDay = null) {
        $prompt = "Generate a brief weekly insight (max 50 words) about habit progress for '{$habitName}'. " .
                  "Completion rate this week: {$completionRate}%. ";
        if ($bestDay) {
            $prompt .= "Best day: {$bestDay}. ";
        }
        $prompt .= "Provide one actionable suggestion. Use 1 emoji.";
        return self::callQwenAPI($prompt);
    }

    public static function generateMilestoneMessage($habitName, $streak) {
        if ($streak < 10) {
            return null;
        }
        $prompt = "Generate an exciting celebration message (max 35 words) for someone who reached a " .
                  "{$streak}-day streak on '{$habitName}'. Use 2-3 celebratory emojis. " .
                  "Make them feel proud of their achievement!";
        return self::callQwenAPI($prompt);
    }

    public static function generateHabitFromGoal($goal, $existingHabits = []) {
        $existingList = empty($existingHabits) ? 'none' : implode(', ', $existingHabits);

        $systemMsg = "You are a habit coach. Generate a habit based on the user's goal. ".
                     "Respond ONLY with valid JSON in this exact format (no markdown, no extra text):\n".
                     "{\"habitName\":\"...\",\"category\":\"health|fitness|learning|mindfulness|productivity|other\",".
                     "\"frequency\":\"daily|specific|weekly\",\"scheduledDays\":[\"mon\",\"tue\",...],".
                     "\"timesPerWeek\":null,\"time\":\"HH:MM\" or null,\"duration\":minutes or null,".
                     "\"reasoning\":\"one short sentence why this habit\"}";

        $userMsg = "Goal: \"{$goal}\". User's existing habits: {$existingList}. ".
                   "Create ONE specific, actionable habit. Pick reasonable defaults for schedule.";

        return self::callQwenAPIRaw($systemMsg, $userMsg);
    }

    public static function generateDailyBriefing($username, $habits) {
        if (empty($habits)) {
            $prompt = "Generate a short welcoming message (max 30 words) for {$username} who has no habits yet. ".
                      "Encourage them to add their first habit. Use 1 emoji.";
            return self::callQwenAPI($prompt);
        }

        $habitSummary = [];
        $totalStreak = 0;
        foreach ($habits as $h) {
            $habitSummary[] = "{$h['name']} ({$h['streak']} day streak)";
            $totalStreak += $h['streak'];
        }

        $habitText = implode(', ', $habitSummary);
        $today = date('l');

        $prompt = "You're a friendly habit coach. Generate a short personal briefing (max 50 words) for ".
                  "{$username} on this {$today}. Their habits: {$habitText}. Total streak days: {$totalStreak}. ".
                  "Mention 1 specific habit by name. Be warm and motivating. Use 1-2 emojis.";

        return self::callQwenAPI($prompt);
    }

    public static function chatWithCoach($userMessage, $userHabits = [], $conversationHistory = []) {
        $habitsContext = "";
        if (!empty($userHabits)) {
            $list = [];
            foreach ($userHabits as $h) {
                $list[] = "- {$h['name']} (category: {$h['category']}, streak: {$h['streak']} days)";
            }
            $habitsContext = "User's current habits:\n" . implode("\n", $list) . "\n\n";
        }

        $systemMsg = "You are a friendly, supportive habit coach named 'Flow'. ".
                     "Keep responses concise (max 80 words). Be warm, practical, and actionable. ".
                     "Use emojis sparingly (1-2 max). When relevant, reference the user's actual habits. ".
                     $habitsContext;

        $messages = [["role" => "system", "content" => $systemMsg]];

        foreach ($conversationHistory as $msg) {
            if (isset($msg['role']) && isset($msg['content'])) {
                $messages[] = ["role" => $msg['role'], "content" => $msg['content']];
            }
        }

        $messages[] = ["role" => "user", "content" => $userMessage];

        return self::callQwenAPIMessages($messages);
    }

    private static function callQwenAPI($prompt) {
        if (!self::hasValidKey()) {
            return self::getFallbackMessage();
        }

        $data = [
            "model" => self::$model,
            "messages" => [
                ["role" => "system", "content" => "You are a supportive habit coach. Responses are encouraging, brief, and use emojis appropriately."],
                ["role" => "user", "content" => $prompt]
            ]
        ];

        $result = self::executeRequest($data);
        return $result !== null ? $result : self::getFallbackMessage();
    }

    private static function callQwenAPIRaw($systemMsg, $userMsg) {
        if (!self::hasValidKey()) {
            return null;
        }

        $data = [
            "model" => self::$model,
            "messages" => [
                ["role" => "system", "content" => $systemMsg],
                ["role" => "user", "content" => $userMsg]
            ]
        ];

        return self::executeRequest($data);
    }

    private static function callQwenAPIMessages($messages) {
        if (!self::hasValidKey()) {
            return "I'd love to chat, but the AI isn't connected yet. Once configured, I can help you with habit advice!";
        }

        $data = [
            "model" => self::$model,
            "messages" => $messages
        ];

        return self::executeRequest($data);
    }

    private static function executeRequest($data) {
        self::$lastError = '';
        $headers = [
            'Authorization: Bearer ' . self::getApiKey(),
            'Content-Type: application/json',
        ];
        $body = json_encode($data);
        $response = null;
        $httpCode = 0;

        if (function_exists('curl_init')) {
            $curl = curl_init(self::$apiUrl);
            curl_setopt_array($curl, [
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);
            $response = curl_exec($curl);
            $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);
            curl_close($curl);

            if ($curlError) {
                self::$lastError = 'Network error: ' . $curlError;
                error_log('Qwen API cURL error: ' . $curlError);
                return null;
            }
        } else {
            $opts = [
                'http' => [
                    'method'  => 'POST',
                    'header'  => implode("\r\n", $headers),
                    'content' => $body,
                    'timeout' => 15,
                ],
            ];
            $response = @file_get_contents(self::$apiUrl, false, stream_context_create($opts));
            if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
                $httpCode = (int) $m[1];
            }
            if ($response === false) {
                self::$lastError = 'Network error: could not reach AI service';
                return null;
            }
        }

        if ($httpCode !== 200) {
            self::$lastError = 'AI API HTTP ' . $httpCode;
            error_log('Qwen API error (HTTP ' . $httpCode . '): ' . $response);
            return null;
        }

        $responseData = json_decode($response, true);

        if (isset($responseData['choices'][0]['message']['content'])) {
            return trim($responseData['choices'][0]['message']['content']);
        }

        self::$lastError = 'Unexpected AI response format';
        return null;
    }

    private static function getFallbackMessage() {
        $messages = [
            "Amazing! You're crushing it! 🔥",
            "Great job! Keep the momentum going! 💪",
            "You're building incredible habits! 🚀",
            "Consistency is paying off! Keep it up! ⭐",
            "Love the dedication! You've got this! 💯",
            "One day at a time - you're doing great! 🎯",
            "Your future self will thank you! 🌟",
            "This is becoming second nature! 🎊"
        ];
        return $messages[array_rand($messages)];
    }

    public static function storeFeedback($userId, $habitId, $message, $type = 'motivation') {
        return [
            'query_type' => 'INSERT',
            'table' => 'ai_feedback',
            'data' => [
                'user_id' => $userId,
                'habit_id' => $habitId,
                'message' => $message,
                'type' => $type,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
}
