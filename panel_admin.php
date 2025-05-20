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
    <div class="table-container">
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
    </div>
    <h2>Zarządzanie użytkownikami</h2>
    <?php
    $sql_users = "SELECT * FROM users WHERE role != 'admin'";
    $result_users = $conn->query($sql_users);
    ?>
    <div class="table-container">
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
    </div>
    <h2>Zamień na pracownika</h2>
    <form action="change_role_to_employee.php" method="post">
        <label for="email">Email klienta:</label>
        <input type="email" name="email" required>
        <input type="submit" value="Zamień">
    </form>
    <h2>Nowe konta do zatwierdzenia</h2>
    <?php
    $sql_pending = "SELECT * FROM pending_users";
    $result_pending = $conn->query($sql_pending);
    ?>
    <div class="table-container">
        <table>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Akcje</th>
            </tr>
            <?php while($row = $result_pending->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td>
                    <form action="approve_user.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="submit" value="Zatwierdź">
                    </form>
                    <form action="reject_user.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="submit" value="Odrzuć">
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
<?php include 'includes/footer.php'; ?>