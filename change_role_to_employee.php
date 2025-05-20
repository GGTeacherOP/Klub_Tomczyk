<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in() || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    $sql_check = "SELECT * FROM users WHERE email = '$email' AND role = 'client'";
    $result = $conn->query($sql_check);

    if ($result->num_rows == 1) {
        $sql_update = "UPDATE users SET role = 'employee' WHERE email = '$email'";
        if ($conn->query($sql_update) === TRUE) {
            echo "Rola użytkownika została zaktualizowana na pracownika.";
        } else {
            echo "Błąd: " . $sql_update . "<br>" . $conn->error;
        }
    } else {
        echo "Użytkownik o podanym emailu nie jest klientem lub nie istnieje.";
    }
}

header('Location: panel_admin.php');
exit;
?>