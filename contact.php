<?php include 'includes/header.php'; ?>

<div class="container">
    <div class="form-container">
        <h2>Kontakt</h2>
        <form action="wyslij_wiadomosc.php" method="post">
            <div class="form-group">
                <label>Imię i nazwisko:</label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Wiadomość:</label>
                <textarea name="message" rows="5" required></textarea>
            </div>
            <button type="submit">Wyślij</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>