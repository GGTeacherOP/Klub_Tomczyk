<?php include 'includes/header.php'; ?>
    <h2>Rejestracja</h2>
    <form method="post" action="process_registration.php">
        <label for="email">Email:</label>
        <input type="email" name="email" required><br>
        <label for="password">Hasło:</label>
        <input type="password" name="password" required><br>
        <input type="submit" value="Zarejestruj się">
    </form>
<?php include 'includes/footer.php'; ?>