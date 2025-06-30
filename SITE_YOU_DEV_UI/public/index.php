<?php
// Démarre la session PHP pour gérer les utilisateurs connectés
session_start();

// Inclut le fichier de connexion à la base de données
require_once '../includes/db.php';

// Récupère les articles avec les informations de l'auteur depuis la base de données
$stmt = $pdo->query("
  SELECT a.id, a.title, a.content, a.created_at, u.username, u.avatar, u.description
  FROM articles a
  JOIN users u ON a.author_id = u.id
  ORDER BY a.created_at DESC
");

// Stocke tous les articles récupérés dans un tableau
$articles = $stmt->fetchAll();

// Fonction pour générer un extrait du contenu HTML (limité à $max_length caractères)
function excerpt_html($html, $max_length = 200) {
  $text = strip_tags($html);

  if (mb_strlen($text) <= $max_length) {
    return $html;
  }

  $truncated_text = mb_substr($text, 0, $max_length) . '...';
  return htmlspecialchars($truncated_text);
}

// Fonction pour afficher le temps écoulé depuis une date donnée (ex: "il y a 2 jours")
function time_elapsed_string($datetime, $full = false) {
  $now = new DateTime;
  $ago = new DateTime($datetime);
  $diff = $now->diff($ago);

  $diff->w = floor($diff->d / 7);
  $diff->d -= $diff->w * 7;

  $string = [
    'y' => 'an',
    'm' => 'mois',
    'w' => 'semaine',
    'd' => 'jour',
    'h' => 'heure',
    'i' => 'minute',
    's' => 'seconde',
  ];

  foreach ($string as $k => &$v) {
    if ($diff->$k) {
      $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
    } else {
      unset($string[$k]);
    }
  }

  if (!$full) $string = array_slice($string, 0, 1);

  if ($string) {
    return 'il y a ' . implode(', ', $string);
  } else {
    return 'à l\'instant';
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title> Accueil - NEXORA </title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png" />
 <!-- Lien vers le fichier CSS -->
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
<header>
  <h1>NEXORA - Articles récents</h1>
<div class="user-links">
  <?php if (isset($_SESSION['user_id'])): ?>
    <a href="create_article.php">Créer un article</a>
    <a href="mes_articles.php">Mes articles</a>
    <a href="profile.php">Profil</a>
    <a href="../admin/login.php">Admin</a>
    <a href="logout.php">Déconnexion</a>
  <?php else: ?>
    <a href="login.php">Se connecter</a>
    <a href="register.php">S'inscrire</a>
  <?php endif; ?>
</div>
</header>

<main class="posts-container">
  <?php if (count($articles) === 0): ?>
    <p>Aucun article trouvé.</p>
  <?php else: ?>
    <?php foreach ($articles as $article): ?>
      <article>
        <h2><a href="article.php?id=<?= $article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
        <p><small>Par <?= htmlspecialchars($article['username']) ?> | <?= time_elapsed_string($article['created_at']) ?></small></p>
        <p><?= excerpt_html($article['content']) ?></p>
        <div class="author">
          <?php if ($article['avatar']): ?>
            <img src="../uploads/avatars/<?= htmlspecialchars($article['avatar']) ?>" alt="Avatar de <?= htmlspecialchars($article['username']) ?>" />
          <?php else: ?>
            <div class="default-avatar">N/A</div>
          <?php endif; ?>
          <div class="info">
            <strong><?= htmlspecialchars($article['username']) ?></strong>
            <small><?= nl2br(htmlspecialchars($article['description'])) ?></small>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<footer>
  <p>&copy; <?= date('Y') ?> NEXORA. Tous droits réservés.</p>
</footer>

</body>
</html>