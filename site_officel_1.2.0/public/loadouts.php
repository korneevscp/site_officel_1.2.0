<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = $success = '';

// Fonction simple pour vérifier le type MIME autorisé
function isValidFileType($mime) {
    $allowed = [
        'image/jpeg', 'image/png', 'image/gif',
        'video/mp4', 'video/webm',
        'audio/mpeg', 'audio/ogg',
        'application/pdf'
    ];
    return in_array($mime, $allowed);
}

// Ajout d’un loadout avec upload de fichiers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['content'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content']);

    json_decode($content);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = "Contenu JSON invalide.";
    } elseif (!$name) {
        $error = "Le nom est requis.";
    } else {
        // Insérer loadout
        $stmt = $pdo->prepare("INSERT INTO loadouts (user_id, name, description, content) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $name, $description, $content]);
        $loadout_id = $pdo->lastInsertId();

        // Gestion des fichiers uploadés
        if (!empty($_FILES['files']['name'][0])) {
            $uploadDir = __DIR__ . '/../uploads/loadouts/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
                $filename = basename($_FILES['files']['name'][$key]);
                $filetype = mime_content_type($tmpName);

                if (isValidFileType($filetype)) {
                    $targetFile = $uploadDir . time() . '_' . $filename;
                    if (move_uploaded_file($tmpName, $targetFile)) {
                        $stmtFile = $pdo->prepare("INSERT INTO loadout_files (loadout_id, filename, filetype) VALUES (?, ?, ?)");
                        $stmtFile->execute([$loadout_id, basename($targetFile), $filetype]);
                    }
                }
            }
        }

        $success = "Loadout ajouté avec fichiers.";
    }
}

// Récupérer les loadouts utilisateur
$stmt = $pdo->prepare("SELECT * FROM loadouts WHERE user_id = ? ORDER BY updated_at DESC");
$stmt->execute([$user_id]);
$loadouts = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Mes Loadouts - TRDCRFT</title>
<style>
  body { background:#111; color:#eee; font-family: Arial, sans-serif; max-width: 900px; margin:auto; padding: 2rem; }
  a { color:#66aaff; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 2rem; }
  th, td { padding: 0.5rem; border: 1px solid #444; vertical-align: top; }
  button, input, textarea { background:#222; border:none; color:#eee; padding:0.3rem; border-radius:3px; }
  textarea { width: 100%; height: 6rem; }
  form { margin-bottom: 2rem; }
  .error { color:#f55; }
  .success { color:#5f5; }
  .file-list img, .file-list video, .file-list audio {
    max-width: 150px;
    margin-right: 10px;
  }
  .file-list {
    margin-top: 0.5rem;
  }
</style>
</head>
<body>

<h1>Mes Loadouts</h1>
<p><a href="index.php">Retour accueil</a> | <a href="logout.php">Déconnexion</a></p>

<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<form method="POST" enctype="multipart/form-data">
  <label>Nom du Loadout</label><br />
  <input type="text" name="name" required /><br />
  <label>Description</label><br />
  <textarea name="description"></textarea><br />
  <label>Contenu JSON</label><br />
  <textarea name="content" required>{}</textarea><br />
  <label>Fichiers (images, vidéos, audio, PDF) :</label><br />
  <input type="file" name="files[]" multiple accept="image/*,video/*,audio/*,application/pdf" /><br /><br />
  <button type="submit">Ajouter</button>
</form>

<table>
  <thead>
    <tr><th>ID</th><th>Nom</th><th>Description</th><th>Fichiers</th><th>Mis à jour</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php foreach ($loadouts as $loadout): 
      // Récupérer fichiers liés
      $stmtFiles = $pdo->prepare("SELECT * FROM loadout_files WHERE loadout_id = ?");
      $stmtFiles->execute([$loadout['id']]);
      $files = $stmtFiles->fetchAll();
    ?>
      <tr>
        <td><?= $loadout['id'] ?></td>
        <td><?= htmlspecialchars($loadout['name']) ?></td>
        <td><?= nl2br(htmlspecialchars($loadout['description'])) ?></td>
        <td class="file-list">
          <?php foreach ($files as $file):
            $filepath = '/uploads/loadouts/' . htmlspecialchars($file['filename']);
            if (strpos($file['filetype'], 'image/') === 0): ?>
              <img src="<?= $filepath ?>" alt="" />
            <?php elseif (strpos($file['filetype'], 'video/') === 0): ?>
              <video controls src="<?= $filepath ?>"></video>
            <?php elseif (strpos($file['filetype'], 'audio/') === 0): ?>
              <audio controls src="<?= $filepath ?>"></audio>
            <?php elseif ($file['filetype'] === 'application/pdf'): ?>
              <a href="<?= $filepath ?>" target="_blank">Voir PDF</a>
            <?php endif; ?>
          <?php endforeach; ?>
        </td>
        <td><?= $loadout['updated_at'] ?></td>
        <td>
          <a href="loadout_edit.php?id=<?= $loadout['id'] ?>">Modifier</a> |
          <a href="loadout_delete.php?id=<?= $loadout['id'] ?>" onclick="return confirm('Supprimer ce loadout ?')">Supprimer</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</body>
</html>
