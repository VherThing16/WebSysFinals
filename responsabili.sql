-- Create the database
CREATE DATABASE IF NOT EXISTS responsabili;
USE responsabili;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Production tips table
CREATE TABLE IF NOT EXISTS production_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tip_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Consumption tips table
CREATE TABLE IF NOT EXISTS consumption_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tip_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Liked tips table (updated structure)
CREATE TABLE IF NOT EXISTS liked_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tip_id INT NOT NULL,
    tip_text TEXT NOT NULL,
    tip_type ENUM('consumption', 'production') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_tip (user_id, tip_id, tip_type)
);

-- Insert sample consumption tips
INSERT INTO consumption_tips (tip_text) VALUES
('Buy products with minimal or biodegradable packaging to reduce waste'),
('Support local producers to reduce transportation emissions'),
('Look for eco-certifications like Fair Trade when shopping'),
('Purchase only what you need to prevent overconsumption'),
('Consider the entire lifecycle of products before buying'),
('Carry reusable shopping bags and containers with you'),
('Choose energy-efficient appliances with Energy Star ratings'),
('Shop at thrift stores before buying new products'),
('Consume seasonal and locally-grown produce'),
('Learn basic repair skills to extend product life');

-- Insert sample production tips
INSERT INTO production_tips (tip_text) VALUES
('Use renewable energy sources in your production process'),
('Implement water recycling systems in your facility'),
('Reduce packaging materials to minimum necessary'),
('Source materials from ethical and sustainable suppliers'),
('Optimize logistics to reduce carbon footprint'),
('Implement waste reduction programs in your facility'),
('Design products for easy repair and recycling'),
('Provide transparency about your supply chain'),
('Offer take-back programs for used products'),
('Invest in employee education about sustainability');

-- Create indexes for better performance
CREATE INDEX idx_liked_tips_user ON liked_tips(user_id);
CREATE INDEX idx_liked_tips_tip ON liked_tips(tip_id);
CREATE INDEX idx_liked_tips_type ON liked_tips(tip_type);