<?php
require_once '../config/db.php';

if(!isAdmin()) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$status = isset($_POST['status']) ? clean($_POST['status']) : '';

$allowed_statuses = ['รอดำเนินการ', 'กำลังทำ', 'เสร็จแล้ว', 'ยกเลิก'];

if(in_array($status, $allowed_statuses)) {
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $success = $stmt->execute([$status, $id]);
    
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false]);
}
?>