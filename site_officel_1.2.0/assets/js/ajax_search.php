<?php
header('Content-Type: application/json');

if (!isset($_POST['q']) || empty($_POST['q'])) {
    echo json_encode([]);
    exit;
}

$search = trim($_POST['q']);

// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=trdcrft;charset=utf8mb4', 'root', ''); // À adapter si besoin
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([]);
    exit;
}

// Requête préparée pour rechercher les utilisateurs
$stmt = $pdo->prepare("
    SELECT id, username, avatar 
    FROM users 
    WHERE username LIKE :search 
    LIMIT 10
");

$stmt->execute(['search' => '%' . $search . '%']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);
