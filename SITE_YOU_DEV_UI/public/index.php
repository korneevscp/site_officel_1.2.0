<?php
session_start();
require_once '../includes/db.php';

// Récupérer les articles avec auteur
$stmt = $pdo->query("
    SELECT a.id, a.title, a.content, a.created_at, u.username, u.avatar, u.description
    FROM articles a
    JOIN users u ON a.author_id = u.id
    ORDER BY a.created_at DESC
");

$articles = $stmt->fetchAll();

function excerpt_html($html, $max_length = 200) {
    $text = strip_tags($html);

    if (mb_strlen($text) <= $max_length) {
        return $html;
    }

    $truncated_text = mb_substr($text, 0, $max_length) . '...';
    return htmlspecialchars($truncated_text);
}

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
      <a href="create_article.php">je post</a> |
      <a href="mes_articles.php">mes post</a> |
      <a href="profile.php">profil</a> |
      <a href="../admin/login.php">admin login</a> |
      <a href="logout.php">Déconnexion</a> |
    <?php else: ?>
      <a href="login.php">Se connecter</a> |
      <a href="register.php">S'inscrire</a> |
    <?php endif; ?>
  </div>
</header>

<?php if (count($articles) === 0): ?>
  <p>Aucun article trouvé.</p>
<?php else: ?>
  <?php foreach ($articles as $article): ?>
    <article>
      <h2><a href="article.php?id=<?= $article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
      <p><small>Par <?= htmlspecialchars($article['username']) ?> | <?= time_elapsed_string($article['created_at']) ?></small></p>
      <p><?= excerpt_html($article['content']) ?></p>
      <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #444; display:flex; align-items:center;">
         <?php if ($article['avatar']): ?>
           <img src="../uploads/avatars/<?= htmlspecialchars($article['avatar']) ?>" alt="Avatar de <?= htmlspecialchars($article['username']) ?>" style="width:50px; height:50px; border-radius:50%; margin-right:1rem;" />
         <?php else: ?>
           <div style="width:50px; height:50px; border-radius:50%; background:#555; margin-right:1rem; display:flex; align-items:center; justify-content:center; color:#999;">N/A</div>
         <?php endif; ?>
          <div>
             <strong><?= htmlspecialchars($article['username']) ?></strong><br />
             <small style="font-style: italic; font-size:0.9rem;"><?= nl2br(htmlspecialchars($article['description'])) ?></small>
          </div>
        </div>
    </article>
  <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
