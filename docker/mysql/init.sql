-- Initialize MySQL database for waBlast
-- This script runs automatically when the MySQL container starts

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS `sikmasyita` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create Baileys API database
CREATE DATABASE IF NOT EXISTS `baileys_api` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges
GRANT ALL PRIVILEGES ON `sikmasyita`.* TO 'client'@'%' IDENTIFIED BY 'Masyita@123';
GRANT ALL PRIVILEGES ON `baileys_api`.* TO 'client'@'%' IDENTIFIED BY 'Masyita@123';

-- Flush privileges
FLUSH PRIVILEGES;
