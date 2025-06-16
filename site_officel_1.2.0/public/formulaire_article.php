<!-- Formulaire pour ajouter ou éditer un article -->
<form method="POST" id="articleForm">
  <!-- Champ caché pour l'identifiant de l'article à éditer -->
  <input type="hidden" name="edit_id" value="<?= $edit_id ?>" />

  <!-- Champ pour le titre de l'article -->
  <label for="title">Titre</label>
  <input type="text" id="title" name="title" required value="<?= htmlspecialchars(isset($title) ? $title : '') ?>" />

  <!-- Champ pour le contenu de l'article -->
  <label for="content">Contenu</label>
  <textarea id="content" name="content" rows="8" required><?= htmlspecialchars(isset($content) ? $content : '') ?></textarea>

  <!-- Bouton pour soumettre le formulaire (Publier ou Mettre à jour selon le cas) -->
  <button type="submit"><?= $edit_id > 0 ? "Mettre à jour" : "Publier" ?></button>
</form>

<script>
// Ajoute un écouteur d'événement lors de la soumission du formulaire
document.getElementById('articleForm').addEventListener('submit', function(e) {
  // Sauvegarde le contenu TinyMCE dans le textarea
  tinymce.triggerSave();

  // Récupère et nettoie les valeurs du titre et du contenu
  const title = document.getElementById('title').value.trim();
  const content = document.getElementById('content').value.trim();

  // Vérifie si le titre ou le contenu sont vides
  if (!title || !content) {
    e.preventDefault(); // Empêche l'envoi du formulaire
    alert('Veuillez remplir le titre et le contenu.');
  }
});
</script>
