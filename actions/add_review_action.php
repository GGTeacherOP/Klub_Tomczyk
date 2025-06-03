<?php
require_once __DIR__ . '/../includes/db_connect.php';
requireLogin();
requireApprovedUser(); // Tylko zatwierdzeni klienci mogą dodawać opinie

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (getUserRole() !== 'klient') {
        $_SESSION['message'] = "Tylko klienci mogą dodawać opinie.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/index.php");
        exit();
    }

    $user_id = getUserId();
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if ($rating < 1 || $rating > 5) {
        $_SESSION['message'] = "Ocena musi być w zakresie od 1 do 5.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/index.php#customer-reviews");
        exit();
    }
    if (empty($comment)) {
        $_SESSION['message'] = "Komentarz nie może być pusty.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/index.php#customer-reviews");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $rating, $comment);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Dziękujemy za Twoją opinię!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Wystąpił błąd podczas dodawania opinii: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: /nightclub/index.php#customer-reviews");
    exit();
} else {
    header("Location: /nightclub/index.php");
    exit();
}
?>