<form method="POST" id="articleForm">
  <input type="hidden" name="edit_id" value="<?= $edit_id ?>" />

  <label for="title">Titre</label>
  <input type="text" id="title" name="title" required value="<?= htmlspecialchars(isset($title) ? $title : '') ?>" />

  <label for="content">Contenu</label>
  <textarea id="content" name="content" rows="8" required><?= htmlspecialchars(isset($content) ? $content : '') ?></textarea>

  <button type="submit"><?= $edit_id > 0 ? "Mettre à jour" : "Publier" ?></button>
</form>

<script>
document.getElementById('articleForm').addEventListener('submit', function(e) {
  tinymce.triggerSave();

  const title = document.getElementById('title').value.trim();
  const content = document.getElementById('content').value.trim();

  if (!title || !content) {
    e.preventDefault();
    alert('Veuillez remplir le titre et le contenu.');
  }
});
</script>
