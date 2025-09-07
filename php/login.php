<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = clean($_POST['email']);
    $password = $_POST['password'];

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: ../admin/index.html");
            } else {
                header("Location: ../dashboard.html");
            }
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz e-posta veya şifre']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Bir hata oluştu: ' . $e->getMessage()]);
    }
} else {
    header("Location: ../login.html");
    exit;
}
?> 