<?php
require_once '../includes/db.php'; // Inclut le fichier de connexion à la base de données
session_start(); // Démarre la session PHP

// Vérifie si l'identifiant de l'article à supprimer est envoyé en POST
if (isset($_POST['delete_id'])) {
  $id = (int) $_POST['delete_id']; // Récupère et convertit l'identifiant en entier

  // Prépare et exécute la requête pour supprimer l'article appartenant à l'utilisateur connecté
  $stmt = $pdo->prepare("DELETE FROM articles WHERE id = ? AND author_id = ?");
  $stmt->execute([$id, $_SESSION['user_id']]);

  // Redirige vers la page d'accueil après la suppression
  header("Location: index.php");
  exit;
}
else {
  // Redirige vers la page d'accueil si aucun identifiant n'est fourni
  header("Location: index.php");
  exit;
}