<?php
require_once 'config.php';

try {
    // Aktif kampanyaları getir
    $sql = "SELECT * FROM campaigns 
            WHERE is_active = 1 
            AND start_date <= NOW() 
            AND end_date >= NOW() 
            ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $campaigns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Kampanya detaylarını ekle
    foreach ($campaigns as &$campaign) {
        // Kampanya türüne göre indirim hesapla
        if ($campaign['discount_type'] === 'percentage') {
            $campaign['discount'] = $campaign['discount_value'];
        } else {
            $campaign['discount'] = number_format($campaign['discount_value'], 2);
        }
        
        // Geçerlilik süresini formatla
        $campaign['valid_until'] = date('Y-m-d', strtotime($campaign['end_date']));
        
        // Kampanya linkini oluştur
        $campaign['link'] = 'campaign.php?id=' . $campaign['id'];
    }
    
    echo json_encode([
        'success' => true,
        'campaigns' => $campaigns
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Kampanyalar yüklenirken bir hata oluştu.'
    ]);
}
?> 