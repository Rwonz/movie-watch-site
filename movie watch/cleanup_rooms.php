<?php
// Bu script, sunucunuz tarafından otomatik olarak (örn. saatte bir) çalıştırılmalıdır.
require_once 'db.php';

echo "Temizlik scripti başladı...\n";

try {
    // 1 saatten daha eski ve içinde hiç üye olmayan odaları bul
    $stmt = $pdo->prepare("
        SELECT r.id FROM rooms r
        LEFT JOIN room_members rm ON r.id = rm.room_id
        WHERE r.created_at < NOW() - INTERVAL 1 HOUR
        GROUP BY r.id
        HAVING COUNT(rm.user_id) = 0
    ");
    $stmt->execute();
    $empty_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($empty_rooms)) {
        echo "Temizlenecek boş oda bulunamadı.\n";
        exit();
    }

    echo count($empty_rooms) . " adet boş oda bulundu. Siliniyor...\n";

    foreach ($empty_rooms as $room) {
        $room_id = $room['id'];

        // Mesajları sil
        $deleteMessages = $pdo->prepare("DELETE FROM messages WHERE room_id = ?");
        $deleteMessages->execute([$room_id]);
        echo "Oda ID $room_id için mesajlar silindi.\n";

        // Odayı sil
        $deleteRoom = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
        $deleteRoom->execute([$room_id]);
        echo "Oda ID $room_id silindi.\n";
    }

    echo "Temizlik tamamlandı.\n";

} catch (PDOException $e) {
    echo "HATA: " . $e->getMessage() . "\n";
}