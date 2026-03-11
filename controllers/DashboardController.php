<?php
require_once __DIR__ . '/../includes/config.php';

class DashboardController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get student statistics
     */
    public function getStudentStats($userId) {
        $stmt = $this->pdo->prepare("
            SELECT u.full_name, u.avatar, s.* 
            FROM users u 
            JOIN user_stats s ON u.id = s.user_id 
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /**
     * Get accounting path (lessons and progress)
     */
    public function getLearningPath($userId) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, IFNULL(p.status, 'locked') as status, IFNULL(p.score, 0) as score, p.completed_at
            FROM lessons l
            LEFT JOIN user_progress p ON l.id = p.lesson_id AND p.user_id = ?
            ORDER BY l.order_index ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get recent activity from user_progress
     */
    public function getRecentActivity($userId) {
        $stmt = $this->pdo->prepare("
            SELECT l.title, l.xp_reward, p.status, p.completed_at
            FROM user_progress p
            JOIN lessons l ON p.lesson_id = l.id
            WHERE p.user_id = ?
            ORDER BY p.completed_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $activities = [];
        foreach ($rows as $row) {
            $date = 'Pendiente';
            if ($row['completed_at']) {
                $dt = new DateTime($row['completed_at']);
                $now = new DateTime();
                $diff = $now->diff($dt);
                if ($diff->days === 0) {
                    $date = 'Hoy';
                } elseif ($diff->days === 1) {
                    $date = 'Ayer';
                } elseif ($diff->days < 7) {
                    $date = 'Hace ' . $diff->days . ' días';
                } else {
                    $date = $dt->format('d/m/Y');
                }
            }

            $xpText = $row['status'] === 'completed' ? '+' . $row['xp_reward'] . ' XP' : 'En progreso';
            
            $activities[] = [
                'type' => $row['status'] === 'completed' ? 'lesson_completed' : 'in_progress',
                'title' => $row['title'],
                'date' => $date,
                'xp' => $xpText
            ];
        }

        // If no real activity, return a welcome entry
        if (empty($activities)) {
            $activities[] = [
                'type' => 'welcome',
                'title' => '¡Bienvenido a DebiHaby!',
                'date' => 'Hoy',
                'xp' => '🎉'
            ];
        }

        return $activities;
    }

    /**
     * Complete a lesson and award points
     */
    public function completeLesson($userId, $lessonId) {
        $pointsPerLesson = 100;

        // Check if already completed
        $stmt = $this->pdo->prepare("SELECT user_id FROM user_progress WHERE user_id = ? AND lesson_id = ? AND status = 'completed'");
        $stmt->execute([$userId, $lessonId]);
        if ($stmt->fetch()) return false;

        // Insert/Update progress
        $stmt = $this->pdo->prepare("
            INSERT INTO user_progress (user_id, lesson_id, status, completed_at) 
            VALUES (?, ?, 'completed', NOW())
            ON DUPLICATE KEY UPDATE status = 'completed', completed_at = NOW()
        ");
        $stmt->execute([$userId, $lessonId]);

        // Unlock next lesson
        $this->unlockNextLesson($userId, $lessonId);

        // Add points
        $this->addXP($userId, $pointsPerLesson);

        // Update streak
        $this->updateStreak($userId);
        
        return true;
    }

    /**
     * Unlock the next lesson in order
     */
    private function unlockNextLesson($userId, $completedLessonId) {
        // Get the order_index of the completed lesson
        $stmt = $this->pdo->prepare("SELECT order_index FROM lessons WHERE id = ?");
        $stmt->execute([$completedLessonId]);
        $currentOrder = $stmt->fetchColumn();

        // Find the next lesson
        $stmt = $this->pdo->prepare("SELECT id FROM lessons WHERE order_index > ? ORDER BY order_index ASC LIMIT 1");
        $stmt->execute([$currentOrder]);
        $nextLessonId = $stmt->fetchColumn();

        if ($nextLessonId) {
            // Check if progress row exists
            $stmt = $this->pdo->prepare("SELECT status FROM user_progress WHERE user_id = ? AND lesson_id = ?");
            $stmt->execute([$userId, $nextLessonId]);
            $existing = $stmt->fetch();

            if ($existing) {
                if ($existing['status'] === 'locked') {
                    $stmt = $this->pdo->prepare("UPDATE user_progress SET status = 'available' WHERE user_id = ? AND lesson_id = ?");
                    $stmt->execute([$userId, $nextLessonId]);
                }
            } else {
                $stmt = $this->pdo->prepare("INSERT INTO user_progress (user_id, lesson_id, status) VALUES (?, ?, 'available')");
                $stmt->execute([$userId, $nextLessonId]);
            }
        }
    }

    /**
     * Add XP and potentially level up
     */
    public function addXP($userId, $amount) {
        $stmt = $this->pdo->prepare("UPDATE user_stats SET points = points + ? WHERE user_id = ?");
        $stmt->execute([$amount, $userId]);

        // Level = 1 + floor(points / 1000)
        $stmt = $this->pdo->prepare("SELECT points FROM user_stats WHERE user_id = ?");
        $stmt->execute([$userId]);
        $xp = $stmt->fetchColumn();
        $newLevel = floor($xp / 1000) + 1;

        $stmt = $this->pdo->prepare("UPDATE user_stats SET level = ? WHERE user_id = ?");
        $stmt->execute([$newLevel, $userId]);
    }

    /**
     * Update daily streak
     */
    private function updateStreak($userId) {
        $stmt = $this->pdo->prepare("SELECT last_activity, streak FROM user_stats WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data = $stmt->fetch();

        $today = date('Y-m-d');
        $lastActivity = $data['last_activity'];
        $streak = $data['streak'];

        if ($lastActivity === $today) {
            // Already active today, do nothing
            return;
        }

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ($lastActivity === $yesterday) {
            $streak++;
        } else {
            $streak = 1; // Reset streak
        }

        $stmt = $this->pdo->prepare("UPDATE user_stats SET streak = ?, last_activity = ? WHERE user_id = ?");
        $stmt->execute([$streak, $today, $userId]);
    }

    /**
     * Admin: Get all users with stats
     */
    public function getUsersList() {
        $stmt = $this->pdo->query("
            SELECT u.id, u.username, u.role, s.points, s.level 
            FROM users u 
            JOIN user_stats s ON u.id = s.user_id 
            ORDER BY s.points DESC
        ");
        return $stmt->fetchAll();
    }
}
