CREATE DATABASE IF NOT EXISTS nightclub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nightclub_db;

-- Tabela użytkowników
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) DEFAULT NULL,
  `last_name` VARCHAR(100) DEFAULT NULL,
  `role` ENUM('klient', 'pracownik', 'wlasciciel') DEFAULT 'klient',
  `is_approved` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela sal
CREATE TABLE `halls` (
  `hall_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT,
  `capacity` INT,
  `base_price` DECIMAL(10, 2) DEFAULT 0.00
);

-- Tabela wydarzeń (koncertów)
CREATE TABLE `events` (
  `event_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `date` DATETIME NOT NULL,
  `hall_id` INT NOT NULL,
  `ticket_price` DECIMAL(10, 2) NOT NULL,
  `total_tickets` INT NOT NULL,
  `tickets_sold` INT DEFAULT 0,
  `image_url` VARCHAR(255) DEFAULT 'images/placeholder_event.png',
  FOREIGN KEY (`hall_id`) REFERENCES `halls`(`hall_id`)
);

-- Tabela biletów
CREATE TABLE `tickets` (
  `ticket_id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `quantity` INT DEFAULT 1,
  `total_price` DECIMAL(10,2) NOT NULL,
  `purchase_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`event_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
);

-- Tabela drinków
CREATE TABLE `drinks` (
  `drink_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `quantity_available` INT NOT NULL,
  `price_per_unit` DECIMAL(10, 2) NOT NULL
);

-- Tabela dodatków do rezerwacji
CREATE TABLE `extras` (
  `extra_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `description` TEXT
);

-- Tabela rezerwacji
CREATE TABLE `reservations` (
  `reservation_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `hall_id` INT NOT NULL,
  `reservation_date` DATE NOT NULL,
  `reservation_time_start` TIME NOT NULL,
  `reservation_time_end` TIME NOT NULL,
  `status` ENUM('oczekujaca', 'potwierdzona', 'anulowana_klient', 'anulowana_pracownik', 'zakonczona') DEFAULT 'oczekujaca',
  `base_hall_price` DECIMAL(10,2) NOT NULL,
  `drinks_price` DECIMAL(10,2) DEFAULT 0.00,
  `extras_price` DECIMAL(10,2) DEFAULT 0.00,
  `total_price` DECIMAL(10, 2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`),
  FOREIGN KEY (`hall_id`) REFERENCES `halls`(`hall_id`)
);

-- Tabela łącząca rezerwacje z drinkami (zamówione drinki)
CREATE TABLE `reservation_drinks` (
  `reservation_drink_id` INT AUTO_INCREMENT PRIMARY KEY,
  `reservation_id` INT NOT NULL,
  `drink_id` INT NOT NULL,
  `quantity_ordered` INT NOT NULL,
  `price_at_reservation` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`reservation_id`) ON DELETE CASCADE,
  FOREIGN KEY (`drink_id`) REFERENCES `drinks`(`drink_id`)
);

-- Tabela łącząca rezerwacje z dodatkami
CREATE TABLE `reservation_extras` (
  `reservation_extra_id` INT AUTO_INCREMENT PRIMARY KEY,
  `reservation_id` INT NOT NULL,
  `extra_id` INT NOT NULL,
  `price_at_reservation` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`reservation_id`) ON DELETE CASCADE,
  FOREIGN KEY (`extra_id`) REFERENCES `extras`(`extra_id`)
);

-- Tabela opinii
CREATE TABLE `reviews` (
  `review_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `rating` INT CHECK (rating >= 1 AND rating <= 5),
  `comment` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
);

-- Tabela wiadomości kontaktowych
CREATE TABLE `contact_messages` (
  `message_id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `subject` VARCHAR(255),
  `message` TEXT NOT NULL,
  `sent_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `is_read` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
);

-- Podstawowe dane do testowania
INSERT INTO `users` (`email`, `password`, `first_name`, `last_name`, `role`, `is_approved`) VALUES
('klient@example.com', 'klient123', 'Jan', 'Kowalski', 'klient', TRUE),
('pracownik@example.com', 'pracownik123', 'Anna', 'Nowak', 'pracownik', TRUE),
('wlasciciel@example.com', 'wlasciciel123', 'Piotr', 'Zieliński', 'wlasciciel', TRUE),
('nowy_klient@example.com', 'nowy123', 'Ewa', 'Testowa', 'klient', FALSE);

INSERT INTO `halls` (`hall_id`, `name`, `description`, `capacity`, `base_price`) VALUES
(1, 'Sala Mała', 'Przytulna sala idealna na mniejsze imprezy, urodziny czy spotkania w gronie przyjaciół. Wyposażona w wygodne loże i dostęp do baru.', 30, 250.00),
(2, 'Sala Duża', 'Przestronna sala z większym parkietem, idealna na większe przyjęcia, imprezy firmowe. Możliwość aranżacji według potrzeb.', 80, 600.00),
(3, 'Sala Koncertowa', 'Główna arena naszego klubu, gdzie odbywają się koncerty największych gwiazd i tematyczne imprezy taneczne. Wyposażona w profesjonalne nagłośnienie i oświetlenie.', 250, 0.00);

INSERT INTO `events` (`name`, `description`, `date`, `hall_id`, `ticket_price`, `total_tickets`, `tickets_sold`, `image_url`) VALUES
('Electro Night z DJ KAPPA', 'Noc pełna elektronicznych brzmień z gościem specjalnym DJ KAPPA.', CONCAT(CURDATE() + INTERVAL 7 DAY, ' 21:00:00'), 3, 40.00, 200, 15, 'images/event1.png'),
('Koncert The Bandits', 'Rockowy wieczór z zespołem The Bandits! Zagrają swoje największe hity oraz materiał z nowej płyty.', CONCAT(CURDATE() + INTERVAL 14 DAY, ' 20:00:00'), 3, 60.00, 150, 5, 'images/event2.png'),
('Retro Party lata 80/90', 'Powrót do przeszłości! Największe hity lat 80. i 90. przez całą noc.', CONCAT(CURDATE() + INTERVAL 21 DAY, ' 22:00:00'), 3, 30.00, 180, 0, 'images/placeholder_event.png');

INSERT INTO `drinks` (`name`, `quantity_available`, `price_per_unit`) VALUES
('Wódka Wyborowa 0.5l', 50, 50.00),
('Whisky Ballantines 0.7l', 30, 120.00),
('Piwo Perła Chmielowa 0.5l', 100, 12.00),
('Sok pomarańczowy Cappy 1l', 40, 15.00),
('Coca-Cola 0.5l', 80, 8.00);

INSERT INTO `extras` (`name`, `price`, `description`) VALUES
('Dedykowany DJ', 350.00, 'Profesjonalny DJ, który zadba o oprawę muzyczną Twojej imprezy zgodnie z Twoimi preferencjami.'),
('Ochroniarz', 200.00, 'Dodatkowy ochroniarz dbający o bezpieczeństwo i porządek podczas Twojej prywatnej imprezy.'),
('Pakiet Przekąsek Standard', 150.00, 'Zestaw podstawowych przekąsek (chipsy, paluszki, orzeszki) dla gości.'),
('Dekoracje Tematyczne', 250.00, 'Przygotowanie sali w wybranym przez Ciebie motywie tematycznym.');

INSERT INTO `reviews` (`user_id`, `rating`, `comment`) VALUES
(1, 5, 'Świetna atmosfera i muzyka na koncercie The Bandits! Polecam!');

INSERT INTO `reservations` (`user_id`, `hall_id`, `reservation_date`, `reservation_time_start`, `reservation_time_end`, `status`, `base_hall_price`, `drinks_price`, `extras_price`, `total_price`) VALUES
(1, 1, CONCAT(CURDATE() + INTERVAL 10 DAY), '20:00:00', '03:00:00', 'oczekujaca', 250.00, 100.00, 0.00, 350.00);

INSERT INTO `reservation_drinks` (`reservation_id`, `drink_id`, `quantity_ordered`, `price_at_reservation`) VALUES
(1, 1, 2, 50.00); -- 2x Wódka


-- Dodatkowe dane: zakończone wydarzenia i rezerwacje

-- Zakończone wydarzenia (daty w przeszłości)
-- Upewnij się, że ID hall_id=3 (Sala Koncertowa) istnieje.
INSERT INTO `events` (`name`, `description`, `date`, `hall_id`, `ticket_price`, `total_tickets`, `tickets_sold`, `image_url`) VALUES
('Koncert Gwiazdy Pop', 'Niezapomniany wieczór z idolem nastolatek!', '2024-05-15 20:00:00', 3, 120.00, 200, 195, 'images/placeholder_event.png'),
('Metal Fest Vol. 3', 'Ciężkie brzmienia przez całą noc!', '2024-04-20 19:00:00', 3, 70.00, 150, 140, 'images/placeholder_event.png'),
('Stand-up Comedy Night', 'Wieczór pełen śmiechu z najlepszymi komikami.', '2024-03-10 20:30:00', 3, 50.00, 100, 90, 'images/placeholder_event.png');

-- Bilety do zakończonych wydarzeń (przykładowe)
-- Zakładamy, że użytkownik o user_id=1 (klient@example.com) istnieje.
INSERT INTO `tickets` (`event_id`, `user_id`, `quantity`, `total_price`, `purchase_date`) VALUES
((SELECT event_id FROM events WHERE name = 'Koncert Gwiazdy Pop' LIMIT 1), 1, 2, 240.00, '2024-05-01 10:00:00'),
((SELECT event_id FROM events WHERE name = 'Metal Fest Vol. 3' LIMIT 1), 1, 4, 280.00, '2024-04-01 12:30:00'),
((SELECT event_id FROM events WHERE name = 'Stand-up Comedy Night' LIMIT 1), (SELECT user_id FROM users WHERE email = 'klient@example.com' LIMIT 1), 1, 50.00, '2024-03-01 15:00:00');


-- Zakończone rezerwacje (daty w przeszłości, status 'zakonczona')
-- Upewnij się, że user_id=1 (klient@example.com) oraz hall_id=1 (Sala Mała) i hall_id=2 (Sala Duża) istnieją.
INSERT INTO `reservations` (`user_id`, `hall_id`, `reservation_date`, `reservation_time_start`, `reservation_time_end`, `status`, `base_hall_price`, `drinks_price`, `extras_price`, `total_price`, `created_at`) VALUES
((SELECT user_id FROM users WHERE email = 'klient@example.com' LIMIT 1), 1, '2024-05-10', '19:00:00', '02:00:00', 'zakonczona', 250.00, 150.00, 150.00, 550.00, '2024-04-20 11:00:00'),
((SELECT user_id FROM users WHERE email = 'klient@example.com' LIMIT 1), 2, '2024-04-05', '20:00:00', '04:00:00', 'zakonczona', 600.00, 300.00, 350.00, 1250.00, '2024-03-15 14:00:00');

-- Drinki i dodatki do zakończonych rezerwacji
-- Upewnij się, że drink_id i extra_id istnieją.
-- Dla rezerwacji (Sala Mała, 2024-05-10)
INSERT INTO `reservation_drinks` (`reservation_id`, `drink_id`, `quantity_ordered`, `price_at_reservation`) VALUES
((SELECT reservation_id FROM reservations WHERE hall_id=1 AND reservation_date = '2024-05-10' LIMIT 1), (SELECT drink_id FROM drinks WHERE name LIKE 'Wódka%' LIMIT 1), 3, 50.00);
INSERT INTO `reservation_extras` (`reservation_id`, `extra_id`, `price_at_reservation`) VALUES
((SELECT reservation_id FROM reservations WHERE hall_id=1 AND reservation_date = '2024-05-10' LIMIT 1), (SELECT extra_id FROM extras WHERE name LIKE 'Pakiet Przekąsek Standard' LIMIT 1), 150.00);

-- Dla rezerwacji (Sala Duża, 2024-04-05)
INSERT INTO `reservation_drinks` (`reservation_id`, `drink_id`, `quantity_ordered`, `price_at_reservation`) VALUES
((SELECT reservation_id FROM reservations WHERE hall_id=2 AND reservation_date = '2024-04-05' LIMIT 1), (SELECT drink_id FROM drinks WHERE name LIKE 'Whisky%' LIMIT 1), 2, 120.00),
((SELECT reservation_id FROM reservations WHERE hall_id=2 AND reservation_date = '2024-04-05' LIMIT 1), (SELECT drink_id FROM drinks WHERE name LIKE 'Piwo Perła%' LIMIT 1), 5, 12.00);
INSERT INTO `reservation_extras` (`reservation_id`, `extra_id`, `price_at_reservation`) VALUES
((SELECT reservation_id FROM reservations WHERE hall_id=2 AND reservation_date = '2024-04-05' LIMIT 1), (SELECT extra_id FROM extras WHERE name LIKE 'Dedykowany DJ' LIMIT 1), 350.00);