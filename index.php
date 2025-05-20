<?php include 'includes/header.php'; ?>
<div class="hero"></div>
<h2>Witamy w Bania u Cygana</h2>
<p>Oferujemy wynajem sal na imprezy urodzinowe z możliwością wyboru dodatków takich jak DJ, fotobudka, ochrona i wiele innych.</p>
<?php if (is_logged_in()): ?>
    <p>Witaj, <?php echo $_SESSION['email']; ?>! Możesz teraz dokonać rezerwacji lub przejść do swojego panelu.</p>
<?php else: ?>
    <p>Zaloguj się, aby dokonać rezerwacji.</p>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>