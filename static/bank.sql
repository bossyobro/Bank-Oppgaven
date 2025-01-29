CREATE DATABASE IF NOT EXISTS bank;
USE bank;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    user_type ENUM('personal', 'business', 'admin') NOT NULL DEFAULT 'personal',
    two_factor_secret VARCHAR(32) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_number VARCHAR(20) UNIQUE NOT NULL,
    account_type ENUM('savings', 'checking', 'business') NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    interest_rate DECIMAL(5,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    account_id INT NOT NULL,
    transaction_type ENUM('deposit', 'withdrawal', 'transfer', 'interest') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    to_account VARCHAR(20),
    kid_number VARCHAR(25),
    description TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    activity VARCHAR(255) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (username, password, email, phone, name, address, user_type) 
VALUES (
    'admin',
    '$2y$10$8tN.cur.YBQZ2J.IU0s8/.KE8O.AI6Z6RyxAVOqH71.k6uNJwZhvq',
    'admin@bank.com',
    '12345678',
    'System Administrator',
    'Bank Address',
    'admin'
) ON DUPLICATE KEY UPDATE username = username;

INSERT INTO accounts (user_id, account_number, account_type, interest_rate)
SELECT 
    (SELECT id FROM users WHERE username = 'admin'),
    '1234.01.00001',
    'savings',
    2.50
WHERE NOT EXISTS (
    SELECT 1 FROM accounts WHERE account_number = '1234.01.00001'
);