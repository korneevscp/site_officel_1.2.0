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
  <style>
  /* Styles CSS pour la mise en page et l'apparence */
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #111;
    color: #eee;
    margin: 0;
    padding: 2rem;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
  }
  header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
  }
  a {
    color: #66aaff;
    text-decoration: none;
  }
  a:hover {
    text-decoration: underline;
  }
  h1 {
    margin: 0;
    color: #66aaff;
  }
  article {
    border-bottom: 1px solid #333;
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
  }
  article h2 {
    margin: 0 0 0.2rem 0;
  }
  article small {
    color: #999;
  }
  .user-links a {
    margin-left: 1rem;
  }
  </style>
</head>
<body>

<header>
  <h1>NEXORA - Articles récents</h1>
  <div class="user-links">
  <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Liens affichés si l'utilisateur est connecté -->
    <a href="create_article.php">je post</a> |
    <a href="mes_articles.php">mes post</a> |
    <a href="profile.php">profil</a> |
    <a href="../admin/login.php">admin login</a> |
    <a href="logout.php">Déconnexion</a> |
  <?php else: ?>
    <!-- Liens affichés si l'utilisateur n'est pas connecté -->
    <a href="login.php">Se connecter</a> |
    <a href="register.php">S'inscrire</a> |
  <?php endif; ?>
  </div>
</header>

<?php if (count($articles) === 0): ?>
  <!-- Message si aucun article n'est trouvé -->
  <p>Aucun article trouvé.</p>
<?php else: ?>
  <!-- Boucle sur chaque article pour l'afficher -->
  <?php foreach ($articles as $article): ?>
  <article>
    <!-- Titre de l'article avec lien vers la page de l'article -->
    <h2><a href="article.php?id=<?= $article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
    <!-- Informations sur l'auteur et la date -->
    <p><small>Par <?= htmlspecialchars($article['username']) ?> | <?= time_elapsed_string($article['created_at']) ?></small></p>
    <!-- Extrait du contenu de l'article -->
    <p><?= excerpt_html($article['content']) ?></p>
    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #444; display:flex; align-items:center;">
     <?php if ($article['avatar']): ?>
       <!-- Affiche l'avatar de l'auteur si disponible -->
       <img src="../uploads/avatars/<?= htmlspecialchars($article['avatar']) ?>" alt="Avatar de <?= htmlspecialchars($article['username']) ?>" style="width:50px; height:50px; border-radius:50%; margin-right:1rem;" />
     <?php else: ?>
       <!-- Affiche un avatar par défaut si non disponible -->
       <div style="width:50px; height:50px; border-radius:50%; background:#555; margin-right:1rem; display:flex; align-items:center; justify-content:center; color:#999;">N/A</div>
     <?php endif; ?>
      <div>
       <!-- Nom d'utilisateur et description de l'auteur -->
       <strong><?= htmlspecialchars($article['username']) ?></strong><br />
       <small style="font-style: italic; font-size:0.9rem;"><?= nl2br(htmlspecialchars($article['description'])) ?></small>
      </div>
    </div>
  </article>
  <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
