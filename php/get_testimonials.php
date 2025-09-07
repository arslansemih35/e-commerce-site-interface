<?php
require_once 'config.php';

try {
    // Onaylanmış yorumları getir
    $sql = "SELECT t.*, u.name as user_name, u.image_url as user_image 
            FROM testimonials t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.is_approved = 1 
            ORDER BY t.created_at DESC 
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $testimonials = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Yorum tarihlerini formatla
    foreach ($testimonials as &$testimonial) {
        $testimonial['created_at'] = date('d.m.Y', strtotime($testimonial['created_at']));
    }
    
    echo json_encode([
        'success' => true,
        'testimonials' => $testimonials
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Yorumlar yüklenirken bir hata oluştu.'
    ]);
}
?> 