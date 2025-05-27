<?php
include 'includes/header.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
    <h2>Rezerwacja</h2>
    <form action="process_booking.php" method="post">
        <label for="sala">Wybierz salę:</label>
        <select name="sala" id="sala" required>
            <option value="Sala X">Sala X</option>
            <option value="Sala Y">Sala Y</option>
        </select>
        <label for="data">Data:</label>
        <input type="date" name="data" id="data" min="<?php echo date('Y-m-d'); ?>" required>
        <label>Dodatki:</label>
        <input type="checkbox" name="dodatki[]" value="DJ"> DJ
        <input type="checkbox" name="dodatki[]" value="Fotobudka"> Fotobudka
        <input type="checkbox" name="dodatki[]" value="Ochrona"> Ochrona
        <h3>Wybierz driniki</h3>
        <?php
        $stmt = $conn->prepare("SELECT id, name FROM drinks");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            echo "<label for='drink_" . $row['id'] . "'>" . htmlspecialchars($row['name']) . ":</label>";
            echo "<input type='number' name='drinks[" . $row['id'] . "]' id='drink_" . $row['id'] . "' min='0' value='0'>";
        }
        $stmt->close();
        ?>
        <input type="submit" value="Zarezerwuj">
    </form>
<?php include 'includes/footer.php'; ?>