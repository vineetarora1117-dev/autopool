CREATE DATABASE IF NOT EXISTS autopool_db;
USE autopool_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sponsor_id INT DEFAULT NULL,
    upline_id INT DEFAULT NULL,
    position ENUM('left', 'right') DEFAULT NULL,
    total_earnings DECIMAL(10, 4) DEFAULT 0.0000,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    from_user_id INT NOT NULL,
    amount DECIMAL(10, 4) NOT NULL,
    type ENUM('sponsor', 'autopool', 'level', 'reward') NOT NULL,
    level INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (from_user_id) REFERENCES users(id)
);
