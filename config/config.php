<?php
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'secure_vault');
define('DB_USER', 'root');
define('DB_PASS', '');

define('UPLOAD_DIR', __DIR__ . '/../vault_storage/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024 * 1024); // 2GB
