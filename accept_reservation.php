<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Rezerwacja została potwierdzona.';
    } else {
        $_SESSION['error_message'] = 'Błąd podczas potwierdzania rezerwacji.';
    }
    $stmt->close();
}

header('Location: panel_pracownika.php');
exit;
?>