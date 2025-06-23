<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username == '' || $password == '') {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
        $stmt->execute(array($username));
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: ../admin/dashboard.php');
            exit;
        } else {
            $error = 'Identifiants invalides.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />

    <title>LOGIN ADMIN - nexora </title>
     <link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
<link rel="stylesheet" href="../assets/admin-style.css" />
</head>
<body>
<h1>Connexion Admin</h1>
<?php if ($error): ?>
<p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<form method="post" action="">
    <label>Nom d'utilisateur: <input type="text" name="username" required></label><br>
    <label>Mot de passe: <input type="password" name="password" required></label><br>
    <button type="submit">Se connecter</button>
</form>
<p><a href="register_admin.php">Créer un compte admin</a></p>
</body>
</html>
