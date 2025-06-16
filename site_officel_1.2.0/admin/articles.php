<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

// Supprimer un article si demandé
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: articles.php');
    exit;
}

// Récupérer tous les articles avec leur auteur
$stmt = $pdo->query("
    SELECT a.id, a.title, a.created_at, u.username 
    FROM articles a 
    JOIN users u ON a.author_id = u.id
    ORDER BY a.created_at DESC
");
$articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Gestion Articles</title>
<style>
  body { font-family: Arial; padding: 2rem; background:#222; color:#eee; }
  table { width: 100%; border-collapse: collapse; }
  th, td { padding: 0.5rem; border-bottom: 1px solid #444; }
  a { color: #66f; text-decoration: none; }
  a.button { background: #444; padding: 0.3rem 0.6rem; border-radius: 3px; }
</style>
</head>
<body>
<h1>Gestion des articles</h1>
<p><a href="article_edit.php" class="button">Ajouter un nouvel article</a></p>

<table>
  <thead>
    <tr>
      <th>Titre</th>
      <th>Auteur</th>
      <th>Date création</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($articles as $art): ?>
    <tr>
      <td><?=htmlspecialchars($art['title'])?></td>
      <td><?=htmlspecialchars($art['username'])?></td>
      <td><?=htmlspecialchars($art['created_at'])?></td>
      <td>
        <a href="article_edit.php?id=<?= $art['id'] ?>">Modifier</a> | 
        <a href="articles.php?delete=<?= $art['id'] ?>" onclick="return confirm('Supprimer cet article ?')">Supprimer</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<p><a href="index.php">Retour au dashboard</a></p>
</body>
</html>
