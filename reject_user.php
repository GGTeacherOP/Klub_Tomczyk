<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in() || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $sql = "DELETE FROM pending_users WHERE id = '$id'";
    if ($conn->query($sql) === TRUE) {
        echo "Rejestracja została odrzucona.";
    } else {
        echo "Błąd: " . $conn->error;
    }
}

header('Location: panel_admin.php');
exit;
?>