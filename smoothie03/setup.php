<?php
// setup.php - ไฟล์สร้างโฟลเดอร์อัตโนมัติ

$folders = [
    'uploads',
    'uploads/slips',
    'images'
];

echo "<h2>กำลังสร้างโฟลเดอร์...</h2>";

foreach($folders as $folder) {
    if(!is_dir($folder)) {
        if(mkdir($folder, 0777, true)) {
            echo "<p style='color: green;'>✓ สร้างโฟลเดอร์ <strong>$folder</strong> สำเร็จ</p>";
        } else {
            echo "<p style='color: red;'>✗ สร้างโฟลเดอร์ <strong>$folder</strong> ไม่สำเร็จ</p>";
        }
    } else {
        echo "<p style='color: blue;'>→ โฟลเดอร์ <strong>$folder</strong> มีอยู่แล้ว</p>";
    }
}

echo "<hr>";
echo "<h3>ตรวจสอบโฟลเดอร์:</h3>";

foreach($folders as $folder) {
    $exists = is_dir($folder) ? '✓ มีอยู่' : '✗ ไม่มี';
    $writable = is_writable($folder) ? '✓ เขียนได้' : '✗ เขียนไม่ได้';
    $color = (is_dir($folder) && is_writable($folder)) ? 'green' : 'red';
    
    echo "<p style='color: $color;'><strong>$folder:</strong> $exists | $writable</p>";
}

echo "<hr>";
echo "<p><a href='admin/login.php'>→ ไปหน้าเข้าสู่ระบบ</a></p>";
echo "<p><a href='index.php'>→ ไปหน้าแรก</a></p>";
?>