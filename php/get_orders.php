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

// Sayfalama parametreleri
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Filtreleme parametreleri
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    // Temel SQL sorgusu
    $sql = "SELECT o.*, 
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
            FROM orders o 
            WHERE o.user_id = ?";
    
    $params = [$_SESSION['user_id']];
    
    // Durum filtresi
    if ($status !== 'all') {
        $sql .= " AND o.status = ?";
        $params[] = $status;
    }
    
    // Toplam sipariş sayısını al
    $count_sql = str_replace("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count", "SELECT COUNT(*)", $sql);
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total_orders = $count_stmt->fetchColumn();
    $total_pages = ceil($total_orders / $per_page);
    
    // Sayfalama ekle
    $sql .= " ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    // Siparişleri getir
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Sipariş detaylarını ekle
    foreach ($orders as &$order) {
        // Sipariş öğelerini getir
        $items_sql = "SELECT oi.*, p.name, p.image_url,
                      pv.name as variation_name,
                      (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image
                      FROM order_items oi
                      JOIN products p ON oi.product_id = p.id
                      LEFT JOIN product_variations pv ON oi.variation_id = pv.id
                      WHERE oi.order_id = ?";
        
        $items_stmt = $conn->prepare($items_sql);
        $items_stmt->execute([$order['id']]);
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
    }
    
    echo json_encode([
        'success' => true,
        'orders' => $orders,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'total_orders' => $total_orders
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Siparişler yüklenirken bir hata oluştu.'
    ]);
}
?> 