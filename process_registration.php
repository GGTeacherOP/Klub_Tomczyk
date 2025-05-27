<?php
session_start();
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_message'] = 'Proszę wypełnić wszystkie pola.';
        header('Location: register.php');
        exit;
    }

    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = 'Hasła nie są zgodne.';
        header('Location: register.php');
        exit;
    }

    $stmt = $conn->prepare("SELECT id FROM pending_users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = 'Email już istnieje w oczekujących.';
        header('Location: register.php');
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['error_message'] = 'Email już istnieje w systemie.';
        header('Location: register.php');
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO pending_users (email, password) VALUES (?, ?)");
    $stmt->bind_param('ss', $email, $password);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Rejestracja pomyślna. Proszę czekać na zatwierdzenie przez administratora.';
    } else {
        $_SESSION['error_message'] = 'Błąd podczas rejestracji: ' . $conn->error;
    }
    $stmt->close();
}

header('Location: register.php');
exit;
?>