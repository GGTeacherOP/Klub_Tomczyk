<?php
include 'includes/header.php';
if (!is_logged_in() || $_SESSION['role'] != 'client') {
    header('Location: login.php');
    exit;
}
$sql = "SELECT DISTINCT sala FROM inventory";
$result = $conn->query($sql);
$sale = [];
while ($row = $result->fetch_assoc()) {
    $sale[] = $row['sala'];
}
$dostepne_dodatki = ['DJ', 'Fotobudka', 'Ochrona'];
?>
<h2>Rezerwacja sali</h2>
<form action="zapisz_rezerwacje.php" method="post">
    <label for="sala">Wybierz salę:</label>
    <select name="sala" id="sala">
        <?php foreach ($sale as $sala): ?>
            <option value="<?php echo $sala; ?>"><?php echo $sala; ?></option>
        <?php endforeach; ?>
    </select><br>
    <label for="data">Data:</label>
    <input type="date" name="data" id="data" min="<?php echo date('Y-m-d'); ?>" required><br>
    <label>Dodatki:</label><br>
    <?php foreach ($dostepne_dodatki as $dodatek): ?>
        <input type="checkbox" name="dodatki[]" value="<?php echo $dodatek; ?>"> <?php echo $dodatek; ?><br>
    <?php endforeach; ?>
    <input type="submit" value="Zarezerwuj">
</form>
<?php include 'includes/footer.php'; ?>