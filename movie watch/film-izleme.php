<?php
// --- 1. SUNUCU TARAFI MANTIĞI ---
session_start();

// Odaya özel bir olay (event) ekleyen yardımcı fonksiyon
function add_room_event($pdo, $room_id, $type, $data) {
    $stmt = $pdo->prepare("INSERT INTO room_events (room_id, event_type, event_data) VALUES (?, ?, ?)");
    $stmt->execute([$room_id, $type, json_encode($data)]);
}

// --- GÖREV YÖNLENDİRİCİ (ACTION DISPATCHER) ---
if (isset($_SESSION['user_id'])) {
    require_once 'db.php';
    $current_user_id = $_SESSION['user_id'];
    $room_id = isset($_REQUEST['room_id']) ? intval($_REQUEST['room_id']) : 0;

    // A) SSE (Server-Sent Events) Akışını Başlatma
    if (isset($_GET['stream']) && $_GET['stream'] === 'true') {
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        session_write_close();

        $last_event_id = 0;
        while (true) {
            if (connection_aborted()) break;

            $stmt = $pdo->prepare("SELECT * FROM room_events WHERE room_id = ? AND id > ? ORDER BY id ASC");
            $stmt->execute([$room_id, $last_event_id]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($events as $event) {
                echo "event: " . htmlspecialchars($event['event_type']) . "\n";
                echo "data: " . $event['event_data'] . "\n\n";
                $last_event_id = $event['id'];
            }
            
            if (rand(1, 10) == 1) { 
                 $userStmt = $pdo->prepare("SELECT u.id, u.username FROM room_members rm JOIN users u ON rm.user_id = u.id WHERE rm.room_id = ?");
                 $userStmt->execute([$room_id]);
                 echo "event: users\n";
                 echo "data: " . json_encode($userStmt->fetchAll(PDO::FETCH_ASSOC)) . "\n\n";
            }
            
            ob_flush(); flush();
            sleep(1);
        }
        exit();
    }

    // B) JavaScript'ten Gelen Eylemleri İşleme
    if (isset($_GET['action'])) {
        header('Content-Type: application/json');
        $action = $_GET['action'];
        $data = json_decode(file_get_contents('php://input'), true) ?? [];

        switch ($action) {
            case 'send_signal':
                add_room_event($pdo, $room_id, 'signal', ['from' => $current_user_id, 'signal' => $data['signal'], 'to' => $data['to']]);
                echo json_encode(['success' => true]);
                break;

            case 'send_message':
                $message = trim($data['message'] ?? '');
                if (!empty($message)) {
                    $userStmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $userStmt->execute([$current_user_id]);
                    $username = $userStmt->fetchColumn();
                    add_room_event($pdo, $room_id, 'message', ['userId' => $current_user_id, 'username' => $username, 'message' => $message]);
                    echo json_encode(['success' => true]);
                }
                break;

            case 'sync_video':
                add_room_event($pdo, $room_id, 'video_sync', ['state' => $data['state'], 'time' => $data['time']]);
                echo json_encode(['success' => true]);
                break;

            case 'leave_room':
                 $stmt = $pdo->prepare("DELETE FROM room_members WHERE room_id = ? AND user_id = ?");
                 $stmt->execute([$room_id, $current_user_id]);
                 add_room_event($pdo, $room_id, 'user_left', ['userId' => $current_user_id]);
                 break;
        }
        exit();
    }
}

// --- 2. NORMAL SAYFA YÜKLEME (HTML OLUŞTURMA) ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';
$room_id = isset($_GET['room']) ? intval($_GET['room']) : 0;

$user_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$current_username = $user_stmt->fetchColumn();

$is_room_owner = false;
$room_exists = false;
if ($room_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT creator_id FROM rooms WHERE id = ?");
        $stmt->execute([$room_id]);
        $creator_id = $stmt->fetchColumn();
        if ($creator_id !== false) {
             $room_exists = true;
             $is_room_owner = ($creator_id == $_SESSION['user_id']);
             $insertStmt = $pdo->prepare("INSERT IGNORE INTO room_members (room_id, user_id) VALUES (?, ?)");
             $insertStmt->execute([$room_id, $_SESSION['user_id']]);
             add_room_event($pdo, $room_id, 'user_joined', ['userId' => $_SESSION['user_id'], 'username' => $current_username]);
        }
    } catch (PDOException $e) { /* Hata loglanabilir */ }
}

if (!$room_exists) {
    header("Location: lobby.php?error=notfound");
    exit();
}

$film_url = 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4';

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İzleme Odası</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        :root{--bg-dark:#101010;--bg-medium:#181818;--bg-light:#282828;--primary:#E50914;--text-light:#FFFFFF;--text-muted:#AAAAAA;--font:'Inter',-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;--ease:cubic-bezier(0.25, 0.8, 0.25, 1)}
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap');
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;font-family:var(--font);background:var(--bg-dark);color:var(--text-light);overflow:hidden}
        .loading-overlay{position:fixed;inset:0;background:var(--bg-dark);z-index:100;display:flex;align-items:center;justify-content:center;flex-direction:column;gap:1rem;transition:opacity .5s ease}
        .loading-overlay.hidden{opacity:0;pointer-events:none}
        .spinner{width:50px;height:50px;border:5px solid var(--bg-light);border-top-color:var(--primary);border-radius:50%;animation:spin 1s linear infinite}
        .main-container{display:flex;height:100vh}
        .video-container{flex:1;display:flex;flex-direction:column;position:relative;background:#000}
        #video-player{width:100%;height:100%;object-fit:contain}
        .controls-container{position:absolute;bottom:0;left:0;right:0;padding:1rem;display:flex;flex-direction:column;gap:.5rem;background:linear-gradient(to top,rgba(0,0,0,.8),transparent);opacity:0;transition:opacity .3s var(--ease)}
        .video-container:hover .controls-container{opacity:1}
        #timeline{width:100%;cursor:pointer;-webkit-appearance:none;background:rgba(255,255,255,.2);height:5px;border-radius:5px;transition:all .2s var(--ease)}#timeline::-webkit-slider-thumb{-webkit-appearance:none;width:15px;height:15px;background:var(--primary);border-radius:50%;cursor:pointer;transform:scale(0);transition:transform .2s var(--ease)}#timeline:hover::-webkit-slider-thumb{transform:scale(1)}
        .controls{display:flex;align-items:center;justify-content:space-between}
        .controls-left,.controls-right{display:flex;align-items:center;gap:1rem}
        .control-btn{background:none;border:none;color:var(--text-light);font-size:1.2rem;cursor:pointer;padding:.5rem;transition:color .2s var(--ease)}.control-btn:hover{color:var(--primary)}
        #time-display{font-size:.9rem;color:var(--text-muted)}
        .sidebar{width:350px;background:var(--bg-medium);border-left:1px solid var(--bg-light);display:flex;flex-direction:column;padding:1rem;transition:width .3s var(--ease)}
        .sidebar-section{margin-bottom:1.5rem}
        .chat-container{flex:1;display:flex;flex-direction:column;min-height:0}
        .sidebar-section h3{font-size:1.1rem;margin-bottom:1rem;color:var(--text-muted);border-bottom:1px solid var(--bg-light);padding-bottom:.5rem;display:flex;align-items:center;gap:.5rem}
        #user-list{display:flex;flex-direction:column;gap:.5rem}
        .user-item{display:flex;align-items:center;gap:.75rem;padding:.5rem;border-radius:5px;transition:all .3s var(--ease);position:relative;overflow:hidden}
        .user-item.entering{animation:anim-enter .4s var(--ease) forwards}
        .user-item.exiting{animation:anim-exit .4s var(--ease) forwards}
        .user-item .avatar{width:32px;height:32px;border-radius:50%;background-image:var(--avatar-url);background-size:cover;border:2px solid transparent;transition:border-color .3s var(--ease)}
        .user-item.speaking .avatar{border-color:var(--primary);animation:anim-pulse 1.5s var(--ease) infinite}
        #chat-messages{flex:1;overflow-y:auto;padding-right:.5rem;display:flex;flex-direction:column;gap:.75rem;min-height:50px}
        .chat-message{max-width:90%;padding:.5rem .75rem;border-radius:10px}
        .chat-message.self{align-self:flex-end;border-bottom-right-radius:2px;animation:anim-chat-self .4s var(--ease) forwards}
        .chat-message.other{align-self:flex-start;border-bottom-left-radius:2px;animation:anim-chat-other .4s var(--ease) forwards}
        .chat-message strong{display:block;font-size:.8rem;margin-bottom:.25rem;opacity:.7}
        .chat-input{display:flex;margin-top:1rem;gap:.5rem}
        #chat-input-field{flex:1;background:var(--bg-light);border:none;color:var(--text-light);padding:.75rem;border-radius:5px;font-family:inherit}
        #send-chat-btn{background:var(--primary);width:45px;height:45px;border:none;border-radius:5px;font-size:1rem;cursor:pointer}
        
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes anim-enter{from{opacity:0;transform:translateX(-20px)}to{opacity:1;transform:translateX(0)}}
        @keyframes anim-exit{from{opacity:1;transform:scale(1)}to{opacity:0;transform:scale(.9)}}
        @keyframes anim-chat-self{from{opacity:0;transform:translateX(20px)}to{opacity:1;transform:translateX(0)}}
        @keyframes anim-chat-other{from{opacity:0;transform:translateX(-20px)}to{opacity:1;transform:translateX(0)}}
        @keyframes anim-pulse{0%{box-shadow:0 0 0 0 var(--primary)}70%{box-shadow:0 0 0 7px transparent}100%{box-shadow:0 0 0 0 transparent}}

        @media(max-width:900px){.sidebar{width:80px}.sidebar-section h3 span,.user-item span{display:none}}
        @media(max-width:768px){.main-container{flex-direction:column}.sidebar{width:100%;height:50vh;border-left:none;border-top:1px solid var(--bg-light);flex-direction:row;gap:1rem}.sidebar-section{flex:1}.chat-container{width:100%}.video-container{height:50vh}.sidebar-section h3 span{display:inline}}
    </style>
</head>
<body>
    <div class="loading-overlay">
        <div class="spinner"></div>
        <p>Odaya Bağlanılıyor...</p>
    </div>

    <div class="main-container">
        <div class="video-container">
            <video id="video-player" src="<?php echo htmlspecialchars($film_url); ?>"></video>
            <div class="controls-container">
                <input type="range" id="timeline" value="0" step="any">
                <div class="controls">
                    <div class="controls-left">
                        <button class="control-btn" id="play-pause-btn"><i class="fa-solid fa-play"></i></button>
                        <span id="time-display">00:00 / 00:00</span>
                    </div>
                    <div class="controls-right">
                        <button class="control-btn" id="mic-btn"><i class="fa-solid fa-microphone-slash"></i></button>
                        <button class="control-btn" id="fullscreen-btn"><i class="fa-solid fa-expand"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar">
            <div class="sidebar-section">
                <h3><i class="fa-solid fa-users"></i> <span>Odadakiler</span></h3>
                <div id="user-list"></div>
            </div>
            <div class="sidebar-section chat-container">
                <h3><i class="fa-solid fa-comments"></i> <span>Sohbet</span></h3>
                <div id="chat-messages"></div>
                <div class="chat-input">
                    <input type="text" id="chat-input-field" placeholder="Mesaj yaz...">
                    <button id="send-chat-btn"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const App = {
                config: {
                    roomId: <?php echo $room_id; ?>,
                    userId: <?php echo $_SESSION['user_id']; ?>,
                    isOwner: <?php echo $is_room_owner ? 'true' : 'false'; ?>,
                    username: "<?php echo addslashes($current_username); ?>",
                },
                elements: {
                    video: document.getElementById('video-player'), playPauseBtn: document.getElementById('play-pause-btn'), timeline: document.getElementById('timeline'),
                    timeDisplay: document.getElementById('time-display'), micBtn: document.getElementById('mic-btn'), userList: document.getElementById('user-list'),
                    chatMessages: document.getElementById('chat-messages'), chatInput: document.getElementById('chat-input-field'), sendChatBtn: document.getElementById('send-chat-btn'),
                    loadingOverlay: document.querySelector('.loading-overlay'), fullscreenBtn: document.getElementById('fullscreen-btn'),
                },
                state: { micEnabled: false, localStream: null, peers: {}, users: [], audioContext: null },

                init() {
                    this.Player.init(); this.Chat.init(); this.Voice.init(); this.SSE.init();
                    this.elements.fullscreenBtn.addEventListener('click', () => {
                        if (!document.fullscreenElement) document.documentElement.requestFullscreen(); else document.exitFullscreen();
                    });
                    window.addEventListener('beforeunload', () => {
                        navigator.sendBeacon(`film-izleme.php?action=leave_room&room_id=${this.config.roomId}`, new Blob());
                    });
                },

                async apiCall(action, body) {
                    try {
                        await fetch(`film-izleme.php?action=${action}&room_id=${this.config.roomId}`, {
                            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body)
                        });
                    } catch (e) { console.error(`API Call failed for ${action}:`, e); }
                },
                
                updateUserList(newUsers) {
                    const existingUserIds = Array.from(this.elements.userList.children).map(el => parseInt(el.dataset.userId));
                    const newUserIds = newUsers.map(u => u.id);

                    existingUserIds.forEach(id => {
                        if (!newUserIds.includes(id)) {
                            const userEl = this.elements.userList.querySelector(`[data-user-id='${id}']`);
                            if (userEl) {
                                userEl.classList.add('exiting');
                                setTimeout(() => userEl.remove(), 400);
                            }
                        }
                    });

                    newUsers.forEach(user => {
                        if (!existingUserIds.includes(user.id)) {
                            const userEl = document.createElement('div');
                            userEl.className = 'user-item entering';
                            userEl.dataset.userId = user.id;
                            userEl.innerHTML = `
                                <div class="avatar" style="--avatar-url: url(https://ui-avatars.com/api/?name=${encodeURIComponent(user.username)}&background=random&color=fff)"></div>
                                <span>${user.username} ${user.id === this.config.userId ? '(Siz)' : ''}</span>`;
                            this.elements.userList.appendChild(userEl);
                        }
                    });
                    this.state.users = newUsers;
                },
                
                Player: {
                    init() {
                        const { video, playPauseBtn, timeline, timeDisplay } = App.elements;
                        const updateUI = () => {
                            playPauseBtn.innerHTML = video.paused ? '<i class="fa-solid fa-play"></i>' : '<i class="fa-solid fa-pause"></i>';
                            if(!isNaN(video.duration)) {
                                timeline.value = video.currentTime;
                                timeDisplay.textContent = `${this.formatTime(video.currentTime)} / ${this.formatTime(video.duration)}`;
                            }
                        };
                        video.addEventListener('loadedmetadata', () => { timeline.max = video.duration; updateUI(); });
                        video.addEventListener('timeupdate', updateUI); video.addEventListener('play', updateUI); video.addEventListener('pause', updateUI);
                        if (App.config.isOwner) {
                            playPauseBtn.addEventListener('click', () => this.sync('toggle'));
                            timeline.addEventListener('input', () => this.sync('seek', timeline.value));
                        } else {
                            timeline.disabled = true; playPauseBtn.style.cursor = 'not-allowed';
                        }
                    },
                    sync(state, time = App.elements.video.currentTime) { App.apiCall('sync_video', { state, time }); },
                    handleSync({ state, time }) {
                        const { video } = App.elements;
                        if (Math.abs(video.currentTime - parseFloat(time)) > 2) video.currentTime = parseFloat(time);
                        if (state === 'toggle') { video.paused ? video.play() : video.pause(); }
                        else if (state === 'play' && video.paused) video.play();
                        else if (state === 'pause' && !video.paused) video.pause();
                    },
                    formatTime: t=>(t=Math.floor(t||0),`${String(Math.floor(t/60)).padStart(2,'0')}:${String(t%60).padStart(2,'0')}`)
                },

                Chat: {
                    init() {
                        App.elements.sendChatBtn.addEventListener('click', () => this.sendMessage());
                        App.elements.chatInput.addEventListener('keypress', e => e.key === 'Enter' && this.sendMessage());
                    },
                    sendMessage() {
                        const message = App.elements.chatInput.value.trim();
                        if (!message) return;
                        App.apiCall('send_message', { message }); App.elements.chatInput.value = '';
                    },
                    addMessage({ userId, username, message }) {
                        const { chatMessages } = App.elements;
                        const msgEl = document.createElement('div');
                        const isSelf = userId === App.config.userId;
                        msgEl.className = `chat-message ${isSelf ? 'self' : 'other'}`;
                        msgEl.innerHTML = `<strong>${isSelf ? 'Siz' : username}</strong><p>${message.replace(/</g,"&lt;")}</p>`;
                        chatMessages.appendChild(msgEl);
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }
                },

                Voice: {
                    init() { App.elements.micBtn.addEventListener('click', () => this.toggleMic()); },
                    async toggleMic() {
                        App.state.micEnabled = !App.state.micEnabled;
                        const { micBtn } = App.elements;
                        micBtn.innerHTML = `<i class="fa-solid ${App.state.micEnabled ? 'fa-microphone' : 'fa-microphone-slash'}"></i>`;
                        micBtn.style.color = App.state.micEnabled ? 'var(--primary)' : 'var(--text-light)';

                        if(App.state.micEnabled && !App.state.localStream) {
                            try {
                                App.state.localStream = await navigator.mediaDevices.getUserMedia({ audio: { echoCancellation: true, noiseSuppression: true } });
                                Object.values(App.state.peers).forEach(pc => App.state.localStream.getTracks().forEach(track => pc.addTrack(track, App.state.localStream)));
                            } catch (e) {
                                alert('Mikrofon erişimi reddedildi veya bir hata oluştu.');
                                App.state.micEnabled = false;
                                micBtn.innerHTML = `<i class="fa-solid fa-microphone-slash"></i>`; micBtn.style.color = 'var(--text-light)';
                            }
                        }
                        if(App.state.localStream) App.state.localStream.getAudioTracks().forEach(t => t.enabled = App.state.micEnabled);
                    },
                    createPeer(targetUserId) {
                        if (App.state.peers[targetUserId] || targetUserId === App.config.userId) return;
                        const pc = new RTCPeerConnection({ iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] });
                        App.state.peers[targetUserId] = pc;

                        if (App.state.localStream) App.state.localStream.getTracks().forEach(track => pc.addTrack(track, App.state.localStream));
                        
                        pc.onicecandidate = e => e.candidate && App.apiCall('send_signal', { to: targetUserId, signal: { ice: e.candidate } });
                        pc.ontrack = e => {
                            let audioEl = document.getElementById(`audio-${targetUserId}`);
                            if (!audioEl) { audioEl = document.createElement('audio'); audioEl.id = `audio-${targetUserId}`; audioEl.autoplay = true; document.body.appendChild(audioEl); }
                            audioEl.srcObject = e.streams[0];
                            this.monitorSpeaking(targetUserId, e.streams[0]);
                        };
                        pc.onconnectionstatechange = () => ['failed','disconnected','closed'].includes(pc.connectionState) && this.removePeer(targetUserId);
                    },
                    removePeer(userId) {
                        if(App.state.peers[userId]) { App.state.peers[userId].close(); delete App.state.peers[userId]; }
                        const audioEl = document.getElementById(`audio-${userId}`);
                        if(audioEl) audioEl.remove();
                    },
                    async handleSignal({ from, signal, to }) {
                        if (to !== App.config.userId) return;
                        const pc = App.state.peers[from] || this.createPeer(from);
                        if(signal.offer) {
                            await pc.setRemoteDescription(new RTCSessionDescription(signal.offer));
                            const answer = await pc.createAnswer(); await pc.setLocalDescription(answer);
                            App.apiCall('send_signal', { to: from, signal: { answer } });
                        } else if(signal.answer) {
                            await pc.setRemoteDescription(new RTCSessionDescription(signal.answer));
                        } else if(signal.ice) {
                            await pc.addIceCandidate(new RTCIceCandidate(signal.ice));
                        }
                    },
                    monitorSpeaking(userId, stream) {
                        if (!App.state.audioContext) App.state.audioContext = new AudioContext();
                        const source = App.state.audioContext.createMediaStreamSource(stream);
                        const analyser = App.state.audioContext.createAnalyser();
                        analyser.fftSize = 256;
                        source.connect(analyser);
                        const dataArray = new Uint8Array(analyser.frequencyBinCount);
                        
                        setInterval(() => {
                            analyser.getByteFrequencyData(dataArray);
                            const avg = dataArray.reduce((a,b) => a+b) / dataArray.length;
                            const userEl = document.querySelector(`.user-item[data-user-id='${userId}']`);
                            if(userEl) userEl.classList.toggle('speaking', avg > 15);
                        }, 200);
                    }
                },

                SSE: {
                    init() {
                        const eventSource = new EventSource(`film-izleme.php?room_id=${App.config.roomId}&stream=true`);
                        eventSource.onopen = () => App.elements.loadingOverlay.classList.add('hidden');
                        
                        eventSource.addEventListener('users', e => {
                            const newUsers = JSON.parse(e.data);
                            App.updateUserList(newUsers);
                            
                            const newUserIds = newUsers.map(u => u.id);
                            newUserIds.forEach(id => {
                                if(id !== App.config.userId && !App.state.peers[id]){
                                    App.Voice.createPeer(id);
                                    if(App.config.userId > id) {
                                        const pc = App.state.peers[id];
                                        pc.createOffer().then(o => pc.setLocalDescription(o))
                                          .then(() => App.apiCall('send_signal', { to: id, signal: { offer: pc.localDescription }}));
                                    }
                                }
                            });
                            Object.keys(App.state.peers).forEach(id => !newUserIds.includes(parseInt(id)) && App.Voice.removePeer(id));
                        });

                        eventSource.addEventListener('message', e => App.Chat.addMessage(JSON.parse(e.data)));
                        eventSource.addEventListener('video_sync', e => App.Player.handleSync(JSON.parse(e.data)));
                        eventSource.addEventListener('signal', e => App.Voice.handleSignal(JSON.parse(e.data)));
                        eventSource.onerror = () => {
                             if (App.elements.loadingOverlay.classList.contains('hidden')) return;
                             App.elements.loadingOverlay.classList.remove('hidden');
                             console.error("SSE connection failed. Retrying...");
                        };
                    }
                }
            };
            App.init();
        });
    </script>
</body>
</html>