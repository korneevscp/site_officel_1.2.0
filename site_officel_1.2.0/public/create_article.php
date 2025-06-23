<?php
session_start();
require_once '../includes/db.php';

// Vérifie si utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$title = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim(isset($_POST['title']) ? $_POST['title'] : '');
    $content = trim(isset($_POST['content']) ? $_POST['content'] : '');

    if (!$title) {
        $error = "Le titre est obligatoire.";
    } elseif (!$content) {
        $error = "Le contenu est obligatoire.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO articles (title, content, author_id, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$title, $content, $_SESSION['user_id']]);
        $success = "Article créé avec succès.";
        // Reset form
        $title = '';
        $content = '';
         header('Location: index.php'); // Redirige vers la page d'accueil après création
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Créer un article - sur korneevscp</title>
     <link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
     <link rel="stylesheet" href="../assets/css/create_article.css"/>
    <script src="https://cdn.tiny.cloud/1/8evtsb6e56jf07xb5lj1pyiqxqm80vhnih1mdlc0op47kiav/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
      selector: '#content',
      menubar: false,
      plugins: 'lists link image preview',
      toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | preview',
      height: 300,
      Text: 'test'
    });
    </script>

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

<h1>Créer un article</h1>

<?php if ($error): ?>
  <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
  <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST" id="articleForm">
  <label for="title">Titre</label>
  <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" />

  <label for="content">Contenu</label>
  <textarea id="content" name="content" rows="8"><?= htmlspecialchars($content) ?></textarea>

  <button type="submit">Publier</button>
</form>

<script>
  document.getElementById('articleForm').addEventListener('submit', function(e) {
    const title = document.getElementById('title').value.trim();
    const content = tinymce.get('content').getContent({ format: 'text' }).trim();

    if (!title || !content) {
      e.preventDefault();
      alert('Veuillez remplir le titre et le contenu.');
    }
  });
</script>

</body>
</html>
