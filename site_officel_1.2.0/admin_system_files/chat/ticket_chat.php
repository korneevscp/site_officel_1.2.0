<?php
session_start();
require_once '../auth/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login_admin.php');
    exit;
}

$ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

if (!$ticket_id) {
    die('Ticket non spécifié.');
}

// Récupérer messages ticket
$stmt = $pdo->prepare("SELECT c.*, a.username AS admin_name, u.username AS user_name
    FROM messages c
    LEFT JOIN admins a ON c.admin_id = a.id
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.ticket_id = ? ORDER BY c.created_at ASC");
$stmt->execute(array($ticket_id));
$messages = $stmt->fetchAll();

// Envoyer un message
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message']);
    if ($message != '') {
        $stmt = $pdo->prepare("INSERT INTO messages (ticket_id, admin_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute(array($ticket_id, $_SESSION['admin_id'], $message));
        header("Location: ticket_chat.php?ticket_id=$ticket_id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Chat ticket #<?php echo $ticket_id; ?></title>
<link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
<link rel="stylesheet" href="../assets/admin-style.css" />
<style>
.message { border-bottom: 1px solid #444; padding: 5px 0; }
.message .author { font-weight: bold; }
.message .date { font-size: 0.8em; color: #ccc; }
</style>
</head>
<body>
<header>
    <h1>Chat Ticket #<?php echo $ticket_id; ?></h1>
    <nav>
        <a href="../admin/dashboard.php">Dashboard</a> |
        <a href="../auth/logout_admin.php">Déconnexion</a>
    </nav>
</header>
<main>
    <div class="chat-box" style="max-height:400px; overflow-y:auto; background:#222; padding:10px; border-radius:5px; margin-bottom:15px;">
        <?php foreach ($messages as $msg): ?>
            <div class="message">
                <span class="author"><?php
                    if ($msg['admin_id']) {
                        echo 'Admin: ' . htmlspecialchars($msg['admin_name'], ENT_QUOTES, 'UTF-8');
                    } elseif ($msg['user_id']) {
                        echo 'User: ' . htmlspecialchars($msg['user_name'], ENT_QUOTES, 'UTF-8');
                    } else {
                        echo 'Système';
                    }
                ?></span>
                <span class="date">[<?php echo $msg['created_at']; ?>]</span>
                <div class="content"><?php echo nl2br(htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8')); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="post" action="">
        <textarea name="message" rows="3" style="width:100%;" required></textarea><br>
        <button type="submit">Envoyer</button>
    </form>
</main>
</body>
</html>

