<?php
session_start();
require_once '../includes/db.php';

// Fonction d'affichage temps écoulé (optionnelle)
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
    return $string ? implode(', ', $string) . ' passé' : 'à l\'instant';
}

// Récupération des articles avec auteur
$stmt = $pdo->query("
    SELECT a.id, a.title, a.content, a.created_at, a.author_id, u.username, u.avatar, u.description
    FROM articles a
    JOIN users u ON a.author_id = u.id
    ORDER BY a.created_at DESC
");

$articles = $stmt->fetchAll();
if (!$articles) $articles = [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>TRDCRFT - Accueil</title>
    <link rel="stylesheet" href="../assets/css/index.css" />
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

<header>
    <h1>les Articles</h1>
</header>

<main>
<?php foreach ($articles as $article): ?>
<?php
    // Likes count
    $stmtLikes = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmtLikes->execute([$article['id']]);
    $likesCount = $stmtLikes->fetchColumn();

    // Check if user liked
    $userLiked = false;
    if (isset($_SESSION['user_id'])) {
        $stmtUserLike = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ? AND user_id = ?");
        $stmtUserLike->execute([$article['id'], $_SESSION['user_id']]);
        $userLiked = $stmtUserLike->fetchColumn() > 0;
    }

    // Récupération des commentaires
    $stmtComments = $pdo->prepare("
       SELECT c.*, u.username, u.avatar 
       FROM comments c 
       JOIN users u ON c.user_id = u.id 
       WHERE c.post_id = ? 
       ORDER BY c.created_at DESC
    ");
    $stmtComments->execute([$article['id']]);
    $comments = $stmtComments->fetchAll();

    $description = isset($article['description']) ? $article['description'] : '';
?>
<article class="post">
    <h2><a href="article.php?id=<?= (int)$article['id'] ?>"><?= htmlspecialchars($article['title']) ?></a></h2>
    <p class="meta">Par <?= htmlspecialchars($article['username']) ?> - <?= time_elapsed_string($article['created_at']) ?></p>

    <div class="article-content">
        <?= $article['content'] /* Contenu HTML TinyMCE, on n’applique pas htmlspecialchars ici */ ?>
    </div>

    <div class="author-info">
        <?php if (!empty($article['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $article['avatar'])): ?>
            <img src="../uploads/avatars/<?= rawurlencode($article['avatar']) ?>" alt="Avatar de <?= htmlspecialchars($article['username']) ?>" class="avatar" />
        <?php else: ?>
            <div class="avatar-fallback" title="Pas d'avatar">N/A</div>
        <?php endif; ?>
        <div class="author-desc"><?= nl2br(htmlspecialchars($description)) ?></div>
    </div>

    <form method="post" action="like.php" class="like-form">
        <input type="hidden" name="post_id" value="<?= (int)$article['id'] ?>" />
        <?php if (isset($_SESSION['user_id'])): ?>
            <button type="submit" class="like-button"><?= $userLiked ? '💙 Je n\'aime plus' : '🤍 J\'aime' ?></button>
        <?php else: ?>
            <em><a href="login.php">Connecte-toi pour liker</a></em>
        <?php endif; ?>
        <span class="likes-count">(<?= $likesCount ?>)</span>
    </form>

    <section class="comments">
        <h3>Commentaires (<?= count($comments) ?>)</h3>
        <?php if (count($comments) === 0): ?>
            <p>Aucun commentaire.</p>
        <?php else: ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <?php if (!empty($comment['avatar']) && file_exists(__DIR__ . '/../uploads/avatars/' . $comment['avatar'])): ?>
                        <img src="../uploads/avatars/<?= rawurlencode($comment['avatar']) ?>" alt="Avatar de <?= htmlspecialchars($comment['username']) ?>" class="comment-avatar" />
                    <?php else: ?>
                        <div class="avatar-fallback comment-avatar" title="Pas d'avatar">N/A</div>
                    <?php endif; ?>
                    <div>
                        <strong><?= htmlspecialchars($comment['username']) ?></strong> : <?= nl2br(htmlspecialchars($comment['comment'])) ?><br/>
                        <small><?= time_elapsed_string($comment['created_at']) ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post" action="comment.php" class="comment-form">
            <input type="hidden" name="post_id" value="<?= (int)$article['id'] ?>" />
            <textarea name="content" placeholder="Ajouter un commentaire..." required></textarea>
            <button type="submit">Envoyer</button>
        </form>
    <?php else: ?>
        <p><em><a href="login.php">Connecte-toi pour commenter</a></em></p>
    <?php endif; ?>
</article>
<hr>
<?php endforeach; ?>
</main>
<script src="../assets/js/actu.js"></script>
</body>
</html>
