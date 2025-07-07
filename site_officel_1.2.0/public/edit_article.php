<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$title = '';
$content = '';
$edit_id = 0;

// Préremplissage si edit_id dans l'URL
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);

    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND author_id = ?");
    $stmt->execute(array($edit_id, $_SESSION['user_id']));
    $article = $stmt->fetch();

    if ($article) {
        $title = $article['title'];
        $content = $article['content'];
    } else {
        $error = "Article introuvable ou accès refusé.";
    }
}

// Soumission du formulaire (mise à jour)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $content = isset($_POST['content']) ? trim($_POST['content']) : '';

    if (!$title) {
        $error = "Le titre est obligatoire.";
    } elseif (!$content) {
        $error = "Le contenu est obligatoire.";
    } elseif ($edit_id > 0) {
        $stmt = $pdo->prepare("UPDATE articles SET title = ?, content = ? WHERE id = ? AND author_id = ?");
        $stmt->execute(array($title, $content, $edit_id, $_SESSION['user_id']));

        if ($stmt->rowCount() > 0) {
            $success = "Article mis à jour avec succès.";
        } else {
            $error = "Aucune modification ou article introuvable.";
        }
    } else {
        $error = "ID d'article invalide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Modifier un article</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.jp" />

      <link rel="stylesheet" href="../assets/css/editarticle.css">

    <script src="https://cdn.tiny.cloud/1/8evtsb6e56jf07xb5lj1pyiqxqm80vhnih1mdlc0op47kiav/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
    tinymce.init({
        selector: '#content',
        menubar: false,
        plugins: 'lists link image preview',
        toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | preview',
        height: 300
    });
    </script>

</head>
<body>

<nav>
  <a href="index.php">Accueil</a>
  <a href="profile.php">Profil</a>
  <a href="logout.php">Déconnexion</a>
</nav>

<h1>Modifier un article</h1>

<?php if (!empty($error)): ?>
  <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
  <p class="success"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<form method="POST" id="articleForm">
  <input type="hidden" name="edit_id" value="<?php echo intval($edit_id); ?>" />

  <label for="title">Titre</label>
  <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($title); ?>" />

  <label for="content">Contenu</label>
  <textarea id="content" name="content" rows="8"><?php echo $content; ?></textarea>

  <button type="submit">Mettre à jour</button>
</form>

<script>
document.getElementById('articleForm').addEventListener('submit', function(e) {
  tinymce.triggerSave();

  var title = document.getElementById('title').value.trim();
  var content = document.getElementById('content').value.trim();

  if (!title || !content) {
    e.preventDefault();
    alert('Veuillez remplir le titre et le contenu.');
  }
});
</script>

</body>
</html>
