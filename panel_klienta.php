<?php
include 'includes/header.php';
if (!is_logged_in() || $_SESSION['role'] != 'client') {
    header('Location: login.php');
    exit;
}
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM bookings WHERE user_id = '$user_id'";
$result = $conn->query($sql);
?>
    <h2>Moje rezerwacje</h2>
    <div class="table-container">
        <table>
            <tr>
                <th>ID</th>
                <th>Sala</th>
                <th>Data</th>
                <th>Dodatki</th>
                <th>Status</th>
                <th>Akcje</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['sala']; ?></td>
                <td><?php echo $row['data']; ?></td>
                <td><?php echo implode(', ', json_decode($row['dodatki'], true)); ?></td>
                <td><?php echo $row['status']; ?></td>
                <td>
                    <?php if ($row['status'] != 'cancelled' && $row['data'] > date('Y-m-d', strtotime('+2 days'))): ?>
                        <form action="cancel_reservation_client.php" method="post">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Anuluj">
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
<?php include 'includes/footer.php'; ?>