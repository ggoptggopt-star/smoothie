<?php 
require_once 'config/db.php';

// ดึงข้อมูลเมนูน้ำปั่นที่พร้อมขาย
$stmt = $pdo->query("SELECT * FROM smoothies WHERE is_available = 1 ORDER BY id DESC");
$smoothies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ร้านน้ำปั่น</title>
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
            font-size: 2em;
        }
        
        .btn-group a {
            margin-left: 10px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }
        
        .btn-group a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .smoothie-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .smoothie-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: 0.3s;
            cursor: pointer;
        }
        
        .smoothie-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        
        .smoothie-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        
        .smoothie-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .smoothie-image-placeholder {
            color: white;
            font-size: 4em;
        }
        
        .smoothie-info {
            padding: 20px;
        }
        
        .smoothie-name {
            font-size: 1.3em;
            color: #333;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .smoothie-price {
            font-size: 1.5em;
            color: #667eea;
            font-weight: bold;
        }
        
        .smoothie-description {
            color: #666;
            margin-top: 10px;
            font-size: 0.9em;
            line-height: 1.5;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 15px;
            color: #666;
        }

        .image-error {
            color: white;
            font-size: 4em;
        }
    </style>
    <link rel="stylesheet" href="css/mobile.css" media="screen and (max-width: 768px)">
</head>
<body>
    <div class="container">
        <header>
            <h1>🍹 ร้านน้ำปั่นนิตูม</h1>
            <div class="btn-group">
                <a href="orders_public.php">📋 ดูออเดอร์ทั้งหมด</a>
                <?php if(isAdmin()): ?>
                    <a href="admin/">👨‍💼 หลังบ้าน</a>
                <?php else: ?>
                    <a href="admin/login.php">🔐 เข้าสู่ระบบ</a>
                <?php endif; ?>
            </div>
        </header>

        <div class="smoothie-grid">
            <?php if(empty($smoothies)): ?>
                <div class="empty-state" style="grid-column: 1/-1;">
                    <h2>ยังไม่มีเมนูน้ำปั่น</h2>
                    <p>กรุณารอสักครู่...</p>
                </div>
            <?php else: ?>
                <?php foreach($smoothies as $smoothie): ?>
                    <div class="smoothie-card" onclick="location.href='order.php?id=<?= $smoothie['id'] ?>'">
                        <div class="smoothie-image">
                            <?php 
                            $image_path = 'uploads/' . $smoothie['image'];
                            if($smoothie['image'] && file_exists($image_path)): 
                            ?>
                                <img src="<?= htmlspecialchars($image_path) ?>" 
                                     alt="<?= htmlspecialchars($smoothie['name']) ?>"
                                     onerror="this.style.display='none'; this.parentElement.innerHTML='<div class=\'image-error\'>🍹</div>';">
                            <?php else: ?>
                                <div class="smoothie-image-placeholder">🍹</div>
                            <?php endif; ?>
                        </div>
                        <div class="smoothie-info">
                            <div class="smoothie-name"><?= htmlspecialchars($smoothie['name']) ?></div>
                            <div class="smoothie-price"><?= number_format($smoothie['price'], 2) ?> บาท</div>
                            <?php if($smoothie['description']): ?>
                                <div class="smoothie-description"><?= htmlspecialchars($smoothie['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Auto refresh ทุก 30 วินาที
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>