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

    $stmt = $pdo->prepare("SELECT id FROM lessons WHERE id > ? ORDER BY id ASC LIMIT 1");
    $stmt->execute([$lessonId]);
    $nextLesson = $stmt->fetch();

    if ($nextLesson) {
        $stmt = $pdo->prepare("SELECT id FROM user_progress WHERE user_id = ? AND lesson_id = ?");
        $stmt->execute([$userId, $nextLesson['id']]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, lesson_id, status) VALUES (?, ?, 'available')");
            $stmt->execute([$userId, $nextLesson['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE user_progress SET status = 'available' WHERE user_id = ? AND lesson_id = ? AND status = 'locked'");
            $stmt->execute([$userId, $nextLesson['id']]);
        }
    }

    $stmt = $pdo->prepare("UPDATE user_stats SET points = points + 100 WHERE user_id = ?");
    $stmt->execute([$userId]);
}

echo json_encode(['success' => true]);
