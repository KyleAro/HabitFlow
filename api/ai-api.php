<?php
require_once __DIR__ . '/init.php';
habitflow_require('auth.php');
habitflow_require('qwen.php');

if (!AuthHandler::authenticateApiRequest()) {
    habitflow_json_response(['success' => false, 'error' => 'Not authenticated. Log out and log back in.'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    habitflow_json_response(['success' => false, 'error' => 'Invalid method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    habitflow_json_response(['success' => false, 'error' => 'Missing action'], 400);
}

$action = $input['action'];

if (!QwenHandler::isConfigured()) {
    habitflow_json_response([
        'success' => false,
        'error'   => 'AI is not configured. Add QWEN_API_KEY in Vercel environment variables.',
    ], 503);
}

try {
    switch ($action) {
        case 'motivation':
            $habitName = $input['habitName'] ?? '';
            $streak = (int)($input['currentStreak'] ?? 0);
            if (empty($habitName)) {
                habitflow_json_response(['success' => false, 'error' => 'Habit name required'], 400);
            }
            $message = QwenHandler::generateMotivationMessage($habitName, $streak);
            habitflow_json_response(['success' => true, 'message' => $message]);
            break;

        case 'generate_habit':
            $goal = trim($input['goal'] ?? '');
            $existingHabits = $input['existingHabits'] ?? [];
            if (empty($goal)) {
                habitflow_json_response(['success' => false, 'error' => 'Please describe your goal'], 400);
            }
            if (strlen($goal) > 200) {
                habitflow_json_response(['success' => false, 'error' => 'Goal too long (max 200 chars)'], 400);
            }

            $rawResponse = QwenHandler::generateHabitFromGoal($goal, $existingHabits);
            if (!$rawResponse) {
                $detail = QwenHandler::getLastError();
                habitflow_json_response([
                    'success' => false,
                    'error'   => $detail ?: 'AI is not available right now. Please add the habit manually.',
                ], 502);
            }

            $cleaned = preg_replace('/^```json\s*|\s*```$/m', '', $rawResponse);
            $cleaned = trim(preg_replace('/^```\s*|\s*```$/m', '', $cleaned));
            $habitData = json_decode($cleaned, true);

            if (!$habitData || !isset($habitData['habitName'])) {
                habitflow_json_response(['success' => false, 'error' => 'AI response was not valid. Try rephrasing your goal.'], 502);
            }

            $validCategories = ['health', 'fitness', 'learning', 'mindfulness', 'productivity', 'other'];
            if (!in_array($habitData['category'] ?? '', $validCategories)) {
                $habitData['category'] = 'other';
            }

            $validFrequencies = ['daily', 'specific', 'weekly'];
            if (!in_array($habitData['frequency'] ?? '', $validFrequencies)) {
                $habitData['frequency'] = 'daily';
            }

            if (!isset($habitData['scheduledDays']) || !is_array($habitData['scheduledDays'])) {
                $habitData['scheduledDays'] = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            }

            habitflow_json_response(['success' => true, 'habit' => $habitData]);
            break;

        case 'daily_briefing':
            $username = $input['username'] ?? AuthHandler::getUsername();
            $habits = $input['habits'] ?? [];
            $message = QwenHandler::generateDailyBriefing($username, $habits);
            habitflow_json_response([
                'success' => true,
                'message' => $message ?: "Welcome back, {$username}! Let's make today count.",
            ]);
            break;

        case 'chat':
            $userMessage = trim($input['message'] ?? '');
            $userHabits = $input['habits'] ?? [];
            $history = $input['history'] ?? [];
            if (empty($userMessage)) {
                habitflow_json_response(['success' => false, 'error' => 'Message required'], 400);
            }
            if (strlen($userMessage) > 500) {
                habitflow_json_response(['success' => false, 'error' => 'Message too long'], 400);
            }
            if (count($history) > 10) {
                $history = array_slice($history, -10);
            }
            $reply = QwenHandler::chatWithCoach($userMessage, $userHabits, $history);
            if (!$reply) {
                habitflow_json_response([
                    'success' => false,
                    'error'   => QwenHandler::getLastError() ?: 'AI request failed. Check QWEN_API_KEY on Vercel.',
                ], 502);
            }
            habitflow_json_response(['success' => true, 'reply' => $reply]);
            break;

        default:
            habitflow_json_response(['success' => false, 'error' => 'Unknown action'], 400);
    }
} catch (Throwable $e) {
    error_log('AI API error: ' . $e->getMessage());
    habitflow_json_response(['success' => false, 'error' => 'Something went wrong. Please try again.'], 500);
}
