<?php
// API Endpoints - Coordinates between handlers

require_once 'auth.php';
require_once 'habits.php';
require_once 'logs.php';
require_once 'qwen.php';

class API {
    
    // Handle incoming requests
    public static function handleRequest($action, $data = []) {
        $action = strtolower($action);
        
        switch ($action) {
            // AUTH ENDPOINTS
            case 'register':
                return self::register($data);
            case 'login':
                return self::login($data);
            case 'logout':
                return AuthHandler::logout();
            case 'check_auth':
                return self::checkAuth();
            
            // HABIT ENDPOINTS
            case 'create_habit':
                return self::createHabit($data);
            case 'get_habits':
                return self::getHabits($data);
            case 'delete_habit':
                return self::deleteHabit($data);
            case 'update_habit':
                return self::updateHabit($data);
            
            // LOGGING ENDPOINTS
            case 'log_habit':
                return self::logHabit($data);
            case 'get_today_status':
                return self::getTodayStatus($data);
            case 'get_habit_history':
                return self::getHabitHistory($data);
            
            default:
                return ['success' => false, 'error' => 'Unknown action: ' . $action];
        }
    }
    
    // Register user
    private static function register($data) {
        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        
        $result = AuthHandler::register($username, $email, $password);
        
        if ($result['success']) {
            // TODO: Insert into database when connected
            return [
                'success' => true,
                'message' => 'Registration successful! Please log in.',
                'user_data' => $result['user']
            ];
        }
        
        return $result;
    }
    
    // Login user
    private static function login($data) {
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        
        $result = AuthHandler::login($email, $password);
        
        if ($result['success']) {
            // TODO: Query database to get user and verify password when connected
            return [
                'success' => true,
                'message' => 'Login successful!',
                'redirect' => 'dashboard.php'
            ];
        }
        
        return $result;
    }
    
    // Check if user is authenticated
    private static function checkAuth() {
        return [
            'authenticated' => AuthHandler::isLoggedIn(),
            'user_id' => AuthHandler::getUserId()
        ];
    }
    
    // Create new habit
    private static function createHabit($data) {
        $userId = AuthHandler::getUserId();
        
        if (!$userId) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }
        
        $habitName = $data['habit_name'] ?? '';
        
        $result = HabitHandler::createHabit($userId, $habitName);
        
        if ($result['success']) {
            // TODO: Insert into database when connected
            return [
                'success' => true,
                'message' => 'Habit created!',
                'habit' => $result['habit']
            ];
        }
        
        return $result;
    }
    
    // Get all habits for user
    private static function getHabits($data) {
        $userId = AuthHandler::getUserId();
        
        if (!$userId) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }
        
        $query = HabitHandler::getUserHabits($userId);
        
        // TODO: Execute query when database connected
        return [
            'success' => true,
            'query' => $query,
            'habits' => [] // Will be populated from DB
        ];
    }
    
    // Delete habit
    private static function deleteHabit($data) {
        $userId = AuthHandler::getUserId();
        $habitId = $data['habit_id'] ?? '';
        
        if (!$userId || !$habitId) {
            return ['success' => false, 'error' => 'Missing required data'];
        }
        
        $result = HabitHandler::deleteHabit($habitId, $userId);
        
        if ($result['success']) {
            // TODO: Execute delete query when database connected
            return ['success' => true, 'message' => 'Habit deleted'];
        }
        
        return $result;
    }
    
    // Update habit
    private static function updateHabit($data) {
        $userId = AuthHandler::getUserId();
        $habitId = $data['habit_id'] ?? '';
        $newName = $data['habit_name'] ?? '';
        
        if (!$userId || !$habitId || !$newName) {
            return ['success' => false, 'error' => 'Missing required data'];
        }
        
        $result = HabitHandler::updateHabit($habitId, $userId, $newName);
        
        if ($result['success']) {
            // TODO: Execute update query when database connected
            return ['success' => true, 'message' => 'Habit updated'];
        }
        
        return $result;
    }
    
    // Log habit completion and trigger AI
    private static function logHabit($data) {
        $userId = AuthHandler::getUserId();
        $habitId = $data['habit_id'] ?? '';
        
        if (!$userId || !$habitId) {
            return ['success' => false, 'error' => 'Missing required data'];
        }
        
        // Log the completion
        $logResult = LogHandler::logHabitCompletion($habitId, $userId);
        
        if ($logResult['success']) {
            // TODO: Insert into daily_logs table when database connected
            
            // Generate AI message
            $habitName = $data['habit_name'] ?? 'this habit';
            $currentStreak = $data['current_streak'] ?? 0;
            
            $aiMessage = QwenHandler::generateMotivationMessage($habitName, $currentStreak, $userId);
            
            // TODO: Store AI feedback in database when connected
            
            return [
                'success' => true,
                'message' => 'Habit logged successfully!',
                'ai_feedback' => $aiMessage
            ];
        }
        
        return $logResult;
    }
    
    // Get today's completion status
    private static function getTodayStatus($data) {
        $userId = AuthHandler::getUserId();
        
        if (!$userId) {
            return ['success' => false, 'error' => 'Not authenticated'];
        }
        
        $query = LogHandler::getTodayStatus($userId);
        
        // TODO: Execute query when database connected
        return [
            'success' => true,
            'query' => $query,
            'status' => [] // Will be populated from DB
        ];
    }
    
    // Get habit history
    private static function getHabitHistory($data) {
        $userId = AuthHandler::getUserId();
        $habitId = $data['habit_id'] ?? '';
        $days = $data['days'] ?? 30;
        
        if (!$userId || !$habitId) {
            return ['success' => false, 'error' => 'Missing required data'];
        }
        
        $query = LogHandler::getHabitHistory($habitId, $userId, $days);
        
        // TODO: Execute query when database connected
        return [
            'success' => true,
            'query' => $query,
            'history' => [] // Will be populated from DB
        ];
    }
}
?>