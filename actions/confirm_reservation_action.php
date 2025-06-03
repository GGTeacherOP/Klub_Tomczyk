<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('pracownik');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_reservation_id'])) {
    $reservation_id = intval($_POST['confirm_reservation_id']);

    if ($reservation_id <= 0) {
        $_SESSION['message'] = "Nieprawidłowe ID rezerwacji.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/employee/panel_pracownika.php?view=reservations");
        exit();
    }

    // Sprawdź, czy rezerwacja istnieje i ma status 'oczekujaca'
    $stmt_check = $conn->prepare("SELECT status FROM reservations WHERE reservation_id = ?");
    $stmt_check->bind_param("i", $reservation_id);
    $stmt_check->execute();
    $reservation = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$reservation) {
        $_SESSION['message'] = "Nie znaleziono rezerwacji o podanym ID.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/employee/panel_pracownika.php?view=reservations");
        exit();
    }

    if ($reservation['status'] !== 'oczekujaca') {
        $_SESSION['message'] = "Można potwierdzać tylko rezerwacje ze statusm 'oczekująca'. Aktualny status: " . sanitize_output($reservation['status']);
        $_SESSION['message_type'] = "warning";
        header("Location: /nightclub/employee/panel_pracownika.php?view=reservations");
        exit();
    }

    $stmt = $conn->prepare("UPDATE reservations SET status = 'potwierdzona' WHERE reservation_id = ? AND status = 'oczekujaca'");
    $stmt->bind_param("i", $reservation_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Rezerwacja została potwierdzona.";
            $_SESSION['message_type'] = "success";
            // TODO: Można dodać wysyłanie e-maila do klienta o potwierdzeniu
        } else {
            $_SESSION['message'] = "Nie udało się potwierdzić rezerwacji (możliwe, że jej status uległ zmianie).";
            $_SESSION['message_type'] = "warning";
        }
    } else {
        $_SESSION['message'] = "Błąd podczas potwierdzania rezerwacji: " . $conn->error;
        $_SESSION['message_type'] = "error";
        error_log("Błąd potwierdzania rezerwacji: " . $conn->error);
    }
    $stmt->close();
    header("Location: /nightclub/employee/panel_pracownika.php?view=reservations");
    exit();

} else {
    header("Location: /nightclub/employee/panel_pracownika.php");
    exit();
}
?>