<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../auth/login_admin.php");
    exit;
}

require_once '../auth/db.php';

// Vérifie si un ID de ticket est fourni
if (!isset($_GET['ticket_id'])) {
    echo "Ticket introuvable.";
    exit;
}

$ticket_id = intval($_GET['ticket_id']);

// Récupère les infos du ticket
$sql = "SELECT t.*, u1.username AS reporter_name, u2.username AS reported_name, u2.id AS reported_user_id
        FROM tickets t
        LEFT JOIN users u1 ON t.reporter_id = u1.id
        LEFT JOIN users u2 ON t.reported_id = u2.id
        WHERE t.id = :ticket_id";

$stmt = $pdo->prepare($sql);
$stmt->execute(['ticket_id' => $ticket_id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    echo "Ticket non trouvé.";
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $user_id = intval($_POST['user_id']);

    if ($action == 'warn') {
        $message = "Utilisateur averti.";
    } elseif ($action == 'block') {
        $update = $pdo->prepare("UPDATE users SET is_blocked = 1 WHERE id = :id");
        $update->execute(['id' => $user_id]);
        $message = "Utilisateur bloqué.";
    } elseif ($action == 'suspend') {
        $update = $pdo->prepare("UPDATE users SET suspended_until = DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE id = :id");
        $update->execute(['id' => $user_id]);
        $message = "Utilisateur suspendu 7 jours.";
    } elseif ($action == 'delete') {
        $delete = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $delete->execute(['id' => $user_id]);
        $message = "Utilisateur supprimé.";
    } else {
        $message = "Action inconnue.";
    }

    // Marquer le ticket comme traité
    $stmt = $pdo->prepare("UPDATE tickets SET status = 'resolved' WHERE id = :id");
    $stmt->execute(['id' => $ticket_id]);

    echo "<p>$message</p><a href='dashboard.php'>Retour au tableau de bord</a>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prendre une action</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
    <link rel="stylesheet" href="../assets/admin-style.css">
</head>
<body>
    <div class="container">
        <h1>Action sur Ticket</h1>

        <p><strong>Signalé par :</strong> <?php echo htmlspecialchars($ticket['reporter_name']); ?></p>
        <p><strong>Utilisateur concerné :</strong> <?php echo htmlspecialchars($ticket['reported_name']); ?></p>
        <p><strong>Raison :</strong> <?php echo nl2br(htmlspecialchars($ticket['reason'])); ?></p>
        <p><strong>Date :</strong> <?php echo htmlspecialchars($ticket['created_at']); ?></p>

        <form method="post">
            <input type="hidden" name="user_id" value="<?php echo intval($ticket['reported_user_id']); ?>">
            <label for="action">Choisir une action :</label><br>
            <select name="action" required>
                <option value="warn">Avertir</option>
                <option value="block">Bloquer</option>
                <option value="suspend">Suspendre (7 jours)</option>
                <option value="delete">Supprimer le compte</option>
            </select><br><br>
            <button type="submit">Exécuter</button>
        </form>

        <p><a href="dashboard.php">← Retour</a></p>
    </div>
</body>
</html>
