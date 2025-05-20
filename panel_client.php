<?php
include 'includes/header.php';
require 'includes/config.php';

// Poprawiona linia: dodano brakujący nawias ")"
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pobierz rezerwacje użytkownika
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<div class="container">
    <div class="form-box">
        <h2>Twoje rezerwacje</h2>
        
        <?php foreach ($bookings as $booking): ?>
        <div class="offer-card" style="margin: 1rem 0; padding: 1rem;">
            <h3><?= htmlspecialchars($booking['sala']) ?></h3>
            <p>Data: <?= $booking['data'] ?></p>
            <p>Status: <?= $booking['status'] ?></p>
            <?php if ($booking['status'] == 'pending'): ?>
                <a href="anuluj.php?id=<?= $booking['id'] ?>" class="btn">Anuluj</a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>