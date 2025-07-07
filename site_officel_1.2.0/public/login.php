<?php
require_once '../includes/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Identifiants invalides.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Connexion - NEXORA</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
    <link rel="stylesheet" href="../assets/css/login.css" />
</head>
<body>

<div class="container">
    <h1>Se connecter</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nom d'utilisateur</label>
        <input type="text" name="username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" />

        <label>Mot de passe</label>
        <input type="password" name="password" required />

        <button type="submit">Connexion</button>
    </form>

    <p class="login-link">Pas encore inscrit ? <a href="register.php">Créer un compte</a></p>
</div>

</body>
</html>
