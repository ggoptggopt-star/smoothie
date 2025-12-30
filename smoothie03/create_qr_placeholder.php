<?php
// create_qr_placeholder.php - สร้างรูป QR แบบ placeholder

header('Content-Type: image/png');

$width = 300;
$height = 300;

$image = imagecreate($width, $height);
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$purple = imagecolorallocate($image, 102, 126, 234);

imagefilledrectangle($image, 0, 0, $width, $height, $white);

// วาดกรอบ
imagerectangle($image, 10, 10, $width-10, $height-10, $purple);
imagerectangle($image, 30, 30, $width-30, $height-30, $black);

// เขียนข้อความ
$text1 = "QR Code";
$text2 = "Payment";
$text3 = "Scan Here";

imagestring($image, 5, 110, 100, $text1, $black);
imagestring($image, 5, 100, 130, $text2, $purple);
imagestring($image, 3, 105, 170, $text3, $black);

// บันทึกไฟล์
if(!is_dir('images')) {
    mkdir('images', 0777, true);
}

imagepng($image, 'images/qr-payment.png');
imagedestroy($image);

echo "QR Code placeholder ถูกสร้างแล้วที่ images/qr-payment.png";
?>