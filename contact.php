<?php include 'includes/header.php'; ?>
    <h2>Kontakt</h2>
    <button id="open_contact_form">Otwórz formularz kontaktowy</button>
    <div id="contact_modal" style="display:none;">
        <div class="modal-content">
            <span id="close_modal">×</span>
            <h2>Skontaktuj się z nami</h2>
            <form action="wyslij_wiadomosc.php" method="post">
                <label for="name">Imię:</label>
                <input type="text" name="name" required>
                <label for="email">Email:</label>
                <input type="email" name="email" required>
                <label for="message">Wiadomość:</label>
                <textarea name="message" rows="5" required></textarea>
                <input type="submit" value="Wyślij">
            </form>
        </div>
    </div>
<?php include 'includes/footer.php'; ?>