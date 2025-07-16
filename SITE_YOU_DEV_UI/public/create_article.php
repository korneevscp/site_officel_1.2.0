<?php
session_start(); // Démarre la session PHP
require_once '../includes/db.php'; // Inclut la connexion à la base de données

// Vérifie si utilisateur connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirige vers la page de connexion si non connecté
    exit;
}

$error = '';
$success = '';
$title = '';
$content = '';

// Si le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim(isset($_POST['title']) ? $_POST['title'] : ''); // Récupère et nettoie le titre
    $content = trim(isset($_POST['content']) ? $_POST['content'] : ''); // Récupère et nettoie le contenu
    
    // Vérifie si le titre est vide
    if (!$title) {
        $error = "Le titre est obligatoire.";
    } elseif (!$content) { // Vérifie si le contenu est vide
        $error = "Le contenu est obligatoire.";
    } else {
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Créer un article - NEXORA</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png" />
    <link rel="stylesheet" href="../assets/css/create_article.css" />
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

        /* Header */
        header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        header h1 {
            text-align: center;
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .user-links {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .user-links a {
            padding: 0.5rem 1rem;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .user-links a:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.4);
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
        form {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
            .user-links {
                gap: 0.5rem;
            }

            .user-links a {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }

            header h1 {
                font-size: 1.5rem;
            }

            form {
                margin: 1rem;
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {
            header h1 {
                font-size: 1.3rem;
            }

            form {
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

        form {
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
    
    <!-- Intègre l'éditeur TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/8evtsb6e56jf07xb5lj1pyiqxqm80vhnih1mdlc0op47kiav/tinymce/6/tinymce.min.js"
        referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#content',
            width: '100%',
            height: 250,           // hauteur totale de l'éditeur (px)
            skin: 'oxide-dark',
            content_css: 'dark',
            menubar: false,
            plugins: 'lists link image preview',
            toolbar: 'undo redo | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | preview',
            content_style: "body { margin:0; padding:0.5rem; line-height:1.8; }"
        });
    </script>
</head>
<body>
    <header>
        <h1>NEXORA - créer un article</h1>
        <div class="user-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php">Accueil 🏠</a>
                <a href="mes_articles.php">Mes articles ✍️</a>
                <a href="profile.php">Profil 👤</a>
                <a href="logout.php">Déconnexion 🚪</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($error): ?>
        <!-- Affiche un message d'erreur si besoin -->
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <!-- Affiche un message de succès si besoin -->
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <!-- Formulaire de création d'article -->
    <form method="POST" id="articleForm">
        <label for="title">Titre</label>
        <input type="text" id="title" name="title" />
        
        <label for="content">Contenu</label>
        <textarea id="content" name="content" rows="8"><?= htmlspecialchars($content) ?></textarea>
        
        <button type="submit">Publier</button>
    </form>

    <script>
        // Validation côté client avant soumission du formulaire
        document.getElementById('articleForm').addEventListener('submit', function (e) {
            const title = document.getElementById('title').value.trim();
            const content = tinymce.get('content').getContent({ format: 'text' }).trim();
            
            if (!title || !content) {
                e.preventDefault();
                alert('Veuillez remplir le titre et le contenu.');
            }
        });
    </script>

    <footer>
    </footer>
</body>
</html>
