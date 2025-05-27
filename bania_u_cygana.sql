-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Maj 26, 2025 at 11:05 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bania_u_cygana`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sala` enum('Sala X','Sala Y') NOT NULL,
  `data` date NOT NULL,
  `dodatki` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `sala`, `data`, `dodatki`, `status`) VALUES
(1, 3, 'Sala X', '2024-06-20', '[\"DJ\", \"Fotobudka\"]', 'confirmed'),
(2, 3, 'Sala Y', '2024-07-15', '[\"Ochrona\"]', 'confirmed'),
(3, 4, 'Sala Y', '2025-05-30', '[\"Fotobudka\",\"Ochrona\"]', 'confirmed'),
(4, 3, 'Sala X', '2025-05-27', '[\"DJ\",\"Ochrona\"]', 'confirmed'),
(5, 3, 'Sala X', '2025-05-31', '[\"Fotobudka\"]', 'confirmed');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `booking_drinks`
--

CREATE TABLE `booking_drinks` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `drink_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_drinks`
--

INSERT INTO `booking_drinks` (`id`, `booking_id`, `drink_id`, `quantity`) VALUES
(1, 3, 2, 3),
(2, 3, 3, 4),
(3, 3, 5, 5),
(4, 3, 8, 20),
(5, 4, 1, 20),
(6, 5, 7, 100);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `drinks`
--

CREATE TABLE `drinks` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drinks`
--

INSERT INTO `drinks` (`id`, `name`) VALUES
(1, 'Cola'),
(2, 'Woda'),
(3, 'Sok'),
(4, 'Piwo'),
(5, 'Wino'),
(6, 'Wódka'),
(7, 'Whisky'),
(8, 'Rum');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `sala` enum('Sala X','Sala Y') NOT NULL,
  `drink_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `ostatnia_aktualizacja` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `sala`, `drink_id`, `quantity`, `ostatnia_aktualizacja`) VALUES
(1, 'Sala X', 1, 80, '2025-05-26 20:48:29'),
(2, 'Sala X', 2, 50, '2025-05-26 20:48:29'),
(3, 'Sala X', 3, 30, '2025-05-26 20:48:29'),
(4, 'Sala X', 4, 80, '2025-05-26 20:48:29'),
(5, 'Sala X', 5, 40, '2025-05-26 20:48:29'),
(6, 'Sala X', 6, 20, '2025-05-26 20:48:29'),
(7, 'Sala X', 7, 100, '2025-05-26 20:48:29'),
(8, 'Sala X', 8, 250, '2025-05-26 20:48:29'),
(9, 'Sala Y', 1, 150, '2025-05-26 20:45:22'),
(10, 'Sala Y', 2, 67, '2025-05-26 20:45:22'),
(11, 'Sala Y', 3, 36, '2025-05-26 20:45:22'),
(12, 'Sala Y', 4, 100, '2025-05-26 20:45:22'),
(13, 'Sala Y', 5, 55, '2025-05-26 20:45:22'),
(14, 'Sala Y', 6, 30, '2025-05-26 20:45:22'),
(15, 'Sala Y', 7, 20, '2025-05-26 20:45:22'),
(16, 'Sala Y', 8, 111, '2025-05-26 20:45:22');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `opinions`
--

CREATE TABLE `opinions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `opinion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `pending_users`
--

CREATE TABLE `pending_users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','employee','admin') NOT NULL DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('unconfirmed','confirmed') DEFAULT 'unconfirmed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `created_at`, `status`) VALUES
(1, 'admin@bania.pl', 'admin123', 'admin', '2025-05-26 20:19:04', 'confirmed'),
(2, 'pracownik@bania.pl', 'pracownik123', 'employee', '2025-05-26 20:19:04', 'confirmed'),
(3, 'klient@test.pl', 'klient123', 'client', '2025-05-26 20:19:04', 'confirmed'),
(4, 'testrej@test.com', 'test123', 'employee', '2025-05-26 20:39:01', 'confirmed');

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Indeksy dla tabeli `booking_drinks`
--
ALTER TABLE `booking_drinks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `drink_id` (`drink_id`);

--
-- Indeksy dla tabeli `drinks`
--
ALTER TABLE `drinks`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `drink_id` (`drink_id`);

--
-- Indeksy dla tabeli `opinions`
--
ALTER TABLE `opinions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeksy dla tabeli `pending_users`
--
ALTER TABLE `pending_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `booking_drinks`
--
ALTER TABLE `booking_drinks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `drinks`
--
ALTER TABLE `drinks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `opinions`
--
ALTER TABLE `opinions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pending_users`
--
ALTER TABLE `pending_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `booking_drinks`
--
ALTER TABLE `booking_drinks`
  ADD CONSTRAINT `booking_drinks_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  ADD CONSTRAINT `booking_drinks_ibfk_2` FOREIGN KEY (`drink_id`) REFERENCES `drinks` (`id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`drink_id`) REFERENCES `drinks` (`id`);

--
-- Constraints for table `opinions`
--
ALTER TABLE `opinions`
  ADD CONSTRAINT `opinions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
