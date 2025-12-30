<?php 
require_once 'config/db.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT o.*, s.name as smoothie_name, s.price 
    FROM orders o 
    JOIN smoothies s ON o.smoothie_id = s.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งซื้อสำเร็จ</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .success-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #4CAF50;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: white;
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        h1 {
            color: #4CAF50;
            margin-bottom: 10px;
        }
        
        .order-number {
            font-size: 1.2em;
            color: #667eea;
            margin-bottom: 30px;
            font-weight: bold;
        }
        
        .order-details {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.2em;
            color: #667eea;
        }
        
        .detail-label {
            color: #666;
        }
        
        .detail-value {
            color: #333;
            font-weight: 500;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 30px;
            margin: 5px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            transition: 0.3s;
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
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9em;
        }

        .status-รอดำเนินการ {
            background: #FFC107;
            color: #333;
        }

        .status-กำลังทำ {
            background: #2196F3;
            color: white;
        }

        .status-เสร็จแล้ว {
            background: #4CAF50;
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: none;
            animation: slideIn 0.5s ease;
            z-index: 1000;
        }

        .notification-toast.show {
            display: block;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .refresh-timer {
            color: #999;
            font-size: 0.9em;
            margin-top: 10px;
        }
    </style>
    <link rel="stylesheet" href="css/mobile.css" media="screen and (max-width: 768px)">
</head>
<body>
    <div class="notification-toast" id="notification">
        <h3>🎉 น้ำปั่นของคุณเสร็จแล้ว!</h3>
        <p>ออเดอร์ #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?> พร้อมเสิร์ฟแล้ว</p>
    </div>

    <div class="success-card">
        <div class="success-icon">✓</div>
        <h1>สั่งซื้อสำเร็จ!</h1>
        <div class="order-number">หมายเลขออเดอร์: #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></div>
        
        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">เมนู:</span>
                <span class="detail-value"><?= htmlspecialchars($order['smoothie_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ชื่อผู้สั่ง:</span>
                <span class="detail-value"><?= htmlspecialchars($order['customer_name']) ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ความหวาน:</span>
                <span class="detail-value"><?= $order['sweetness_level'] ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">การชำระเงิน:</span>
                <span class="detail-value"><?= $order['payment_method'] ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">สถานะ:</span>
                <span class="detail-value">
                    <span class="status-badge status-<?= $order['status'] ?>" id="orderStatus">
                        <?= $order['status'] ?>
                    </span>
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">ยอดรวม:</span>
                <span class="detail-value"><?= number_format($order['total_price'], 2) ?> บาท</span>
            </div>
        </div>
        
        <p style="color: #666; margin-bottom: 20px;">
            <span id="statusMessage">
                <?php if($order['status'] == 'เสร็จแล้ว'): ?>
                    🎉 น้ำปั่นของคุณพร้อมแล้ว! รับได้เลยครับ
                <?php else: ?>
                    กรุณารอสักครู่ เราจะแจ้งเตือนเมื่อน้ำปั่นของคุณเสร็จแล้ว
                <?php endif; ?>
            </span>
        </p>

        <div class="refresh-timer">
            หน้านี้จะรีเฟรชอัตโนมัติทุก 5 วินาที
        </div>
        
        <div style="margin-top: 20px;">
            <a href="index.php" class="btn btn-primary">🏠 กลับหน้าแรก</a>
            <a href="orders_public.php" class="btn btn-secondary">📋 ดูออเดอร์ทั้งหมด</a>
        </div>
    </div>
    
    <script>
        let currentStatus = '<?= $order['status'] ?>';
        let hasNotified = <?= $order['status'] == 'เสร็จแล้ว' ? 'true' : 'false' ?>;

        // ตรวจสอบสถานะออเดอร์ทุก 5 วินาที
        setInterval(function() {
            fetch('check_order_status.php?id=<?= $order_id ?>')
                .then(response => response.json())
                .then(data => {
                    if(data.status !== currentStatus) {
                        // สถานะเปลี่ยน - รีเฟรชหน้า
                        location.reload();
                    }
                    
                    if(data.status === 'เสร็จแล้ว' && !hasNotified) {
                        // แสดงการแจ้งเตือน
                        showNotification();
                        
                        // Browser notification
                        if(Notification.permission === "granted") {
                            new Notification("น้ำปั่นของคุณเสร็จแล้ว! 🎉", {
                                body: "ออเดอร์ #<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?> พร้อมเสิร์ฟแล้ว",
                                icon: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Ctext y='75' font-size='75'%3E🍹%3C/text%3E%3C/svg%3E"
                            });
                        }
                        
                        // เล่นเสียงแจ้งเตือน (ถ้าต้องการ)
                        playNotificationSound();
                        
                        hasNotified = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }, 5000); // ตรวจสอบทุก 5 วินาที

        // แสดง Toast notification
        function showNotification() {
            const toast = document.getElementById('notification');
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 5000);
        }

        // เล่นเสียงแจ้งเตือน
        function playNotificationSound() {
            // สร้างเสียง beep ง่ายๆ
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        }
        
        // ขอสิทธิ์แสดง notification
        if(Notification.permission !== "granted" && Notification.permission !== "denied") {
            Notification.requestPermission();
        }

        // ถ้าสถานะเสร็จแล้ว แสดง notification ทันที
        <?php if($order['status'] == 'เสร็จแล้ว'): ?>
        if(!hasNotified) {
            setTimeout(showNotification, 500);
        }
        <?php endif; ?>
        function playSuccessSound() {
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTGH0fPTgjMGHm7A7+OZURE');
    audio.play();
}
    </script>

</body>
</html>