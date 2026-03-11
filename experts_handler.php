<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $userId = $_SESSION['user_id'];
    $question = trim($_POST['question']);
    
    if (!empty($question)) {
        $stmt = $pdo->prepare("INSERT INTO expert_questions (user_id, question) VALUES (?, ?)");
        $stmt->execute([$userId, $question]);
        
        // Redirect back to dashboard with success message
        header('Location: dashboard.php?expert_success=1#experts');
        exit;
    }
}

header('Location: dashboard.php');
exit;
