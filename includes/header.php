<?php
require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bania u Cygana</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.php">Strona główna</a>
            <a href="offer.php">Oferta</a>
            <a href="contact.php">Kontakt</a>
            <a href="booking.php">Rezerwacja</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="panel_klienta.php" class="my-bookings">Moje rezerwacje</a>
                <span class="welcome-msg">Witaj, <?= explode('@', $_SESSION['email'])[0] ?>!</span>
                <a href="logout.php" class="logout-btn">Wyloguj</a>
            <?php else: ?>
                <a href="login.php" class="login-btn">Zaloguj</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>