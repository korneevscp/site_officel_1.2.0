<?php
session_start(); // Démarre la session utilisateur
require_once '../includes/db.php'; // Inclut la connexion à la base de données

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

$user_id = $_SESSION['user_id'];

// Récupère les informations de l'utilisateur depuis la base de données
$stmt = $pdo->prepare("SELECT username, email, avatar, description, password_hash FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur non trouvé");
}

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUsername = trim(isset($_POST['username']) ? $_POST['username'] : '');
    $newEmail = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $newDescription = trim(isset($_POST['description']) ? $_POST['description'] : '');
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    // Gestion de l'upload de l'avatar
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
                mkdir($uploadDir, 0755, true); // Crée le dossier s'il n'existe pas
            }
            $uploadPath = $uploadDir . $avatarFilename;
            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadPath)) {
                $error = "Impossible de sauvegarder l'avatar.";
            } else {
                // Supprime l'ancien avatar si existant
                if ($user['avatar'] && file_exists($uploadDir . $user['avatar'])) {
                    @unlink($uploadDir . $user['avatar']);
                }
                $user['avatar'] = $avatarFilename;
            }
        }
    }

    // Vérifie que le pseudo et l'email sont renseignés
    if (!$newUsername || !$newEmail) {
        $error = "Pseudo et email obligatoires.";
    } else {
        // Vérifie si le pseudo ou l'email sont déjà utilisés par un autre utilisateur
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $stmt->execute([$newEmail, $newUsername, $user_id]);
        if ($stmt->fetch()) {
            $error = "Pseudo ou email déjà utilisé par un autre utilisateur.";
        } else {
            // Si l'utilisateur souhaite changer de mot de passe
            if ($newPassword || $confirmPassword) {
                if (!$currentPassword) {
                    $error = "Veuillez saisir votre mot de passe actuel pour le changer.";
                } elseif (!password_verify($currentPassword, $user['password_hash'])) {
                    $error = "Mot de passe actuel incorrect.";
                } elseif ($newPassword !== $confirmPassword) {
                    $error = "Le nouveau mot de passe et sa confirmation ne correspondent pas.";
                } else {
                    // Met à jour le mot de passe
                    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$newHash, $user_id]);
                }
            }

            if (!$error) {
                // Met à jour le pseudo, l'email, la description et l'avatar si uploadé
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

                // Recharge les informations utilisateur
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Blog</title>
    <style>
        /* Reset et base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
            line-height: 1.6;
        }

        /* Navigation */
        nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .app-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .app-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .app-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
        }

        .app-button-icon {
            font-size: 1.2rem;
        }

        /* Header */
        header {
            text-align: center;
            padding: 8rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            margin-top: 0;
        }

        header h1 {
            font-size: 3rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 0.5rem;
        }

        /* Main content */
        main {
            max-width: 600px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        /* Profile form */
        .profile-form {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .profile-form h2 {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 1rem;
            margin: -2rem -2rem 2rem -2rem;
            font-size: 1.5rem;
            border-radius: 20px 20px 0 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #667eea;
        }

        .current-avatar {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .current-avatar img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }

        .avatar-fallback {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .password-section {
            border-top: 1px solid rgba(102, 126, 234, 0.1);
            padding-top: 1.5rem;
            margin-top: 1.5rem;
        }

        .password-section h3 {
            color: #667eea;
            margin-bottom: 1rem;
        }

        /* Amélioration des formulaires */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="file"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Boutons */
        button,
        input[type="submit"] {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            width: 100%;
        }

        button:hover,
        input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Messages */
        .message {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }

        .message.success {
            background: linear-gradient(45deg, #4caf50, #81c784);
            color: white;
        }

        .message.error {
            background: linear-gradient(45deg, #f44336, #ef5350);
            color: white;
        }

        /* Links */
        a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #764ba2;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .app-buttons {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .app-button {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .app-button span:not(.app-button-icon) {
                display: none;
            }

            header h1 {
                font-size: 2rem;
            }

            main {
                padding: 1rem;
            }

            .profile-form {
                padding: 1.5rem;
            }

            .profile-form h2 {
                margin: -1.5rem -1.5rem 1.5rem -1.5rem;
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            header {
                padding: 6rem 1rem 2rem;
            }

            .app-button {
                padding: 0.5rem 0.8rem;
                border-radius: 40px;
            }

            .profile-form {
                border-radius: 15px;
                padding: 1rem;
            }

            .profile-form h2 {
                margin: -1rem -1rem 1rem -1rem;
            }
        }

        /* Animations */
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-form {
            animation: slideIn 0.5s ease;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
        }
    </style>
</head>
<body>
    <nav>
        <div class="app-buttons">
            <a href="index.php" class="app-button">
                <span class="app-button-icon">🏠</span>
                <span>Accueil</span>
            </a>
            <a href="write.php" class="app-button">
                <span class="app-button-icon">✍️</span>
                <span>Post<span>
            </a>
            <a href="profile.php" class="app-button">
                <span class="app-button-icon">👤</span>
                <span>Profil</span>
            </a>
            <a href="logout.php" class="app-button">
                <span class="app-button-icon">🚪</span>
                <span>Déconnexion</span>
            </a>
        </div>
    </nav>

    <header>
        <h1>Mon Profil</h1>
    </header>

    <main>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="profile-form">
            <h2>Modifier mon profil</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">Pseudo :</label>
                    <input type="text" id="username" name="username" 

                <div class="form-group">
                    <label for="email">Email :</label>
                    <input type="email" id="email" name="email"  required>
                </div>

                <div class="form-group">
                    <label for="description">Description :</label>
                    <textarea id="description" name="description" placeholder="Parlez-nous de vous..."></textarea>
                </div>

                <div class="form-group">
                    <label for="avatar">Avatar :</label>
                    <div class="current-avatar">
                        <?php if ($user['avatar']): ?>
                            <img src="../uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar actuel">
                        <?php else: ?>
                            <div class="avatar-fallback">
                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                        <div>
                            <strong>Avatar actuel</strong><br>
                            <small>Choisissez un nouveau fichier pour le modifier</small>
                        </div>
                    </div>
                    <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/gif">
                </div>

                <div class="password-section">
                    <h3>Changer le mot de passe</h3>
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel :</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe :</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>

                <button type="submit">Mettre à jour le profil</button>
            </form>
        </div>
    </main>
</body>
</html>
