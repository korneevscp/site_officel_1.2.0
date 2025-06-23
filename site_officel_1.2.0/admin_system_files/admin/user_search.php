<?php
session_start();
require_once '../auth/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login_admin.php');
    exit;
}

$search = '';
$users = array();

if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    if ($search != '') {
        $stmt = $pdo->prepare("SELECT id, username, email, status FROM users WHERE username LIKE ? OR email LIKE ? LIMIT 30");
        $like = '%' . $search . '%';
        $stmt->execute(array($like, $like));
        $users = $stmt->fetchAll();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Recherche utilisateur</title>
<link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
<link rel="stylesheet" href="../assets/admin-style.css" />
</head>
<body>
<header>
    <h1>Recherche utilisateur</h1>
    <nav>
        <a href="dashboard.php">Retour au dashboard</a> |
        <a href="../auth/logout_admin.php">Déconnexion</a>
    </nav>
</header>
<main>
    <form method="get" action="">
        <input type="text" name="search" placeholder="Nom ou email utilisateur" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>" required />
        <button type="submit">Rechercher</button>
    </form>

    <?php if ($search !== ''): ?>
        <h2>Résultats pour "<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"</h2>
        <?php if (count($users) === 0): ?>
            <p>Aucun utilisateur trouvé.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>ID</th><th>Nom d'utilisateur</th><th>Email</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($u['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <a href="take_action.php?user_id=<?php echo $u['id']; ?>">Actions</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</main>
</body>
</html>
