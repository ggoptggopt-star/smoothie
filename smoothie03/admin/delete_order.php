<?php
require_once '../config/db.php';

if(!isAdmin()) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

$stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
$success = $stmt->execute([$id]);

echo json_encode(['success' => $success]);
?>