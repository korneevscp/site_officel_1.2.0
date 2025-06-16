<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM articles ORDER BY created_at DESC");
$articles = $stmt->fetchAll();
?>
<h1>Admin - Articles</h1>
<a href="logout.php">Déconnexion</a> | <a href="article_add.php">Ajouter un article</a>
<ul>
<?php foreach ($articles as $a): ?>
  <li>
    <strong><?= htmlspecialchars($a['title']) ?></strong>
    [<a href="article_edit.php?id=<?= $a['id'] ?>">Modifier</a>] 
    [<a href="article_delete.php?id=<?= $a['id'] ?>" onclick="return confirm('Supprimer ?')">Supprimer</a>]
  </li>
<?php endforeach; ?>
</ul>
