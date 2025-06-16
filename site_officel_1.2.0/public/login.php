<?php
// Inclusion du fichier de connexion à la base de données
require_once '../includes/db.php';
// Démarrage de la session PHP
session_start();

// Initialisation de la variable d'erreur
$error = null; // ← Ajouté ici pour éviter l'erreur

// Vérifie si le formulaire a été soumis en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Récupère le nom d'utilisateur et le mot de passe depuis le formulaire
  $username = isset($_POST['username']) ? $_POST['username'] : '';
  $password = isset($_POST['password']) ? $_POST['password'] : '';

  // Prépare et exécute la requête pour récupérer l'utilisateur correspondant
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  // Vérifie si l'utilisateur existe et si le mot de passe est correct
  if ($user && password_verify($password, $user['password_hash'])) {
    // Stocke l'identifiant de l'utilisateur dans la session
    $_SESSION['user_id'] = $user['id'];
    // Redirige vers la page d'accueil après connexion
    header('Location: index.php');
    exit;
  } else {
    // Affiche un message d'erreur si les identifiants sont invalides
    $error = "Identifiants invalides.";
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Se connecter - NEXORA </title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png" />
<style>
  /* Styles CSS pour la page de connexion */
  body { background:#111; color:#eee; font-family: Arial, sans-serif; max-width: 400px; margin:auto; padding: 2rem; }
  input { width: 100%; padding: 0.5rem; margin: 0.3rem 0; background:#222; border:none; color:#eee; border-radius:3px;}
  button { background:#66aaff; border:none; color:#111; padding: 0.5rem 1rem; cursor:pointer; border-radius:3px;}
  .error { color: #f55; }
  a { color:#66aaff; }
</style>
</head>
<body>

<h1>Se connecter</h1>

<?php if ($error): ?>
  <!-- Affiche le message d'erreur s'il existe -->
  <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<!-- Formulaire de connexion -->
<form method="POST">
  <input name="username" placeholder="Nom d'utilisateur" required>
  <input type="password" name="password" placeholder="Mot de passe" required>
  <button type="submit">Connexion</button>
</form>

<!-- Lien vers la page d'inscription -->
<p>Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>

</body>
</html>
