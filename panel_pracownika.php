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
    <h2>Zarządzanie rezerwacjami</h2>
    <?php
    $sql_reservations = "SELECT * FROM bookings WHERE status = 'pending'";
    $result_reservations = $conn->query($sql_reservations);
    ?>
    <div class="table-container">
        <table>
            <tr>
                <th>ID</th>
                <th>Użytkownik</th>
                <th>Sala</th>
                <th>Data</th>
                <th>Dodatki</th>
                <th>Akcje</th>
            </tr>
            <?php while($row = $result_reservations->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['user_id']; ?></td>
                <td><?php echo $row['sala']; ?></td>
                <td><?php echo $row['data']; ?></td>
                <td><?php echo implode(', ', json_decode($row['dodatki'], true)); ?></td>
                <td>
                    <form action="manage_reservation_employee.php" method="post">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="submit" name="action" value="Potwierdź">
                        <input type="submit" name="action" value="Anuluj">
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
<?php include 'includes/footer.php'; ?>