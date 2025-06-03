<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('pracownik');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_drink_submit'])) {
    $drink_id = isset($_POST['drink_id_to_update']) ? intval($_POST['drink_id_to_update']) : 0;
    $quantity_to_add = isset($_POST['quantity_to_add']) ? intval($_POST['quantity_to_add']) : 0;

    if ($drink_id <= 0) {
        $_SESSION['message'] = "Nie wybrano drinka do aktualizacji.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/employee/panel_pracownika.php?view=drinks");
        exit();
    }
    if ($quantity_to_add == 0) {
        $_SESSION['message'] = "Podaj ilość do dodania (różną od zera).";
        $_SESSION['message_type'] = "warning";
        header("Location: /nightclub/employee/panel_pracownika.php?view=drinks");
        exit();
    }
    // Jeśli chcemy umożliwić odejmowanie, usuń warunek $quantity_to_add < 0.
    // Obecnie tylko dodawanie.
    if ($quantity_to_add < 0) {
        $_SESSION['message'] = "Można tylko dodawać ilość drinków. Aby zmniejszyć, użyj innej funkcji (niezaimplementowana).";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/employee/panel_pracownika.php?view=drinks");
        exit();
    }


    $stmt = $conn->prepare("UPDATE drinks SET quantity_available = quantity_available + ? WHERE drink_id = ?");
    $stmt->bind_param("ii", $quantity_to_add, $drink_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Stan drinka został zaktualizowany.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Nie znaleziono drinka o podanym ID lub nie dokonano zmiany.";
            $_SESSION['message_type'] = "warning";
        }
    } else {
        $_SESSION['message'] = "Błąd podczas aktualizacji stanu drinka: " . $conn->error;
        $_SESSION['message_type'] = "error";
        error_log("Błąd aktualizacji drinka: " . $conn->error);
    }
    $stmt->close();
    header("Location: /nightclub/employee/panel_pracownika.php?view=drinks");
    exit();

} else {
    header("Location: /nightclub/employee/panel_pracownika.php?view=drinks");
    exit();
}
?>