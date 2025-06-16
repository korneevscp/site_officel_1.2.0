<?php
require_once '../includes/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);

    header('Location: login.php');
    exit;
}
?>
<form method="POST">
  <input name="username" placeholder="Admin" required>
  <input type="password" name="password" placeholder="Mot de passe" required>
  <button type="submit">Créer un compte admin</button>
</form>
