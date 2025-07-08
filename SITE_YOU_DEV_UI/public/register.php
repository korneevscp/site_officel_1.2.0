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
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>S'inscrire - NEXORA</title>
  <link rel="icon" type="image/png" href="../assets/images/logo.png" />
  <style>
    :root {
      --background: #1e1e2f;
      --card-bg: #2c2c47;
      --text: #f0f0f0;
      --text-light: #9ca3af;
      --primary: #8a2be2;
      --border: #3a3a4a;
      --avatar-bg: #3f3f57;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      background-color: var(--background);
      color: var(--text);
      font-family: 'Inter', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 2rem;
      min-height: 100vh;
    }
    .signup-container {
      background: var(--card-bg);
      padding: 2rem;
      border-radius: 10px;
      border: 1px solid var(--border);
      width: 100%;
      max-width: 400px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
      opacity: 0.95;
    }
    h1 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: var(--text);
    }
    label {
      display: block;
      margin-top: 1rem;
      margin-bottom: 0.3rem;
      font-size: 0.95rem;
      color: var(--text);
    }
    input {
      width: 100%;
      padding: 0.5rem;
      background: var(--background);
      border: 1px solid var(--border);
      color: var(--text);
      border-radius: 5px;
    }
    input:focus {
      outline: none;
      border-color: var(--primary);
    }
    button {
      background: var(--primary);
      border: none;
      color: white;
      padding: 0.6rem 1.2rem;
      margin-top: 1.5rem;
      width: 100%;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }
    button:hover {
      background: #a24bff;
    }
    .error {
      color: #ff6b6b;
      margin-top: 1rem;
      font-size: 0.9rem;
      text-align: center;
    }
    p {
      text-align: center;
      margin-top: 1.2rem;
      font-size: 0.9rem;
      color: var(--text-light);
    }
    a {
      color: var(--primary);
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="signup-container">
    <h1>S'inscrire</h1>
  

<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

<form method="POST">
  <label>Nom d'utilisateur</label>
  <!-- Champ pour le nom d'utilisateur -->
  <input type="text" name="username" required value/>

  <label>Email</label>
  <!-- Champ pour l'email -->
  <input type="email" name="email" required value/>

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
