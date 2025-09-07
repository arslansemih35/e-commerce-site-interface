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

// POST verilerini al
$data = json_decode(file_get_contents('php://input'), true);
$cart_id = isset($data['cart_id']) ? (int)$data['cart_id'] : 0;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 0;

if ($cart_id <= 0 || $quantity <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek.'
    ]);
    exit;
}

try {
    // Sepet öğesini kontrol et
    $check_sql = "SELECT c.*, p.stock, pv.stock as variation_stock 
                  FROM cart c
                  JOIN products p ON c.product_id = p.id
                  LEFT JOIN product_variations pv ON c.variation_id = pv.id
                  WHERE c.id = ? AND c.user_id = ?";
    
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->execute([$cart_id, $_SESSION['user_id']]);
    $cart_item = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$cart_item) {
        echo json_encode([
            'success' => false,
            'message' => 'Sepet öğesi bulunamadı.'
        ]);
        exit;
    }
    
    // Stok kontrolü
    $available_stock = $cart_item['variation_id'] ? 
                      $cart_item['variation_stock'] : 
                      $cart_item['stock'];
    
    if ($available_stock < $quantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Yetersiz stok.'
        ]);
        exit;
    }
    
    // Sepet öğesini güncelle
    $update_sql = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);
    
    // Güncel sepet durumunu getir
    $cart_sql = "SELECT c.*, p.name, p.price, p.sale_price,
                 pv.name as variation_name, pv.price as variation_price
                 FROM cart c
                 JOIN products p ON c.product_id = p.id
                 LEFT JOIN product_variations pv ON c.variation_id = pv.id
                 WHERE c.id = ?";
    
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->execute([$cart_id]);
    $updated_item = $cart_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Ürün fiyatını hesapla
    $price = $updated_item['variation_price'] ?? $updated_item['price'];
    $sale_price = $updated_item['sale_price'];
    $final_price = $sale_price ? $sale_price : $price;
    $total = $final_price * $quantity;
    
    echo json_encode([
        'success' => true,
        'message' => 'Sepet güncellendi.',
        'item' => [
            'id' => $updated_item['id'],
            'name' => $updated_item['name'],
            'variation_name' => $updated_item['variation_name'],
            'quantity' => $quantity,
            'price' => $final_price,
            'total' => $total
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu.'
    ]);
}
?> 