<?php
session_start();
require_once 'db.php'; // Veritabanı bağlantı dosyanız

if (!isset($_SESSION['user_id']) || !isset($_GET['room_id'])) {
    echo json_encode([]); // Boş dizi döndür
    exit();
}

$room_id = intval($_GET['room_id']);
// last_id parametresi, sadece son alınan mesajdan sonrakileri getirmek için kullanılır.
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

try {
    $stmt = $pdo->prepare(
        "SELECT id, username, message, user_id FROM chat_messages WHERE room_id = ? AND id > ? ORDER BY timestamp ASC"
    );
    $stmt->execute([$room_id, $last_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Oturumdaki kullanıcı ID'sini de mesaja ekleyelim ki kendi mesajımızı ayırt edebilelim
    foreach($messages as &$msg) {
        $msg['is_self'] = ($msg['user_id'] == $_SESSION['user_id']);
    }

    echo json_encode($messages);

} catch (PDOException $e) {
    echo json_encode([]); // Hata durumunda boş dizi döndür
}
?>