<?php require_once __DIR__ . '/includes/header.php';
if (isLoggedIn()) { // Jeśli już zalogowany, przekieruj na stronę główną
    header("Location: /nightclub/index.php");
    exit();
}
?>

<h2>Zaloguj się</h2>
<form action="/nightclub/actions/login_action.php" method="POST" class="styled-form">
    <div>
        <label for="email">Adres e-mail:</label>
        <input type="email" id="email" name="email" required value="<?php echo isset($_SESSION['form_data']['email']) ? sanitize_output($_SESSION['form_data']['email']) : ''; ?>">
    </div>
    <div>
        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <label for="role_choice">Loguję się jako:</label>
        <select id="role_choice" name="role_choice">
            <option value="klient" <?php echo (isset($_SESSION['form_data']['role_choice']) && $_SESSION['form_data']['role_choice'] == 'klient') ? 'selected' : ''; ?>>Klient</option>
            <option value="pracownik" <?php echo (isset($_SESSION['form_data']['role_choice']) && $_SESSION['form_data']['role_choice'] == 'pracownik') ? 'selected' : ''; ?>>Pracownik</option>
            <option value="wlasciciel" <?php echo (isset($_SESSION['form_data']['role_choice']) && $_SESSION['form_data']['role_choice'] == 'wlasciciel') ? 'selected' : ''; ?>>Właściciel</option>
        </select>
    </div>
    <button type="submit" class="btn">Zaloguj</button>
</form>
<?php unset($_SESSION['form_data']); // Usuń dane formularza po wyświetleniu ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>