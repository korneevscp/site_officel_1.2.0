<?php
// Démarre la session PHP
session_start();

// Détruit toutes les données de la session en cours
session_destroy();

// Redirige l'utilisateur vers la page d'accueil (index.php)
header("Location: index.php");

// Arrête l'exécution du script
exit;
