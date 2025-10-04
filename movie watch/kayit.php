<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Veritabanı bağlantısı
require_once 'db.php';

// Herkese açık odaları veritabanından al (Bu kısım sayfanın ilk açılışında hızlı yüklenmesi için kalır)
$public_rooms = [];
try {
    $stmt = $pdo->prepare("
        SELECT r.*, u.username as creator_name, 
               (SELECT COUNT(*) FROM room_members WHERE room_id = r.id) as member_count
        FROM rooms r 
        JOIN users u ON r.creator_id = u.id 
        WHERE r.room_type = 'public' AND r.is_active = 1
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $public_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Hata durumunda boş dizi kalacak
    error_log("Odalar getirilirken hata: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lobi - FilmArkadaşı</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Silme butonunu kırmızı yapmak için */
        .btn-danger {
            background-color: #dc3545; /* Kırmızı renk */
            color: white;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
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
                <a href="lobby.php" class="active">Lobi</a>
                <a href="arkadas-ekle.php">Arkadaş Ekle</a>
                <a href="profil.php">Profil</a>
                <a href="logout.php" id="logout-btn">Çıkış Yap</a>
            </nav>
        </header>

        <main class="main-content">
            <div class="lobby-header">
                <h1>Film Odaları</h1>
                <button class="btn btn-primary" id="create-room-btn">
                    <i class="fas fa-plus"></i> Yeni Oda Oluştur
                </button>
            </div>

            <div class="rooms-grid" id="rooms-grid">
                <?php if (empty($public_rooms)): ?>
                    <div class="no-rooms-message">
                        <i class="fas fa-film fa-3x"></i>
                        <p>Henüz hiç oda oluşturulmamış. İlk odayı siz oluşturun!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($public_rooms as $room): ?>
                        <div class="room-card">
                            <div class="room-header">
                                <h3 class="room-name"><?php echo htmlspecialchars($room['room_name']); ?></h3>
                                <span class="room-members"><?php echo $room['member_count'] . '/' . $room['max_users']; ?></span>
                            </div>
                            <div class="room-content">
                                <p class="room-movie">Oluşturan: <?php echo htmlspecialchars($room['creator_name']); ?></p>
                                <p class="room-type"><?php echo $room['room_type'] == 'public' ? 'Herkese Açık' : 'Özel'; ?></p>
                            </div>
                            <div class="room-footer">
                                <?php if ($room['member_count'] < $room['max_users']): ?>
                                    <a href="film-izleme.php?room=<?php echo $room['id']; ?>" class="btn btn-sm btn-primary">Katıl</a>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-disabled" disabled>Dolu</button>
                                <?php endif; ?>

                                <?php // Sadece oda sahibi için silme butonu göster ?>
                                <?php if ($room['creator_id'] == $_SESSION['user_id']): ?>
                                    <button class="btn btn-sm btn-danger delete-room-btn" data-room-id="<?php echo $room['id']; ?>">Sil</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="create-room-modal" class="modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2>Yeni Oda Oluştur</h2>
                        <span class="close">&times;</span>
                    </div>
                    <div class="modal-body">
                        <form id="create-room-form" action="create_room.php" method="POST">
                            <div class="form-group">
                                <label for="room-name">Oda Adı</label>
                                <input type="text" id="room-name" name="room_name" placeholder="Oda için bir isim girin" required>
                            </div>
                            <div class="form-group">
                                <label for="room-type">Oda Türü</label>
                                <select id="room-type" name="room_type" required>
                                    <option value="">Seçiniz</option>
                                    <option value="public">Herkese Açık</option>
                                    <option value="private">Özel</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="max-users">Maksimum Kullanıcı</label>
                                <input type="number" id="max-users" name="max_users" min="2" max="10" value="5" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-full">Oluştur</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>© 2025 FilmArkadaşı - Arkadaşlarınla film izlemenin en eğlenceli yolu</p>
        </footer>
    </div>

    <script>
        // --- Modal Logic ---
        const modal = document.getElementById("create-room-modal");
        const btn = document.getElementById("create-room-btn");
        const span = document.getElementsByClassName("close")[0];
        btn.onclick = () => modal.style.display = "block";
        span.onclick = () => modal.style.display = "none";
        window.onclick = (event) => {
            if (event.target == modal) modal.style.display = "none";
        };

        // --- Canlı Oda Güncelleme Mantığı ---
        const roomsGrid = document.getElementById('rooms-grid');
        const currentUserId = <?php echo $_SESSION['user_id']; ?>; // Mevcut kullanıcı ID'sini al

        const escapeHTML = (str) => {
            const p = document.createElement('p');
            p.appendChild(document.createTextNode(str));
            return p.innerHTML;
        };

        const updateRoomsView = (rooms) => {
            roomsGrid.innerHTML = ''; 
            if (rooms.length === 0) {
                roomsGrid.innerHTML = `
                    <div class="no-rooms-message">
                        <i class="fas fa-film fa-3x"></i>
                        <p>Henüz hiç oda oluşturulmamış. İlk odayı siz oluşturun!</p>
                    </div>`;
            } else {
                rooms.forEach(room => {
                    const isFull = room.member_count >= room.max_users;
                    
                    const joinButtonHTML = isFull 
                        ? `<button class="btn btn-sm btn-disabled" disabled>Dolu</button>`
                        : `<a href="film-izleme.php?room=${room.id}" class="btn btn-sm btn-primary">Katıl</a>`;
                    
                    const deleteButtonHTML = (room.creator_id == currentUserId)
                        ? `<button class="btn btn-sm btn-danger delete-room-btn" data-room-id="${room.id}">Sil</button>`
                        : '';

                    const roomCardHTML = `
                        <div class="room-card">
                            <div class="room-header">
                                <h3 class="room-name">${escapeHTML(room.room_name)}</h3>
                                <span class="room-members">${room.member_count}/${room.max_users}</span>
                            </div>
                            <div class="room-content">
                                 <p class="room-movie">Oluşturan: ${escapeHTML(room.creator_name)}</p>
                                 <p class="room-type">${room.room_type == 'public' ? 'Herkese Açık' : 'Özel'}</p>
                            </div>
                            <div class="room-footer">
                                ${joinButtonHTML}
                                ${deleteButtonHTML}
                            </div>
                        </div>`;
                    
                    roomsGrid.innerHTML += roomCardHTML;
                });
            }
        };

        const fetchRooms = async () => {
            try {
                const response = await fetch('get_rooms.php');
                if (!response.ok) return;
                const data = await response.json();
                if (data.success) {
                    updateRoomsView(data.rooms);
                }
            } catch (error) {
                console.error('Fetch hatası:', error);
            }
        };

        // Silme butonuna tıklandığında çalışacak fonksiyon
        roomsGrid.addEventListener('click', async (event) => {
            if (event.target.classList.contains('delete-room-btn')) {
                const button = event.target;
                const roomId = button.dataset.roomId;

                if (confirm('Bu odayı kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
                    const formData = new FormData();
                    formData.append('room_id', roomId);

                    try {
                        const response = await fetch('delete_room.php', {
                            method: 'POST',
                            body: formData
                        });
                        const result = await response.json();

                        if (result.success) {
                            fetchRooms(); 
                        } else {
                            alert('Hata: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Oda silinirken hata oluştu:', error);
                        alert('Bir ağ hatası oluştu. Lütfen tekrar deneyin.');
                    }
                }
            }
        });

        fetchRooms();
        setInterval(fetchRooms, 5000); 
    </script>
</body>
</html>