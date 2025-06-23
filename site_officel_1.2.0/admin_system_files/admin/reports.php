<?php
session_start();
$_SESSION['admin_logged_in'] = true; // TEMPORAIRE pour test local

require '../auth/db.php'; // chemin corrigé

$reports = $pdo->query("SELECT reports.*, users.username FROM reports JOIN users ON reports.reported_user_id = users.id ORDER BY report_date DESC")->fetchAll();

foreach ($reports as $report) {
    echo "<div class='report'>";
    echo "<p><strong>Signalé :</strong> " . htmlspecialchars($report['username']) . "</p>";
    echo "<p><strong>Motif :</strong> " . htmlspecialchars($report['report_text']) . "</p>";
    echo "<p><a href='delete_user.php?id=" . $report['reported_user_id'] . "' onclick=\"return confirm('Supprimer ce compte et tout son contenu ?');\">Supprimer utilisateur</a></p>";
    echo "</div><hr>";
}
?>
