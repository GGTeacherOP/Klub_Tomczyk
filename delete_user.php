<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in() || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    $sql_check = "SELECT role FROM users WHERE id = '$id'";
    $result = $conn->query($sql_check);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($user['role'] != 'admin') {
            $sql_delete = "DELETE FROM users WHERE id = '$id'";
            if ($conn->query($sql_delete) === TRUE) {
                echo "Użytkownik został usunięty.";
            } else {
                echo "Błąd: " . $sql_delete . "<br>" . $conn->error;
            }
        } else {
            echo "Nie możesz usunąć konta administratora.";
        }
    } else {
        echo "Użytkownik nie istnieje.";
    }
}

header('Location: panel_admin.php');
exit;
?>