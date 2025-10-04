-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 23 Ağu 2025, 12:47:06
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `ark`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `room_id`, `user_id`, `username`, `message`, `timestamp`) VALUES
(1, 1, 1, 'beyz', 'sa', '2025-08-23 09:27:00'),
(2, 1, 1, 'beyz', 'sa', '2025-08-23 09:27:03'),
(3, 1, 2, 'Beyaz', 'Sa', '2025-08-23 09:56:35'),
(4, 1, 2, 'Beyaz', 'Yakup gotunu s', '2025-08-23 10:44:02');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `successful` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip_address`, `attempt_time`, `successful`) VALUES
(1, '192.168.1.11', '2025-08-23 09:42:56', 1),
(2, '192.168.1.11', '2025-08-23 09:56:22', 1),
(3, '192.168.1.11', '2025-08-23 10:01:59', 1),
(4, '192.168.1.11', '2025-08-23 10:43:38', 1),
(5, '192.168.1.10', '2025-08-23 10:45:39', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `room_name` varchar(255) NOT NULL,
  `room_type` enum('public','private') NOT NULL DEFAULT 'public',
  `creator_id` int(11) NOT NULL,
  `max_users` int(11) NOT NULL DEFAULT 5,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `rooms`
--

INSERT INTO `rooms` (`id`, `room_name`, `room_type`, `creator_id`, `max_users`, `is_active`, `created_at`) VALUES
(1, '123', 'public', 1, 5, 1, '2025-08-23 09:21:23');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `room_members`
--

CREATE TABLE `room_members` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `room_members`
--

INSERT INTO `room_members` (`id`, `room_id`, `user_id`, `joined_at`) VALUES
(53, 1, 2, '2025-08-23 10:20:39'),
(56, 1, 1, '2025-08-23 10:43:17');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `reset_token`, `reset_expires`, `created_at`) VALUES
(1, 'mustafa', 'beyz', 'beyazthe40@gmail.com', '$2y$10$AdKRUZ4wmA/AqLTDULPuk.ZmASsL.tOVGfEK73PN0T8uxouixIAXu', NULL, NULL, '2025-08-23 09:21:12'),
(2, 'Beyaz', 'Beyaz', 'beyazthe4040@gmail.com', '$2y$10$I8shiXQfd2zPXEiKuFtL.eT3wCYOktFoGzQrIPhoFgT5Z./iig3qu', NULL, NULL, '2025-08-23 09:32:34');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `webrtc_signals`
--

CREATE TABLE `webrtc_signals` (
  `id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `signal_data` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `webrtc_signals`
--

INSERT INTO `webrtc_signals` (`id`, `room_id`, `from_user_id`, `to_user_id`, `signal_data`, `timestamp`) VALUES
(55, 1, 1, 2, '{\"offer\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 948612956622611251 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\n\"}}', '2025-08-23 10:45:47'),
(56, 1, 1, 2, '{\"offer\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 8064267160841328955 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\n\"}}', '2025-08-23 10:46:18'),
(57, 1, 1, 2, '{\"offer\":{\"type\":\"offer\",\"sdp\":\"v=0\\r\\no=- 3274340515202537503 2 IN IP4 127.0.0.1\\r\\ns=-\\r\\nt=0 0\\r\\na=extmap-allow-mixed\\r\\na=msid-semantic: WMS\\r\\n\"}}', '2025-08-23 10:46:34');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Tablo için indeksler `room_members`
--
ALTER TABLE `room_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_room_user` (`room_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Tablo için indeksler `webrtc_signals`
--
ALTER TABLE `webrtc_signals`
  ADD PRIMARY KEY (`id`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `room_members`
--
ALTER TABLE `room_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `webrtc_signals`
--
ALTER TABLE `webrtc_signals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `room_members`
--
ALTER TABLE `room_members`
  ADD CONSTRAINT `room_members_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
