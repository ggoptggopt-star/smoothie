<?php 
require_once 'config/db.php';

$stmt = $pdo->query("
    SELECT o.*, s.name as smoothie_name 
    FROM orders o 
    JOIN smoothies s ON o.smoothie_id = s.id 
    ORDER BY o.created_at DESC 
    LIMIT 50
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title>ออเดอร์ทั้งหมด</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }
        
        header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        h1 {
            color: #667eea;
        }
        
        .btn {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn:hover {
            background: #764ba2;
        }
        
        .orders-grid {
            display: grid;
            gap: 20px;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 20px;
            align-items: center;
            transition: 0.3s;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        
        .order-number {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
        }
        
        .order-info {
            display: grid;
            gap: 5px;
        }
        
        .order-title {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
        }
        
        .order-detail {
            color: #666;
            font-size: 0.9em;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            white-space: nowrap;
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
        
        .status-ยกเลิก {
            background: #F44336;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 15px;
        }

        .refresh-timer {
            text-align: center;
            color: white;
            margin-bottom: 20px;
            font-size: 0.9em;
            background: rgba(255,255,255,0.2);
            padding: 10px;
            border-radius: 8px;
        }
    </style>
    
    <!-- เพิ่ม CSS สำหรับมือถือ -->
    <link rel="stylesheet" href="css/mobile.css" media="screen and (max-width: 768px)">
</head>
<body>
    <div class="container">
        <header>
            <h1>📋 ออเดอร์ทั้งหมด</h1>
            <a href="index.php" class="btn">← กลับหน้าแรก</a>
        </header>

        <div class="refresh-timer">
            หน้านี้จะรีเฟรชอัตโนมัติทุก <span id="countdown">10</span> วินาที
        </div>
        
        <div class="orders-grid">
            <?php if(empty($orders)): ?>
                <div class="empty-state">
                    <h2>ยังไม่มีออเดอร์</h2>
                    <p>เริ่มสั่งน้ำปั่นเลย!</p>
                    <a href="index.php" class="btn" style="display: inline-block; margin-top: 20px;">สั่งเลย</a>
                </div>
            <?php else: ?>
                <?php foreach($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-number">#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></div>
                        <div class="order-info">
                            <div class="order-title"><?= htmlspecialchars($order['smoothie_name']) ?></div>
                            <div class="order-detail">
                                👤 <?= htmlspecialchars($order['customer_name']) ?> | 
                                🍯 <?= $order['sweetness_level'] ?> | 
                                💳 <?= $order['payment_method'] ?>
                            </div>
                            <div class="order-detail">
                                🕐 <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?> | 
                                💰 <?= number_format($order['total_price'], 2) ?> บาท
                            </div>
                        </div>
                        <div class="status-badge status-<?= $order['status'] ?>">
                            <?= $order['status'] ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        let timeLeft = 10;
        const countdownElement = document.getElementById('countdown');
        
        // Countdown timer
        const countdownTimer = setInterval(() => {
            timeLeft--;
            if(countdownElement) {
                countdownElement.textContent = timeLeft;
            }
            
            if(timeLeft <= 0) {
                location.reload();
            }
        }, 1000);

        // Auto refresh ทุก 10 วินาที
        const refreshTimer = setInterval(() => {
            location.reload();
        }, 10000);

        // ล้าง timer เมื่อออกจากหน้า
        window.addEventListener('beforeunload', () => {
            clearInterval(countdownTimer);
            clearInterval(refreshTimer);
        });
    </script>
</body>
</html>
