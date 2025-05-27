<?php
include 'includes/header.php';
if (!isLoggedIn() || $_SESSION['role'] !== 'client') {
    header('Location: login.php');
    exit;
}

if (isset($_SESSION['success_message'])) {
    echo '<div class="success-message">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="error-message">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
?>
    <h2>Panel Klienta</h2>
    <h3>Twoje rezerwacje</h3>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Sala</th><th>Data</th><th>Dodatki</th><th>Drinki</th><th>Status</th><th>Akcje</th></tr>
            <?php
            $user_id = $_SESSION['user_id'];
            $stmt = $conn->prepare("SELECT b.*, GROUP_CONCAT(CONCAT(d.name, ': ', bd.quantity)) AS drinks FROM bookings b 
                LEFT JOIN booking_drinks bd ON b.id = bd.booking_id 
                LEFT JOIN drinks d ON bd.drink_id = d.id 
                WHERE b.user_id = ? 
                GROUP BY b.id");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['sala']) . "</td>";
                echo "<td>" . htmlspecialchars($row['data']) . "</td>";
                echo "<td>" . htmlspecialchars(implode(', ', json_decode($row['dodatki'], true))) . "</td>";
                echo "<td>" . htmlspecialchars($row['drinks'] ?? '-') . "</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "<td>";
                if ($row['status'] === 'pending' && strtotime($row['data']) >= strtotime('+2 days')) {
                    echo '<form action="cancel_reservation_client.php" method="post" style="display:inline;">';
                    echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
                    echo '<input type="submit" value="Anuluj">';
                    echo '</form>';
                }
                echo "</td>";
                echo "</tr>";
            }
            $stmt->close();
            ?>
        </table>
    </div>
    <h3>Dodaj opinię</h3>
    <form action="add_opinion.php" method="post">
        <textarea name="message" rows="5" required placeholder="Wpisz swoją opinię"></textarea>
        <input type="submit" value="Wyślij opinię">
    </form>
<?php include 'includes/footer.php'; ?>