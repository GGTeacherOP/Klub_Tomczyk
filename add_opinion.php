<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opinion = trim($_POST['message'] ?? '');
    $user_id = (int)$_SESSION['user_id'];

    if (empty($opinion)) {
        $_SESSION['error_message'] = 'Opinia nie może być pusta.';
    } else {
        $stmt = $conn->prepare("INSERT INTO opinions (user_id, opinion) VALUES (?, ?)");
        $stmt->bind_param('is', $user_id, $opinion);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Opinia została dodana.';
        } else {
            $_SESSION['error_message'] = 'Błąd podczas dodawania opinii.';
        }
        $stmt->close();
    }
}

header('Location: panel_klienta.php');
exit;
?>