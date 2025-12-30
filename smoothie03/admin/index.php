<?php 
require_once '../config/db.php';

if(!isAdmin()) {
    header('Location: login.php');
    exit;
}

// ลบออเดอร์ทั้งหมด
if(isset($_POST['delete_all_orders'])) {
    $pdo->exec("DELETE FROM orders");
    header('Location: index.php?success=ลบออเดอร์ทั้งหมดสำเร็จ');
    exit;
}

// ดึงข้อมูลออเดอร์
$stmt = $pdo->query("
    SELECT o.*, s.name as smoothie_name, s.price 
    FROM orders o 
    JOIN smoothies s ON o.smoothie_id = s.id 
    ORDER BY 
        CASE o.status
            WHEN 'รอดำเนินการ' THEN 1
            WHEN 'กำลังทำ' THEN 2
            WHEN 'เสร็จแล้ว' THEN 3
            ELSE 4
        END,
        o.created_at ASC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// สถิติ
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'รอดำเนินการ' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'กำลังทำ' THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN status = 'เสร็จแล้ว' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status IN ('เสร็จแล้ว', 'กำลังทำ', 'รอดำเนินการ') THEN total_price ELSE 0 END) as total_sales
    FROM orders
    WHERE DATE(created_at) = CURDATE()
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title>จัดการออเดอร์ - แอดมิน</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar h1 {
            font-size: 1.5em;
        }
        
        .navbar-menu {
            display: flex;
            gap: 15px;
        }
        
        .navbar-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            transition: 0.3s;
        }
        
        .navbar-menu a:hover,
        .navbar-menu a.active {
            background: rgba(255,255,255,0.2);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }

        .alert-success {
            padding: 15px 20px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #666;
            margin-top: 5px;
        }
        
        .orders-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .section-header h2 {
            color: #333;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
            font-size: 14px;
            font-weight: 500;
        }

        .refresh-select {
            padding: 10px 15px;
            border: 2px solid #667eea;
            border-radius: 6px;
            background: white;
            color: #667eea;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: 0.3s;
        }

        .refresh-select:hover {
            background: #667eea;
            color: white;
        }

        .refresh-btn {
            background: #667eea;
            color: white;
        }
        
        .refresh-btn:hover {
            background: #764ba2;
        }

        .delete-all-btn {
            background: #F44336;
            color: white;
        }

        .delete-all-btn:hover {
            background: #D32F2F;
            transform: translateY(-1px);
        }

        .refresh-status {
            font-size: 0.85em;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .refresh-status .dot {
            width: 8px;
            height: 8px;
            background: #4CAF50;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        .refresh-status.paused .dot {
            background: #F44336;
            animation: none;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th {
            background: #f9f9f9;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .orders-table tr:hover {
            background: #f9f9f9;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-รอดำเนินการ { background: #FFC107; color: #333; }
        .status-กำลังทำ { background: #2196F3; color: white; }
        .status-เสร็จแล้ว { background: #4CAF50; color: white; }
        .status-ยกเลิก { background: #F44336; color: white; }
        
        .action-btns {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85em;
            transition: 0.3s;
        }
        
        .btn-info { background: #2196F3; color: white; }
        .btn-success { background: #4CAF50; color: white; }
        .btn-warning { background: #FF9800; color: white; }
        .btn-danger { background: #F44336; color: white; }
        
        .btn-sm:hover {
            opacity: 0.8;
            transform: translateY(-1px);
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close-modal {
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: 0.3s;
        }

        .close-modal:hover {
            color: #333;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .slip-image {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 10px;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .confirm-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .confirm-modal.active {
            display: flex;
        }

        .confirm-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 450px;
            width: 90%;
            text-align: center;
        }

        .confirm-icon {
            width: 80px;
            height: 80px;
            background: #F44336;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 40px;
            color: white;
        }

        .confirm-content h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .confirm-content p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .confirm-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-confirm {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
            font-weight: 500;
        }

        .btn-confirm-yes {
            background: #F44336;
            color: white;
        }

        .btn-confirm-yes:hover {
            background: #D32F2F;
        }

        .btn-confirm-no {
            background: #e0e0e0;
            color: #333;
        }

        .btn-confirm-no:hover {
            background: #d0d0d0;
        }
    </style>
    
    <!-- เพิ่ม CSS สำหรับมือถือ -->
    <link rel="stylesheet" href="../css/mobile.css" media="screen and (max-width: 768px)">
</head>
<body>
    <div class="navbar">
        <h1>👨‍💼 ระบบจัดการร้านน้ำปั่น</h1>
        <div class="navbar-menu">
            <a href="index.php" class="active">📋 ออเดอร์</a>
            <a href="smoothies.php">🍹 เมนู</a>
            <a href="../index.php">🏠 หน้าร้าน</a>
            <a href="logout.php">🚪 ออกจากระบบ</a>
        </div>
    </div>
    
    <div class="container">
        <?php if(isset($_GET['success'])): ?>
            <div class="alert-success">
                <span>✓</span>
                <span><?= htmlspecialchars($_GET['success']) ?></span>
            </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_orders'] ?></div>
                <div class="stat-label">ออเดอร์วันนี้</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['pending'] ?></div>
                <div class="stat-label">รอดำเนินการ</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['processing'] ?></div>
                <div class="stat-label">กำลังทำ</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['completed'] ?></div>
                <div class="stat-label">เสร็จแล้ว</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['total_sales'], 0) ?> ฿</div>
                <div class="stat-label">ยอดขายวันนี้</div>
            </div>
        </div>
        
        <div class="orders-section">
            <div class="section-header">
                <h2>📋 รายการออเดอร์</h2>
                <div class="header-actions">
                    <select id="refreshInterval" class="refresh-select">
                        <option value="5000">รีเฟรชทุก 5 วินาที</option>
                        <option value="10000">รีเฟรชทุก 10 วินาที</option>
                        <option value="15000" selected>รีเฟรชทุก 15 วินาที</option>
                        <option value="30000">รีเฟรชทุก 30 วินาที</option>
                        <option value="60000">รีเฟรชทุก 1 นาที</option>
                        <option value="0">ปิดการรีเฟรช</option>
                    </select>
                    <div class="refresh-status" id="refreshStatus">
                        <span class="dot"></span>
                        <span id="statusText">Auto refresh: 15s</span>
                    </div>
                    <button class="btn refresh-btn" onclick="manualRefresh()">🔄 รีเฟรช</button>
                    <?php if(!empty($orders)): ?>
                        <button class="btn delete-all-btn" onclick="confirmDeleteAll()">🗑️ ลบทั้งหมด</button>
                    <?php endif; ?>
                </div>
            </div>
            
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>เมนู</th>
                        <th>ลูกค้า</th>
                        <th>รายละเอียด</th>
                        <th>ราคา</th>
                        <th>สถานะ</th>
                        <th>เวลา</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($orders)): ?>
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <h3>ยังไม่มีออเดอร์</h3>
                                    <p>รอลูกค้าสั่งซื้อ...</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td data-label="เลขที่"><strong>#<?= str_pad($order['id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                                <td data-label="เมนู"><?= htmlspecialchars($order['smoothie_name']) ?></td>
                                <td data-label="ลูกค้า"><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td data-label="รายละเอียด">
                                    <small>
                                        ความหวาน: <?= $order['sweetness_level'] ?><br>
                                        ชำระเงิน: <?= $order['payment_method'] ?>
                                    </small>
                                </td>
                                <td data-label="ราคา"><?= number_format($order['total_price'], 2) ?> ฿</td>
                                <td data-label="สถานะ">
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= $order['status'] ?>
                                    </span>
                                </td>
                                <td data-label="เวลา"><small><?= date('H:i', strtotime($order['created_at'])) ?></small></td>
                                <td data-label="จัดการ">
                                    <div class="action-btns">
                                        <button class="btn-sm btn-info" onclick="viewOrder(<?= $order['id'] ?>)" title="ดู">👁️</button>
                                        <?php if($order['status'] != 'เสร็จแล้ว' && $order['status'] != 'ยกเลิก'): ?>
                                            <button class="btn-sm btn-success" onclick="updateStatus(<?= $order['id'] ?>, 'เสร็จแล้ว')" title="เสร็จแล้ว">✓</button>
                                            <button class="btn-sm btn-warning" onclick="updateStatus(<?= $order['id'] ?>, 'กำลังทำ')" title="กำลังทำ">🔨</button>
                                        <?php endif; ?>
                                        <button class="btn-sm btn-danger" onclick="deleteOrder(<?= $order['id'] ?>)" title="ลบ">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Modal รายละเอียดออเดอร์ -->
    <div id="orderModal" class="modal" onclick="if(event.target === this) closeModal()">
        <div class="modal-content">
            <div class="modal-header">
                <h2>รายละเอียดออเดอร์</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div id="orderDetails"></div>
        </div>
    </div>

    <!-- Confirm Delete All Modal -->
    <div id="confirmModal" class="confirm-modal" onclick="if(event.target === this) closeConfirmModal()">
        <div class="confirm-content">
            <div class="confirm-icon">⚠️</div>
            <h3>ยืนยันการลบ</h3>
            <p>คุณแน่ใจหรือไม่ที่จะลบออเดอร์ทั้งหมด?<br>
            <strong style="color: #F44336;">การดำเนินการนี้ไม่สามารถย้อนกลับได้!</strong></p>
            <div class="confirm-buttons">
                <button class="btn-confirm btn-confirm-no" onclick="closeConfirmModal()">ยกเลิก</button>
                <button class="btn-confirm btn-confirm-yes" onclick="deleteAllOrders()">ยืนยันลบ</button>
            </div>
        </div>
    </div>

    <!-- Form สำหรับลบออเดอร์ทั้งหมด -->
    <form id="deleteAllForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_all_orders" value="1">
    </form>
    
    <script>
        let refreshTimer;
        let countdown;
        let timeLeft;
        
        function setAutoRefresh() {
            const interval = parseInt(document.getElementById('refreshInterval').value);
            const statusElement = document.getElementById('refreshStatus');
            const statusText = document.getElementById('statusText');
            
            if(refreshTimer) clearInterval(refreshTimer);
            if(countdown) clearInterval(countdown);
            
            if(interval > 0) {
                timeLeft = interval / 1000;
                
                refreshTimer = setInterval(() => {
                    location.reload();
                }, interval);
                
                countdown = setInterval(() => {
                    timeLeft--;
                    if(timeLeft <= 0) timeLeft = interval / 1000;
                    statusText.textContent = `รีเฟรชใน ${timeLeft}s`;
                }, 1000);
                
                statusElement.classList.remove('paused');
                statusText.textContent = `รีเฟรชใน ${timeLeft}s`;
                localStorage.setItem('refreshInterval', interval);
            } else {
                statusElement.classList.add('paused');
                statusText.textContent = 'ปิดการรีเฟรช';
                localStorage.setItem('refreshInterval', 0);
            }
        }
        
        function manualRefresh() {
            location.reload();
        }
        
        window.onload = function() {
            const savedInterval = localStorage.getItem('refreshInterval');
            if(savedInterval) {
                document.getElementById('refreshInterval').value = savedInterval;
            }
            setAutoRefresh();
        };
        
        document.getElementById('refreshInterval').addEventListener('change', setAutoRefresh);
        
        function viewOrder(id) {
            fetch(`get_order.php?id=${id}`)
                .then(res => res.json())
                .then(data => {
                    let html = `
                        <div class="detail-row">
                            <strong>เลขที่ออเดอร์:</strong>
                            <span>#${String(data.id).padStart(5, '0')}</span>
                        </div>
                        <div class="detail-row">
                            <strong>เมนู:</strong>
                            <span>${data.smoothie_name}</span>
                        </div>
                        <div class="detail-row">
                            <strong>ชื่อลูกค้า:</strong>
                            <span>${data.customer_name}</span>
                        </div>
                        <div class="detail-row">
                            <strong>ความหวาน:</strong>
                            <span>${data.sweetness_level}</span>
                        </div>
                        <div class="detail-row">
                            <strong>การชำระเงิน:</strong>
                            <span>${data.payment_method}</span>
                        </div>
                        <div class="detail-row">
                            <strong>ราคา:</strong>
                            <span>${parseFloat(data.total_price).toFixed(2)} บาท</span>
                        </div>
                        <div class="detail-row">
                            <strong>สถานะ:</strong>
                            <span class="status-badge status-${data.status}">${data.status}</span>
                        </div>
                        <div class="detail-row">
                            <strong>เวลาสั่ง:</strong>
                            <span>${new Date(data.created_at).toLocaleString('th-TH')}</span>
                        </div>
                    `;
                    
                    if(data.slip_image) {
                        html += `
                            <div style="margin-top: 20px;">
                                <strong>สลิปการโอนเงิน:</strong><br>
                                <img src="../uploads/slips/${data.slip_image}" class="slip-image">
                            </div>
                        `;
                    }
                    
                    document.getElementById('orderDetails').innerHTML = html;
                    document.getElementById('orderModal').classList.add('active');
                });
        }
        
        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
        }
        
        function updateStatus(id, status) {
            if(confirm(`ต้องการเปลี่ยนสถานะเป็น "${status}" ใช่หรือไม่?`)) {
                fetch('update_order.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${id}&status=${status}`
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาด');
                    }
                });
            }
        }
        
        function deleteOrder(id) {
            if(confirm('ต้องการลบออเดอร์นี้ใช่หรือไม่?')) {
                fetch('delete_order.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: `id=${id}`
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('เกิดข้อผิดพลาด');
                    }
                });
            }
        }

        function confirmDeleteAll() {
            document.getElementById('confirmModal').classList.add('active');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('active');
        }

        function deleteAllOrders() {
            document.getElementById('deleteAllForm').submit();
        }
    </script>
</body>
</html>