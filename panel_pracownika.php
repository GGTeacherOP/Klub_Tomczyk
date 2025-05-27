<?php
include 'includes/header.php';
if (!isLoggedIn() || $_SESSION['role'] !== 'employee') {
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
    <h2>Panel Pracownika</h2>
    <h3>Zarządzanie zapasami</h3>
    <form action="update_inventory.php" method="post" id="inventory_form">
        <label for="sala">Wybierz salę:</label>
        <select name="sala" id="sala" required>
            <option value="">Wybierz salę</option>
            <option value="Sala X">Sala X</option>
            <option value="Sala Y">Sala Y</option>
        </select>
        <div id="drinks_container" style="display:none;"></div>
        <input type="submit" value="Aktualizuj" id="submit_inventory" style="display:none;">
    </form>
    <h3>Stan zapasów</h3>
    <div class="table-container">
        <table>
            <tr><th>Sala</th><th>Drink</th><th>Ilość</th><th>Ostatnia aktualizacja</th></tr>
            <?php
            $stmt = $conn->prepare("SELECT i.sala, d.name, i.quantity, i.ostatnia_aktualizacja FROM inventory i JOIN drinks d ON i.drink_id = d.id ORDER BY i.sala, d.name");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['sala']) . "</td>";
                echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['quantity']) . "</td>";
                echo "<td>" . htmlspecialchars($row['ostatnia_aktualizacja']) . "</td>";
                echo "</tr>";
            }
            $stmt->close();
            ?>
        </table>
    </div>
    <h3>Rezerwacje do zatwierdzenia</h3>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Użytkownik</th><th>Sala</th><th>Data</th><th>Dodatki</th><th>Akcje</th></tr>
            <?php
            $stmt = $conn->prepare("SELECT b.*, u.email FROM bookings b JOIN users u ON b.user_id = u.id WHERE b.status = 'pending'");
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['sala']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['data']) . "</td>";
                    echo "<td>" . htmlspecialchars(implode(', ', json_decode($row['dodatki'], true))) . "</td>";
                    echo "<td>";
                    echo '<form action="accept_reservation.php" method="post" style="display:inline;">';
                    echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
                    echo '<input type="submit" value="Potwierdź">';
                    echo '</form>';
                    echo '<form action="cancel_reservation_employee.php" method="post" style="display:inline;">';
                    echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
                    echo '<input type="submit" value="Anuluj">';
                    echo '</form>';
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>Brak rezerwacji do zatwierdzenia.</td></tr>";
            }
            $stmt->close();
            ?>
        </table>
    </div>
<?php include 'includes/footer.php'; ?>