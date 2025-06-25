<?php 
require_once '../includes/db.php'; 
session_start();  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {     
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");     
    $stmt->execute([$_POST['username']]);     
    $admin = $stmt->fetch();      
    
    if ($admin && password_verify($_POST['password'], $admin['password'])) {         
        $_SESSION['admin_id'] = $admin['id'];         
        header('Location: dashboard.php');         
        exit;     
    } else {         
        $error = "Identifiants invalides.";     
    } 
} 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion Admin - NEXORA</title>
  <style>
    :root {
      --background: #1e1e2f;
      --card-bg: #2c2c47;
      --text: #f0f0f0;
      --text-light: #9ca3af;
      --primary: #8a2be2;
      --border: #3a3a4a;
      --avatar-bg: #3f3f57;
      --error: #ef4444;
    }
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Inter', sans-serif;
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
      border-radius: 10px;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.2);
      border: 1px solid var(--border);
      width: 100%;
    }
    .login-form h2 {
      text-align: center;
      margin-bottom: 30px;
      color: var(--primary);
      font-size: 1.5rem;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group input {
      width: 100%;
      padding: 12px 15px;
      background: var(--background);
      border: 1px solid var(--border);
      border-radius: 8px;
      color: var(--text);
      font-size: 1rem;
      transition: border-color 0.3s ease;
    }
    .form-group input:focus {
      outline: none;
      border-color: var(--primary);
    }
    .form-group input::placeholder {
      color: var(--text-light);
    }
    .login-btn {
      width: 100%;
      padding: 12px;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .login-btn:hover {
      background: #7c22d4;
    }
    .error-message {
      background: rgba(239, 68, 68, 0.1);
      color: var(--error);
      padding: 10px 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid rgba(239, 68, 68, 0.3);
      text-align: center;
    }
    .user-links {
      text-align: center;
      margin-top: 20px;
    }
    .user-links a {
      color: var(--primary);
      margin: 0 8px;
      font-weight: 500;
      text-decoration: none;
      font-size: 0.95rem;
    }
    .user-links a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <header>
    <h1>NEXORA</h1>
    <div class="user-links">
      <a href="../index.php">Retour au site</a>
    </div>
  </header>

  <div class="login-container">
    <div class="login-form">
      <h2>Connexion Administrateur</h2>
      
      <?php if (isset($error)): ?>
        <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="form-group">
          <input type="text" name="username" placeholder="Nom d'utilisateur admin" required>
        </div>
        <div class="form-group">
          <input type="password" name="password" placeholder="Mot de passe" required>
        </div>
        <button type="submit" class="login-btn">Connexion</button>
      </form>
    </div>
  </div>
</body>
</html>
