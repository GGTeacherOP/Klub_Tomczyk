<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sala = $_POST['sala'] ?? '';
    $data = $_POST['data'] ?? '';
    $dodatki = isset($_POST['dodatki']) ? json_encode($_POST['dodatki']) : '[]';
    $drinks = $_POST['drinks'] ?? [];
    $user_id = $_SESSION['user_id'];

    if (!in_array($sala, ['Sala X', 'Sala Y']) || empty($data)) {
        $_SESSION['error_message'] = 'Nieprawidłowe dane rezerwacji.';
        header('Location: booking.php');
        exit;
    }

    // Sprawdzenie, czy sala jest wolna
    $stmt_check = $conn->prepare("SELECT id FROM bookings WHERE sala = ? AND data = ? AND status != 'cancelled'");
    $stmt_check->bind_param('ss', $sala, $data);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check->num_rows > 0) {
        $_SESSION['error_message'] = 'Sala jest już zarezerwowana w tym terminie.';
        $stmt_check->close();
        header('Location: booking.php');
        exit;
    }
    $stmt_check->close();

    // Wstawienie rezerwacji
    $stmt_booking = $conn->prepare("INSERT INTO bookings (user_id, sala, data, dodatki) VALUES (?, ?, ?, ?)");
    $stmt_booking->bind_param('isss', $user_id, $sala, $data, $dodatki);
    if ($stmt_booking->execute()) {
        $booking_id = $conn->insert_id;

        // Przetwarzanie drinków
        foreach ($drinks as $drink_id => $quantity) {
            $quantity = (int)$quantity;
            $drink_id = (int)$drink_id;
            if ($quantity > 0) {
                // Aktualizacja zapasów
                $stmt_inventory = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE sala = ? AND drink_id = ?");
                $stmt_inventory->bind_param('isi', $quantity, $sala, $drink_id);
                $stmt_inventory->execute();
                $stmt_inventory->close();

                // Zapis drinków do rezerwacji
                $stmt_booking_drinks = $conn->prepare("INSERT INTO booking_drinks (booking_id, drink_id, quantity) VALUES (?, ?, ?)");
                $stmt_booking_drinks->bind_param('iii', $booking_id, $drink_id, $quantity);
                $stmt_booking_drinks->execute();
                $stmt_booking_drinks->close();
            }
        }

        $_SESSION['success_message'] = 'Rezerwacja została dodana i czeka na potwierdzenie.';
    } else {
        $_SESSION['error_message'] = 'Błąd podczas rezerwacji: ' . $conn->error;
    }
    $stmt_booking->close();
}

header('Location: panel_klienta.php');
exit;
?>