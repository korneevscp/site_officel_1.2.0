<?php
// Définition des paramètres de connexion à la base de données
$host = 'localhost';
$db   = 'trdcrft';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Création de la chaîne de connexion DSN pour PDO
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Options pour la connexion PDO (gestion des erreurs, mode de récupération)
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Affiche les erreurs sous forme d'exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC        // Récupère les résultats sous forme de tableau associatif
];

try {
    // Tentative de connexion à la base de données avec PDO
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Affiche un message d'erreur en cas d'échec de connexion
    die('Erreur DB : ' . $e->getMessage());
}
?>
