<?php
require_once __DIR__ . '/../includes/config.php';

class DashboardController {
    private $pdo;

    public function getCourses($userId) {
        $stmt = $this->pdo->prepare("
            SELECT c.*, 
            (SELECT COUNT(*) FROM lessons WHERE course_id = c.id) as total_lessons,
            (SELECT COUNT(*) FROM user_progress up 
             JOIN lessons l ON up.lesson_id = l.id 
             WHERE l.course_id = c.id AND up.user_id = ? AND up.status = 'completed') as completed_lessons
            FROM courses c
            ORDER BY c.order_index ASC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

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

    public function getCurrentLesson($userId) {
        $stmt = $this->pdo->prepare("
            SELECT l.*, up.status, up.score 
            FROM lessons l
            LEFT JOIN user_progress up ON l.id = up.lesson_id AND up.user_id = ?
            WHERE up.status = 'available' OR up.status IS NULL
            ORDER BY l.order_index ASC
            LIMIT 1
        ");
        $stmt->execute([$userId]);
        $current = $stmt->fetch();
        
        if (!$current) {
            $stmt = $this->pdo->prepare("
                SELECT l.*, up.status, up.score 
                FROM lessons l
                LEFT JOIN user_progress up ON l.id = up.lesson_id AND up.user_id = ?
                WHERE up.status = 'completed'
                ORDER BY l.order_index DESC
                LIMIT 1
            ");
            $stmt->execute([$userId]);
            $current = $stmt->fetch();
        }
        
        return $current;
    }

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
            $dateLabel = 'Pendiente';
            if ($row['completed_at']) {
                $dt  = new DateTime($row['completed_at']);
                $now = new DateTime();
                $diff = $now->diff($dt);
                if ($diff->days === 0) {
                    $dateLabel = 'Hoy';
                } elseif ($diff->days === 1) {
                    $dateLabel = 'Ayer';
                } elseif ($diff->days < 7) {
                    $dateLabel = 'Hace ' . $diff->days . ' días';
                } else {
                    $dateLabel = $dt->format('d/m/Y');
                }
            }

            $xpLabel = $row['status'] === 'completed' ? '+' . $row['xp_reward'] . ' XP' : 'En progreso';
            
            $activities[] = [
                'type' => $row['status'] === 'completed' ? 'lesson_completed' : 'in_progress',
                'title' => $row['title'],
                'date' => $dateLabel,
                'xp' => $xpLabel
            ];
        }

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

    public function completeLesson($userId, $lessonId) {
        $pointsPerLesson = 100;

        $stmt = $this->pdo->prepare("SELECT user_id FROM user_progress WHERE user_id = ? AND lesson_id = ? AND status = 'completed'");
        $stmt->execute([$userId, $lessonId]);
        if ($stmt->fetch()) return false;

        $stmt = $this->pdo->prepare("
            INSERT INTO user_progress (user_id, lesson_id, status, completed_at) 
            VALUES (?, ?, 'completed', NOW())
            ON DUPLICATE KEY UPDATE status = 'completed', completed_at = NOW()
        ");
        $stmt->execute([$userId, $lessonId]);

        $this->unlockNextLesson($userId, $lessonId);
        $this->addXP($userId, $pointsPerLesson);
        $this->updateStreak($userId);
        
        return true;
    }

    private function unlockNextLesson($userId, $completedLessonId) {
        $stmt = $this->pdo->prepare("SELECT order_index, course_id FROM lessons WHERE id = ?");
        $stmt->execute([$completedLessonId]);
        $row = $stmt->fetch();
        $currentOrder = $row['order_index'];
        $courseId = $row['course_id'];

        // Unlock next lesson in SAME course
        $stmt = $this->pdo->prepare("SELECT id FROM lessons WHERE course_id = ? AND order_index > ? ORDER BY order_index ASC LIMIT 1");
        $stmt->execute([$courseId, $currentOrder]);
        $nextLessonId = $stmt->fetchColumn();

        // If no more lessons in current course, unlock first lesson of NEXT course
        if (!$nextLessonId) {
            $stmt = $this->pdo->prepare("SELECT order_index FROM courses WHERE id = ?");
            $stmt->execute([$courseId]);
            $currentCourseOrder = $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("SELECT id FROM courses WHERE order_index > ? ORDER BY order_index ASC LIMIT 1");
            $stmt->execute([$currentCourseOrder]);
            $nextCourseId = $stmt->fetchColumn();

            if ($nextCourseId) {
                $stmt = $this->pdo->prepare("SELECT id FROM lessons WHERE course_id = ? ORDER BY order_index ASC LIMIT 1");
                $stmt->execute([$nextCourseId]);
                $nextLessonId = $stmt->fetchColumn();
            }
        }

        if ($nextLessonId) {
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

    public function addXP($userId, $amount) {
        $stmt = $this->pdo->prepare("UPDATE user_stats SET points = points + ? WHERE user_id = ?");
        $stmt->execute([$amount, $userId]);

        $stmt = $this->pdo->prepare("SELECT points FROM user_stats WHERE user_id = ?");
        $stmt->execute([$userId]);
        $xp = $stmt->fetchColumn();
        $newLevel = floor($xp / 1000) + 1;

        $stmt = $this->pdo->prepare("UPDATE user_stats SET level = ? WHERE user_id = ?");
        $stmt->execute([$newLevel, $userId]);
    }

    private function updateStreak($userId) {
        $stmt = $this->pdo->prepare("SELECT last_activity, streak FROM user_stats WHERE user_id = ?");
        $stmt->execute([$userId]);
        $data = $stmt->fetch();

        $today = date('Y-m-d');
        $lastActivity = $data['last_activity'];
        $streak = $data['streak'];

        if ($lastActivity === $today) {
            return;
        }

        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ($lastActivity === $yesterday) {
            $streak++;
        } else {
            $streak = 1;
        }

        $stmt = $this->pdo->prepare("UPDATE user_stats SET streak = ?, last_activity = ? WHERE user_id = ?");
        $stmt->execute([$streak, $today, $userId]);
    }

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
