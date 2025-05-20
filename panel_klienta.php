<?php
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/config.php';

if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<div class="container">
    <div class="form-box">
        <h2>Twoje rezerwacje</h2>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="success-msg">Rezerwacja została dodana!</div>
        <?php endif; ?>

        <?php if (empty($bookings)): ?>
            <div class="info-msg">Brak aktualnych rezerwacji</div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="reservation-card">
                    <h3><?= htmlspecialchars($booking['sala']) ?></h3>
                    <p>Data: <?= $booking['data'] ?></p>
                    <p>Status: <span class="status-<?= $booking['status'] ?>"><?= $booking['status'] ?></span></p>
                    <?php if (!empty($booking['dodatki'])): 
                        $dodatki = json_decode($booking['dodatki']); ?>
                        <p>Dodatki: <?= implode(', ', $dodatki) ?></p>
                    <?php endif; ?>
                    <?php if ($booking['status'] === 'pending'): ?>
                        <a href="anuluj.php?id=<?= $booking['id'] ?>" class="btn cancel-btn">Anuluj</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';
?>