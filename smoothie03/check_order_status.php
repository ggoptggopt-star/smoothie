<?php
require_once 'config/db.php';

header('Content-Type: application/json');

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if($order) {
    echo json_encode($order);
} else {
    echo json_encode(['status' => null]);
}
?>