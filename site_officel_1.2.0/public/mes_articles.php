<?php
session_start();
require_once '../includes/db.php';
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$author_id = $_SESSION['user_id'];

// Récupération des articles de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT * FROM articles WHERE author_id = ? ORDER BY created_at DESC");
$stmt->execute([$author_id]);
$articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mes Articles</title>
  <style>
    .article-card {
      border: 1px solid #444;
      padding: 10px;
      margin-bottom: 15px;
      background-color: #222;
      color: #eee;
    }
    button {
      margin-right: 10px;
    }
  </style>
</head>
<body>
  <h1>Mes Articles</h1>

  <?php foreach ($articles as $article): ?>
    <div class="article-card">
      <h2><?= htmlspecialchars($article['title']) ?></h2>
      <p>Publié le <?= date('d/m/Y H:i', strtotime($article['created_at'])) ?></p>
      <div><?= $article['content'] ?></div>

      <!-- Bouton Modifier -->
      <form method="POST" action="edit_article.php" style="display:inline;">
        <input type="hidden" name="edit_id" value="<?= $article['id'] ?>">
        <button type="submit">✏️ Modifier</button>
      </form>

      <!-- Bouton Supprimer -->
      <form method="POST" action="delete_article.php" onsubmit="return confirm('Supprimer cet article ?');" style="display:inline;">
        <input type="hidden" name="delete_id" value="<?= $article['id'] ?>">
        <button type="submit">🗑️ Supprimer</button>
      </form>
    </div>
  <?php endforeach; ?>

</body>
</html>
