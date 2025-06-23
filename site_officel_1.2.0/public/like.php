<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
  header('Location: index.php');
  exit;
}

$userId = $_SESSION['user_id'];
$postId = (int)$_POST['post_id'];

// Vérifie si déjà liké
$stmt = $pdo->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$postId, $userId]);

if ($stmt->rowCount() > 0) {
  // Supprimer le like
  $pdo->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?")->execute([$postId, $userId]);
} else {
  // Ajouter un like
  $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)")->execute([$postId, $userId]);
}

header('Location: index.php');
exit;
