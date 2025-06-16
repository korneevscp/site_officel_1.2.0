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
