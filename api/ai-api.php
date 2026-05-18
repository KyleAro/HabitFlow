<?php
session_start();
require_once __DIR__ . '/../includes/bootstrap.php';
habitflow_require('auth.php');
habitflow_require('qwen.php');

header('Content-Type: application/json');

if (!AuthHandler::isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action'])) {
    echo json_encode(['success' => false, 'error' => 'Missing action']);
    exit;
}

$action = $input['action'];

try {
    switch ($action) {
        case 'motivation':
            $habitName = $input['habitName'] ?? '';
            $streak = (int)($input['currentStreak'] ?? 0);
            if (empty($habitName)) { echo json_encode(['success' => false, 'error' => 'Habit name required']); exit; }
            $message = QwenHandler::generateMotivationMessage($habitName, $streak);
            echo json_encode(['success' => true, 'message' => $message]);
            break;

        case 'generate_habit':
            $goal = trim($input['goal'] ?? '');
            $existingHabits = $input['existingHabits'] ?? [];
            if (empty($goal)) { echo json_encode(['success' => false, 'error' => 'Please describe your goal']); exit; }
            if (strlen($goal) > 200) { echo json_encode(['success' => false, 'error' => 'Goal too long (max 200 chars)']); exit; }

            $rawResponse = QwenHandler::generateHabitFromGoal($goal, $existingHabits);
            if (!$rawResponse) {
                echo json_encode(['success' => false, 'error' => 'AI is not available right now. Please add the habit manually.']);
                exit;
            }

            $cleaned = preg_replace('/^```json\s*|\s*```$/m', '', $rawResponse);
            $cleaned = trim(preg_replace('/^```\s*|\s*```$/m', '', $cleaned));
            $habitData = json_decode($cleaned, true);

            if (!$habitData || !isset($habitData['habitName'])) {
                echo json_encode(['success' => false, 'error' => 'AI response was not valid. Try rephrasing your goal.']);
                exit;
            }

            $validCategories = ['health', 'fitness', 'learning', 'mindfulness', 'productivity', 'other'];
            if (!in_array($habitData['category'] ?? '', $validCategories)) $habitData['category'] = 'other';

            $validFrequencies = ['daily', 'specific', 'weekly'];
            if (!in_array($habitData['frequency'] ?? '', $validFrequencies)) $habitData['frequency'] = 'daily';

            if (!isset($habitData['scheduledDays']) || !is_array($habitData['scheduledDays'])) {
                $habitData['scheduledDays'] = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            }

            echo json_encode(['success' => true, 'habit' => $habitData]);
            break;

        case 'daily_briefing':
            $username = $input['username'] ?? AuthHandler::getUsername();
            $habits = $input['habits'] ?? [];
            $message = QwenHandler::generateDailyBriefing($username, $habits);
            echo json_encode(['success' => true, 'message' => $message ?: "Welcome back, {$username}! Let's make today count."]);
            break;

        case 'chat':
            $userMessage = trim($input['message'] ?? '');
            $userHabits = $input['habits'] ?? [];
            $history = $input['history'] ?? [];
            if (empty($userMessage)) { echo json_encode(['success' => false, 'error' => 'Message required']); exit; }
            if (strlen($userMessage) > 500) { echo json_encode(['success' => false, 'error' => 'Message too long']); exit; }
            if (count($history) > 10) $history = array_slice($history, -10);
            $reply = QwenHandler::chatWithCoach($userMessage, $userHabits, $history);
            echo json_encode(['success' => true, 'reply' => $reply ?: "I'm having trouble thinking right now. Try again!"]);
            break;

        default:
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
    }
} catch (Exception $e) {
    error_log("AI API error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Something went wrong. Please try again.']);
}
