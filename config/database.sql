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

-- 8. CRM Comercial & Pipeline de Negociação (Histórico Auditável de Atividades)
CREATE TABLE IF NOT EXISTS crm_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    contact_type VARCHAR(50) NOT NULL,
    contact_date DATE NOT NULL,
    contact_time TIME NOT NULL,
    responsible_name VARCHAR(100) NOT NULL,
    objective VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    result_summary TEXT DEFAULT NULL,
    negotiation_status VARCHAR(50) NOT NULL,
    interest_level VARCHAR(50) DEFAULT 'Alto',
    priority VARCHAR(50) DEFAULT 'Média',
    next_action VARCHAR(255) DEFAULT NULL,
    followup_date DATE DEFAULT NULL,
    product_id INT DEFAULT NULL,
    product_title VARCHAR(255) DEFAULT NULL,
    marketplace VARCHAR(100) DEFAULT NULL,
    attachment_url VARCHAR(500) DEFAULT NULL,
    created_by_name VARCHAR(100) DEFAULT 'Sistema / Usuário',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_crm_date (contact_date),
    INDEX idx_crm_company (company_name),
    INDEX idx_crm_status (negotiation_status),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 9. Log Geral de Atividades (Auditoria Imutável)
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    user_name VARCHAR(100) DEFAULT 'Sistema',
    module VARCHAR(100) NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    target_record VARCHAR(255) DEFAULT NULL,
    old_values TEXT DEFAULT NULL,
    new_values TEXT DEFAULT NULL,
    ip_address VARCHAR(50) DEFAULT '127.0.0.1',
    device_info VARCHAR(255) DEFAULT 'Navegador Web',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_log_module (module),
    INDEX idx_log_action (action_type),
    INDEX idx_log_user (user_name)
) ENGINE=InnoDB;

-- 10. Produtos Oceano Azul
CREATE TABLE IF NOT EXISTS blue_ocean_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    niche VARCHAR(100) NOT NULL,
    target_audience VARCHAR(255) NOT NULL,
    problem_solved TEXT NOT NULL,
    avg_price DECIMAL(10,2) NOT NULL,
    est_cost DECIMAL(10,2) NOT NULL,
    proj_margin DECIMAL(5,2) NOT NULL,
    approx_competitors INT DEFAULT 10,
    trend_score INT DEFAULT 90,
    seasonality VARCHAR(100) DEFAULT 'Ano Todo',
    related_suppliers TEXT DEFAULT NULL,
    suggested_kits TEXT DEFAULT NULL,
    opportunity_badge VARCHAR(50) DEFAULT 'Alta Oportunidade',
    investment_recommendation TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 11. Nicho Bebês & Primeira Infância
CREATE TABLE IF NOT EXISTS baby_niche_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    sub_category VARCHAR(100) NOT NULL, -- alimentação, banho, higiene, sono, passeio, organização, maternidade, segurança, desenvolvimento infantil, brinquedos educativos, brinquedos sensoriais, Montessori
    age_range VARCHAR(100) NOT NULL,
    safety_cert VARCHAR(100) DEFAULT 'INMETRO / Atóxico',
    material_info VARCHAR(255) DEFAULT 'Livre de BPA',
    cleaning_ease VARCHAR(100) DEFAULT 'Fácil higienização',
    small_parts_risk VARCHAR(50) DEFAULT 'Baixo Risco',
    avg_price DECIMAL(10,2) NOT NULL,
    est_cost DECIMAL(10,2) NOT NULL,
    suggested_kits TEXT DEFAULT NULL,
    income_bracket VARCHAR(100) DEFAULT 'Todas as faixas (Acessível)',
    ai_analysis TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


