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
    <title>Profilim - FilmArkadaşı</title>
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
                <a href="arkadas-ekle.php">Arkadaş Ekle</a>
                <a href="profil.php" class="active">Profil</a>
                <a href="logout.php" id="logout-btn">Çıkış Yap</a>
            </nav>
        </header>
        <main class="main-content">
            <h1><i class="fas fa-user-circle"></i> Profilim</h1>
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar"><i class="fas fa-user"></i></div>
                    <h2 id="profile-name"><?php echo $_SESSION['username']; ?></h2>
                    <p>E-posta: <?php echo $_SESSION['email']; ?></p>
                    <p>Katılım Tarihi: <?php echo date('d F Y'); ?></p>
                </div>
                <div class="profile-body">
                    <div class="profile-edit-section">
                        <h3><i class="fas fa-edit"></i> Bilgileri Düzenle</h3>
                        <form id="profile-edit-form" class="auth-form">
                            <div class="form-group">
                                <label for="username">Kullanıcı Adı</label>
                                <input type="text" id="username" value="<?php echo $_SESSION['username']; ?>" placeholder="Kullanıcı adınız">
                            </div>
                            <div class="form-group">
                                <label for="email">E-posta</label>
                                <input type="email" id="email" value="<?php echo $_SESSION['email']; ?>" placeholder="E-posta adresiniz" disabled>
                            </div>
                            <div class="form-group">
                                <label for="password">Yeni Şifre</label>
                                <input type="password" id="password" placeholder="Değiştirmek istemiyorsanız boş bırakın">
                            </div>
                            <button type="submit" class="btn btn-primary">Değişiklikleri Kaydet</button>
                        </form>
                    </div>

                    <div class="profile-friends-section">
                        <h3><i class="fas fa-users"></i> Arkadaşlarım</h3>
                        <div class="friends-list" id="friends-list">
                            <p>Henüz arkadaşınız yok. <a href="arkadas-ekle.php">Arkadaş eklemek için tıklayın</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <footer>
            <p>© 2025 FilmArkadaşı - Arkadaşlarınla film izlemenin en eğlenceli yolu</p>
        </footer>
    </div>
    <script>
        document.getElementById('profile-edit-form').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Değişiklikler kaydedildi! (Bu bir demo, gerçekte veritabanını günceller)');
        });
    </script>
</body>
</html>