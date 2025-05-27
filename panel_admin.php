<?php
include 'includes/header.php';
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
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
    <h2>Panel Admina</h2>
    <h3>Oczekujący użytkownicy</h3>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Email</th><th>Akcje</th></tr>
            <?php
            $stmt = $conn->prepare("SELECT id, email FROM pending_users");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>";
                echo '<form action="approve_user.php" method="post" style="display:inline;">';
                echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
                echo '<input type="submit" value="Zatwierdź">';
                echo '</form>';
                echo '<form action="reject_user.php" method="post" style="display:inline;">';
                echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
                echo '<input type="submit" value="Odrzuć">';
                echo '</form>';
                echo "</td>";
                echo "</tr>";
            }
            $stmt->close();
            ?>
        </table>
    </div>
    <h3>Potwierdzeni użytkownicy</h3>
    <div class="table-container">
        <table>
            <tr><th>ID</th><th>Email</th><th>Rola</th><th>Akcje</th></tr>
            <?php
            $stmt = $conn->prepare("SELECT id, email, role FROM users WHERE status = 'confirmed'");
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                echo "<td>";
                if ($row['role'] === 'client') {
                    echo '<form action="change_role_to_employee.php" method="post" style="display:inline;">';
                    echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
                    echo '<input type="submit" value="Awansuj na pracownika">';
                    echo '</form>';
                }
                echo '<form action="delete_user.php" method="post" style="display:inline;">';
                echo '<input type="hidden" name="id" value="' . htmlspecialchars($row['id']) . '">';
                echo '<input type="submit" value="Usuń">';
                echo '</form>';
                echo "</td>";
                echo "</tr>";
            }
            $stmt->close();
            ?>
        </table>
    </div>
<?php include 'includes/footer.php'; ?>