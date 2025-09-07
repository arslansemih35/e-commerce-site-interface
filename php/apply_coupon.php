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
$code = isset($data['code']) ? trim($data['code']) : '';

if (empty($code)) {
    echo json_encode([
        'success' => false,
        'message' => 'Kupon kodu gerekli.'
    ]);
    exit;
}

try {
    // Kupon kodunu kontrol et
    $coupon_sql = "SELECT * FROM coupons 
                   WHERE code = ? AND is_active = 1 
                   AND start_date <= NOW() AND end_date >= NOW()";
    
    $coupon_stmt = $conn->prepare($coupon_sql);
    $coupon_stmt->execute([$code]);
    $coupon = $coupon_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$coupon) {
        echo json_encode([
            'success' => false,
            'message' => 'Geçersiz veya süresi dolmuş kupon kodu.'
        ]);
        exit;
    }
    
    // Kullanım limiti kontrolü
    if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
        echo json_encode([
            'success' => false,
            'message' => 'Bu kupon kodunun kullanım limiti dolmuş.'
        ]);
        exit;
    }
    
    // Kullanıcının bu kuponu daha önce kullanıp kullanmadığını kontrol et
    $used_sql = "SELECT id FROM used_coupons 
                 WHERE user_id = ? AND coupon_id = ?";
    
    $used_stmt = $conn->prepare($used_sql);
    $used_stmt->execute([$_SESSION['user_id'], $coupon['id']]);
    
    if ($used_stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Bu kupon kodunu daha önce kullandınız.'
        ]);
        exit;
    }
    
    // Sepet toplamını hesapla
    $cart_sql = "SELECT SUM(c.quantity * COALESCE(pv.price, p.price)) as total
                 FROM cart c
                 JOIN products p ON c.product_id = p.id
                 LEFT JOIN product_variations pv ON c.variation_id = pv.id
                 WHERE c.user_id = ?";
    
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_stmt->execute([$_SESSION['user_id']]);
    $cart_total = $cart_stmt->fetchColumn();
    
    // Minimum alışveriş tutarı kontrolü
    if ($cart_total < $coupon['min_purchase']) {
        echo json_encode([
            'success' => false,
            'message' => 'Bu kupon kodu minimum ' . number_format($coupon['min_purchase'], 2) . ' TL alışveriş için geçerlidir.'
        ]);
        exit;
    }
    
    // Kuponu kullanıcıya ekle
    $insert_sql = "INSERT INTO used_coupons (user_id, coupon_id) VALUES (?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->execute([$_SESSION['user_id'], $coupon['id']]);
    
    // Kupon kullanım sayısını güncelle
    $update_sql = "UPDATE coupons SET used_count = used_count + 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->execute([$coupon['id']]);
    
    // İndirim tutarını hesapla
    if ($coupon['discount_type'] === 'percentage') {
        $discount = $cart_total * ($coupon['discount_value'] / 100);
    } else {
        $discount = $coupon['discount_value'];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Kupon kodu başarıyla uygulandı.',
        'discount' => $discount,
        'coupon' => [
            'code' => $coupon['code'],
            'discount_type' => $coupon['discount_type'],
            'discount_value' => $coupon['discount_value']
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu.'
    ]);
}
?> 