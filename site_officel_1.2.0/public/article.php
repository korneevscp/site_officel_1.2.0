<?php
require_once '../includes/db.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id) die("Article non trouvé");

$stmt = $pdo->prepare("
    SELECT a.title, a.content, a.created_at, u.username 
    FROM articles a 
    JOIN users u ON a.author_id = u.id 
    WHERE a.id = ?
");
$stmt->execute([$id]);
$article = $stmt->fetch();
if (!$article) die("Article introuvable");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($article['title']) ?> - TRDCRFT</title>
  <link rel="stylesheet" href="../assets/css/article.css" />
  
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #111;
      color: #eee;
      padding: 2rem;
    }
    a {
      color: #66f;
    }
  </style>
</head>
<body>
  <h1><?= htmlspecialchars($article['title']) ?></h1>
  <p><small>Par <?= htmlspecialchars($article['username']) ?> | <?= htmlspecialchars($article['created_at']) ?></small></p>

  <div class="article-content"><?= $article['content'] ?></div>

  <p><a href="index.php">← Retour</a></p>
</body>
</html>
