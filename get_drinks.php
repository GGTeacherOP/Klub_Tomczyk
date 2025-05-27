<?php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sala = $_POST['sala'] ?? '';
    if (!in_array($sala, ['Sala X', 'Sala Y'])) {
        echo '<p>Nieprawidłowa sala.</p>';
        exit;
    }

    $stmt = $conn->prepare("SELECT d.id, d.name, i.quantity FROM drinks d LEFT JOIN inventory i ON d.id = i.drink_id AND i.sala = ?");
    $stmt->bind_param('s', $sala);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $quantity = $row['quantity'] ?? 0;
            echo '<label for="drink_' . $row['id'] . '">' . htmlspecialchars($row['name']) . ' (aktualnie: ' . $quantity . '):</label>';
            echo '<input type="number" name="drinks[' . $row['id'] . ']" id="drink_' . $row['id'] . '" min="0" value="' . $quantity . '" required><br>';
        }
    } else {
        echo '<p>Brak drinków w bazie danych.</p>';
    }
    $stmt->close();
}
?>