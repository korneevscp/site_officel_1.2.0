<?php
session_start();
require_once '../auth/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $file = $_FILES['photo'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if ($file['error'] === 0) {
        if (in_array($ext, $allowed)) {
            $newName = uniqid('report_', true) . '.' . $ext;
            $destination = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                echo json_encode(['status' => 'success', 'file' => $newName]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Erreur lors du déplacement du fichier.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Extension non autorisée.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Erreur de téléchargement.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Aucun fichier reçu.']);
}
