<?php
include 'includes/config.php';
include 'includes/auth.php';
if (!is_logged_in() || $_SESSION['role'] != 'employee') {
    header('Location: login.php');
    exit;
}
$id = $_POST['id'];
$drinki = $_POST['drinki'];
$sql = "UPDATE inventory SET drinki = '$drinki', ostatnia_aktualizacja = CURRENT_TIMESTAMP WHERE id = '$id'";
if ($conn->query($sql) === TRUE) {
    echo "Zapas został zaktualizowany.";
} else {
    echo "Błąd: " . $sql . "<br>" . $conn->error;
}
?>