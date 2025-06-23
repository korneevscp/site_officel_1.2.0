<?php
session_start();
require_once '../includes/db.php';

$stmt = $pdo->query("
  SELECT a.id, a.title, a.content, a.created_at, a.author_id, u.username, u.avatar, u.description
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
  <title>Accueil - NEXORA</title>
  <link rel="icon" type="image/png" href="../assets/images/logo.jp" />
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
  nav {
    position: sticky;
    top: 0;
    background: #1e1e1e;
    border-bottom: 1px solid #333;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    z-index: 999;
  }
  nav .logo {
    color: #66aaff;
    font-weight: 700;
    font-size: 1.4rem;
    text-decoration: none;
  }
  nav .user-links a {
    color: #aaa;
    margin-left: 1rem;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
  }
  nav .user-links a:hover {
    color: #66aaff;
  }
  a {
    color: #66aaff;
    text-decoration: none;
  }
  a:hover {
    text-decoration: underline;
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
  article .author-info {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #444;
    display: flex;
    align-items: center;
  }
  article .author-info img,
  article .author-info .avatar-fallback {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    margin-right: 1rem;
    object-fit: cover;
    border: 2px solid #66aaff;
    flex-shrink: 0;
  }
  article .author-info .avatar-fallback {
    background: #555;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-weight: 600;
    font-size: 1.1rem;
  }
  article .author-info div {
    line-height: 1.2;
  }
  /* Likes & comments */
  .like-form {
    margin-top: 1rem;
  }
  .like-button {
    background: none;
    border: none;
    color: #66aaff;
    font-weight: 600;
    cursor: pointer;
    font-size: 1rem;
    padding: 0;
  }
  .like-button:hover {
    text-decoration: underline;
  }
  .comments {
    margin-top: 1rem;
    border-top: 1px solid #444;
    padding-top: 1rem;
  }
  .comments p {
    margin: 0.3rem 0;
  }
  .comments strong {
    color: #99ccff;
  }
  form.comment-form {
    margin-top: 1rem;
  }
  form.comment-form textarea {
    width: 100%;
    background-color: #222;
    border: none;
    color: #eee;
    padding: 0.5rem;
    border-radius: 4px;
    resize: vertical;
    font-family: inherit;
    font-size: 1rem;
  }
  form.comment-form button {
    background-color: #66aaff;
    border: none;
    color: #121212;
    padding: 0.5rem 1rem;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    margin-top: 0.5rem;
    transition: background-color 0.2s;
  }
  form.comment-form button:hover {
    background-color: #5599dd;
  }
  </style>
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

<?php if (count($articles) === 0): ?>
  <p>Aucun article trouvé.</p>
<?php else: ?>
  <?php foreach ($articles as $article): ?>
  <?php
    // Likes count
    $stmtLikes = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmtLikes->execute([$article['id']]);
    $likesCount = $stmtLikes->fetchColumn();

    // Check if user liked this article
    $userLiked = false;
    if (isset($_SESSION['user_id'])) {
      $stmtUserLike = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND user_id = ?");
      $stmtUserLike->execute([$article['id'], $_SESSION['user_id']]);
      $userLiked = $stmtUserLike->fetchColumn() > 0;
    }

    // Fetch comments with avatar
    $stmtComments = $pdo->prepare("
      SELECT c.*, u.username, u.avatar 
      FROM comments c 
      JOIN users u ON c.user_id = u.id 
      WHERE c.post_id = ? 
      ORDER BY c.created_at DESC
    ");
    $stmtComments->execute([$article['id']]);
    $comments = $stmtComments->fetchAll();
  ?>
  <article>
    <h2><a href="article.php?id=<?= $article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
    <p><small>Par <?= htmlspecialchars($article['username']) ?> | <?= time_elapsed_string($article['created_at']) ?></small></p>
    <p><?= excerpt_html($article['content']) ?></p>

    <div class="author-info">
      <?php if ($article['avatar'] && file_exists('../uploads/avatars/' . $article['avatar'])): ?>
        <img src="../uploads/avatars/<?= htmlspecialchars($article['avatar']) ?>" alt="Avatar de <?= htmlspecialchars($article['username']) ?>" />
      <?php else: ?>
        <div class="avatar-fallback" title="Pas d'avatar">N/A</div>
      <?php endif; ?>
      <div>
        <strong><?= htmlspecialchars($article['username']) ?></strong><br />
        <small style="font-style: italic; font-size:0.9rem;"><?= nl2br(htmlspecialchars($article['description'])) ?></small>
      </div>
    </div>

    <form class="like-form" method="post" action="like.php">
      <input type="hidden" name="post_id" value="<?= $article['id'] ?>">
      <?php if (isset($_SESSION['user_id'])): ?>
        <button class="like-button" type="submit"><?= $userLiked ? '💙 Je n\'aime plus' : '🤍 J\'aime' ?></button>
      <?php else: ?>
        <em><a href="login.php">Connecte-toi pour liker</a></em>
      <?php endif; ?>
      (<?= $likesCount ?>)
    </form>

    <div class="comments">
      <h4>Commentaires (<?= count($comments) ?>)</h4>
      <?php if (count($comments) === 0): ?>
        <p>Aucun commentaire.</p>
      <?php else: ?>
        <?php foreach ($comments as $comment): ?>
          <div style="display:flex; align-items:center; margin-bottom:0.5rem;">
            <?php if ($comment['avatar'] && file_exists('../uploads/avatars/' . $comment['avatar'])): ?>
              <img src="../uploads/avatars/<?= htmlspecialchars($comment['avatar']) ?>" alt="Avatar de <?= htmlspecialchars($comment['username']) ?>" style="width:30px; height:30px; border-radius:50%; margin-right:0.5rem; object-fit:cover;">
            <?php else: ?>
              <div style="width:30px; height:30px; border-radius:50%; background:#555; color:#999; font-weight:600; display:flex; align-items:center; justify-content:center; margin-right:0.5rem;">N/A</div>
            <?php endif; ?>
            <p style="margin:0;">
              <strong><?= htmlspecialchars($comment['username']) ?></strong> : <?= nl2br(htmlspecialchars($comment['content'])) ?> 
              <small>(<?= time_elapsed_string($comment['created_at']) ?>)</small>
            </p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
    <form class="comment-form" method="post" action="comment.php">
      <input type="hidden" name="post_id" value="<?= $article['id'] ?>">
      <textarea name="content" rows="2" placeholder="Ajouter un commentaire..." required></textarea>
      <button type="submit">Envoyer</button>
    </form>
    <?php else: ?>
      <p><em><a href="login.php">Connecte-toi pour commenter</a></em></p>
    <?php endif; ?>

  </article>
  <?php endforeach; ?>
<?php endif; ?>
<script src="../assets/js/actu.js"></script>
</body>
</html>
