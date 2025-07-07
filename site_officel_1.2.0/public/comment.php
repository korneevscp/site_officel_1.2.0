<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = isset($_POST['post_id']) ? $_POST['post_id'] : null;
    $userId = $_SESSION['user_id'];
    $comment = isset($_POST['content']) ? trim($_POST['content']) : '';

    if (!$postId || empty($comment)) {
        header('Location: index.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $userId, $comment]);

    header("Location: article.php?id=" . (int)$postId);
    exit;
}
