<?php
include 'includes/header.php';
require 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<div class="container">
    <div class="form-container">
        <h2>Rezerwacja sali</h2>
        <form action="zapisz_rezerwacje.php" method="post">
            <div class="form-group">
                <label>Wybierz salę:</label>
                <select name="sala" required>
                    <option value="Sala X">Sala X</option>
                    <option value="Sala Y">Sala Y</option>
                </select>
            </div>
            <div class="form-group">
                <label>Data:</label>
                <input type="date" name="data" required>
            </div>
            <div class="form-group">
                <label>Dodatki:</label>
                <label><input type="checkbox" name="dodatki[]" value="DJ"> DJ (+500 zł)</label>
                <label><input type="checkbox" name="dodatki[]" value="Fotobudka"> Fotobudka (+800 zł)</label>
            </div>
            <button type="submit">Zarezerwuj</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>