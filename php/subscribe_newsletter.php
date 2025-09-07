<?php
require_once 'config.php';

// POST verilerini al
$data = json_decode(file_get_contents('php://input'), true);
$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);

// E-posta kontrolü
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz e-posta adresi.'
    ]);
    exit;
}

try {
    // E-posta zaten kayıtlı mı kontrol et
    $sql = "SELECT id FROM newsletter_subscribers WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Bu e-posta adresi zaten kayıtlı.'
        ]);
        exit;
    }
    
    // Yeni abone ekle
    $sql = "INSERT INTO newsletter_subscribers (email, status, created_at) VALUES (?, 'active', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$email]);
    
    // Başarılı kayıt
    echo json_encode([
        'success' => true,
        'message' => 'Bülten aboneliğiniz başarıyla tamamlandı.'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Bir hata oluştu. Lütfen daha sonra tekrar deneyin.'
    ]);
}
?> 