/*CREATE DATABASE secure_vault;
USE secure_vault;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_uid BIGINT(16) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE file_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT NOT NULL,
    owner_id INT NOT NULL,
    shared_with INT NOT NULL,
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_with) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE (file_id, shared_with)
);*/



CREATE DATABASE IF NOT EXISTS secure_vault;
USE secure_vault;

-- =========================
-- USERS TABLE
-- =========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_uid BIGINT(16) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- FOLDERS TABLE
-- =========================
CREATE TABLE folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE
);

-- =========================
-- FILES TABLE
-- =========================
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    folder_id INT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL
);

-- =========================
-- FILE SHARING TABLE
-- =========================
CREATE TABLE file_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT NOT NULL,
    owner_id INT NOT NULL,
    shared_with INT NOT NULL,
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_with) REFERENCES users(id) ON DELETE CASCADE,

    UNIQUE (file_id, shared_with)
);

-- =========================
-- ACTIVITY LOG TABLE
-- =========================
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    file_name VARCHAR(255) NULL,
    target_user INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (target_user) REFERENCES users(id) ON DELETE SET NULL
);