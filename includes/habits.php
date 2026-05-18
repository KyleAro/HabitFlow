<?php
// Habit Management Logic

class HabitHandler {
    
    // Create new habit
    public static function createHabit($userId, $habitName) {
        // Validate
        if (empty($userId) || empty($habitName)) {
            return ['success' => false, 'error' => 'User ID and habit name required'];
        }
        
        if (strlen($habitName) > 100) {
            return ['success' => false, 'error' => 'Habit name too long (max 100 characters)'];
        }
        
        // Return structure for DB insertion
        return [
            'success' => true,
            'habit' => [
                'user_id' => $userId,
                'habit_name' => $habitName,
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
    
    // Get all habits for user
    public static function getUserHabits($userId) {
        // Structure for DB query
        return [
            'query_type' => 'SELECT',
            'table' => 'habits',
            'where' => ['user_id' => $userId],
            'order_by' => 'created_at DESC'
        ];
    }
    
    // Get single habit
    public static function getHabit($habitId, $userId) {
        return [
            'query_type' => 'SELECT',
            'table' => 'habits',
            'where' => ['habit_id' => $habitId, 'user_id' => $userId]
        ];
    }
    
    // Delete habit
    public static function deleteHabit($habitId, $userId) {
        if (empty($habitId) || empty($userId)) {
            return ['success' => false, 'error' => 'Habit ID and User ID required'];
        }
        
        return [
            'success' => true,
            'query_type' => 'DELETE',
            'table' => 'habits',
            'where' => ['habit_id' => $habitId, 'user_id' => $userId]
        ];
    }
    
    // Update habit name
    public static function updateHabit($habitId, $userId, $newName) {
        if (empty($habitId) || empty($userId) || empty($newName)) {
            return ['success' => false, 'error' => 'Missing required fields'];
        }
        
        return [
            'success' => true,
            'query_type' => 'UPDATE',
            'table' => 'habits',
            'set' => ['habit_name' => $newName],
            'where' => ['habit_id' => $habitId, 'user_id' => $userId]
        ];
    }
}
