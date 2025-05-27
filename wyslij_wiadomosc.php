<?php
session_start();
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $_SESSION['error_message'] = 'Proszę wypełnić wszystkie pola.';
    } else {
        $_SESSION['success_message'] = 'Wiadomość została wysłana. Skontaktujemy się wkrótce!';
    }
}

header('Location: contact.php');
exit;
?>