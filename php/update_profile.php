<?php
require_once 'config.php';

// Oturum kontrolü
checkLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $phone = clean($_POST['phone']);
    $address = clean($_POST['address']);
    
    try {
        // E-posta kontrolü (kendi e-postası hariç)
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor'
            ]);
            exit;
        }
        
        // Profil güncelleme
        $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $email, $phone, $address, $_SESSION['user_id']]);
        
        // Oturum bilgilerini güncelle
        $_SESSION['user_name'] = $name;
        
        echo json_encode([
            'success' => true,
            'message' => 'Profil başarıyla güncellendi'
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Bir hata oluştu: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz istek metodu'
    ]);
}
?> 