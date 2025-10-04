


<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FilmArkadaşı - Arkadaşlarınla Birlikte Film İzle</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Buton stilleri eklendi */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            margin: 5px;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: #4a6bff;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3a5bef;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
        }
        
        .hero-buttons {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <i class="fas fa-film"></i>
                <span>FilmArkadaşı</span>
            </div>
            <nav>
                <!-- Yönlendirme hatası giderildi -->
                <a href="login.php" class="btn btn-secondary">Giriş Yap</a>
                <a href="kayit.php" class="btn btn-primary">Hemen Kayıt Ol</a>
            </nav>
        </header>

        <main class="main-content">
            <section class="hero-section" style="padding: 5rem 1rem;">
                <h1>Arkadaşlarınla Film İzlemenin Keyfini Yeniden Keşfet</h1>
                <p>Mesafeler engel değil! FilmArkadaşı ile kendi özel odanı oluştur, arkadaşlarını davet et ve filmleri eş zamanlı olarak birlikte izleyin.</p>
                <div class="hero-buttons">
                    <!-- Yönlendirme hatası giderildi -->
                    <a href="kayit.php" class="btn btn-primary btn-lg"><i class="fas fa-rocket"></i> Hemen Başla</a>
                </div>
            </section>

            <section class="features-section">
                <h2><i class="fas fa-cogs"></i> Nasıl Çalışır?</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <i class="fas fa-user-plus"></i>
                        <h3>1. Kayıt Ol</h3>
                        <p>Birkaç basit adımda ücretsiz hesabını oluştur ve profilini ayarla.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-door-open"></i>
                        <h3>2. Odanı Kur</h3>
                        <p>Herkese açık veya özel bir film odası oluştur. İzleyeceğiniz filmi seç ve arkadaşlarını davet et.</p>
                    </div>
                    <div class="feature-card">
                        <i class="fas fa-play-circle"></i>
                        <h3>3. Birlikte İzle</h3>
                        <p>Film izlerken görüntülü ve yazılı sohbet ile anın tadını çıkarın. Durdurma ve oynatma herkes için senkronize çalışır.</p>
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