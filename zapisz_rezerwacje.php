<?php
include 'includes/config.php';
include 'includes/auth.php';
if (!is_logged_in() || $_SESSION['role'] != 'client') {
    header('Location: login.php');
    exit;
}
$sala = $_POST['sala'];
$data = $_POST['data'];
$dodatki = isset($_POST['dodatki']) ? json_encode($_POST['dodatki']) : '[]';
$user_id = $_SESSION['user_id'];

$sql_check = "SELECT * FROM bookings WHERE sala = '$sala' AND data = '$data' AND status IN ('pending', 'confirmed')";
$result_check = $conn->query($sql_check);
if ($result_check->num_rows > 0) {
    echo "Sala jest już zarezerwowana na ten dzień.";
} else {
    $sql = "INSERT INTO bookings (user_id, sala, data, dodatki, status) VALUES ('$user_id', '$sala', '$data', '$dodatki', 'pending')";
    if ($conn->query($sql) === TRUE) {
        echo "Rezerwacja została zapisana. Przekierowanie za 3 sekundy...";
        echo "<script>setTimeout(function(){ window.location.href = 'panel_klienta.php'; }, 3000);</script>";
    } else {
        echo "Błąd: " . $sql . "<br>" . $conn->error;
    }
}
?>