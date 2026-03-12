<?php
session_start();
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$lessonId = (int)($_POST['lesson_id'] ?? 0);
$userId   = (int)$_SESSION['user_id'];

if ($lessonId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid lesson ID']);
    exit;
}

$stmt = $pdo->prepare("SELECT status FROM user_progress WHERE user_id = ? AND lesson_id = ?");
$stmt->execute([$userId, $lessonId]);
$progress = $stmt->fetch();

if (!$progress || !in_array($progress['status'], ['available', 'completed'])) {
    echo json_encode(['success' => false, 'message' => 'Lesson not available']);
    exit;
}

if ($progress['status'] !== 'completed') {
    $stmt = $pdo->prepare("UPDATE user_progress SET status = 'completed', completed_at = NOW() WHERE user_id = ? AND lesson_id = ?");
    $stmt->execute([$userId, $lessonId]);

    // Use DashboardController logic for unlocking next lesson
    require_once '../controllers/DashboardController.php';
    $dash = new DashboardController($pdo);
    
    // The unlock logic is private in DashboardController, but we can replicate the query here 
    // to find the next lesson in course order.
    
    $stmt = $pdo->prepare("SELECT order_index, course_id FROM lessons WHERE id = ?");
    $stmt->execute([$lessonId]);
    $row = $stmt->fetch();
    $currentOrder = $row['order_index'];
    $courseId = $row['course_id'];

    // Find next in same course
    $stmt = $pdo->prepare("SELECT id FROM lessons WHERE course_id = ? AND order_index > ? ORDER BY order_index ASC LIMIT 1");
    $stmt->execute([$courseId, $currentOrder]);
    $nextLessonId = $stmt->fetchColumn();

    // If course finished, find next course
    if (!$nextLessonId) {
        $stmt = $pdo->prepare("SELECT order_index FROM courses WHERE id = ?");
        $stmt->execute([$courseId]);
        $courseOrder = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT id FROM courses WHERE order_index > ? ORDER BY order_index ASC LIMIT 1");
        $stmt->execute([$courseOrder]);
        $nextCourseId = $stmt->fetchColumn();

        if ($nextCourseId) {
            $stmt = $pdo->prepare("SELECT id FROM lessons WHERE course_id = ? ORDER BY order_index ASC LIMIT 1");
            $stmt->execute([$nextCourseId]);
            $nextLessonId = $stmt->fetchColumn();
        }
    }

    if ($nextLessonId) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO user_progress (user_id, lesson_id, status) VALUES (?, ?, 'available')");
        $stmt->execute([$userId, $nextLessonId]);
        
        $stmt = $pdo->prepare("UPDATE user_progress SET status = 'available' WHERE user_id = ? AND lesson_id = ? AND status = 'locked'");
        $stmt->execute([$userId, $nextLessonId]);
    }

    $stmt = $pdo->prepare("UPDATE user_stats SET points = points + 100 WHERE user_id = ?");
    $stmt->execute([$userId]);
}

echo json_encode(['success' => true]);
