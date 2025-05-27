<?php
include 'includes/header.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['success_message'])) {
    echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
?>
    <div id="register_form">
        <h2>Rejestracja</h2>
        <form method="post" action="process_registration.php" id="register_form">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <input type="password" name="confirm_password" placeholder="Potwierdź hasło" required>
            <input type="submit" value="Zarejestruj się">
        </form>
    </div>
<?php include 'includes/footer.php'; ?>