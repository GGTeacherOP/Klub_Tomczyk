<?php
include 'includes/header.php';
if (!is_logged_in() || $_SESSION['role'] != 'employee') {
    header('Location: login.php');
    exit;
}
$sql = "SELECT * FROM inventory";
$result = $conn->query($sql);
?>
<h2>Zarządzanie zapasami</h2>
<?php while($row = $result->fetch_assoc()): ?>
    <h3><?php echo $row['sala']; ?></h3>
    <form action="update_inventory.php" method="post">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <label for="drinki">Liczba drinków:</label>
        <input type="number" name="drinki" value="<?php echo $row['drinki']; ?>" required><br>
        <input type="submit" value="Aktualizuj">
    </form>
<?php endwhile; ?>
<?php include 'includes/footer.php'; ?>