<?php
session_start();
require_once '../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if (!$username || !$email || !$password || !$password_confirm) {
        $error = "Tous les champs sont requis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide.";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Nom d'utilisateur ou email déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'user')");
            $stmt->execute([$username, $email, $hash]);
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
    <title>S'inscrire - NEXORA</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
    <link rel="stylesheet" href="../assets/css/register.css" />
</head>
<body>

<div class="container">
    <h1>S'inscrire</h1>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Nom d'utilisateur</label>
        <input type="text" name="username" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>" />

        <label>Email</label>
        <input type="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" />

        <label>Mot de passe</label>
        <input type="password" name="password" required />

        <label>Confirmer mot de passe</label>
        <input type="password" name="password_confirm" required />

        <button type="submit">S'inscrire</button>
    </form>

    <p class="login-link">Déjà un compte ? <a href="login.php">Se connecter</a></p>
</div>

</body>
</html>
