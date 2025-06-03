<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('klient');
requireApprovedUser();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_reservation_id'])) {
    $reservation_id = intval($_POST['cancel_reservation_id']);
    $user_id = getUserId();

    $conn->begin_transaction();
    try {
        // Sprawdź, czy rezerwacja należy do użytkownika i czy może być anulowana
        $stmt_check = $conn->prepare("SELECT status, reservation_date FROM reservations WHERE reservation_id = ? AND user_id = ?");
        $stmt_check->bind_param("ii", $reservation_id, $user_id);
        $stmt_check->execute();
        $reservation = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if (!$reservation) {
            throw new Exception("Nie znaleziono rezerwacji lub nie masz do niej uprawnień.");
        }

        // Prosta zasada: można anulować co najmniej 24h przed datą rezerwacji
        $can_cancel_until = strtotime($reservation['reservation_date'] . ' 00:00:00') - (24 * 60 * 60);
        if (time() > $can_cancel_until) {
            // Sprawdzenie czy jest to dzień rezerwacji (a nie np. 23h przed)
             if (date('Y-m-d') >= $reservation['reservation_date']) {
                 throw new Exception("Nie można anulować rezerwacji w dniu jej rozpoczęcia lub później.");
             }
        }

        if ($reservation['status'] !== 'oczekujaca' && $reservation['status'] !== 'potwierdzona') {
            throw new Exception("Tej rezerwacji nie można już anulować (status: " . sanitize_output($reservation['status']) . ").");
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
        $stmt_cancel = $conn->prepare("UPDATE reservations SET status = 'anulowana_klient' WHERE reservation_id = ?");
        $stmt_cancel->bind_param("i", $reservation_id);
        if (!$stmt_cancel->execute()) throw new Exception("Błąd zmiany statusu rezerwacji.");
        $stmt_cancel->close();

        $conn->commit();
        $_SESSION['message'] = "Rezerwacja została pomyślnie anulowana.";
        $_SESSION['message_type'] = "success";

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['message'] = "Błąd podczas anulowania rezerwacji: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        error_log("Błąd anulowania rezerwacji (klient): " . $e->getMessage());
    }
    header("Location: /nightclub/client/panel_klienta.php?view=reservations");
    exit();

} else {
    header("Location: /nightclub/client/panel_klienta.php");
    exit();
}
?>