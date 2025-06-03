<?php require_once __DIR__ . '/db_connect.php'; ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NightClub Projekt</title>
    <link rel="stylesheet" href="/nightclub/css/style.css?v=<?php echo time(); // Cache busting ?>">
</head>
<body>
    <header>
        <div class="container">
            <a href="/nightclub/index.php" id="logo-link">
                <img src="/nightclub/images/logo.png" alt="Logo Klubu" id="logo-img">
            </a>
            <nav>
                <ul>
                    <li><a href="/nightclub/index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Strona Główna</a></li>
                    <li><a href="/nightclub/oferta.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'oferta.php' ? 'active' : ''; ?>">Oferta</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="/nightclub/kontakt.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kontakt.php' ? 'active' : ''; ?>">Kontakt</a></li>
                        <?php if (getUserRole() == 'klient'): ?>
                            <li><a href="/nightclub/client/kup_bilety.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'kup_bilety.php' ? 'active' : ''; ?>">Kup Bilety</a></li>
                            <li><a href="/nightclub/client/rezerwacja_sali.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'rezerwacja_sali.php' ? 'active' : ''; ?>">Rezerwuj Salę</a></li>
                            <li><a href="/nightclub/client/panel_klienta.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'panel_klienta.php' ? 'active' : ''; ?>">Mój Panel</a></li>
                        <?php elseif (getUserRole() == 'pracownik'): ?>
                            <li><a href="/nightclub/employee/panel_pracownika.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'panel_pracownika.php' ? 'active' : ''; ?>">Panel Pracownika</a></li>
                        <?php elseif (getUserRole() == 'wlasciciel'): ?>
                            <li><a href="/nightclub/owner/panel_wlasciciela.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'panel_wlasciciela.php' ? 'active' : ''; ?>">Panel Właściciela</a></li>
                        <?php endif; ?>
                        <li><a href="/nightclub/wyloguj.php">Wyloguj (<?php echo sanitize_output($_SESSION['email']); ?>)</a></li>
                    <?php else: ?>
                        <li><a href="/nightclub/zaloguj.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'zaloguj.php' ? 'active' : ''; ?>">Zaloguj</a></li>
                        <li><a href="/nightclub/zarejestruj.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'zarejestruj.php' ? 'active' : ''; ?>">Zarejestruj</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">
        <?php
        if (isset($_SESSION['message'])) {
            echo '<div class="message ' . sanitize_output($_SESSION['message_type']) . '">' . sanitize_output($_SESSION['message']) . '</div>';
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>