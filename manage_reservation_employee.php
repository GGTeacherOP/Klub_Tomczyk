<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in() || $_SESSION['role'] != 'employee') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action == 'Potwierdź') {
        $status = 'confirmed';
    } elseif ($action == 'Anuluj') {
        $status = 'cancelled';
    } else {
        echo "Nieprawidłowa akcja.";
        exit;
    }

    $sql = "UPDATE bookings SET status = '$status' WHERE id = '$id'";
    if ($conn->query($sql) === TRUE) {
        echo "Rezerwacja została zaktualizowana.";
    } else {
        echo "Błąd: " . $sql . "<br>" . $conn->error;
    }
}

header('Location: panel_pracownika.php');
exit;
?>