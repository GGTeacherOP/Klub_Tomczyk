<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in() || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $sql = "SELECT * FROM pending_users WHERE id = '$id'";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $email = $user['email'];
        $password = $user['password'];

        $sql_insert = "INSERT INTO users (email, password, role, status) VALUES ('$email', '$password', 'client', 'confirmed')";
        if ($conn->query($sql_insert) === TRUE) {
            $sql_delete = "DELETE FROM pending_users WHERE id = '$id'";
            $conn->query($sql_delete);
            echo "Konto zostało zatwierdzone.";
        } else {
            echo "Błąd: " . $conn->error;
        }
    }
}

header('Location: panel_admin.php');
exit;
?>