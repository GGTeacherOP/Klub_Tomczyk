<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireRole('wlasciciel');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_role_submit'])) {
    $user_email_to_change = isset($_POST['user_email_to_change']) ? trim($_POST['user_email_to_change']) : '';

    if (empty($user_email_to_change) || !filter_var($user_email_to_change, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Podaj poprawny adres e-mail użytkownika.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/owner/panel_wlasciciela.php?view=users");
        exit();
    }

    // Sprawdź, czy użytkownik istnieje i jest klientem
    $stmt_check = $conn->prepare("SELECT user_id, role FROM users WHERE email = ?");
    $stmt_check->bind_param("s", $user_email_to_change);
    $stmt_check->execute();
    $user_data = $stmt_check->get_result()->fetch_assoc();
    $stmt_check->close();

    if (!$user_data) {
        $_SESSION['message'] = "Nie znaleziono użytkownika o podanym adresie e-mail.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/owner/panel_wlasciciela.php?view=users");
        exit();
    }

    if ($user_data['role'] !== 'klient') {
        $_SESSION['message'] = "Można zmienić rolę tylko użytkownikowi, który jest aktualnie klientem. Obecna rola: " . sanitize_output($user_data['role']);
        $_SESSION['message_type'] = "warning";
        header("Location: /nightclub/owner/panel_wlasciciela.php?view=users");
        exit();
    }
    // Dodatkowo, zatwierdź konto, jeśli nie było
    $stmt = $conn->prepare("UPDATE users SET role = 'pracownik', is_approved = TRUE WHERE email = ? AND role = 'klient'");
    $stmt->bind_param("s", $user_email_to_change);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Rola użytkownika " . sanitize_output($user_email_to_change) . " została zmieniona na 'pracownik', a konto zatwierdzone.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Nie udało się zmienić roli użytkownika (możliwe, że nie jest klientem lub wystąpił inny problem).";
            $_SESSION['message_type'] = "warning";
        }
    } else {
        $_SESSION['message'] = "Błąd podczas zmiany roli użytkownika: " . $conn->error;
        $_SESSION['message_type'] = "error";
        error_log("Błąd zmiany roli użytkownika: " . $conn->error);
    }
    $stmt->close();
    header("Location: /nightclub/owner/panel_wlasciciela.php?view=users");
    exit();

} else {
    header("Location: /nightclub/owner/panel_wlasciciela.php");
    exit();
}
?>