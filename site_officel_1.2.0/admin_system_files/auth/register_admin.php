<?php
session_start();
require_once 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    if ($username == '' || $password == '' || $password_confirm == '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif ($password !== $password_confirm) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        // Vérifier si le username existe
        $stmt = $pdo->prepare('SELECT id FROM admins WHERE username = ?');
        $stmt->execute(array($username));
        if ($stmt->fetch()) {
            $error = 'Nom d\'admin déjà utilisé.';
        } else {
            // Insérer admin avec hash bcrypt
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO admins (username, password) VALUES (?, ?)');
            if ($stmt->execute(array($username, $hash))) {
                header('Location: login_admin.php');
                exit;
            } else {
                $error = 'Erreur lors de l\'inscription.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
    <title>REGISTER ADMIN - nexora </title>
     <link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
<link rel="stylesheet" href="../assets/admin-style.css" />
</head>
<body>
<h1>Inscription Admin</h1>
<?php if ($error): ?>
<p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<form method="post" action="">
    <label>Nom d'utilisateur: <input type="text" name="username" required></label><br>
    <label>Mot de passe: <input type="password" name="password" required></label><br>
    <label>Confirmer mot de passe: <input type="password" name="password_confirm" required></label><br>
    <button type="submit">S'inscrire</button>
</form>
<p><a href="login_admin.php">Connexion admin</a></p>
</body>
</html>
