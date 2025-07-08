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
   :root {
      --background: #1e1e2f;
      --card-bg: #2c2c47;
      --text: #f0f0f0;
      --text-light: #9ca3af;
      --primary: #8a2be2;
      --secondary: #66aaff;
      --border: #3a3a4a;
      --error: #ef4444;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background-color: var(--background);
      color: var(--text);
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    header {
      background: #1e1e2f;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
      border-bottom: 5px solid #2c2c47;
      text-align: center;
      width: 100%;
      padding: 20px 0;
      opacity: 0.58;
    }
    header h1 {
      font-size: 2rem;
      color: white;
      margin-bottom: 10px;
    }
    .login-container {
      max-width: 400px;
      margin: 50px auto;
      padding: 20px;
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-form {
      background: var(--card-bg);
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      border: 1px solid var(--border);
      width: 100%;
      backdrop-filter: blur(10px);
    }
    .login-form h2 {
      text-align: center;
      margin-bottom: 30px;
      color: var(--primary);
      font-size: 1.8rem;
      font-weight: 600;
    }
    .form-group {
      margin-bottom: 20px;
      position: relative;
    }
    .form-group input {
      width: 100%;
      padding: 15px 20px;
      background: var(--background);
      border: 2px solid var(--border);
      border-radius: 10px;
      color: var(--text);
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.1);
      transform: translateY(-2px);
    }
    .form-group input::placeholder {
      color: var(--text-light);
    }
    .login-btn {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, var(--primary), #9d4edd);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-transform: uppercase;
      letter-spacing: 1px;
    }
    .login-btn:hover {
      background: linear-gradient(135deg, #7c22d4, #8a2be2);
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(138, 43, 226, 0.3);
    }
    .login-btn:active {
      transform: translateY(0);
    }
    .error-message {
      background: rgba(239, 68, 68, 0.1);
      color: var(--error);
      padding: 12px 20px;
      border-radius: 10px;
      margin-bottom: 25px;
      border: 1px solid rgba(239, 68, 68, 0.3);
      text-align: center;
      font-weight: 500;
      backdrop-filter: blur(5px);
    }
    .user-links {
      text-align: center;
      margin-top: 20px;
    }
    .user-links a {
      color: var(--secondary);
      margin: 0 10px;
      font-weight: 500;
      text-decoration: none;
      font-size: 0.95rem;
      transition: color 0.3s ease;
    }
    .user-links a:hover {
      color: var(--primary);
      text-decoration: underline;
    }
    .register-link {
      text-align: center;
      margin-top: 25px;
      padding-top: 20px;
      border-top: 1px solid var(--border);
    }
    .register-link p {
      color: var(--text-light);
      font-size: 0.9rem;
    }
    .register-link a {
      color: var(--secondary);
      font-weight: 600;
      text-decoration: none;
      transition: color 0.3s ease;
    }
    .register-link a:hover {
      color: var(--primary);
      text-decoration: underline;
    }
    .logo-container {
      text-align: center;
      margin-bottom: 20px;
    }
    .logo-container img {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      border: 3px solid var(--primary);
      padding: 5px;
      background: var(--card-bg);
    }
    /* Animation d'entrée */
    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .login-form {
      animation: slideIn 0.6s ease-out;
    }
    /* Responsive */
    @media (max-width: 480px) {
      .login-container {
        margin: 20px auto;
        padding: 15px;
      }
      .login-form {
        padding: 30px 25px;
      }
      header h1 {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>
  <header>
    <h1>NEXORA</h1>
    <div class="user-links">
      <a href="../index.php">Accueil</a>
      <a href="admin/login.php">Espace Admin</a>
    </div>
  </header>

  <div class="login-container">
    <div class="login-form">
      <div class="logo-container">
        <img src="../assets/images/logo.png" alt="NEXORA Logo" onerror="this.style.display='none'">
      </div>
      
      <h2>Se connecter</h2>
      
      <?php if ($error): ?>
        <div class="error-message">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="form-group">
          <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        </div>
        <div class="form-group">
          <input type="password" name="password" placeholder="Mot de passe" required>
        </div>
        <button type="submit" class="login-btn">Connexion</button>
      </form>
      
      <div class="register-link">
        <p>Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>
      </div>
    </div>
  </div>
</body>
</html>
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
