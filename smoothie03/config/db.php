<?php
session_start();

$host = 'localhost';
$dbname = 'smoothie_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $e->getMessage());
}

// ฟังก์ชันตรวจสอบการล็อกอินแอดมิน
function isAdmin() {
    return isset($_SESSION['admin_id']);
}

// ฟังก์ชันรับข้อมูลแบบปลอดภัย
function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>