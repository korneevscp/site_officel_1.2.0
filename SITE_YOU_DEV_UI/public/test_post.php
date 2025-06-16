<?php
session_start(); // Démarre la session PHP
require_once '../includes/db.php'; // Inclut la connexion à la base de données

// Vérifie si utilisateur connecté
if (!isset($_SESSION['user_id'])) { // Si l'utilisateur n'est pas connecté
  header('Location: login.php'); // Redirige vers la page de connexion
  exit;
}

$error = '';
$success = '';
$title = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Si le formulaire est soumis
  $title = trim($_POST['title'] ?? ''); // Récupère et nettoie le titre
  $content = trim($_POST['content'] ?? ''); // Récupère et nettoie le contenu

  if (!$title) { // Vérifie si le titre est vide
    $error = "Le titre est obligatoire.";
  } elseif (!$content) { // Vérifie si le contenu est vide
    $error = "Le contenu est obligatoire.";
  } else {
    // Prépare et exécute la requête d'insertion dans la base de données
    $stmt = $pdo->prepare("INSERT INTO articles (title, content, author_id, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$title, $content, $_SESSION['user_id']]);
    $success = "Article créé avec succès.";
    // Réinitialise le formulaire
    $title = '';
    $content = '';
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Créer un article - NEXORA </title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png" />
  <!-- Inclusion de TinyMCE pour l'éditeur de texte -->
  <script src="https://cdn.tiny.cloud/1/8evtsb6e56jf07xb5lj1pyiqxqm80vhnih1mdlc0op47kiav/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
  // Initialisation de TinyMCE sur le textarea "content"
  tinymce.init({
    selector: '#content',
    menubar: false,
    plugins: 'lists link image preview',
    toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | preview',
    height: 300
  });
  </script>

  <style>
    /* Styles CSS pour la page */
    body {
    background: #111;
    color: #eee;
    font-family: Arial, sans-serif;
    max-width: 700px;
    margin: 2rem auto;
    padding: 1rem;
    }
    input, textarea {
    width: 100%;
    background: #222;
    border: none;
    color: #eee;
    padding: 0.5rem;
    margin-bottom: 1rem;
    border-radius: 4px;
    font-size: 1rem;
    }
    button {
    background: #66aaff;
    border: none;
    padding: 0.6rem 1.2rem;
    color: #111;
    font-weight: bold;
    cursor: pointer;
    border-radius: 4px;
    }
    button:hover {
    background: #5599dd;
    }
    .error {
    color: #f55;
    margin-bottom: 1rem;
    }
    .success {
    color: #5f5;
    margin-bottom: 1rem;
    }
    label {
    font-weight: bold;
    display: block;
    margin-bottom: 0.3rem;
    }
    nav {
    background: #222;
    padding: 0.5rem 1rem;
    margin-bottom: 2rem;
    border-radius: 6px;
    }
    nav a {
    color: #66aaff;
    margin-right: 1rem;
    text-decoration: none;
    font-weight: bold;
    }
    nav a:hover {
    text-decoration: underline;
    }
  </style>
</head>
<body>

<nav>
  <!-- Menu de navigation -->
  <a href="index.php">Home</a>
  <a href="profile.php">Profile</a>
  <a href="edit_post.php">Edit Post</a>
  <a href="logout.php">Déconnexion</a>
</nav>

<h1>Créer un article</h1>

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
