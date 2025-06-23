<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: loadouts.php');
    exit;
}

// Récupérer le loadout à éditer
$stmt = $pdo->prepare("SELECT * FROM loadouts WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$loadout = $stmt->fetch();

if (!$loadout) {
    die('Loadout non trouvé.');
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content = trim($_POST['content'] ?? '');

    // Validation JSON
    json_decode($content);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = "Contenu JSON invalide.";
    } elseif (!$name) {
        $error = "Le nom est requis.";
    } else {
        $stmt = $pdo->prepare("UPDATE loadouts SET name = ?, description = ?, content = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$name, $description, $content, $id, $user_id]);
        $success = "Loadout mis à jour.";
        // Recharger les données
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
<title>Modifier Loadout - TRDCRFT</title>

<!-- CodeMirror CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/theme/dracula.min.css" />

<style>
  body { background:#111; color:#eee; font-family: Arial, sans-serif; max-width: 900px; margin:auto; padding: 2rem; }
  input, textarea { background:#222; border:none; color:#eee; padding: 0.5rem; border-radius: 3px; width: 100%; }
  label { font-weight: bold; margin-top: 1rem; display: block; }
  button { margin-top: 1rem; background:#66aaff; border:none; color:#111; padding: 0.7rem 1rem; cursor:pointer; border-radius: 3px; }
  .error { color: #f55; }
  .success { color: #5f5; }
  #editor { height: 300px; border: 1px solid #444; margin-top: 0.5rem; }
  a { color:#66aaff; }
</style>

<!-- CodeMirror JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.13/mode/javascript/javascript.min.js"></script>

</head>
<body>

<h1>Modifier Loadout</h1>
<p><a href="loadouts.php">Retour</a> | <a href="logout.php">Déconnexion</a></p>

<?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<form method="POST" id="loadout-form">
  <label for="name">Nom</label>
  <input type="text" id="name" name="name" value="<?= htmlspecialchars($loadout['name']) ?>" required />

  <label for="description">Description</label>
  <textarea id="description" name="description"><?= htmlspecialchars($loadout['description']) ?></textarea>

  <label for="content">Contenu JSON</label>
  <textarea id="content" name="content" style="display:none;" required><?= htmlspecialchars($loadout['content']) ?></textarea>
  <div id="editor"></div>

  <button type="submit">Enregistrer</button>
</form>

<script>
  const textarea = document.getElementById('content');
  const editor = CodeMirror(document.getElementById('editor'), {
    value: textarea.value,
    mode: {name: "javascript", json: true},
    theme: "dracula",
    lineNumbers: true,
    tabSize: 2,
    autofocus: true,
  });

  // Synchroniser CodeMirror avec textarea au submit du formulaire
  const form = document.getElementById('loadout-form');
  form.addEventListener('submit', (e) => {
    // Validation JSON client
    try {
      JSON.parse(editor.getValue());
    } catch(err) {
      e.preventDefault();
      alert('JSON invalide : ' + err.message);
      return false;
    }
    textarea.value = editor.getValue();
  });
</script>

</body>
</html>

