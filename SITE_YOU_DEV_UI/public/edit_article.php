<?php
session_start(); // Démarre la session PHP pour gérer l'authentification
require_once '../includes/db.php'; // Inclut le fichier de connexion à la base de données

// Vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';
$title = '';
$content = '';
$edit_id = 0;

// Préremplissage du formulaire si un edit_id est présent dans l'URL (méthode GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Modifier un article - NEXORA</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png" />
    
    <!-- Inclusion de TinyMCE pour l'éditeur de texte enrichi -->
    <script src="https://cdn.tiny.cloud/1/8evtsb6e56jf07xb5lj1pyiqxqm80vhnih1mdlc0op47kiav/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        // Initialisation de TinyMCE sur le textarea "content"
        tinymce.init({
            selector: '#content',
            width: '100%',
            height: 250,
            skin: 'oxide-dark',
            content_css: 'dark',
            menubar: false,
            plugins: 'lists link image preview',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | preview',
            content_style: "body { margin:0; padding:0.5rem; line-height:1.8; }"
        });
    </script>

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
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            flex-wrap: wrap;
        }

        .nav-links a {
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

        .nav-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
        }

        /* Header */
        .page-header {
            text-align: center;
            padding: 3rem 1rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 0.5rem;
        }

        /* Messages */
        .error, .success {
            padding: 1rem;
            margin: 1rem auto;
            max-width: 800px;
            border-radius: 10px;
            text-align: center;
            font-weight: 500;
        }

        .success {
            background: linear-gradient(45deg, #4caf50, #81c784);
            color: white;
        }

        .error {
            background: linear-gradient(45deg, #f44336, #ef5350);
            color: white;
        }

        /* Formulaire */
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.5s ease;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.3s ease;
            margin-bottom: 1.5rem;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        button[type="submit"] {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            display: block;
            margin: 0 auto;
        }

        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            color: white;
            margin-top: 2rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .nav-links {
                gap: 0.5rem;
            }

            .nav-links a {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .form-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            .nav-links a {
                padding: 0.5rem 0.8rem;
                font-size: 0.8rem;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .form-container {
                padding: 1rem;
            }

            button[type="submit"] {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
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
        <!-- Menu de navigation -->
        <div class="nav-links">
            <a href="index.php">🏠 Accueil</a>
            <a href="create_article.php">✏️ Créer</a>
            <a href="mes_articles.php">📝 Mes articles</a>
            <a href="profile.php">👤 Profil</a>
            <a href="logout.php">🚪 Déconnexion</a>
        </div>
    </nav>

    <div class="page-header">
        <h1>Modifier un article</h1>
    </div>

    <!-- Affichage des messages d'erreur ou de succès -->
    <?php if (!empty($error)): ?>
        <p class="error"><</p>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <p class="success"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <!-- Formulaire de modification d'article -->
    <div class="form-container">
        <form method="POST" id="articleForm">
            <input type="hidden" name="edit_id" value="<?php echo intval($edit_id); ?>" />

            <label for="title">Titre</label>
            <input type="text" id="title" name="title" required value

            <label for="content">Contenu</label>
            <textarea id="content" name="content" rows="8"><?php echo $content; ?></textarea>

            <button type="submit">Mettre à jour</button>
        </form>
    </div>

    <footer>

    </footer>

    <script>
        // Validation côté client avant soumission du formulaire
        document.getElementById('articleForm').addEventListener('submit', function(e) {
            tinymce.triggerSave(); // Sauvegarde le contenu TinyMCE dans le textarea

            var title = document.getElementById('title').value.trim();
            var content = document.getElementById('content').value.trim();

            if (!title || !content) {
                e.preventDefault();
                alert('Veuillez remplir le titre et le contenu.');
            }
        });
    </script>

</body>
</html>
