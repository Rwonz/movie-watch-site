<?php
// Oturumu başlat
session_start();

// Hata raporlama (geliştirme aşamasında açık, production'da kapatın)
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "sql211.infinityfree.com";
$user = "if0_38694193";
$pass = "aGyOEh6s0t6HY";
$db   = "if0_38694193_arkkkk";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Güvenlik fonksiyonları
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Brute-force kontrol fonksiyonu
function is_brute_force($ip) {
    global $conn;
    $now = time();
    $valid_attempts = $now - (15 * 60); // Son 15 dakika
    
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = ? AND attempt_time > FROM_UNIXTIME(?)");
    $stmt->bind_param("si", $ip, $valid_attempts);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return ($row['attempts'] > 5); // 15 dakikada 5'ten fazla deneme
}

// Giriş denemesi kaydetme fonksiyonu
function record_login_attempt($ip, $successful) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, successful) VALUES (?, ?)");
    $stmt->bind_param("si", $ip, $successful);
    $stmt->execute();
}

// reCAPTCHA anahtarları (Kendi anahtarlarınızı almalısınız)
define('RECAPTCHA_SITE_KEY', '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'); // Test anahtarı
define('RECAPTCHA_SECRET_KEY', '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'); // Test anahtarı

// reCAPTCHA doğrulama fonksiyonu
function verify_recaptcha($response) {
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = array(
        'secret' => RECAPTCHA_SECRET_KEY,
        'response' => $response
    );
    
    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );
    
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result);
    
    return $response->success;
}

// Şifre sıfırlama token oluşturma
function generate_reset_token() {
    return bin2hex(random_bytes(32));
}

// E-posta gönderme fonksiyonu (test amaçlı basit versiyon)
function send_email($to, $subject, $message) {
    // Gerçek uygulamada PHPMailer veya benzeri kütüphane kullanın
    $headers = "From: no-reply@filmarkadasi.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    // Test için dosyaya yazma
    file_put_contents('email_log.txt', "To: $to\nSubject: $subject\nMessage: $message\n\n", FILE_APPEND);
    
    return true; // Test amaçlı her zaman true döndür
}
?>