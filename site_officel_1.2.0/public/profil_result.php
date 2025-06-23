<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

$host = 'localhost';
$dbname = 'trdcrft';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
if ($user_id <= 0) {
    die("Utilisateur invalide.");
}

$current_user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// Récupère l'utilisateur
$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

if (!$user) {
    die("Utilisateur introuvable.");
}

// Récupère ses articles (posts)
$stmtPosts = $pdo->prepare("SELECT * FROM articles WHERE author_id = ? ORDER BY created_at DESC");
$stmtPosts->execute([$user_id]);
$posts = $stmtPosts->fetchAll();

// Ajout ami
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_friend'])) {
    if ($current_user_id && $current_user_id !== $user_id) {
        $check = $pdo->prepare("SELECT * FROM friend_requests WHERE sender_id = ? AND receiver_id = ?");
        $check->execute([$current_user_id, $user_id]);
        if ($check->rowCount() === 0) {
            $insert = $pdo->prepare("INSERT INTO friend_requests (sender_id, receiver_id) VALUES (?, ?)");
            $insert->execute([$current_user_id, $user_id]);
            echo "<script>alert('Demande d\'ami envoyée.');</script>";
        } else {
            echo "<script>alert('Demande déjà envoyée.');</script>";
        }
    }
}

// Signalement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_user'])) {
    $stmtReport = $pdo->prepare("INSERT INTO reports (reported_user_id, report_text, report_date) VALUES (?, ?, NOW())");
    $stmtReport->execute([$user_id, "Signalement via profil"]);
    echo "<script>alert('Utilisateur signalé.');</script>";
}
?>

<!DOCTYPE html>
<html lang="fr">
    <link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
<head>
    <meta charset="UTF-8">
    <title>Profil de <?php echo htmlspecialchars($user['username']); ?></title>
    <style>
        body { font-family: sans-serif; background: #f9f9f9; padding: 20px; }
        .profile { background: white; padding: 20px; border-radius: 10px; max-width: 700px; margin: auto; }
        .avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; }
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px; }
        .btn-friend { background-color: #28a745; color: white; }
        .btn-report { background-color: #dc3545; color: white; }
        .post { margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px; }
        .post-title { font-weight: bold; font-size: 18px; margin-bottom: 5px; }
        .post-date { font-size: 12px; color: #666; margin-bottom: 10px; }
    </style>
</head>
<body>

<div class="profile">
    <img class="avatar" src="<?php echo $user['avatar'] ? htmlspecialchars($user['avatar']) : 'default-avatar.png'; ?>" alt="Avatar">
    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
    <p><strong>Bio :</strong> <?php echo nl2br(htmlspecialchars($user['description'])); ?></p>

    <?php if ($current_user_id !== $user_id && $current_user_id != 0): ?>
        <form method="post" style="display:inline;">
            <button type="submit" name="add_friend" class="btn btn-friend">Ajouter comme ami</button>
        </form>
        <form method="post" style="display:inline;">
            <button type="submit" name="report_user" class="btn btn-report">Signaler</button>
        </form>
    <?php endif; ?>

    <h3>Articles publiés</h3>
    <?php if (count($posts) > 0): ?>
        <?php foreach ($posts as $post): ?>
            <div class="post">
                <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>
                <div class="post-date">Publié le <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?></div>
                <div><?php echo nl2br(htmlspecialchars(substr($post['content'], 0, 100))) . '...'; ?></div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Aucun article publié.</p>
    <?php endif; ?>
</div>

</body>
</html>
