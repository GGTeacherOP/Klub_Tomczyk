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

    $stmt = $conn->prepare("SELECT email, password FROM pending_users WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $email = $user['email'];
        $password = $user['password'];
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO users (email, password, status) VALUES (?, ?, 'confirmed')");
        $stmt->bind_param('ss', $email, $password);
        if ($stmt->execute()) {
            $stmt = $conn->prepare("DELETE FROM pending_users WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $_SESSION['success_message'] = 'Użytkownik został zatwierdzony.';
        } else {
            $_SESSION['error_message'] = 'Błąd podczas zatwierdzania użytkownika.';
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = 'Użytkownik nie znaleziony.';
    }
}

header('Location: panel_admin.php');
exit;
?>