-- Create database and posts table
CREATE DATABASE IF NOT EXISTS post_management;
USE post_management;

CREATE TABLE IF NOT EXISTS posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255), -- store the image file name or path
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
