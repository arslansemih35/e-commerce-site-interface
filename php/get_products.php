<?php
require_once 'config.php';

// Sayfalama parametreleri
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Filtreleme parametreleri
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$search = isset($_GET['search']) ? $_GET['search'] : '';

try {
    // Temel SQL sorgusu
    $sql = "SELECT p.*, c.name as category_name, 
            (SELECT COUNT(*) FROM product_reviews WHERE product_id = p.id) as review_count,
            (SELECT AVG(rating) FROM product_reviews WHERE product_id = p.id) as rating
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1";
    
    $params = [];
    
    // Kategori filtresi
    if ($category !== 'all') {
        $sql .= " AND c.name = ?";
        $params[] = $category;
    }
    
    // Arama filtresi
    if (!empty($search)) {
        $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    // Sıralama
    switch ($sort) {
        case 'price_asc':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_desc':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'popular':
            $sql .= " ORDER BY p.views DESC";
            break;
        case 'rating':
            $sql .= " ORDER BY rating DESC";
            break;
        default:
            $sql .= " ORDER BY p.created_at DESC";
    }
    
    // Toplam ürün sayısını al
    $count_sql = str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*)", $sql);
    $count_sql = preg_replace("/ORDER BY.*$/", "", $count_sql);
    
    $stmt = $conn->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetchColumn();
    $total_pages = ceil($total_products / $per_page);
    
    // Sayfalama ekle
    $sql .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    
    // Ürünleri getir
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Ürün detaylarını ekle
    foreach ($products as &$product) {
        // Ürün resimlerini getir
        $image_sql = "SELECT image_url FROM product_images WHERE product_id = ? AND is_main = 1 LIMIT 1";
        $image_stmt = $conn->prepare($image_sql);
        $image_stmt->execute([$product['id']]);
        $image = $image_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($image) {
            $product['image_url'] = $image['image_url'];
        }
        
        // Ürün özelliklerini getir
        $attr_sql = "SELECT name, value FROM product_attributes WHERE product_id = ?";
        $attr_stmt = $conn->prepare($attr_sql);
        $attr_stmt->execute([$product['id']]);
        $product['attributes'] = $attr_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ürün varyasyonlarını getir
        $var_sql = "SELECT * FROM product_variations WHERE product_id = ?";
        $var_stmt = $conn->prepare($var_sql);
        $var_stmt->execute([$product['id']]);
        $product['variations'] = $var_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tarihleri formatla
        $product['created_at'] = date('d.m.Y', strtotime($product['created_at']));
        $product['production_date'] = date('d.m.Y', strtotime($product['production_date']));
        $product['expiry_date'] = date('d.m.Y', strtotime($product['expiry_date']));
        
        // Rating'i yuvarla
        $product['rating'] = round($product['rating'], 1);
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'total_pages' => $total_pages,
        'current_page' => $page,
        'total_products' => $total_products
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Ürünler yüklenirken bir hata oluştu.'
    ]);
}
?> 