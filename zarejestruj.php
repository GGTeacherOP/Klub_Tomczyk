<?php require_once __DIR__ . '/includes/header.php';
if (isLoggedIn()) { // Jeśli już zalogowany, przekieruj na stronę główną
    header("Location: /nightclub/index.php");
    exit();
}
?>

<h2>Zarejestruj się</h2>
<p>Dołącz do naszej społeczności! Po rejestracji Twoje konto będzie oczekiwać na zatwierdzenie przez pracownika.</p>
<form action="/nightclub/actions/register_action.php" method="POST" class="styled-form">
    <div>
        <label for="email">Adres e-mail:</label>
        <input type="email" id="email" name="email" required>
    </div>
    <div>
        <label for="password">Hasło:</label>
        <input type="password" id="password" name="password" required minlength="6">
    </div>
     <div>
        <label for="confirm_password">Potwierdź hasło:</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
    </div>
    <div>
        <label for="first_name">Imię:</label>
        <input type="text" id="first_name" name="first_name" maxlength="100">
    </div>
    <div>
        <label for="last_name">Nazwisko:</label>
        <input type="text" id="last_name" name="last_name" maxlength="100">
    </div>
    <button type="submit" class="btn">Zarejestruj</button>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>