<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login_admin.php');
    exit;
}
require_once '../auth/db.php';

// Récupérer les tickets en attente (status = 'pending')
$sqlPending = "SELECT t.*, u1.username AS reporter_name, u2.username AS reported_name
               FROM tickets t
               LEFT JOIN users u1 ON t.reporter_id = u1.id
               LEFT JOIN users u2 ON t.reported_id = u2.id
               WHERE t.status = 'pending'
               ORDER BY t.created_at DESC";

$stmtPending = $pdo->prepare($sqlPending);
$stmtPending->execute();
$pendingTickets = $stmtPending->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les tickets (historique)
$sqlAll = "SELECT t.*, u1.username AS reporter_name, u2.username AS reported_name
           FROM tickets t
           LEFT JOIN users u1 ON t.reporter_id = u1.id
           LEFT JOIN users u2 ON t.reported_id = u2.id
           ORDER BY t.created_at DESC";

$stmtAll = $pdo->prepare($sqlAll);
$stmtAll->execute();
$allTickets = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
require '../auth/db.php';

$reports = $pdo->query("SELECT reports.*, users.username FROM reports JOIN users ON reports.reported_user_id = users.id ORDER BY report_date DESC")->fetchAll();

foreach ($reports as $report) {
    echo "<div class='report'>";
    echo "<p><strong>Signalé :</strong> " . htmlspecialchars($report['username']) . "</p>";
    echo "<p><strong>Motif :</strong> " . htmlspecialchars($report['report_text']) . "</p>";
    echo "<p><a href='delete_user.php?id=" . $report['reported_user_id'] . "' onclick=\"return confirm('Supprimer ce compte et tout son contenu ?');\">Supprimer utilisateur</a></p>";
    
    echo "</div><hr>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
      <title>ADMIN - DASHBOARD - nexora </title>
     <link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
    <link rel="stylesheet" href="../assets/admin-style.css">
</head>
<body>
    <div class="container">
        <h1>Tableau de bord Admin</h1>

        <h2>Tickets en attente</h2>
        <?php if (count($pendingTickets) == 0): ?>
            <p>Aucun ticket en attente.</p>
        <?php else: ?>
            <ul class="ticket-list">
                <?php foreach ($pendingTickets as $ticket): ?>
                    <li class="ticket">
                        <strong>Signalé par :</strong> <?php echo htmlspecialchars($ticket['reporter_name']); ?><br>
                        <strong>Utilisateur concerné :</strong> <?php echo htmlspecialchars($ticket['reported_name']); ?><br>
                        <strong>Raison :</strong> <?php echo htmlspecialchars($ticket['reason']); ?><br>
                        <strong>Date :</strong> <?php echo htmlspecialchars($ticket['created_at']); ?><br>
                        <a href="take_action.php?ticket_id=<?php echo $ticket['id']; ?>">Prendre une action</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2>Historique de tous les tickets</h2>
        <ul class="ticket-list">
            <?php foreach ($allTickets as $ticket): ?>
                <li class="ticket">
                    <strong>Signalé par :</strong> <?php echo htmlspecialchars($ticket['reporter_name']); ?><br>
                    <strong>Utilisateur concerné :</strong> <?php echo htmlspecialchars($ticket['reported_name']); ?><br>
                    <strong>Raison :</strong> <?php echo htmlspecialchars($ticket['reason']); ?><br>
                    <strong>Date :</strong> <?php echo htmlspecialchars($ticket['created_at']); ?><br>
                    <strong>Statut :</strong> <?php echo htmlspecialchars($ticket['status']); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
