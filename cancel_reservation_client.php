<?php
include 'includes/config.php';
include 'includes/auth.php';

if (!is_logged_in() || $_SESSION['role'] != 'client') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM bookings WHERE id = '$id' AND user_id = '$user_id' AND status != 'cancelled' AND data > DATE_ADD(CURDATE(), INTERVAL 2 DAY)";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $sql_update = "UPDATE bookings SET status = 'cancelled' WHERE id = '$id'";
        if ($conn->query($sql_update) === TRUE) {
            echo "Rezerwacja została anulowana.";
        } else {
            echo "Błąd: " . $sql_update . "<br>" . $conn->error;
        }
    } else {
        echo "Nie możesz anulować tej rezerwacji.";
    }
}

header('Location: panel_klienta.php');
exit;
?>