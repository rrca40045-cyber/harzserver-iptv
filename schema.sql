-- ============================================
-- شغل هاد الملف مرة وحدة فـ phpMyAdmin ديال الاستضافة
-- ============================================

CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS channels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category_id INT DEFAULT 1,
    logo VARCHAR(500) DEFAULT '',
    stream_url TEXT NOT NULL,
    epg_id VARCHAR(255) DEFAULT '',
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- إلا ماكانش عندك جدول subscribers ديجا فـ البانل، هادو هوما الأعمدة اللي خاصك
CREATE TABLE IF NOT EXISTS subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(100) NOT NULL,
    expire_date DATETIME NOT NULL,
    status ENUM('active','expired','disabled') DEFAULT 'active',
    max_connections INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO categories (name) VALUES ('عام') ON DUPLICATE KEY UPDATE name=name;
