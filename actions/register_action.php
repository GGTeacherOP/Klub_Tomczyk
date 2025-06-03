<?php
require_once __DIR__ . '/../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Bez hashowania, zgodnie z poleceniem
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $role = 'klient';
    $is_approved = FALSE; // Domyślnie konto niezatwierdzone

    // Podstawowa walidacja
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['message'] = "Email i hasła są wymagane.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/zarejestruj.php");
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['message'] = "Nieprawidłowy format adresu e-mail.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/zarejestruj.php");
        exit();
    }
    if (strlen($password) < 6) {
        $_SESSION['message'] = "Hasło musi mieć co najmniej 6 znaków.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/zarejestruj.php");
        exit();
    }
    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Hasła nie są zgodne.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/zarejestruj.php");
        exit();
    }

    // Sprawdzenie, czy email już istnieje
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['message'] = "Użytkownik z tym adresem e-mail już istnieje.";
        $_SESSION['message_type'] = "error";
        $stmt->close();
        header("Location: /nightclub/zarejestruj.php");
        exit();
    }
    $stmt->close();

    // Dodanie użytkownika
    $stmt_insert = $conn->prepare("INSERT INTO users (email, password, first_name, last_name, role, is_approved) VALUES (?, ?, ?, ?, ?, ?)");
    // Konwersja BOOLEAN na integer dla bind_param
    $is_approved_int = (int)$is_approved;
    $stmt_insert->bind_param("sssssi", $email, $password, $first_name, $last_name, $role, $is_approved_int);

    if ($stmt_insert->execute()) {
        $_SESSION['message'] = "Rejestracja pomyślna! Twoje konto oczekuje na zatwierdzenie przez pracownika.";
        $_SESSION['message_type'] = "success";
        header("Location: /nightclub/zaloguj.php");
    } else {
        $_SESSION['message'] = "Błąd rejestracji: " . $conn->error;
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/zarejestruj.php");
    }
    $stmt_insert->close();
} else {
    header("Location: /nightclub/zarejestruj.php");
}
exit();
?>