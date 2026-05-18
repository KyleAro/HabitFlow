<?php
// Daily Logging & Streak Logic

class LogHandler {
    
    // Log habit completion for today
    public static function logHabitCompletion($habitId, $userId) {
        if (empty($habitId) || empty($userId)) {
            return ['success' => false, 'error' => 'Habit ID and User ID required'];
        }
        
        $today = date('Y-m-d');
        
        return [
            'success' => true,
            'log' => [
                'habit_id' => $habitId,
                'user_id' => $userId,
                'log_date' => $today,
                'completed' => true,
                'created_at' => date('Y-m-d H:i:s')
            ],
            'query_type' => 'INSERT_OR_UPDATE',
            'table' => 'daily_logs'
        ];
    }
    
    // Get today's status for all user habits
    public static function getTodayStatus($userId) {
        $today = date('Y-m-d');
        
        return [
            'query_type' => 'SELECT',
            'table' => 'daily_logs',
            'where' => ['user_id' => $userId, 'log_date' => $today],
            'description' => 'Get completion status for today'
        ];
    }
    
    // Get habit history (last 30 days)
    public static function getHabitHistory($habitId, $userId, $days = 30) {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        return [
            'query_type' => 'SELECT',
            'table' => 'daily_logs',
            'where' => [
                'habit_id' => $habitId,
                'user_id' => $userId,
                'log_date >' => $startDate
            ],
            'order_by' => 'log_date ASC',
            'description' => "Get last $days days of logs"
        ];
    }
    
    // Calculate current streak
    public static function calculateStreak($habitLogs) {
        if (empty($habitLogs)) {
            return 0;
        }
        
        $streak = 0;
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        // Sort logs by date descending
        usort($habitLogs, function($a, $b) {
            return strtotime($b['log_date']) - strtotime($a['log_date']);
        });
        
        // Count consecutive completed days from today backwards
        foreach ($habitLogs as $log) {
            if (!$log['completed']) {
                break; // Streak broken
            }
            
            $logDate = new DateTime($log['log_date']);
            $logDate->setTime(0, 0, 0);
            
            $diff = $today->diff($logDate)->days;
            
            // If log is today or yesterday, increment streak
            if ($diff <= 1) {
                $streak++;
                $today = $logDate;
            } else {
                break; // Gap detected
            }
        }
        
        return $streak;
    }
    
    // Calculate longest streak ever
    public static function calculateLongestStreak($habitLogs) {
        if (empty($habitLogs)) {
            return 0;
        }
        
        // Sort by date ascending
        usort($habitLogs, function($a, $b) {
            return strtotime($a['log_date']) - strtotime($b['log_date']);
        });
        
        $longestStreak = 0;
        $currentStreak = 0;
        
        foreach ($habitLogs as $log) {
            if ($log['completed']) {
                $currentStreak++;
                $longestStreak = max($longestStreak, $currentStreak);
            } else {
                $currentStreak = 0;
            }
        }
        
        return $longestStreak;
    }
    
    // Get completion stats for user
    public static function getCompletionStats($userId, $days = 7) {
        $startDate = date('Y-m-d', strtotime("-$days days"));
        
        return [
            'query_type' => 'SELECT_AGGREGATE',
            'table' => 'daily_logs',
            'where' => [
                'user_id' => $userId,
                'log_date >' => $startDate
            ],
            'aggregate' => [
                'total_logs' => 'COUNT(*)',
                'completed_logs' => 'SUM(completed)'
            ],
            'description' => "Get completion stats for last $days days"
        ];
    }
}
