<?php
require 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $sala = $_POST['sala'];
    $data = $_POST['data'];
    $dodatki = json_encode($_POST['dodatki'] ?? []);

    try {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, sala, data, dodatki) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $sala, $data, $dodatki]);
        header("Location: panel_klienta.php?success=1");
    } catch (PDOException $e) {
        die("Błąd rezerwacji: " . $e->getMessage());
    }
}
?>