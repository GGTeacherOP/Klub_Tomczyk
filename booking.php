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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sala = $_POST['sala'];
    $data = $_POST['data'];
    $dodatki = isset($_POST['dodatki']) ? json_encode($_POST['dodatki']) : '[]';
    $user_id = $_SESSION['user_id'];

    $sql_check = "SELECT * FROM bookings WHERE sala = '$sala' AND data = '$data' AND status IN ('pending', 'confirmed')";
    $result_check = $conn->query($sql_check);
    if ($result_check->num_rows > 0) {
        echo "<p style='color: red;'>Sala jest już zarezerwowana na ten dzień.</p>";
        $sql_next = "SELECT MIN(data) AS next_date FROM bookings WHERE sala = '$sala' AND data > CURDATE() AND status IN ('pending', 'confirmed')";
        $result_next = $conn->query($sql_next);
        $next_date = $result_next->fetch_assoc()['next_date'];
        if ($next_date) {
            $suggested_date = date('Y-m-d', strtotime($next_date . ' +1 day'));
            echo "<p>Najbliższy wolny termin: $suggested_date</p>";
        } else {
            echo "<p>Brak zajętych terminów, możesz zarezerwować dowolny dzień.</p>";
        }
    } else {
        $sql = "INSERT INTO bookings (user_id, sala, data, dodatki, status) VALUES ('$user_id', '$sala', '$data', '$dodatki', 'pending')";
        if ($conn->query($sql) === TRUE) {
            echo "<p>Rezerwacja została zapisana. Przekierowanie za 3 sekundy...</p>";
            echo "<script>setTimeout(function(){ window.location.href = 'panel_klienta.php'; }, 3000);</script>";
        } else {
            echo "<p>Błąd: " . $sql . "<br>" . $conn->error . "</p>";
        }
    }
}
?>
    <h2>Rezerwacja sali</h2>
    <form method="post">
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