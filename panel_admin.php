<?php
include 'includes/header.php';
if (!is_logged_in() || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}
$sql = "SELECT b.*, u.email FROM bookings b JOIN users u ON b.user_id = u.id";
$result = $conn->query($sql);
?>
<h2>Wszystkie rezerwacje</h2>
<table>
    <tr>
        <th>ID</th>
        <th>Użytkownik</th>
        <th>Sala</th>
        <th>Data</th>
        <th>Dodatki</th>
        <th>Status</th>
    </tr>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo $row['sala']; ?></td>
        <td><?php echo $row['data']; ?></td>
        <td><?php echo implode(', ', json_decode($row['dodatki'], true)); ?></td>
        <td><?php echo $row['status']; ?></td>
    </tr>
    <?php endwhile; ?>
</table>

<h2>Zarządzanie użytkownikami</h2>
<?php
$sql_users = "SELECT * FROM users WHERE role != 'admin'";
$result_users = $conn->query($sql_users);
?>
<table>
    <tr>
        <th>ID</th>
        <th>Email</th>
        <th>Rola</th>
        <th>Akcje</th>
    </tr>
    <?php while($row = $result_users->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo $row['role']; ?></td>
        <td>
            <form action="delete_user.php" method="post">
                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                <input type="submit" value="<?php echo $row['role'] == 'employee' ? 'Zwolnij' : 'Usuń konto'; ?>">
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php include 'includes/footer.php'; ?>