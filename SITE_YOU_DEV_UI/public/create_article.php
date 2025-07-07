<?php
session_start(); // Démarre la session PHP
require_once '../includes/db.php'; // Inclut la connexion à la base de données

// Vérifie si utilisateur connecté
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php'); // Redirige vers la page de connexion si non connecté
  exit;
}

$error = '';
$success = '';
$title = '';
$content = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim(isset($_POST['title']) ? $_POST['title'] : ''); // Récupère et nettoie le titre
  $content = trim(isset($_POST['content']) ? $_POST['content'] : ''); // Récupère et nettoie le contenu

  // Vérifie si le titre est vide
  if (!$title) {
    $error = "Le titre est obligatoire.";
  } elseif (!$content) { // Vérifie si le contenu est vide
    $error = "Le contenu est obligatoire.";
  } else {
    // Prépare et exécute la requête d'insertion de l'article
    $stmt = $pdo->prepare("INSERT INTO articles (title, content, author_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$title, $content, $_SESSION['user_id']]);
    $success = "Article créé avec succès.";
    // Réinitialise le formulaire
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
  <title>Créer un article - NEXORA </title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png" />
  <link rel="stylesheet" href="../assets/css/create_article.css" />

  <!-- Intègre l'éditeur TinyMCE -->
  <script src="https://cdn.tiny.cloud/1/8evtsb6e56jf07xb5lj1pyiqxqm80vhnih1mdlc0op47kiav/tinymce/6/tinymce.min.js"
    referrerpolicy="origin"></script>
  <script>
    tinymce.init({
      selector: '#content',
      width: '100%',
      height: 250,           // hauteur totale de l’éditeur (px)
      skin: 'oxide-dark',
      content_css: 'dark',
      menubar: false,
      plugins: 'lists link image preview',
      toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | preview',
      content_style: "body { margin:0; padding:0.5rem; line-height:1.8; }"
    });
  </script>
</head>

<body>

  <header>
    <h1>NEXORA - créer un article</h1>
    <div class="user-links">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="index.php">Home</a>
        <a href="mes_articles.php">Mes articles</a>
        <a href="profile.php">Profil</a>
        <a href="logout.php">Déconnexion</a>
      <?php endif; ?>
    </div>
  </header>


  <?php if ($error): ?>
    <!-- Affiche un message d'erreur si besoin -->
    <p class="error"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>
  <?php if ($success): ?>
    <!-- Affiche un message de succès si besoin -->
    <p class="success"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <!-- Formulaire de création d'article -->
  <form method="POST" id="articleForm">
    <label for="title">Titre</label>
    <input type="text" id="title" name="title" value="<?= htmlspecialchars($title) ?>" />

    <label for="content">Contenu</label>
    <textarea id="content" name="content" rows="8"><?= htmlspecialchars($content) ?></textarea>

    <button type="submit">Publier</button>
  </form>

  <script>
    // Validation côté client avant soumission du formulaire
    document.getElementById('articleForm').addEventListener('submit', function (e) {
      const title = document.getElementById('title').value.trim();
      const content = tinymce.get('content').getContent({ format: 'text' }).trim();

      if (!title || !content) {
        e.preventDefault();
        alert('Veuillez remplir le titre et le contenu.');
      }
    });
  </script>

  <footer>
    <p>&copy; <?= date('Y') ?> NEXORA. Tous droits réservés.</p>
  </footer>

</body>

</html>