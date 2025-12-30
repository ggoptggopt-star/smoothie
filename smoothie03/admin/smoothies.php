<?php 
require_once '../config/db.php';

if(!isAdmin()) {
    header('Location: login.php');
    exit;
}

// ประมวลผลการเพิ่ม/แก้ไข/ลบเมนู
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    // เพิ่มเมนูใหม่
    if($action == 'add') {
        $name = clean($_POST['name']);
        $price = (float)$_POST['price'];
        $description = clean($_POST['description']);
        $image = null;
        
        // สร้างโฟลเดอร์ถ้าไม่มี
        if(!is_dir('../uploads')) {
            mkdir('../uploads', 0777, true);
        }
        
        // อัพโหลดรูปภาพ
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                // ตรวจสอบขนาดไฟล์ (max 5MB)
                if($_FILES['image']['size'] <= 5242880) {
                    $new_filename = uniqid('smoothie_') . '.' . $ext;
                    $upload_path = '../uploads/' . $new_filename;
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $image = $new_filename;
                    } else {
                        header('Location: smoothies.php?error=อัพโหลดไฟล์ไม่สำเร็จ กรุณาลองใหม่');
                        exit;
                    }
                } else {
                    header('Location: smoothies.php?error=ไฟล์มีขนาดใหญ่เกิน 5MB');
                    exit;
                }
            } else {
                header('Location: smoothies.php?error=รองรับเฉพาะไฟล์ jpg, jpeg, png, gif, webp เท่านั้น');
                exit;
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO smoothies (name, price, description, image) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $price, $description, $image]);
        
        header('Location: smoothies.php?success=เพิ่มเมนูสำเร็จ');
        exit;
    }
    
    // แก้ไขเมนู
    if($action == 'edit') {
        $id = (int)$_POST['id'];
        $name = clean($_POST['name']);
        $price = (float)$_POST['price'];
        $description = clean($_POST['description']);
        $is_available = isset($_POST['is_available']) ? 1 : 0;
        
        // สร้างโฟลเดอร์ถ้าไม่มี
        if(!is_dir('../uploads')) {
            mkdir('../uploads', 0777, true);
        }
        
        // ตรวจสอบว่ามีการอัพโหลดรูปใหม่หรือไม่
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $filename = $_FILES['image']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                if($_FILES['image']['size'] <= 5242880) {
                    // ลบรูปเก่า
                    $old = $pdo->prepare("SELECT image FROM smoothies WHERE id = ?");
                    $old->execute([$id]);
                    $old_image = $old->fetchColumn();
                    if($old_image && file_exists('../uploads/' . $old_image)) {
                        unlink('../uploads/' . $old_image);
                    }
                    
                    $new_filename = uniqid('smoothie_') . '.' . $ext;
                    $upload_path = '../uploads/' . $new_filename;
                    
                    if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                        $stmt = $pdo->prepare("UPDATE smoothies SET name = ?, price = ?, description = ?, image = ?, is_available = ? WHERE id = ?");
                        $stmt->execute([$name, $price, $description, $new_filename, $is_available, $id]);
                    } else {
                        header('Location: smoothies.php?error=อัพโหลดไฟล์ไม่สำเร็จ');
                        exit;
                    }
                } else {
                    header('Location: smoothies.php?error=ไฟล์มีขนาดใหญ่เกิน 5MB');
                    exit;
                }
            } else {
                header('Location: smoothies.php?error=รองรับเฉพาะไฟล์ jpg, jpeg, png, gif, webp เท่านั้น');
                exit;
            }
        } else {
            // ไม่มีการอัพโหลดรูปใหม่
            $stmt = $pdo->prepare("UPDATE smoothies SET name = ?, price = ?, description = ?, is_available = ? WHERE id = ?");
            $stmt->execute([$name, $price, $description, $is_available, $id]);
        }
        
        header('Location: smoothies.php?success=แก้ไขเมนูสำเร็จ');
        exit;
    }
    
    // ลบเมนู
    if($action == 'delete') {
        $id = (int)$_POST['id'];
        
        // ลบรูปภาพ
        $stmt = $pdo->prepare("SELECT image FROM smoothies WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();
        if($image && file_exists('../uploads/' . $image)) {
            unlink('../uploads/' . $image);
        }
        
        // ลบเมนู
        $stmt = $pdo->prepare("DELETE FROM smoothies WHERE id = ?");
        $stmt->execute([$id]);
        
        header('Location: smoothies.php?success=ลบเมนูสำเร็จ');
        exit;
    }
}

// ดึงข้อมูลเมนูทั้งหมด
$stmt = $pdo->query("SELECT * FROM smoothies ORDER BY id DESC");
$smoothies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// สถิติ
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_menu,
        SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available,
        AVG(price) as avg_price
    FROM smoothies
")->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการเมนู - แอดมิน</title>
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-header h2 {
            color: #333;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .smoothies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .smoothie-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: 0.3s;
        }
        
        .smoothie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .smoothie-image {
            width: 100%;
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
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
        
        .availability-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            z-index: 1;
        }
        
        .badge-available {
            background: #4CAF50;
            color: white;
        }
        
        .badge-unavailable {
            background: #F44336;
            color: white;
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
            margin-bottom: 10px;
        }
        
        .smoothie-description {
            color: #666;
            margin-bottom: 15px;
            font-size: 0.9em;
            line-height: 1.5;
        }
        
        .action-btns {
            display: flex;
            gap: 10px;
        }
        
        .btn-sm {
            flex: 1;
            padding: 10px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9em;
            transition: 0.3s;
        }
        
        .btn-edit {
            background: #2196F3;
            color: white;
        }
        
        .btn-edit:hover {
            background: #1976D2;
        }
        
        .btn-delete {
            background: #F44336;
            color: white;
        }
        
        .btn-delete:hover {
            background: #D32F2F;
        }
        
        /* Modal Styles */
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
            padding: 20px;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            margin: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .modal-header h2 {
            color: #333;
        }
        
        .close-modal {
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: 0.3s;
        }
        
        .close-modal:hover {
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }
        
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
            transition: 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 2px dashed #667eea;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .image-preview {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            display: none;
        }
        
        .current-image {
            max-width: 100%;
            max-height: 200px;
            margin-top: 10px;
            border-radius: 8px;
            object-fit: contain;
        }
        
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        
        .btn-submit:hover {
            background: #764ba2;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state h3 {
            color: #666;
            margin-bottom: 20px;
        }

        .file-info {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
    </style>
    <link rel="stylesheet" href="css/mobile.css" media="screen and (max-width: 768px)">
</head>
<body>
    <div class="navbar">
        <h1>👨‍💼 ระบบจัดการร้านน้ำปั่น</h1>
        <div class="navbar-menu">
            <a href="index.php">📋 ออเดอร์</a>
            <a href="smoothies.php" class="active">🍹 เมนู</a>
            <a href="../index.php">🏠 หน้าร้าน</a>
            <a href="logout.php">🚪 ออกจากระบบ</a>
        </div>
    </div>
    
    <div class="container">
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <span>✓</span>
                <span><?= htmlspecialchars($_GET['success']) ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <span>✗</span>
                <span><?= htmlspecialchars($_GET['error']) ?></span>
            </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_menu'] ?></div>
                <div class="stat-label">เมนูทั้งหมด</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['available'] ?></div>
                <div class="stat-label">เมนูที่เปิดขาย</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= number_format($stats['avg_price'], 0) ?> ฿</div>
                <div class="stat-label">ราคาเฉลี่ย</div>
            </div>
        </div>
        
        <div class="section-header">
            <h2>🍹 จัดการเมนูน้ำปั่น</h2>
            <button class="btn btn-primary" onclick="openAddModal()">+ เพิ่มเมนูใหม่</button>
        </div>
        
        <?php if(empty($smoothies)): ?>
            <div class="empty-state">
                <h3>ยังไม่มีเมนูน้ำปั่น</h3>
                <p>เริ่มเพิ่มเมนูใหม่เลย!</p>
                <button class="btn btn-primary" onclick="openAddModal()" style="margin-top: 20px;">+ เพิ่มเมนูใหม่</button>
            </div>
        <?php else: ?>
            <div class="smoothies-grid">
                <?php foreach($smoothies as $smoothie): ?>
                    <div class="smoothie-card">
                        <div class="smoothie-image">
                            <?php 
                            $image_path = '../uploads/' . $smoothie['image'];
                            if($smoothie['image'] && file_exists($image_path)): 
                            ?>
                                <img src="<?= htmlspecialchars($image_path) ?>" 
                                     alt="<?= htmlspecialchars($smoothie['name']) ?>"
                                     onerror="this.style.display='none'; this.parentElement.innerHTML += '<div class=\'smoothie-image-placeholder\'>🍹</div>';">
                            <?php else: ?>
                                <div class="smoothie-image-placeholder">🍹</div>
                            <?php endif; ?>
                            <span class="availability-badge <?= $smoothie['is_available'] ? 'badge-available' : 'badge-unavailable' ?>">
                                <?= $smoothie['is_available'] ? '✓ เปิดขาย' : '✗ ปิดขาย' ?>
                            </span>
                        </div>
                        <div class="smoothie-info">
                            <div class="smoothie-name"><?= htmlspecialchars($smoothie['name']) ?></div>
                            <div class="smoothie-price"><?= number_format($smoothie['price'], 2) ?> บาท</div>
                            <?php if($smoothie['description']): ?>
                                <div class="smoothie-description"><?= htmlspecialchars($smoothie['description']) ?></div>
                            <?php endif; ?>
                            <div class="action-btns">
                                <button class="btn-sm btn-edit" onclick='openEditModal(<?= json_encode($smoothie, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                    ✏️ แก้ไข
                                </button>
                                <button class="btn-sm btn-delete" onclick="deleteSmoothie(<?= $smoothie['id'] ?>, '<?= addslashes($smoothie['name']) ?>')">
                                    🗑️ ลบ
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Modal เพิ่มเมนู -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>เพิ่มเมนูใหม่</h2>
                <span class="close-modal" onclick="closeAddModal()">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data" id="addForm">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label>ชื่อเมนู *</label>
                    <input type="text" name="name" required placeholder="เช่น น้ำมะม่วงปั่น">
                </div>
                
                <div class="form-group">
                    <label>ราคา (บาท) *</label>
                    <input type="number" name="price" step="0.01" min="0" required placeholder="0.00">
                </div>
                
                <div class="form-group">
                    <label>คำอธิบาย</label>
                    <textarea name="description" placeholder="รายละเอียดของเมนู (ถ้ามี)"></textarea>
                </div>
                
                <div class="form-group">
                    <label>รูปภาพ</label>
                    <input type="file" name="image" accept="image/*" onchange="previewAddImage(this)">
                    <div class="file-info">รองรับไฟล์: JPG, JPEG, PNG, GIF, WEBP (ขนาดไม่เกิน 5MB)</div>
                    <img id="addImagePreview" class="image-preview">
                </div>
                
                <button type="submit" class="btn-submit">เพิ่มเมนู</button>
            </form>
        </div>
    </div>
    
    <!-- Modal แก้ไขเมนู -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>แก้ไขเมนู</h2>
                <span class="close-modal" onclick="closeEditModal()">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <div class="form-group">
                    <label>ชื่อเมนู *</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                
                <div class="form-group">
                    <label>ราคา (บาท) *</label>
                    <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>คำอธิบาย</label>
                    <textarea name="description" id="edit_description"></textarea>
                </div>
                
                <div class="form-group">
                    <label>รูปภาพปัจจุบัน</label>
                    <img id="edit_current_image" class="current-image" style="display: none;">
                    <div id="edit_no_image" style="color: #999;">ไม่มีรูปภาพ</div>
                </div>
                
                <div class="form-group">
                    <label>เปลี่ยนรูปภาพ (ถ้าต้องการ)</label>
                    <input type="file" name="image" accept="image/*" onchange="previewEditImage(this)">
                    <div class="file-info">รองรับไฟล์: JPG, JPEG, PNG, GIF, WEBP (ขนาดไม่เกิน 5MB)</div>
                    <img id="editImagePreview" class="image-preview">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" name="is_available" id="edit_is_available" value="1">
                        <label for="edit_is_available" style="margin: 0;">เปิดขาย</label>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">บันทึกการแก้ไข</button>
            </form>
        </div>
    </div>
    
    <!-- Form สำหรับลบ (ซ่อน) -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" id="delete_id">
    </form>
    
    <script>
        // เปิด Modal เพิ่มเมนู
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
            document.getElementById('addForm').reset();
            document.getElementById('addImagePreview').style.display = 'none';
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
        }
        
        // เปิด Modal แก้ไขเมนู
        function openEditModal(smoothie) {
            document.getElementById('edit_id').value = smoothie.id;
            document.getElementById('edit_name').value = smoothie.name;
            document.getElementById('edit_price').value = smoothie.price;
            document.getElementById('edit_description').value = smoothie.description || '';
            document.getElementById('edit_is_available').checked = smoothie.is_available == 1;
            
            const currentImage = document.getElementById('edit_current_image');
            const noImage = document.getElementById('edit_no_image');
            
            if(smoothie.image) {
                currentImage.src = '../uploads/' + smoothie.image;
                currentImage.style.display = 'block';
                noImage.style.display = 'none';
            } else {
                currentImage.style.display = 'none';
                noImage.style.display = 'block';
            }
            
            document.getElementById('editImagePreview').style.display = 'none';
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        
        // ลบเมนู
        function deleteSmoothie(id, name) {
            if(confirm(`ต้องการลบเมนู "${name}" ใช่หรือไม่?\n\nการลบจะไม่สามารถกู้คืนได้`)) {
                document.getElementById('delete_id').value = id;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Preview รูปภาพสำหรับเพิ่มเมนู
        function previewAddImage(input) {
            const preview = document.getElementById('addImagePreview');
            if(input.files && input.files) {
                // ตรวจสอบขนาดไฟล์
                if(input.files.size > 5242880) {
                    alert('ไฟล์มีขนาดใหญ่เกิน 5MB');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // Preview รูปภาพสำหรับแก้ไขเมนู
        function previewEditImage(input) {
            const preview = document.getElementById('editImagePreview');
            if(input.files && input.files) {
                // ตรวจสอบขนาดไฟล์
                if(input.files.size > 5242880) {
                    alert('ไฟล์มีขนาดใหญ่เกิน 5MB');
                    input.value = '';
                    preview.style.display = 'none';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files);
            } else {
                preview.style.display = 'none';
            }
        }
        
        // ปิด Modal เมื่อคลิกนอก content
        window.onclick = function(event) {
            const addModal = document.getElementById('addModal');
            const editModal = document.getElementById('editModal');
            
            if(event.target == addModal) {
                closeAddModal();
            }
            if(event.target == editModal) {
                closeEditModal();
            }
        }

        // ป้องกันการ submit ซ้ำ
        document.getElementById('addForm').addEventListener('submit', function() {
            const btn = this.querySelector('.btn-submit');
            btn.disabled = true;
            btn.textContent = 'กำลังเพิ่มเมนู...';
        });

        document.getElementById('editForm').addEventListener('submit', function() {
            const btn = this.querySelector('.btn-submit');
            btn.disabled = true;
            btn.textContent = 'กำลังบันทึก...';
        });
    </script>
</body>
</html>