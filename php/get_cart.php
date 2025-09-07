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

try {
    // Sepet içeriğini getir
    $sql = "SELECT c.*, p.name, p.description, p.price, p.sale_price, p.image_url,
            pv.name as variation_name, pv.price as variation_price,
            (SELECT image_url FROM product_images WHERE product_id = p.id AND is_main = 1 LIMIT 1) as main_image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            LEFT JOIN product_variations pv ON c.variation_id = pv.id
            WHERE c.user_id = ?
            ORDER BY c.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Toplam tutarı hesapla
    $total = 0;
    $subtotal = 0;
    $discount = 0;
    
    foreach ($cart_items as &$item) {
        // Ürün fiyatını belirle
        $price = $item['variation_price'] ?? $item['price'];
        $sale_price = $item['sale_price'];
        
        if ($sale_price) {
            $item['final_price'] = $sale_price;
            $discount += ($price - $sale_price) * $item['quantity'];
        } else {
            $item['final_price'] = $price;
        }
        
        $item['total'] = $item['final_price'] * $item['quantity'];
        $subtotal += $item['total'];
    }
    
    // Kargo ücreti (150 TL altı için 15 TL)
    $shipping = $subtotal < 150 ? 15 : 0;
    
    // Toplam tutar
    $total = $subtotal + $shipping;
    
    // Kupon kontrolü
    $coupon_sql = "SELECT c.* FROM coupons c
                   JOIN used_coupons uc ON c.id = uc.coupon_id
                   WHERE uc.user_id = ? AND c.is_active = 1
                   AND c.start_date <= NOW() AND c.end_date >= NOW()
                   ORDER BY c.created_at DESC LIMIT 1";
    
    $coupon_stmt = $conn->prepare($coupon_sql);
    $coupon_stmt->execute([$_SESSION['user_id']]);
    $coupon = $coupon_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($coupon) {
        if ($coupon['discount_type'] === 'percentage') {
            $coupon_discount = $subtotal * ($coupon['discount_value'] / 100);
        } else {
            $coupon_discount = $coupon['discount_value'];
        }
        
        $discount += $coupon_discount;
        $total -= $coupon_discount;
    }
    
    echo json_encode([
        'success' => true,
        'cart_items' => $cart_items,
        'summary' => [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $discount,
            'total' => $total,
            'coupon' => $coupon
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Sepet yüklenirken bir hata oluştu.'
    ]);
}
?> 