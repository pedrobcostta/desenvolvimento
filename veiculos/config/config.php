<?php
// Database configuration
define('DB_HOST', '8.218.82.130');
define('DB_USER', 'parque3');
define('DB_PASS', 'DEV@jpbc84800');
define('DB_NAME', 'parque3');

// Application configuration
define('BASE_URL', '/veiculos');
define('UPLOAD_DIR', __DIR__ . '/../uploads/images/');
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Image dimensions
define('THUMBNAIL_WIDTH', 300);
define('THUMBNAIL_HEIGHT', 200);
define('LARGE_IMAGE_WIDTH', 800);
define('LARGE_IMAGE_HEIGHT', 600);

// Pagination
define('ITEMS_PER_PAGE', 12);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed. Please try again later.");
}
