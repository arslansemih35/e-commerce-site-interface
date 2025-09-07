<?php
session_start();
require_once 'config.php';

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Lütfen önce giriş yapın.'
    ]);
    exit;
}

// Sipariş ID kontrolü
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($order_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz sipariş ID.'
    ]);
    exit;
}

try {
    // Sipariş bilgilerini getir
    $sql = "SELECT o.*, u.name as user_name, u.email as user_email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? AND o.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Sipariş bulunamadı.'
        ]);
        exit;
    }
    
    // Sipariş öğelerini getir
    $items_sql = "SELECT oi.*, p.name, p.description, p.image_url,
                  pv.name as variation_name, pv.sku as variation_sku,
                  (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image
                  FROM order_items oi
                  JOIN products p ON oi.product_id = p.id
                  LEFT JOIN product_variations pv ON oi.variation_id = pv.id
                  WHERE oi.order_id = ?";
    
    $items_stmt = $conn->prepare($items_sql);
    $items_stmt->execute([$order_id]);
    $order['items'] = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Adres bilgisini decode et
    $order['shipping_address'] = json_decode($order['shipping_address'], true);
    
    // Tarihleri formatla
    $order['created_at'] = date('d.m.Y H:i', strtotime($order['created_at']));
    $order['updated_at'] = date('d.m.Y H:i', strtotime($order['updated_at']));
    
    // Durum ve ödeme durumu metinlerini ekle
    $order['status_text'] = [
        'bekleyen' => 'Sipariş Bekliyor',
        'onaylandi' => 'Sipariş Onaylandı',
        'hazirlaniyor' => 'Sipariş Hazırlanıyor',
        'kargoda' => 'Kargoya Verildi',
        'tamamlandi' => 'Tamamlandı',
        'iptal' => 'İptal Edildi'
    ][$order['status']] ?? $order['status'];
    
    $order['payment_status_text'] = [
        'bekleyen' => 'Ödeme Bekliyor',
        'tamamlandi' => 'Ödeme Tamamlandı',
        'iptal' => 'Ödeme İptal Edildi'
    ][$order['payment_status']] ?? $order['payment_status'];
    
    // Kargo bilgilerini ekle
    if ($order['tracking_number']) {
        $order['tracking_url'] = "https://www.yurticikargo.com/track?code=" . $order['tracking_number'];
    }
    
    echo json_encode([
        'success' => true,
        'order' => $order
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Sipariş detayları yüklenirken bir hata oluştu.'
    ]);
}
?> 