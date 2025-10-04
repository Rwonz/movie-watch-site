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
    <title>Arkadaş Ekle - FilmArkadaşı</title>
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
                <a href="ana.php">Ana Sayfa</a>
                <a href="lobby.php">Lobi</a>
                <a href="arkadas-ekle.php" class="active">Arkadaş Ekle</a>
                <a href="profil.php">Profil</a>
                <a href="logout.php" id="logout-btn">Çıkış Yap</a>
            </nav>
        </header>

        <main class="main-content">
            <h1><i class="fas fa-user-plus"></i> Arkadaş Ekle</h1>
            <div class="auth-card" style="max-width: 600px; margin: 2rem auto;">
                <form class="auth-form" id="search-friends-form">
                    <div class="form-group">
                        <label for="search-user">Kullanıcı Adı veya E-posta ile Ara</label>
                        <input type="text" id="search-user-input" placeholder="Arkadaşını bul..." required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">Ara</button>
                </form>
            </div>

            <div class="search-results" id="search-results-container" style="max-width: 600px; margin: 2rem auto;">
                <div class="activity-item">
                    <div class="activity-avatar"><i class="fas fa-info-circle"></i></div>
                    <div class="activity-content">
                        <p>Arkadaş aramak için yukarıdaki arama kutusunu kullanın.</p>
                        <span class="activity-time">Şimdi</span>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>© 2025 FilmArkadaşı - Arkadaşlarınla film izlemenin en eğlenceli yolu</p>
        </footer>
    </div>
    <script>
        document.getElementById('search-friends-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const searchTerm = document.getElementById('search-user-input').value;
            const resultsContainer = document.getElementById('search-results-container');
            
            resultsContainer.innerHTML = `
                <div class="activity-item">
                    <div class="activity-avatar"><i class="fas fa-user"></i></div>
                    <div class="activity-content">
                        <p><b>${searchTerm}</b> kullanıcısı bulundu.</p>
                        <button class="btn btn-sm btn-primary">Arkadaş Ekle</button>
                        <span class="activity-time">Şimdi</span>
                    </div>
                </div>
            `;
        });
    </script>
</body>
</html>