<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    $stmt = $conn->prepare("UPDATE users SET role = 'employee' WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Rola użytkownika zmieniona na pracownika.';
    } else {
        $_SESSION['error_message'] = 'Błąd podczas zmiany roli.';
    }
    $stmt->close();
}

header('Location: panel_admin.php');
exit;
?>