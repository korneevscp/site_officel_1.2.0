<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header('Location: login.php');
    exit;
}

require_once '../includes/db.php';

// Récupérer nombre d'articles et utilisateurs
$totalArticles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Dashboard Admin</title>
<style>
  body { font-family: Arial; padding: 2rem; background:#222; color:#eee; }
  a { color: #66f; }
  nav a { margin-right: 1rem; }
</style>
</head>
<body>
<h1>Dashboard Admin</h1>
<nav>
  <a href="articles.php">Gérer les articles</a>
  <a href="users.php">Gérer les utilisateurs</a>
  <a href="logout.php">Déconnexion</a>
</nav>

<p>Total articles : <?= $totalArticles ?></p>
<p>Total utilisateurs : <?= $totalUsers ?></p>

</body>
</html>
