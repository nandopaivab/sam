-- TrendHunter Brasil Database Schema
CREATE DATABASE IF NOT EXISTS trendhunter_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE trendhunter_db;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    dark_mode TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Products Table (Saves details for tracked/monitored products and search cache results)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marketplace VARCHAR(50) NOT NULL,
    external_id VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    image_url TEXT DEFAULT NULL,
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2) DEFAULT NULL,
    sales_count_est INT DEFAULT 0,
    reviews_count INT DEFAULT 0,
    rating DECIMAL(3,2) DEFAULT 0.00,
    store_name VARCHAR(150) DEFAULT NULL,
    shipping_type VARCHAR(100) DEFAULT NULL,
    category VARCHAR(100) DEFAULT NULL,
    trend_score INT DEFAULT 0,
    competition_level VARCHAR(20) DEFAULT 'medium',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_market_ext (marketplace, external_id),
    INDEX idx_marketplace (marketplace),
    INDEX idx_trend_score (trend_score)
) ENGINE=InnoDB;

-- 3. Product Price & Sales History (For price chart tracking)
CREATE TABLE IF NOT EXISTS product_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sales_count_est INT DEFAULT 0,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_recorded_at (recorded_at)
) ENGINE=InnoDB;

-- 4. User Search History
CREATE TABLE IF NOT EXISTS searches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    query VARCHAR(255) NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    marketplace VARCHAR(50) DEFAULT NULL,
    results_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_search (user_id, created_at)
) ENGINE=InnoDB;

-- 5. Favorites Table
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_product (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 6. Alerts Table
CREATE TABLE IF NOT EXISTS alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    alert_type VARCHAR(50) NOT NULL DEFAULT 'price_drop', -- 'price_drop', 'sales_spike'
    target_value DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 7. Niche & Category Analysis Table (Saves AI evaluations and trend data)
CREATE TABLE IF NOT EXISTS niche_analysis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(100) DEFAULT NULL,
    trend_score INT DEFAULT 0,
    avg_price DECIMAL(10,2) DEFAULT 0.00,
    demand_level VARCHAR(20) DEFAULT 'medium',
    competition_level VARCHAR(20) DEFAULT 'medium',
    growth_rate DECIMAL(5,2) DEFAULT 0.00,
    seasonality VARCHAR(50) DEFAULT NULL,
    ai_summary TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed default admin user (password: admin123)
-- Hash generated via password_hash('admin123', PASSWORD_BCRYPT)
INSERT INTO users (name, email, password_hash, role) VALUES 
('Administrador', 'admin@trendhunter.com.br', '$2y$10$O0HlP452D3kZq91w2vQYSu.W.8W6x9LpZcWf0n2lT4z31vBqKx3iG', 'admin')
ON DUPLICATE KEY UPDATE id=id;
