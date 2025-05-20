<?php
include 'config.php';
include 'auth.php';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Bania u Cygana</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Bania u Cygana</h1>
        <nav>
            <a href="index.php">Strona główna</a>
            <a href="offer.php">Oferta</a>
            <a href="contact.php">Kontakt</a>
            <?php if (is_logged_in()): ?>
                <a href="booking.php">Rezerwacja</a>
                <?php if ($_SESSION['role'] == 'client'): ?>
                    <a href="panel_klienta.php">Panel klienta</a>
                <?php elseif ($_SESSION['role'] == 'employee'): ?>
                    <a href="panel_pracownika.php">Panel pracownika</a>
                <?php elseif ($_SESSION['role'] == 'admin'): ?>
                    <a href="panel_admin.php">Panel admina</a>
                <?php endif; ?>
                <a href="logout.php">Wyloguj</a>
                <span>Zalogowany jako: <?php echo $_SESSION['email']; ?></span>
            <?php else: ?>
                <a href="login.php">Zaloguj</a>
                <a href="register.php">Rejestracja</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <div class="banner"></div>