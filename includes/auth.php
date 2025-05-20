<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password']; // Hasło z formularza
    $role = $_POST['role'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    // Porównaj hasła bez hashowania
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        header("Location: /bania-u-cygana/panel_" . $user['role'] . ".php");
        exit();
    } else {
        header("Location: login.php?error=1");
        exit();
    }
}
?>