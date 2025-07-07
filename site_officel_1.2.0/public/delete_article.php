<?php
require_once '../includes/db.php';
session_start();

if (isset($_POST['delete_id'])) {
    $id = (int) $_POST['delete_id'];

    // Vérifie que l'article appartient à l'utilisateur
    $stmtCheck = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND author_id = ?");
    $stmtCheck->execute([$id, $_SESSION['user_id']]);
    $article = $stmtCheck->fetch();

    if ($article) {
        // Supprimer les commentaires liés à cet article
        $stmtComments = $pdo->prepare("DELETE FROM comments WHERE post_id = ?");
        $stmtComments->execute([$id]);

        // Supprimer l'article
        $stmtDelete = $pdo->prepare("DELETE FROM articles WHERE id = ?");
        $stmtDelete->execute([$id]);
    }

    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
