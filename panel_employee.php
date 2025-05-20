<?php
include 'includes/header.php';
require 'includes/config.php';

// Sprawdź uprawnienia
if ($_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

// Obsługa formularza aktualizacji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sala = $_POST['sala'];
    $drinki = $_POST['drinki'];
    
    $stmt = $pdo->prepare("UPDATE inventory SET drinki = ? WHERE sala = ?");
    $stmt->execute([$drinki, $sala]);
}

// Pobierz aktualny stan
$inventory = $pdo->query("SELECT * FROM inventory")->fetchAll();
?>

<div class="container">
    <h1>Panel pracownika</h1>
    
    <div class="form-box">
        <h2>Aktualizacja stanu drinków</h2>
        <form method="POST">
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
                <input type="number" name="drinki" min="0" required>
            </div>
            
            <button type="submit">Zapisz</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>