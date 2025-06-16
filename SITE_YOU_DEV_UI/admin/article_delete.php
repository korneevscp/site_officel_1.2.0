<?php
require_once '../includes/db.php';
session_start();
if (!isset($_SESSION['admin_id'])) exit;

$id = $_GET['id'];
$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
$stmt->execute([$id]);

header('Location: dashboard.php');
