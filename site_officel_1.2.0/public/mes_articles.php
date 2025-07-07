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
   <link rel="stylesheet" href="../assets/css/mes_articles.css">
</head>
<body>
  <nav>
  <a href="index.php" class="logo">NEXORA</a>
  <div class="user-links">
  <?php if (isset($_SESSION['user_id'])): ?>
    <a href="create_article.php">je post</a> |
    <a href="mes_articles.php">mes post</a> |
    <a href="profile.php">profil</a> |
    <a href="../admin_system_files/auth/login.php">admin login</a> |
    <a href="logout.php">Déconnexion</a>
  <?php else: ?>
    <a href="login.php">Se connecter</a> |
    <a href="register.php">S'inscrire</a>
  <?php endif; ?>
  </div>
</nav>
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
