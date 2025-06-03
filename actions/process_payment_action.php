<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('klient');
requireApprovedUser();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['ticket_order'])) {
    $order = $_SESSION['ticket_order'];
    $user_id = getUserId();

    // Sprawdź ponownie dostępność biletów (na wszelki wypadek, jeśli ktoś inny kupił w międzyczasie)
    $stmt_check = $conn->prepare("SELECT total_tickets, tickets_sold FROM events WHERE event_id = ?");
    $stmt_check->bind_param("i", $order['event_id']);
    $stmt_check->execute();
    $event_data = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$event_data) {
        $_SESSION['message'] = "Wystąpił błąd przy przetwarzaniu płatności (brak wydarzenia).";
        $_SESSION['message_type'] = "error";
        unset($_SESSION['ticket_order']);
        header("Location: /nightclub/client/kup_bilety.php");
        exit();
    }

    $available_tickets_now = $event_data['total_tickets'] - $event_data['tickets_sold'];

    if ($order['quantity'] > $available_tickets_now) {
        $_SESSION['message'] = "Przepraszamy, w międzyczasie ktoś wykupił część biletów. Dostępne: " . $available_tickets_now;
        $_SESSION['message_type'] = "error";
        unset($_SESSION['ticket_order']);
        header("Location: /nightclub/client/kup_bilety.php?event_id=" . $order['event_id']);
        exit();
    }

    // Rozpocznij transakcję
    $conn->begin_transaction();

    try {
        // 1. Dodaj bilet(y) do tabeli tickets
        $stmt_insert_ticket = $conn->prepare("INSERT INTO tickets (event_id, user_id, quantity, total_price, purchase_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt_insert_ticket->bind_param("iiid", $order['event_id'], $user_id, $order['quantity'], $order['total_price']);
        $stmt_insert_ticket->execute();
        $stmt_insert_ticket->close();

        // 2. Zaktualizuj liczbę sprzedanych biletów w tabeli events
        $stmt_update_event = $conn->prepare("UPDATE events SET tickets_sold = tickets_sold + ? WHERE event_id = ?");
        $stmt_update_event->bind_param("ii", $order['quantity'], $order['event_id']);
        $stmt_update_event->execute();
        $stmt_update_event->close();

        // Zatwierdź transakcję
        $conn->commit();

        $_SESSION['message'] = "Płatność zakończona pomyślnie! Twoje bilety zostały zakupione.";
        $_SESSION['message_type'] = "success";
        unset($_SESSION['ticket_order']); // Usuń dane zamówienia z sesji
        header("Location: /nightclub/client/panel_klienta.php?view=tickets"); // Przekieruj do panelu klienta, zakładka bilety
        exit();

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback(); // Wycofaj transakcję w przypadku błędu
        $_SESSION['message'] = "Wystąpił błąd podczas przetwarzania zakupu: " . $exception->getMessage();
        $_SESSION['message_type'] = "error";
        unset($_SESSION['ticket_order']);
        error_log("Błąd transakcji zakupu biletów: " . $exception->getMessage());
        header("Location: /nightclub/client/kup_bilety.php");
        exit();
    }

} else {
    // Jeśli ktoś trafił tu bezpośrednio lub bez danych zamówienia
    $_SESSION['message'] = "Nieprawidłowe żądanie płatności.";
    $_SESSION['message_type'] = "error";
    if (isset($_SESSION['ticket_order'])) unset($_SESSION['ticket_order']);
    header("Location: /nightclub/client/kup_bilety.php");
    exit();
}
?>