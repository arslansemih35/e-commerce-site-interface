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
$address_id = isset($data['address_id']) ? (int)$data['address_id'] : 0;
$payment_method = isset($data['payment_method']) ? $data['payment_method'] : '';
$shipping_method = isset($data['shipping_method']) ? $data['shipping_method'] : '';

if ($address_id <= 0 || empty($payment_method) || empty($shipping_method)) {
    echo json_encode([
        'success' => false,
        'message' => 'Lütfen tüm alanları doldurun.'
    ]);
    exit;
}

try {
    // Adres kontrolü
    $address_sql = "SELECT * FROM addresses WHERE id = ? AND user_id = ?";
    $address_stmt = $conn->prepare($address_sql);
    $address_stmt->execute([$address_id, $_SESSION['user_id']]);
    $address = $address_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$address) {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz adres.'
        ]);
        exit;
    }
    
    // Sepet içeriğini kontrol et
    $cart_sql = "SELECT c.*, p.stock, pv.stock as variation_stock,
                 p.price, p.sale_price, pv.price as variation_price
                 FROM cart c
                 JOIN products p ON c.product_id = p.id
                 LEFT JOIN product_variations pv ON c.variation_id = pv.id
                 WHERE c.user_id = ?";
    
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->execute([$_SESSION['user_id']]);
    $cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($cart_items)) {
        echo json_encode([
            'success' => false,
            'message' => 'Sepetiniz boş.'
        ]);
        exit;
    }
    
    // Stok kontrolü ve toplam tutarı hesapla
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $price = $item['variation_price'] ?? $item['price'];
        $sale_price = $item['sale_price'];
        $final_price = $sale_price ? $sale_price : $price;
        
        $available_stock = $item['variation_id'] ? 
                          $item['variation_stock'] : 
                          $item['stock'];
        
        if ($available_stock < $item['quantity']) {
            echo json_encode([
                'success' => false,
                'message' => 'Bazı ürünlerin stok miktarı yetersiz.'
            ]);
            exit;
        }
        
        $subtotal += $final_price * $item['quantity'];
    }
    
    // Kargo ücreti
    $shipping_amount = $subtotal < 150 ? 15 : 0;
    
    // Kupon indirimi
    $coupon_sql = "SELECT c.* FROM coupons c
                   JOIN used_coupons uc ON c.id = uc.coupon_id
                   WHERE uc.user_id = ? AND c.is_active = 1
                   AND c.start_date <= NOW() AND c.end_date >= NOW()
                   ORDER BY c.created_at DESC LIMIT 1";
    
    $coupon_stmt = $conn->prepare($coupon_sql);
    $coupon_stmt->execute([$_SESSION['user_id']]);
    $coupon = $coupon_stmt->fetch(PDO::FETCH_ASSOC);
    
    $discount_amount = 0;
    if ($coupon) {
        if ($coupon['discount_type'] === 'percentage') {
            $discount_amount = $subtotal * ($coupon['discount_value'] / 100);
        } else {
            $discount_amount = $coupon['discount_value'];
        }
    }
    
    // Toplam tutar
    $total_amount = $subtotal + $shipping_amount - $discount_amount;
    
    // Siparişi oluştur
    $order_sql = "INSERT INTO orders (user_id, total_amount, discount_amount, 
                   shipping_amount, status, payment_status, payment_method,
                   shipping_address) VALUES (?, ?, ?, ?, 'bekleyen', 'bekleyen', ?, ?)";
    
    $order_stmt = $conn->prepare($order_sql);
    $order_stmt->execute([
        $_SESSION['user_id'],
        $total_amount,
        $discount_amount,
        $shipping_amount,
        $payment_method,
        json_encode($address)
    ]);
    
    $order_id = $conn->lastInsertId();
    
    // Sipariş detaylarını ekle
    $order_item_sql = "INSERT INTO order_items (order_id, product_id, variation_id, 
                        quantity, price) VALUES (?, ?, ?, ?, ?)";
    
    $order_item_stmt = $conn->prepare($order_item_sql);
    
    // Stokları güncelle
    $update_stock_sql = "UPDATE products SET stock = stock - ? WHERE id = ?";
    $update_variation_sql = "UPDATE product_variations SET stock = stock - ? WHERE id = ?";
    
    $update_stock_stmt = $conn->prepare($update_stock_sql);
    $update_variation_stmt = $conn->prepare($update_variation_sql);
    
    foreach ($cart_items as $item) {
        $price = $item['variation_price'] ?? $item['price'];
        $sale_price = $item['sale_price'];
        $final_price = $sale_price ? $sale_price : $price;
        
        // Sipariş detayı ekle
        $order_item_stmt->execute([
            $order_id,
            $item['product_id'],
            $item['variation_id'],
            $item['quantity'],
            $final_price
        ]);
        
        // Stok güncelle
        if ($item['variation_id']) {
            $update_variation_stmt->execute([$item['quantity'], $item['variation_id']]);
        } else {
            $update_stock_stmt->execute([$item['quantity'], $item['product_id']]);
        }
    }
    
    // Sepeti temizle
    $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
    $clear_cart_stmt = $conn->prepare($clear_cart_sql);
    $clear_cart_stmt->execute([$_SESSION['user_id']]);
    
    // Kullanılan kuponu sil
    if ($coupon) {
        $delete_coupon_sql = "DELETE FROM used_coupons WHERE user_id = ? AND coupon_id = ?";
        $delete_coupon_stmt = $conn->prepare($delete_coupon_sql);
        $delete_coupon_stmt->execute([$_SESSION['user_id'], $coupon['id']]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Siparişiniz başarıyla oluşturuldu.',
        'order_id' => $order_id,
        'order_details' => [
            'subtotal' => $subtotal,
            'shipping_amount' => $shipping_amount,
            'discount_amount' => $discount_amount,
            'total_amount' => $total_amount
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu.'
    ]);
}
?> 