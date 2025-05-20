<?php
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<div class="container">
    <div class="form-box">
        <h2>Zarezerwuj salę</h2>
        <form action="zapisz_rezerwacje.php" method="post">
            <div class="form-group">
                <label>Wybierz salę:</label>
                <select name="sala" required>
                    <option value="Sala X">Sala X (do 50 osób)</option>
                    <option value="Sala Y">Sala Y (do 100 osób)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Data rezerwacji:</label>
                <input type="date" name="data" required>
            </div>
            
            <div class="form-group">
                <label>Dodatki:</label>
                <div class="checkboxes">
                    <label><input type="checkbox" name="dodatki[]" value="DJ"> DJ (+500 zł)</label>
                    <label><input type="checkbox" name="dodatki[]" value="Fotobudka"> Fotobudka (+800 zł)</label>
                    <label><input type="checkbox" name="dodatki[]" value="Ochrona"> Ochrona (+200 zł/h)</label>
                </div>
            </div>
            
            <button type="submit" class="btn">Zarezerwuj</button>
        </form>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';
?>