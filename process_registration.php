<?php
include 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Nieprawidłowy email.";
    } elseif (strlen($password) < 6) {
        echo "Hasło musi mieć co najmniej 6 znaków.";
    } else {
        $sql = "INSERT INTO pending_users (email, password) VALUES ('$email', '$password')";
        if ($conn->query($sql) === TRUE) {
            echo "Twoje konto zostało utworzone i czeka na zatwierdzenie przez administratora.";
        } else {
            echo "Błąd: " . $conn->error;
        }
    }
}
?>