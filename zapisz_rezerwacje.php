<?php
require __DIR__ . '/includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $sala = $_POST['sala'];
    $data = $_POST['data'];
    $dodatki = isset($_POST['dodatki']) ? json_encode($_POST['dodatki']) : '[]';

    try {
        $stmt = $pdo->prepare("INSERT INTO bookings (user_id, sala, data, dodatki) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $sala, $data, $dodatki]);
        header("Location: panel_klienta.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Błąd podczas rezerwacji: " . $e->getMessage());
    }
} else {
    header("Location: booking.php");
    exit();
}
?>