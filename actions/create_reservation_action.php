<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('klient');
requireApprovedUser();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = getUserId();
    $hall_id = isset($_POST['hall_id']) ? intval($_POST['hall_id']) : 0;
    $reservation_date = isset($_POST['reservation_date']) ? $_POST['reservation_date'] : '';
    $time_start = isset($_POST['reservation_time_start']) ? $_POST['reservation_time_start'] : '';
    $time_end = isset($_POST['reservation_time_end']) ? $_POST['reservation_time_end'] : '';
    $ordered_drinks_input = isset($_POST['drinks']) ? $_POST['drinks'] : []; // np. ['drink_id' => quantity]
    $selected_extras_input = isset($_POST['extras']) ? $_POST['extras'] : []; // np. ['extra_id1', 'extra_id2']

    // Podstawowa walidacja danych wejściowych
    if ($hall_id <= 0 || empty($reservation_date) || empty($time_start) || empty($time_end)) {
        $_SESSION['message'] = "Wszystkie pola dotyczące sali i terminu są wymagane.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/client/rezerwacja_sali.php");
        exit();
    }
    if (strtotime($reservation_date) < strtotime(date('Y-m-d'))) {
        $_SESSION['message'] = "Data rezerwacji nie może być z przeszłości.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/client/rezerwacja_sali.php");
        exit();
    }
    if ($time_start >= $time_end) {
        $_SESSION['message'] = "Godzina zakończenia musi być późniejsza niż rozpoczęcia.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/client/rezerwacja_sali.php");
        exit();
    }

    // Sprawdzenie czy termin nie jest już zajęty (uproszczone - tylko data, dla pełnego sprawdzenia potrzebne bardziej złożone zapytanie z godzinami)
    // Dla projektu szkolnego to może wystarczyć, albo można dodać pełniejsze sprawdzenie kolizji godzinowych
    $stmt_check_collision = $conn->prepare(
        "SELECT reservation_id FROM reservations 
         WHERE hall_id = ? AND reservation_date = ? AND status IN ('oczekujaca', 'potwierdzona') AND 
         NOT (? <= reservation_time_start OR ? >= reservation_time_end)"
    );
    $stmt_check_collision->bind_param("isss", $hall_id, $reservation_date, $time_end, $time_start);
    $stmt_check_collision->execute();
    $stmt_check_collision->store_result();
    if ($stmt_check_collision->num_rows > 0) {
        $_SESSION['message'] = "Wybrany termin dla tej sali jest już zajęty lub koliduje z inną rezerwacją.";
        $_SESSION['message_type'] = "error";
        $stmt_check_collision->close();
        header("Location: /nightclub/client/rezerwacja_sali.php");
        exit();
    }
    $stmt_check_collision->close();


    // Rozpocznij transakcję
    $conn->begin_transaction();
    try {
        // 1. Pobierz cenę bazową sali
        $stmt_hall = $conn->prepare("SELECT base_price FROM halls WHERE hall_id = ?");
        $stmt_hall->bind_param("i", $hall_id);
        $stmt_hall->execute();
        $hall_data = $stmt_hall->get_result()->fetch_assoc();
        $stmt_hall->close();
        if (!$hall_data) throw new Exception("Nie znaleziono sali.");
        $base_hall_price = floatval($hall_data['base_price']);

        $current_drinks_price_total = 0;
        $drinks_to_insert = [];

        // 2. Przetwórz zamówione drinki i zaktualizuj ich stan
        foreach ($ordered_drinks_input as $drink_id => $quantity_ordered) {
            $drink_id = intval($drink_id);
            $quantity_ordered = intval($quantity_ordered);

            if ($quantity_ordered > 0) {
                // Pobierz aktualną cenę i dostępność drinka (z blokadą FOR UPDATE)
                $stmt_drink = $conn->prepare("SELECT name, price_per_unit, quantity_available FROM drinks WHERE drink_id = ? FOR UPDATE");
                $stmt_drink->bind_param("i", $drink_id);
                $stmt_drink->execute();
                $drink_data = $stmt_drink->get_result()->fetch_assoc();
                $stmt_drink->close();

                if (!$drink_data) throw new Exception("Nie znaleziono drinka o ID: $drink_id.");
                if ($quantity_ordered > $drink_data['quantity_available']) {
                    throw new Exception("Niewystarczająca ilość drinka '" . sanitize_output($drink_data['name']) . "'. Dostępne: " . $drink_data['quantity_available'] . ", zamówiono: $quantity_ordered.");
                }

                // Zmniejsz ilość dostępnych drinków
                $stmt_update_drink = $conn->prepare("UPDATE drinks SET quantity_available = quantity_available - ? WHERE drink_id = ?");
                $stmt_update_drink->bind_param("ii", $quantity_ordered, $drink_id);
                if (!$stmt_update_drink->execute()) throw new Exception("Błąd aktualizacji stanu drinka: " . $conn->error);
                $stmt_update_drink->close();

                $drink_price_at_reservation = floatval($drink_data['price_per_unit']);
                $current_drinks_price_total += $quantity_ordered * $drink_price_at_reservation;
                $drinks_to_insert[] = ['id' => $drink_id, 'qty' => $quantity_ordered, 'price' => $drink_price_at_reservation];
            }
        }

        $current_extras_price_total = 0;
        $extras_to_insert = [];
        // 3. Przetwórz wybrane dodatki
        if (!empty($selected_extras_input)) {
            // Zamień tablicę stringów na placeholder dla IN()
            $placeholders = implode(',', array_fill(0, count($selected_extras_input), '?'));
            $types = str_repeat('i', count($selected_extras_input));

            $stmt_extras = $conn->prepare("SELECT extra_id, price FROM extras WHERE extra_id IN ($placeholders)");
            $stmt_extras->bind_param($types, ...$selected_extras_input);
            $stmt_extras->execute();
            $extras_result = $stmt_extras->get_result();
            while ($extra_data = $extras_result->fetch_assoc()) {
                $extra_price_at_reservation = floatval($extra_data['price']);
                $current_extras_price_total += $extra_price_at_reservation;
                $extras_to_insert[] = ['id' => $extra_data['extra_id'], 'price' => $extra_price_at_reservation];
            }
            $stmt_extras->close();
        }

        $total_reservation_price = $base_hall_price + $current_drinks_price_total + $current_extras_price_total;

        // 4. Dodaj rezerwację do tabeli reservations
        $stmt_insert_res = $conn->prepare("INSERT INTO reservations (user_id, hall_id, reservation_date, reservation_time_start, reservation_time_end, status, base_hall_price, drinks_price, extras_price, total_price, created_at) VALUES (?, ?, ?, ?, ?, 'oczekujaca', ?, ?, ?, ?, NOW())");
        $stmt_insert_res->bind_param("iisssdddd", $user_id, $hall_id, $reservation_date, $time_start, $time_end, $base_hall_price, $current_drinks_price_total, $current_extras_price_total, $total_reservation_price);
        if (!$stmt_insert_res->execute()) throw new Exception("Błąd tworzenia rezerwacji: " . $conn->error);
        $new_reservation_id = $conn->insert_id; // Pobierz ID nowej rezerwacji
        $stmt_insert_res->close();

        // 5. Dodaj zamówione drinki do reservation_drinks
        if (!empty($drinks_to_insert)) {
            $stmt_res_drink = $conn->prepare("INSERT INTO reservation_drinks (reservation_id, drink_id, quantity_ordered, price_at_reservation) VALUES (?, ?, ?, ?)");
            foreach ($drinks_to_insert as $drink_item) {
                $stmt_res_drink->bind_param("iiid", $new_reservation_id, $drink_item['id'], $drink_item['qty'], $drink_item['price']);
                if (!$stmt_res_drink->execute()) throw new Exception("Błąd dodawania drinka do rezerwacji: " . $conn->error);
            }
            $stmt_res_drink->close();
        }

        // 6. Dodaj wybrane dodatki do reservation_extras
        if (!empty($extras_to_insert)) {
            $stmt_res_extra = $conn->prepare("INSERT INTO reservation_extras (reservation_id, extra_id, price_at_reservation) VALUES (?, ?, ?)");
            foreach ($extras_to_insert as $extra_item) {
                $stmt_res_extra->bind_param("iid", $new_reservation_id, $extra_item['id'], $extra_item['price']);
                if (!$stmt_res_extra->execute()) throw new Exception("Błąd dodawania dodatku do rezerwacji: " . $conn->error);
            }
            $stmt_res_extra->close();
        }

        // Zatwierdź transakcję
        $conn->commit();
        $_SESSION['message'] = "Rezerwacja została złożona pomyślnie i oczekuje na potwierdzenie. Płatność na miejscu po imprezie.";
        $_SESSION['message_type'] = "success";
        header("Location: /nightclub/client/panel_klienta.php?view=reservations");
        exit();

    } catch (Exception $e) {
        $conn->rollback(); // Wycofaj transakcję
        $_SESSION['message'] = "Błąd podczas tworzenia rezerwacji: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
        error_log("Błąd transakcji rezerwacji: " . $e->getMessage());
        header("Location: /nightclub/client/rezerwacja_sali.php");
        exit();
    }
} else {
    header("Location: /nightclub/client/rezerwacja_sali.php");
    exit();
}
?>