<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) exit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO articles (title, content) VALUES (?, ?)");
    $stmt->execute([$_POST['title'], $_POST['content']]);
    header('Location: dashboard.php');
    exit;
}
?>
<form method="POST">
  <input name="title" placeholder="Titre" required>
  <textarea name="content" placeholder="Contenu" required></textarea>
  <button>Publier</button>
</form>
