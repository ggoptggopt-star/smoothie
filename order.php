<?php 
require_once 'config/db.php';

$smoothie_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ดึงข้อมูลน้ำปั่น
$stmt = $pdo->prepare("SELECT * FROM smoothies WHERE id = ? AND is_available = 1");
$stmt->execute([$smoothie_id]);
$smoothie = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$smoothie) {
    header('Location: index.php');
    exit;
}

// ประมวลผลการสั่งซื้อ
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_name = clean($_POST['customer_name']);
    $sweetness = clean($_POST['sweetness']);
    $payment_method = clean($_POST['payment_method']);
    $slip_image = null;
    
    // อัพโหลดสลิป (ถ้ามี)
    if($payment_method == 'สแกน' && isset($_FILES['slip']) && $_FILES['slip']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['slip']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if(in_array($ext, $allowed)) {
            $new_filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/slips/' . $new_filename;
            
            if(move_uploaded_file($_FILES['slip']['tmp_name'], $upload_path)) {
                $slip_image = $new_filename;
            }
        }
    }
    
    // บันทึกออเดอร์
    $stmt = $pdo->prepare("INSERT INTO orders (smoothie_id, customer_name, sweetness_level, payment_method, slip_image, total_price) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$smoothie_id, $customer_name, $sweetness, $payment_method, $slip_image, $smoothie['price']]);
    
    $order_id = $pdo->lastInsertId();
    header("Location: order_success.php?id=$order_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งซื้อ - <?= htmlspecialchars($smoothie['name']) ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .product-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .product-header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .product-header .price {
            font-size: 1.5em;
            font-weight: bold;
        }
        
        .order-form {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
            font-weight: bold;
        }
        
        input[type="text"],
        select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s;
        }
        
        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .sweetness-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }
        
        .sweetness-option {
            position: relative;
        }
        
        .sweetness-option input[type="radio"] {
            display: none;
        }
        
        .sweetness-option label {
            display: block;
            padding: 15px;
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: 0.3s;
            margin: 0;
        }
        
        .sweetness-option input[type="radio"]:checked + label {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .payment-options {
            display: grid;
            gap: 10px;
        }
        
        .payment-option {
            position: relative;
        }
        
        .payment-option input[type="radio"] {
            display: none;
        }
        
        .payment-option label {
            display: block;
            padding: 15px;
            background: #f5f5f5;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
            margin: 0;
        }
        
        .payment-option input[type="radio"]:checked + label {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        #qr-code {
            display: none;
            text-align: center;
            margin-top: 15px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
        }
        
        #qr-code img {
            max-width: 200px;
            margin: 10px 0;
        }
        
        #slip-upload {
            display: none;
            margin-top: 15px;
        }
        
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #667eea;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            flex: 1;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            text-align: center;
            display: block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #d0d0d0;
        }
    </style>
    <link rel="stylesheet" href="css/mobile.css" media="screen and (max-width: 768px)">
</head>
<body>
    <div class="container">
        <div class="order-card">
            <div class="product-header">
                <h1><?= htmlspecialchars($smoothie['name']) ?></h1>
                <div class="price"><?= number_format($smoothie['price'], 2) ?> บาท</div>
            </div>
            
            <form class="order-form" method="POST" enctype="multipart/form-data" id="orderForm">
                <div class="form-group">
                    <label>ชื่อผู้สั่ง *</label>
                    <input type="text" name="customer_name" required placeholder="กรอกชื่อของคุณ">
                </div>
                
                <div class="form-group">
                    <label>ระดับความหวาน *</label>
                    <div class="sweetness-options">
                        <div class="sweetness-option">
                            <input type="radio" name="sweetness" value="น้อย" id="sweet1" required>
                            <label for="sweet1">🧊 น้อย</label>
                        </div>
                        <div class="sweetness-option">
                            <input type="radio" name="sweetness" value="ปานกลาง" id="sweet2" checked>
                            <label for="sweet2">🍹 ปานกลาง</label>
                        </div>
                        <div class="sweetness-option">
                            <input type="radio" name="sweetness" value="หวาน" id="sweet3">
                            <label for="sweet3">🍯 หวาน</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>ช่องทางการชำระเงิน *</label>
                    <div class="payment-options">
                        <div class="payment-option">
                            <input type="radio" name="payment_method" value="เงินสด" id="payment1" checked>
                            <label for="payment1">💵 จ่ายเงินสด</label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" name="payment_method" value="คนละครึ่ง" id="payment2">
                            <label for="payment2">🏛️ คนละครึ่ง</label>
                        </div>
                        <div class="payment-option">
                            <input type="radio" name="payment_method" value="สแกน" id="payment3">
                            <label for="payment3">📱 สแกน QR Code</label>
                        </div>
                    </div>
                    
                    <div id="qr-code">
                        <h3>สแกน QR Code เพื่อชำระเงิน</h3>
                        <img src="images/qr-payment.png" alt="QR Code" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect width=%22200%22 height=%22200%22 fill=%22%23ddd%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22%3EQR Code%3C/text%3E%3C/svg%3E'">
                        <p>ยอดชำระ: <strong><?= number_format($smoothie['price'], 2) ?> บาท</strong></p>
                    </div>
                    
                    <div id="slip-upload">
                        <label>แนบสลิปการโอนเงิน *</label>
                        <input type="file" name="slip" accept="image/*" id="slipFile">
                    </div>
                </div>
                
                <div class="btn-group">
                    <a href="index.php" class="btn btn-secondary">← กลับ</a>
                    <button type="submit" class="btn btn-primary">ส่งออเดอร์ →</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // แสดง/ซ่อน QR Code และช่องอัพโหลดสลิป
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const qrCode = document.getElementById('qr-code');
                const slipUpload = document.getElementById('slip-upload');
                const slipFile = document.getElementById('slipFile');
                
                if(this.value === 'สแกน') {
                    qrCode.style.display = 'block';
                    slipUpload.style.display = 'block';
                    slipFile.required = true;
                } else {
                    qrCode.style.display = 'none';
                    slipUpload.style.display = 'none';
                    slipFile.required = false;
                }
            });
        });
    </script>
</body>
</html>
