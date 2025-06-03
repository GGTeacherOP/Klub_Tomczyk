<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('pracownik');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_reservation_id_employee'])) {
    $reservation_id = intval($_POST['cancel_reservation_id_employee']);

    if ($reservation_id <= 0) {
        $_SESSION['message'] = "Nieprawidłowe ID rezerwacji.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/employee/panel_pracownika.php?view=reservations");
        exit();
    }

    $conn->begin_transaction();
    try {
        // Sprawdź status rezerwacji
        $stmt_check = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
        $stmt_check->bind_param("i", $reservation_id);
        $stmt_check->execute();
        $reservation = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if (!$reservation) {
            throw new Exception("Nie znaleziono rezerwacji.");
        }

        // Pracownik może anulować rezerwacje 'oczekujące' lub 'potwierdzone'
        if ($reservation['status'] !== 'oczekujaca' && $reservation['status'] !== 'potwierdzona') {
            throw new Exception("Tej rezerwacji nie można anulować z obecnym statusem: " . sanitize_output($reservation['status']));
        }

        // 1. Zwróć zamówione drinki do magazynu
        $stmt_drinks = $conn->prepare("SELECT drink_id, quantity_ordered FROM reservation_drinks WHERE reservation_id = ?");
        $stmt_drinks->bind_param("i", $reservation_id);
        $stmt_drinks->execute();
        $ordered_drinks = $stmt_drinks->get_result();
        
        $stmt_update_drink_qty = $conn->prepare("UPDATE drinks SET quantity_available = quantity_available + ? WHERE drink_id = ?");
        while ($drink = $ordered_drinks->fetch_assoc()) {
            $stmt_update_drink_qty->bind_param("ii", $drink['quantity_ordered'], $drink['drink_id']);
            if (!$stmt_update_drink_qty->execute()) throw new Exception("Błąd zwracania drinków do magazynu.");
        }
        $stmt_drinks->close();
        $stmt_update_drink_qty->close();

        // 2. Zmień status rezerwacji
        $stmt_cancel = $conn->prepare("UPDATE reservations SET status = 'anulowana_pracownik' WHERE reservation_id = ?");
        $stmt_cancel->bind_param("i", $reservation_id);
        if (!$stmt_cancel->execute()) throw new Exception("Błąd zmiany statusu rezerwacji.");
        $stmt_cancel->close();

        $conn->commit();
        $_SESSION['message'] = "Rezerwacja została pomyślnie anulowana przez pracownika.";
        $_SESSION['message_type'] = "success";
        // TODO: Można dodać wysyłanie e-maila do klienta o anulowaniu

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Błąd podczas anulowania rezerwacji: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        error_log("Błąd anulowania rezerwacji (pracownik): " . $e->getMessage());
    }
    header("Location: /nightclub/employee/panel_pracownika.php?view=reservations");
    exit();

} else {
    header("Location: /nightclub/employee/panel_pracownika.php");
    exit();
}
?>