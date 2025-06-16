<?php
session_start(); // Démarre la session PHP
require_once '../includes/db.php'; // Inclut la connexion à la base de données

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php'); // Redirige vers la page de connexion si non connecté
  exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null; // Récupère l'ID du loadout à éditer depuis l'URL
if (!$id) {
  header('Location: loadouts.php'); // Redirige si aucun ID fourni
  exit;
}

// Récupérer le loadout à éditer depuis la base de données
$stmt = $pdo->prepare("SELECT * FROM loadouts WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$loadout = $stmt->fetch();

if (!$loadout) {
  die('Loadout non trouvé.'); // Arrête le script si le loadout n'existe pas ou n'appartient pas à l'utilisateur
}

$error = $success = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? ''); // Récupère et nettoie le nom
  $description = trim($_POST['description'] ?? ''); // Récupère et nettoie la description
  $content = trim($_POST['content'] ?? ''); // Récupère et nettoie le contenu JSON

  // Validation du JSON
  json_decode($content);
  if (json_last_error() !== JSON_ERROR_NONE) {
    $error = "Contenu JSON invalide."; // Erreur si le JSON n'est pas valide
  } elseif (!$name) {
    $error = "Le nom est requis."; // Erreur si le nom est vide
  } else {
    // Met à jour le loadout dans la base de données
    $stmt = $pdo->prepare("UPDATE loadouts SET name = ?, description = ?, content = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$name, $description, $content, $id, $user_id]);
    $success = "Loadout mis à jour."; // Message de succès
    // Recharge les données modifiées
    $loadout['name'] = $name;
    $loadout['description'] = $description;
    $loadout['content'] = $content;
  }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8" />
<title>Modifier Loadout - NEXORA </title>
<link rel="icon" type="image/png" href="../assets/images/logo.png" />
<!-- CodeMirror CSS pour l'éditeur de code -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css" />

<style>
  /* Styles de base pour la page */
  body { background:#111; color:#eee; font-family: Arial, sans-serif; max-width: 900px; margin:auto; padding: 2rem; }
  input, textarea { background:#222; border:none; color:#eee; padding: 0.5rem; border-radius: 3px; width: 100%; }
  label { font-weight: bold; margin-top: 1rem; display: block; }
  button { margin-top: 1rem; background:#66aaff; border:none; color:#111; padding: 0.7rem 1rem; cursor:pointer; border-radius: 3px; }
  .error { color: #f55; }
  .success { color: #5f5; }
  #editor { height: 300px; border: 1px solid #444; margin-top: 0.5rem; }
  a { color:#66aaff; }
</style>

<!-- CodeMirror JS pour l'éditeur de code JSON -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>

</head>
<body>

<h1>Modifier Loadout</h1>
<p><a href="loadouts.php">Retour</a> | <a href="logout.php">Déconnexion</a></p>

<!-- Affiche les messages d'erreur ou de succès -->
<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<!-- Formulaire d'édition du loadout -->
<form method="POST" id="loadout-form">
  <label for="name">Nom</label>
  <input type="text" id="name" name="name" value="<?= htmlspecialchars($loadout['name']) ?>" required />

  <label for="description">Description</label>
  <textarea id="description" name="description"><?= htmlspecialchars($loadout['description']) ?></textarea>

  <label for="content">Contenu JSON</label>
  <!-- Zone cachée pour le JSON, synchronisée avec CodeMirror -->
  <textarea id="content" name="content" style="display:none;" required><?= htmlspecialchars($loadout['content']) ?></textarea>
  <div id="editor"></div> <!-- Éditeur CodeMirror -->

  <button type="submit">Enregistrer</button>
</form>

<script>
  // Initialise CodeMirror pour éditer le JSON
  const textarea = document.getElementById('content');
  const editor = CodeMirror(document.getElementById('editor'), {
  value: textarea.value,
  mode: {name: "javascript", json: true},
  theme: "dracula",
  lineNumbers: true,
  tabSize: 2,
  autofocus: true,
  });

  // Synchronise CodeMirror avec le textarea lors de la soumission du formulaire
  const form = document.getElementById('loadout-form');
  form.addEventListener('submit', (e) => {
  // Validation JSON côté client
  try {
    JSON.parse(editor.getValue());
  } catch(err) {
    e.preventDefault();
    alert('JSON invalide : ' + err.message);
    return false;
  }
  textarea.value = editor.getValue(); // Met à jour le textarea avec le contenu de CodeMirror
  });
</script>

</body>
</html>
