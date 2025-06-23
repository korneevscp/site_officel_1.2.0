<?php
session_start();
$_SESSION['admin_logged_in'] = true; // TEMPORAIRE pour test local

require '../auth/db.php'; // chemin corrigé

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $user_id = intval($_GET['id']);

    // Supprimer les messages manuellement (si pas de FK directe)
    $stmt = $pdo->prepare("DELETE FROM messages WHERE sender = (SELECT username FROM users WHERE id = ?)");
    $stmt->execute([$user_id]);

    // Supprimer l'utilisateur (ce qui déclenche les cascades)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    echo "Utilisateur et tout son contenu supprimé.";
} else {
    echo "ID invalide.";
}
?>
