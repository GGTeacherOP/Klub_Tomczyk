<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireLogin();
// Jeśli rola to klient, wymagaj zatwierdzenia
if (getUserRole() == 'klient') {
    requireApprovedUser();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_message'])) {
    $user_id = getUserId();
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : 'Brak tematu';
    $message_text = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($message_text)) {
        $_SESSION['message'] = "Wiadomość nie może być pusta.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/kontakt.php");
        exit();
    }
    if (empty($subject)) {
        $subject = "Wiadomość od " . $_SESSION['email']; // Domyślny temat
    }


    $stmt = $conn->prepare("INSERT INTO contact_messages (user_id, subject, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $subject, $message_text);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Twoja wiadomość została wysłana. Skontaktujemy się z Tobą wkrótce.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Wystąpił błąd podczas wysyłania wiadomości: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: /nightclub/kontakt.php");
    exit();
} else {
    header("Location: /nightclub/kontakt.php");
    exit();
}
?>