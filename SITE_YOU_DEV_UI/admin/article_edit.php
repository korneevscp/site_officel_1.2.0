<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) exit;

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$id]);
$article = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ? WHERE id = ?");
    $stmt->execute([$_POST['title'], $_POST['content'], $id]);
    header('Location: dashboard.php');
    exit;
}
?>
<form method="POST">
  <input name="title" value="<?= htmlspecialchars($article['title']) ?>" required>
  <textarea name="content" required><?= htmlspecialchars($article['content']) ?></textarea>
  <button>Enregistrer</button>
</form>

