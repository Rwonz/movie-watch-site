<?php
// Veritabanı bağlantı bilgileri
$host = 'sql211.infinityfree.com';
$dbname = 'if0_38694193_arkkkk';
$user = 'if0_38694193';
$password = 'aGyOEh6s0t6HY'; // <-- BURAYA KENDİ vPanel ŞİFRENİZİ GİRİN

// Veri Kaynağı Adı (DSN)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

// PDO bağlantı seçenekleri
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Hataları yakalamak için
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Sonuçları anahtar-değer çifti olarak almak için
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Gerçek 'prepared statements' kullanmak için
];

try {
    // PDO ile veritabanına bağlan
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (\PDOException $e) {
    // Bağlantı başarısız olursa, hatayı göster ve programı durdur
    // (Geliştirme aşamasında bu yararlıdır, canlıda daha genel bir mesaj gösterilebilir)
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}