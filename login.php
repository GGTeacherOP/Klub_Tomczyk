<?php
include 'includes/header.php';
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (login($email, $password)) {
        header('Location: index.php');
        exit;
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
    <input type="submit" value="Zaloguj">
</form>
<?php include 'includes/footer.php'; ?>