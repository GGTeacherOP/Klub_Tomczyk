<?php
require_once __DIR__ . '/includes/header.php';
requireLogin(); // Tylko zalogowani mogą wysyłać wiadomości przez formularz
// Dodatkowo, jeśli chcesz, aby tylko zatwierdzeni klienci mogli kontaktować, dodaj:
if (getUserRole() == 'klient') {
    requireApprovedUser();
}
?>

<h2>Kontakt</h2>
<p>Masz pytania lub sugestie? Skontaktuj się z nami za pomocą poniższego formularza.</p>

<form action="/nightclub/actions/contact_form_action.php" method="POST" class="styled-form">
    <div>
        <label for="subject">Temat:</label>
        <input type="text" id="subject" name="subject" required maxlength="255">
    </div>
    <div>
        <label for="message">Wiadomość:</label>
        <textarea id="message" name="message" rows="6" required></textarea>
    </div>
    <button type="submit" name="send_message" class="btn">Wyślij Wiadomość</button>
</form>

<div class="contact-info">
    <h3>Inne formy kontaktu:</h3>
    <p><strong>Adres:</strong> ul. Klubowa 1, 00-001 MiastoImprez</p>
    <p><strong>Telefon:</strong> +48 123 456 789 (pn-pt, 10:00-18:00)</p>
    <p><strong>Email ogólny:</strong> info@nightclubprojekt.example.com</p>
</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>