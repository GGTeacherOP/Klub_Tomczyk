<?php
require __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="form-box">
        <h2>Logowanie</h2>
        <?php if (isset($_GET['error'])): ?>
            <div class="error">Błędne dane logowania!</div>
        <?php endif; ?>
        <form action="includes/auth.php" method="post">
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Hasło:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Rola:</label>
                <select name="role" required>
                    <option value="client">Klient</option>
                    <option value="employee">Pracownik</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit">Zaloguj</button>
        </form>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';
?>