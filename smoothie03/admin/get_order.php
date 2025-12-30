<?php
require_once '../config/db.php';

if(!isAdmin()) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT o.*, s.name as smoothie_name 
    FROM orders o 
    JOIN smoothies s ON o.smoothie_id = s.id 
    WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($order);
?>