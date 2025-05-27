<?php
include 'includes/header.php';

if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: panel_admin.php');
    } elseif ($_SESSION['role'] === 'employee') {
        header('Location: panel_pracownika.php');
    } else {
        header('Location: panel_klienta.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = 'Proszę wypełnić wszystkie pola.';
    } elseif (login($email, $password)) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: panel_admin.php');
        } elseif ($_SESSION['role'] === 'employee') {
            header('Location: panel_pracownika.php');
        } else {
            header('Location: panel_klienta.php');
        }
        exit;
    } else {
        $error = 'Nieprawidłowy email lub hasło.';
    }
}
?>
    <div id="login_form">
        <h2>Logowanie</h2>
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" id="login_form">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Hasło" required>
            <input type="submit" value="Zaloguj się">
        </form>
        <p>Nie masz konta? <a href="register.php">Zarejestruj się!</a></p>
    </div>
<?php include 'includes/footer.php'; ?>