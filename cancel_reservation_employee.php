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

    $stmt = $conn->prepare("SELECT sala FROM bookings WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sala = $row['sala'];
        $stmt->close();

        $stmt = $conn->prepare("SELECT drink_id, quantity FROM booking_drinks WHERE booking_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $drinks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($drinks as $drink) {
            $drink_id = (int)$drink['drink_id'];
            $quantity = (int)$drink['quantity'];
            $stmt_update = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE sala = ? AND drink_id = ?");
            $stmt_update->bind_param('isi', $quantity, $sala, $drink_id);
            $stmt_update->execute();
            $stmt_update->close();
        }

        $stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Rezerwacja została anulowana.';
        } else {
            $_SESSION['error_message'] = 'Błąd podczas anulowania rezerwacji.';
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = 'Rezerwacja nie znaleziona.';
    }
}

header('Location: panel_pracownika.php');
exit;
?>