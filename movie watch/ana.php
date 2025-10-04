<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FilmArkadaşı - Ana Sayfa</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header>
            <a href="ana.php" style="text-decoration: none;">
                <div class="logo">
                    <i class="fas fa-film"></i>
                    <span>FilmArkadaşı</span>
                </div>
            </a>
            <nav>
                <a href="ana.php" class="active">Ana Sayfa</a>
                <a href="lobby.php">Lobi</a>
                <a href="arkadas-ekle.php">Arkadaş Ekle</a>
                <a href="profil.php">Profil</a>
                <a href="logout.php" id="logout-btn">Çıkış Yap</a>
            </nav>
        </header>

        <main class="main-content">
            <section class="hero-section">
                <h1 id="welcome-message">FilmArkadaşı'na Hoş Geldin, <?php echo $_SESSION['username']; ?>!</h1>
                <p>Arkadaşlarınla birlikte film izlemenin en eğlenceli ve kolay yolu.</p>
                <div class="hero-buttons">
                    <a href="lobby.php" class="btn btn-primary"><i class="fas fa-door-open"></i> Lobiye Göz At</a>
                    <a href="#features" class="btn btn-secondary">Özellikleri Keşfet</a>
                </div>
            </section>

            <section class="recent-activity">
                <h2><i class="fas fa-history"></i> Son Aktiviteler</h2>
                <div class="activity-list" id="activity-feed">
                    <div class="activity-item">
                        <div class="activity-avatar"><i class="fas fa-info-circle"></i></div>
                        <div class="activity-content">
                            <p>Son aktiviteleri görmek için Lobi'ye göz atın veya arkadaş ekleyin.</p>
                            <span class="activity-time">Şimdi</span>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="features-section">
                <h2><i class="fas fa-star"></i> Öne Çıkan Özellikler</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fas fa-video"></i>
                        <h3>Eş Zamanlı İzleme</h3>
                        <p>Arkadaşlarınla filmleri aynı anda, duraklatma ve oynatma senkronizasyonu ile izle.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-comments"></i>
                        <h3>Görüntülü ve Sesli Sohbet</h3>
                        <p>Film izlerken arkadaşlarınla sohbet et, tepkilerini anında gör.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-users"></i>
                        <h3>Özelleştirilebilir Odalar</h3>
                        <p>Herkese açık veya özel odalar oluştur, izleyici limitini sen belirle.</p>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <p>© 2025 FilmArkadaşı - Arkadaşlarınla film izlemenin en eğlenceli yolu</p>
        </footer>
    </div>
</body>
</html>