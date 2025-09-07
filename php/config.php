<?php
// Veritabanı bağlantı bilgileri
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecommerce');

// Veritabanı bağlantısı
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("SET NAMES 'utf8'");
} catch(PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
    exit;
}

// Oturum başlatma
session_start();

// Güvenlik fonksiyonları
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Oturum kontrolü
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.html");
        exit;
    }
}

// Admin kontrolü
function checkAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header("Location: ../login.html");
        exit;
    }
}
?> 