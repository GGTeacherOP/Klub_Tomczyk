<?php
require __DIR__ . '/includes/config.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $booking_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        header("Location: panel_klienta.php?cancel=1");
        exit();
    } catch (PDOException $e) {
        die("Błąd podczas anulowania: " . $e->getMessage());
    }
} else {
    header("Location: login.php");
    exit();
}
?>