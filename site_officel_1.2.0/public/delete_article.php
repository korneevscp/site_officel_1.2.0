<?php
require_once '../includes/db.php';
session_start();

if (isset($_POST['delete_id'])) {
  $id = (int) $_POST['delete_id'];

  // Vérifie que l'article appartient à l'utilisateur
  $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ? AND author_id = ?");
  $stmt->execute([$id, $_SESSION['user_id']]);

  header("Location: index.php");
  exit;
}
 else {
  header("Location: index.php");
  exit;
}