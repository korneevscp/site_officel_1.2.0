<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: loadouts.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("DELETE FROM loadouts WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);

header('Location: loadouts.php');
exit;
