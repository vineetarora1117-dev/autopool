CREATE DATABASE IF NOT EXISTS autopool_db;
USE autopool_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    sponsor_id INT DEFAULT NULL,
    upline_id INT DEFAULT NULL,
    position ENUM('left', 'right') DEFAULT NULL,
    total_earnings DECIMAL(10, 4) DEFAULT 0.0000,
    reward_level INT DEFAULT 0,
    sponsor_team_size INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    from_user_id INT NOT NULL,
    amount DECIMAL(10, 4) NOT NULL,
    type ENUM('sponsor', 'autopool', 'level', 'reward') NOT NULL,
    level INT DEFAULT 0,
    status ENUM('completed', 'pending') DEFAULT 'completed',
    blocked_by_user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (from_user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS reward_targets (
    level INT PRIMARY KEY,
    strong_leg_target INT NOT NULL,
    other_legs_target INT NOT NULL,
    reward_amount DECIMAL(10, 4) NOT NULL
);

CREATE TABLE IF NOT EXISTS user_rewards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    level INT NOT NULL,
    amount DECIMAL(10, 4) NOT NULL,
    achieved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_user_level (user_id, level)
);

