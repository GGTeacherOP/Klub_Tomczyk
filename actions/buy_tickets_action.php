<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('klient');
requireApprovedUser();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['buy_tickets_submit'])) {
    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

    if ($event_id <= 0 || $quantity <= 0) {
        $_SESSION['message'] = "Nieprawidłowe dane zakupu biletów.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/client/kup_bilety.php");
        exit();
    }

    // Pobierz informacje o wydarzeniu i sprawdź dostępność biletów
    $stmt = $conn->prepare("SELECT name, ticket_price, total_tickets, tickets_sold FROM events WHERE event_id = ? AND date >= CURDATE()");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $event = $result->fetch_assoc();
        $available_tickets = $event['total_tickets'] - $event['tickets_sold'];

        if ($quantity > $available_tickets) {
            $_SESSION['message'] = "Niewystarczająca liczba dostępnych biletów. Dostępne: " . $available_tickets;
            $_SESSION['message_type'] = "error";
            header("Location: /nightclub/client/kup_bilety.php?event_id=" . $event_id);
            exit();
        }

        // Zapisz tymczasowe dane o zamówieniu w sesji
        $_SESSION['ticket_order'] = [
            'event_id' => $event_id,
            'event_name' => $event['name'],
            'quantity' => $quantity,
            'ticket_price' => $event['ticket_price'],
            'total_price' => $quantity * $event['ticket_price']
        ];
        
        $stmt->close();
        header("Location: /nightclub/client/symulacja_platnosci.php");
        exit();

    } else {
        $_SESSION['message'] = "Nie znaleziono wydarzenia lub jest ono już nieaktualne.";
        $_SESSION['message_type'] = "error";
        $stmt->close();
        header("Location: /nightclub/client/kup_bilety.php");
        exit();
    }
} else {
    header("Location: /nightclub/client/kup_bilety.php");
    exit();
}
?>