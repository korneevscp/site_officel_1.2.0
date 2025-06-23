<?php
session_start();
require_once '../auth/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login_admin.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reported_username = trim($_POST['reported_username']);
    $report_text = trim($_POST['report_text']);

    if ($reported_username === '' || $report_text === '') {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Trouver l'ID à partir du pseudo
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$reported_username]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'Utilisateur introuvable.';
        } else {
            $reported_user_id = $user['id'];
            // Insérer le report
            $stmt = $pdo->prepare('INSERT INTO reports (reported_user_id, report_text, report_date) VALUES (?, ?, NOW())');
            if ($stmt->execute([$reported_user_id, $report_text])) {
                $success = 'Signalement envoyé avec succès.';
            } else {
                $error = 'Erreur lors de l\'envoi du signalement.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Formulaire de signalement</title>
<link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
<link rel="stylesheet" href="../assets/admin-style.css" />
</head>
<body>
<header>
    <h1>Formulaire de signalement</h1>
    <nav>
        <a href="../admin/dashboard.php">Dashboard</a> |
        <a href="../auth/logout_admin.php">Déconnexion</a>
    </nav>
</header>
<main>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php elseif ($success): ?>
        <p class="info"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data">
        <label>Pseudo de l'utilisateur signalé:<br>
            <input type="text" name="reported_username" required />
        </label><br><br>
        <label>Description du problème:<br>
            <textarea name="report_text" rows="4" cols="50" required></textarea>
        </label><br><br>
        <label>Ajouter une photo (optionnel):<br>
            <input type="file" name="photo" accept="image/*" />
        </label><br><br>
        <button type="submit">Envoyer signalement</button>
    </form>
</main>
</body>
</html>

