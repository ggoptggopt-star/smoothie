CREATE DATABASE smoothie_shop;
USE smoothie_shop;

-- ตารางแอดมิน
CREATE TABLE admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตารางเมนูน้ำปั่น
CREATE TABLE smoothies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    description TEXT,
    is_available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตารางออเดอร์
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    smoothie_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    sweetness_level ENUM('น้อย', 'ปานกลาง', 'หวาน') NOT NULL,
    payment_method ENUM('เงินสด', 'คนละครึ่ง', 'สแกน') NOT NULL,
    slip_image VARCHAR(255),
    status ENUM('รอดำเนินการ', 'กำลังทำ', 'เสร็จแล้ว', 'ยกเลิก') DEFAULT 'รอดำเนินการ',
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (smoothie_id) REFERENCES smoothies(id)
);

-- เพิ่มแอดมินตัวอย่าง (username: admin, password: admin123)
INSERT INTO admins (username, password) 
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
