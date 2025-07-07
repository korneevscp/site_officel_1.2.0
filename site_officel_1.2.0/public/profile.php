<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

$user_id = $_SESSION['user_id'];

// Récupérer infos utilisateur (username, email, avatar, description, password_hash)
$stmt = $pdo->prepare("SELECT username, email, avatar, description, password_hash FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur non trouvé");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $newEmail = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $newDescription = trim(isset($_POST['description']) ? $_POST['description'] : '');
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Gestion upload avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $error = "Erreur lors de l'upload de l'avatar.";
        } elseif (!in_array($_FILES['avatar']['type'], $allowedTypes)) {
            $error = "Format d'image non supporté (jpeg, png, gif uniquement).";
        } else {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $avatarFilename = 'avatar_'.$user_id.'_'.time().'.'.$ext;
            $uploadDir = __DIR__ . '/../uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $uploadPath = $uploadDir . $avatarFilename;
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                $error = "Impossible de sauvegarder l'avatar.";
            } else {
                if ($user['avatar'] && file_exists($uploadDir . $user['avatar'])) {
                    @unlink($uploadDir . $user['avatar']);
                }
                $user['avatar'] = $avatarFilename;
            }
        }
    }

    if (!$newUsername || !$newEmail) {
        $error = "Pseudo et email obligatoires.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $stmt->execute([$newEmail, $newUsername, $user_id]);
        if ($stmt->fetch()) {
            $error = "Pseudo ou email déjà utilisé par un autre utilisateur.";
        } else {
            if ($newPassword || $confirmPassword) {
                if (!$currentPassword) {
                    $error = "Veuillez saisir votre mot de passe actuel pour le changer.";
                } elseif (!password_verify($currentPassword, $user['password_hash'])) {
                    $error = "Mot de passe actuel incorrect.";
                } elseif ($newPassword !== $confirmPassword) {
                    $error = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
                } else {
                    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $user_id]);
                }
            }

            if (!$error) {
                if (isset($avatarFilename)) {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, description = ?, avatar = ? WHERE id = ?");
                    $stmt->execute([$newUsername, $newEmail, $newDescription, $avatarFilename, $user_id]);
                    $user['avatar'] = $avatarFilename;
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, description = ? WHERE id = ?");
                    $stmt->execute([$newUsername, $newEmail, $newDescription, $user_id]);
                }
                $_SESSION['username'] = $newUsername;
                $success = "Profil mis à jour avec succès.";

                // Recharger les infos
                $user['username'] = $newUsername;
                $user['email'] = $newEmail;
                $user['description'] = $newDescription;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Mon profil</title>
<link rel="icon" type="image/png" href="../assets/images/logo.jpg" />
<link rel="stylesheet" href="../assets/css/profil.css" />
</head>
<body>

<nav>
  <a href="index.php" class="logo">NEXORA</a>
  <div class="user-links">
  <?php if (isset($_SESSION['user_id'])): ?>
    <a href="create_article.php">je post</a> |
    <a href="mes_articles.php">mes post</a> |
    <a href="profile.php">profil</a> |
    <a href="../admin_system_files/auth/login.php">admin login</a> |
    <a href="logout.php">Déconnexion</a>
  <?php else: ?>
    <a href="login.php">Se connecter</a> |
    <a href="register.php">S'inscrire</a>
  <?php endif; ?>
  </div>
</nav>

<h1>Mon profil</h1>

<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <label for="username">Pseudo</label>
  <input id="username" name="username" type="text" required value="<?= htmlspecialchars($user['username']) ?>" />

  <label for="email">Email</label>
  <input id="email" name="email" type="email" required value="<?= htmlspecialchars($user['email']) ?>" />

  <label for="description">Description</label>
  <textarea id="description" name="description" rows="4"><?= htmlspecialchars(isset($user['description']) ? $user['description'] : '') ?></textarea>

  <label for="avatar">Avatar (jpeg, png, gif)</label>
  <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/gif" />
  <?php if ($user['avatar']): ?>
    <img src="../uploads/avatars/<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="avatar-preview" />
  <?php endif; ?>

  <hr style="border-color:#333; margin: 2rem 0;">

  <p>Changer le mot de passe (optionnel) :</p>

  <label for="current_password">Mot de passe actuel</label>
  <input id="current_password" name="current_password" type="password" autocomplete="current-password" />

  <label for="new_password">Nouveau mot de passe</label>
  <input id="new_password" name="new_password" type="password" autocomplete="new-password" />

  <label for="confirm_password">Confirmer nouveau mot de passe</label>
  <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" />

  <button type="submit">Mettre à jour</button>
</form>

<p><a href="index.php">← Retour à l'accueil</a> | <a href="logout.php">Se déconnecter</a></p>

</body>
</html>
