<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('pracownik');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['message_id_to_mark'])) {
    $message_id = intval($_POST['message_id_to_mark']);

    if ($message_id > 0) {
        $stmt = $conn->prepare("UPDATE contact_messages SET is_read = TRUE WHERE message_id = ? AND is_read = FALSE");
        $stmt->bind_param("i", $message_id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                // Nie wysyłamy komunikatu sesji, bo to będzie obsłużone przez AJAX w JS
                // lub po prostu strona się odświeży (jeśli JS nie używa AJAX)
                // Można zwrócić JSON response jeśli obsługa jest przez AJAX
                // Dla uproszczenia, po prostu przekierowujemy
            }
        } else {
            error_log("Błąd oznaczania wiadomości jako przeczytanej: " . $conn->error);
        }
        $stmt->close();
    }
}
// Przekierowanie z powrotem do panelu pracownika, zakładka wiadomości
header("Location: /nightclub/employee/panel_pracownika.php?view=messages");
exit();
?>