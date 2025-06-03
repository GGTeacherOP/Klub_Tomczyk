<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('pracownik');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_user_id'])) {
    $user_to_approve_id = intval($_POST['approve_user_id']);

    if ($user_to_approve_id <= 0) {
        $_SESSION['message'] = "Nieprawidłowe ID użytkownika.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/employee/panel_pracownika.php?view=users");
        exit();
    }

    // Sprawdź, czy użytkownik istnieje i jest klientem niezatwierdzonym
    $stmt_check = $conn->prepare("SELECT role, is_approved FROM users WHERE user_id = ?");
    $stmt_check->bind_param("i", $user_to_approve_id);
    $stmt_check->execute();
    $user_data = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$user_data) {
        $_SESSION['message'] = "Nie znaleziono użytkownika o podanym ID.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/employee/panel_pracownika.php?view=users");
        exit();
    }

    if ($user_data['role'] !== 'klient') {
        $_SESSION['message'] = "Można zatwierdzać tylko konta klientów.";
        $_SESSION['message_type'] = "warning";
        header("Location: /nightclub/employee/panel_pracownika.php?view=users");
        exit();
    }

    if ($user_data['is_approved']) {
        $_SESSION['message'] = "To konto jest już zatwierdzone.";
        $_SESSION['message_type'] = "info";
        header("Location: /nightclub/employee/panel_pracownika.php?view=users");
        exit();
    }

    $stmt = $conn->prepare("UPDATE users SET is_approved = TRUE WHERE user_id = ? AND role = 'klient'");
    $stmt->bind_param("i", $user_to_approve_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Konto użytkownika zostało zatwierdzone.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Nie udało się zatwierdzić konta (możliwe, że już było zatwierdzone lub nie jest klientem).";
            $_SESSION['message_type'] = "warning";
        }
    } else {
        $_SESSION['message'] = "Błąd podczas zatwierdzania konta: " . $conn->error;
        $_SESSION['message_type'] = "error";
        error_log("Błąd zatwierdzania użytkownika: " . $conn->error);
    }
    $stmt->close();
    header("Location: /nightclub/employee/panel_pracownika.php?view=users");
    exit();

} else {
    header("Location: /nightclub/employee/panel_pracownika.php");
    exit();
}
?>