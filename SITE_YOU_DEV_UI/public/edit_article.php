<?php
session_start(); // Démarre la session PHP pour gérer l'authentification
require_once '../includes/db.php'; // Inclut le fichier de connexion à la base de données

// Vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$error = '';
$success = '';
$title = '';
$content = '';
$edit_id = 0;

// Préremplissage du formulaire si un edit_id est présent dans l'URL (méthode GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
  $edit_id = intval($_GET['edit_id']);

  // Récupère l'article correspondant à l'utilisateur connecté
  $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ? AND author_id = ?");
  $stmt->execute(array($edit_id, $_SESSION['user_id']));
  $article = $stmt->fetch();

  if ($article) {
    $title = $article['title']; // Préremplit le titre
    $content = $article['content']; // Préremplit le contenu
  } else {
    $error = "Article introuvable ou accès refusé.";
  }
}

// Traitement de la soumission du formulaire (méthode POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
  $title = isset($_POST['title']) ? trim($_POST['title']) : '';
  $content = isset($_POST['content']) ? trim($_POST['content']) : '';

  // Vérifie que le titre et le contenu ne sont pas vides
  if (!$title) {
    $error = "Le titre est obligatoire.";
  } elseif (!$content) {
    $error = "Le contenu est obligatoire.";
  } elseif ($edit_id > 0) {
    // Met à jour l'article dans la base de données
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
  <title> Modifier un article - NEXORA </title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png" />
  <link rel="stylesheet" href="../assets/css/edit_article.css"/>
  <!-- Inclusion de TinyMCE pour l'éditeur de texte enrichi -->
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
    /* Styles CSS pour la mise en page et l'apparence du formulaire */
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
  <a href="index.php">Accueil</a>
  <a href="profile.php">Profil</a>
  <a href="logout.php">Déconnexion</a>
</nav>

<h1>Modifier un article</h1>

<!-- Affichage des messages d'erreur ou de succès -->
<?php if (!empty($error)): ?>
  <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>
<?php if (!empty($success)): ?>
  <p class="success"><?php echo htmlspecialchars($success); ?></p>
<?php endif; ?>

<!-- Formulaire de modification d'article -->
<form method="POST" id="articleForm">
  <input type="hidden" name="edit_id" value="<?php echo intval($edit_id); ?>" />

  <label for="title">Titre</label>
  <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($title); ?>" />

  <label for="content">Contenu</label>
  <textarea id="content" name="content" rows="8"><?php echo $content; ?></textarea>

  <button type="submit">Mettre à jour</button>
</form>

<script>
// Validation côté client avant soumission du formulaire
document.getElementById('articleForm').addEventListener('submit', function(e) {
  tinymce.triggerSave(); // Sauvegarde le contenu TinyMCE dans le textarea

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
