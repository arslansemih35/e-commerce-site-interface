<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $phone = clean($_POST['phone']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $address = clean($_POST['address']);

    // Şifre kontrolü
    if ($password !== $password_confirm) {
        echo json_encode(['success' => false, 'message' => 'Şifreler eşleşmiyor']);
        exit;
    }

    // E-posta kontrolü
    try {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'message' => 'Bu e-posta adresi zaten kayıtlı']);
            exit;
        }

        // Yeni kullanıcı kaydı
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, phone, password, address, role) VALUES (?, ?, ?, ?, ?, 'user')");
        $stmt->execute([$name, $email, $phone, $hashed_password, $address]);

        echo json_encode(['success' => true, 'message' => 'Kayıt başarıyla tamamlandı']);
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
    }
} else {
    header("Location: ../register.html");
    exit;
}
?> 