<?php
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/config.php';

if ($_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sala = $_POST['sala'];
    $drinki = $_POST['drinki'];
    
    $stmt = $pdo->prepare("UPDATE inventory SET drinki = ? WHERE sala = ?");
    $stmt->execute([$drinki, $sala]);
}

$inventory = $pdo->query("SELECT * FROM inventory")->fetchAll();
?>

<div class="container">
    <div class="form-box">
        <h2>Panel pracownika</h2>
        
        <form method="post">
            <div class="form-group">
                <label>Sala:</label>
                <select name="sala" required>
                    <?php foreach ($inventory as $item): ?>
                        <option value="<?= $item['sala'] ?>"><?= $item['sala'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Ilość drinków:</label>
                <input type="number" name="drinki" required>
            </div>
            <button type="submit">Aktualizuj</button>
        </form>

        <h3>Aktualny stan</h3>
        <?php foreach ($inventory as $item): ?>
            <div class="inventory-item">
                <p><?= $item['sala'] ?>: <?= $item['drinki'] ?> drinków</p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';
?>