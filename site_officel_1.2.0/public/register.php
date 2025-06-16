<?php
session_start(); // Démarre la session PHP
require_once '../includes/db.php'; // Inclut le fichier de connexion à la base de données

$error = ''; // Initialise la variable d'erreur

// Vérifie si le formulaire a été soumis en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère et nettoie les champs du formulaire
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    // Vérifie que tous les champs sont remplis
    if (!$username || !$email || !$password || !$password_confirm) {
        $error = "Tous les champs sont requis.";
    // Vérifie que l'email est valide
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    // Vérifie que les mots de passe correspondent
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Vérifie si le nom d'utilisateur ou l'email existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Nom d'utilisateur ou email déjà utilisé.";
        } else {
            // Hash le mot de passe
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Insère le nouvel utilisateur dans la base de données
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $hash]);
            // Redirige vers la page de connexion
            header('Location: login.php');
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>S'inscrire - NEXORA </title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png" />
<style>
  body { background:#111; color:#eee; font-family: Arial, sans-serif; max-width: 400px; margin:auto; padding: 2rem; }
  input { width: 100%; padding: 0.5rem; margin: 0.3rem 0; background:#222; border:none; color:#eee; border-radius:3px;}
  button { background:#66aaff; border:none; color:#111; padding: 0.5rem 1rem; cursor:pointer; border-radius:3px;}
  .error { color: #f55; }
  a { color:#66aaff; }
</style>
</head>
<body>

<h1>S'inscrire</h1>

<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<form method="POST">
  <label>Nom d'utilisateur</label>
  <!-- Champ pour le nom d'utilisateur -->
  <input type="text" name="username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" />

  <label>Email</label>
  <!-- Champ pour l'email -->
  <input type="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" />

  <label>Mot de passe</label>
  <!-- Champ pour le mot de passe -->
  <input type="password" name="password" required />

  <label>Confirmer mot de passe</label>
  <!-- Champ pour confirmer le mot de passe -->
  <input type="password" name="password_confirm" required />

  <button type="submit">S'inscrire</button>
</form>

<p>Déjà un compte ? <a href="login.php">Se connecter</a></p>

</body>
</html>
