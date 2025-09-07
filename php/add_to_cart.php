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
$product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
$variation_id = isset($data['variation_id']) ? (int)$data['variation_id'] : null;
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz ürün.'
    ]);
    exit;
}

try {
    // Ürün kontrolü
    $product_sql = "SELECT * FROM products WHERE id = ? AND is_active = 1";
    $product_stmt = $conn->prepare($product_sql);
    $product_stmt->execute([$product_id]);
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Ürün bulunamadı.'
        ]);
        exit;
    }
    
    // Varyasyon kontrolü
    if ($variation_id) {
        $var_sql = "SELECT * FROM product_variations WHERE id = ? AND product_id = ?";
        $var_stmt = $conn->prepare($var_sql);
        $var_stmt->execute([$variation_id, $product_id]);
        $variation = $var_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$variation) {
            echo json_encode([
                'success' => false,
                'message' => 'Geçersiz varyasyon.'
            ]);
            exit;
        }
        
        // Stok kontrolü
        if ($variation['stock'] < $quantity) {
            echo json_encode([
                'success' => false,
                'message' => 'Yetersiz stok.'
            ]);
            exit;
        }
    } else {
        // Ürün stok kontrolü
        if ($product['stock'] < $quantity) {
            echo json_encode([
                'success' => false,
                'message' => 'Yetersiz stok.'
            ]);
            exit;
        }
    }
    
    // Sepette ürün var mı kontrol et
    $cart_sql = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND variation_id " . ($variation_id ? "= ?" : "IS NULL");
    $cart_stmt = $conn->prepare($cart_sql);
    $cart_params = [$product_id];
    if ($variation_id) {
        $cart_params[] = $variation_id;
    }
    $cart_stmt->execute($cart_params);
    $cart_item = $cart_stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cart_item) {
        // Sepetteki ürünü güncelle
        $update_sql = "UPDATE cart SET quantity = quantity + ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->execute([$quantity, $cart_item['id']]);
    } else {
        // Yeni ürün ekle
        $insert_sql = "INSERT INTO cart (user_id, product_id, variation_id, quantity) VALUES (?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->execute([$_SESSION['user_id'], $product_id, $variation_id, $quantity]);
    }
    
    // Sepet sayısını al
    $count_sql = "SELECT COUNT(*) FROM cart WHERE user_id = ?";
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute([$_SESSION['user_id']]);
    $cart_count = $count_stmt->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'message' => 'Ürün sepete eklendi.',
        'cart_count' => $cart_count
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu.'
    ]);
}
?> 