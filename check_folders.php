<?php
// check_folders.php - ตรวจสอบสถานะโฟลเดอร์

$folders = [
    'uploads' => 'เก็บรูปเมนูน้ำปั่น',
    'uploads/slips' => 'เก็บสลิปการโอนเงิน',
    'images' => 'เก็บ QR Code และรูปภาพอื่นๆ'
];

$files = [
    'images/qr-payment.png' => 'QR Code สำหรับรับเงิน'
];

echo "<style>
    body { font-family: Arial; padding: 20px; }
    .ok { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #667eea; color: white; }
</style>";

echo "<h2>ตรวจสอบโครงสร้างโฟลเดอร์</h2>";

echo "<h3>โฟลเดอร์:</h3>";
echo "<table>";
echo "<tr><th>โฟลเดอร์</th><th>คำอธิบาย</th><th>สถานะ</th><th>สิทธิ์การเขียน</th></tr>";

foreach($folders as $folder => $desc) {
    $exists = is_dir($folder);
    $writable = $exists ? is_writable($folder) : false;
    
    $status = $exists ? '<span class="ok">✓ มีอยู่</span>' : '<span class="error">✗ ไม่มี</span>';
    $write = $writable ? '<span class="ok">✓ เขียนได้</span>' : '<span class="error">✗ เขียนไม่ได้</span>';
    
    echo "<tr>";
    echo "<td><strong>$folder/</strong></td>";
    echo "<td>$desc</td>";
    echo "<td>$status</td>";
    echo "<td>$write</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>ไฟล์สำคัญ:</h3>";
echo "<table>";
echo "<tr><th>ไฟล์</th><th>คำอธิบาย</th><th>สถานะ</th></tr>";

foreach($files as $file => $desc) {
    $exists = file_exists($file);
    $status = $exists ? '<span class="ok">✓ มีอยู่</span>' : '<span class="warning">⚠ ไม่มี (ยังใช้งานได้)</span>';
    
    echo "<tr>";
    echo "<td><strong>$file</strong></td>";
    echo "<td>$desc</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

// แสดงโครงสร้างปัจจุบัน
echo "<h3>โครงสร้างไฟล์ปัจจุบัน:</h3>";
echo "<pre>";
function showTree($dir, $prefix = '') {
    $items = scandir($dir);
    foreach($items as $item) {
        if($item == '.' || $item == '..') continue;
        
        $path = $dir . '/' . $item;
        echo $prefix . ($is_dir = is_dir($path) ? '📁 ' : '📄 ') . $item . "\n";
        
        if($is_dir && $item != 'vendor' && $item != 'node_modules') {
            showTree($path, $prefix . '  ');
        }
    }
}
showTree('.');
echo "</pre>";

echo "<hr>";
echo "<h3>คำแนะนำ:</h3>";
echo "<ul>";
if(!is_dir('uploads')) echo "<li>สร้างโฟลเดอร์ <code>uploads</code></li>";
if(!is_dir('uploads/slips')) echo "<li>สร้างโฟลเดอร์ <code>uploads/slips</code></li>";
if(!is_dir('images')) echo "<li>สร้างโฟลเดอร์ <code>images</code></li>";
if(!file_exists('images/qr-payment.png')) echo "<li class='warning'>เพิ่มไฟล์ <code>images/qr-payment.png</code> (ไม่บังคับ)</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='setup.php'>→ สร้างโฟลเดอร์อัตโนมัติ</a></p>";
echo "<p><a href='index.php'>→ ไปหน้าแรก</a></p>";
?>
