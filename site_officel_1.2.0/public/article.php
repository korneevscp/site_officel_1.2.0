<?php
// Inclusion du fichier de connexion à la base de données
require_once '../includes/db.php';

// Récupère l'identifiant de l'article depuis l'URL (GET), ou null si absent
$id = $_GET['id'] ?? null;
// Si aucun identifiant n'est fourni, affiche un message d'erreur et arrête le script
if (!$id) die("Article non trouvé");

// Prépare la requête SQL pour récupérer les informations de l'article et de son auteur
$stmt = $pdo->prepare("
  SELECT a.title, a.content, a.created_at, u.username 
  FROM articles a 
  JOIN users u ON a.author_id = u.id 
  WHERE a.id = ?
");
// Exécute la requête avec l'identifiant de l'article
$stmt->execute([$id]);
// Récupère le résultat sous forme de tableau associatif
$article = $stmt->fetch();
// Si aucun article n'est trouvé, affiche un message d'erreur et arrête le script
if (!$article) die("Article introuvable");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <!-- Affiche le titre de l'article dans la balise title -->
  <title><?= htmlspecialchars($article['title']) ?> - NEXORA </title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png" />
  <style>
  body { font-family: Arial; background: #111; color: #eee; padding: 2rem; }
  a { color: #66f; }
  </style>
</head>
<body>
  <!-- Affiche le titre de l'article -->
  <h1><?= htmlspecialchars($article['title']) ?></h1>
  <!-- Affiche le nom de l'auteur et la date de création -->
  <p><small>Par <?= htmlspecialchars($article['username']) ?> | <?= htmlspecialchars($article['created_at']) ?></small></p>
  <!-- Affiche le contenu de l'article, en conservant les sauts de ligne -->
  <div><?= nl2br(htmlspecialchars($article['content'])) ?></div>
  <!-- Lien pour revenir à la page d'accueil -->
  <p><a href="index.php">← Retour</a></p>
</body>
</html>
