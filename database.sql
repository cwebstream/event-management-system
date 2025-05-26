-- Create database
CREATE DATABASE event_management;
USE event_management;

-- Super Admin table
CREATE TABLE super_admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Modules table
CREATE TABLE modules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    module_name VARCHAR(50) NOT NULL,
    status ENUM('enabled', 'disabled') DEFAULT 'enabled',
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Event Admins table
CREATE TABLE event_admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    permissions JSON,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Check-in logs
CREATE TABLE checkin_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    participant_id VARCHAR(50),
    check_in_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Access zones logs
CREATE TABLE access_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    zone_name VARCHAR(50),
    participant_id VARCHAR(50),
    access_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- F&B distribution logs
CREATE TABLE fb_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    participant_id VARCHAR(50),
    item_name VARCHAR(100),
    quantity INT,
    distribution_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Goodies distribution logs
CREATE TABLE goodies_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    participant_id VARCHAR(50),
    item_name VARCHAR(100),
    collected_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Wallet transactions
CREATE TABLE wallet_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    participant_id VARCHAR(50),
    amount DECIMAL(10,2),
    transaction_type ENUM('credit', 'debit'),
    transaction_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);

-- Analytics data
CREATE TABLE analytics_data (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    metric_name VARCHAR(50),
    metric_value JSON,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id)
);
