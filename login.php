<?php
include 'includes/config.php';
include 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (login($email, $password)) {
        if ($_SESSION['status'] == 'unconfirmed') {
            echo "Konto nie zostało jeszcze zatwierdzone przez administratora.";
            logout();
        } else {
            header('Location: index.php');
            exit;
        }
    } else {
        echo "Nieprawidłowy email lub hasło.";
    }
}
?>
    <h2>Logowanie</h2>
    <form method="post">
        <label for="email">Email:</label>
        <input type="email" name="email" required><br>
        <label for="password">Hasło:</label>
        <input type="password" name="password" required><br>
        <input type="submit" value="Zaloguj się">
    </form>
<?php include 'includes/footer.php'; ?>