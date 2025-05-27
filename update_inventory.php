<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sala = $_POST['sala'] ?? '';
    $drinks = $_POST['drinks'] ?? [];

    if (!in_array($sala, ['Sala X', 'Sala Y'])) {
        $_SESSION['error_message'] = 'Nieprawidłowa sala.';
        header('Location: panel_pracownika.php');
        exit;
    }

    $stmt = $conn->prepare("UPDATE inventory SET quantity = ?, ostatnia_aktualizacja = CURRENT_TIMESTAMP WHERE sala = ? AND drink_id = ?");
    foreach ($drinks as $drink_id => $quantity) {
        $quantity = (int)$quantity;
        $drink_id = (int)$drink_id;
        if ($quantity < 0) {
            $_SESSION['error_message'] = 'Ilość drinków nie może być negatywna.';
            header('Location: panel_pracownika.php');
            exit;
        }
        $stmt->bind_param('isi', $quantity, $sala, $drink_id);
        $stmt->execute();
    }
    $stmt->close();

    $_SESSION['success_message'] = 'Zapasy zostały zaktualizowane.';
}

header('Location: panel_pracownika.php');
exit;
?>