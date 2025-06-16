<?php
session_start(); // Démarre la session PHP
require_once '../includes/db.php'; // Inclut le fichier de connexion à la base de données

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirige vers la page de connexion si non connecté
    exit;
}

$id = $_GET['id'] ?? null; // Récupère l'identifiant du loadout à supprimer depuis l'URL
if (!$id) {
    header('Location: loadouts.php'); // Redirige si aucun identifiant n'est fourni
    exit;
}

$user_id = $_SESSION['user_id']; // Récupère l'identifiant de l'utilisateur connecté
// Prépare et exécute la requête pour supprimer le loadout correspondant à l'utilisateur
$stmt = $pdo->prepare("DELETE FROM loadouts WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

header('Location: loadouts.php'); // Redirige vers la liste des loadouts après suppression
exit;
