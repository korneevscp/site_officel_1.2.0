<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'trdcrft';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
} catch (PDOException $e) {
    echo json_encode([]);
    exit;
}

$q = isset($_POST['q']) ? trim($_POST['q']) : '';

if ($q !== '') {
    $stmt = $pdo->prepare("SELECT id, username, avatar FROM users WHERE username LIKE ? LIMIT 10");
    $stmt->execute(array('%' . $q . '%'));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
} else {
    echo json_encode([]);
}
