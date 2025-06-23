<?php
session_start();
require_once '../includes/db.php';

// Vérifier que l'utilisateur est connecté et que les données POST sont présentes
if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'], $_POST['comment'])) {
  header('Location: index.php');
  exit;
}

$userId = $_SESSION['user_id'];
$postId = (int)$_POST['post_id'];
$comment = trim($_POST['comment']);

// Insérer le commentaire uniquement s'il n'est pas vide
if ($comment !== '') {
  // Préparation de la requête d'insertion (attention à la table comments : id doit être AUTO_INCREMENT)
  $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
  $stmt->execute([$postId, $userId, $comment]);
}

// Redirection vers la page d'accueil (ou la page souhaitée)
header('Location: index.php');
exit;
