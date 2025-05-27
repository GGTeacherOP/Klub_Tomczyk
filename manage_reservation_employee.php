<?php
include 'includes/header.php';
if (!isLoggedIn() || $_SESSION['role'] !== 'employee') {
    header('Location: login.php');
    exit;
}
?>
    <h2>Zarządzanie rezerwacjami</h2>
    <p>Ten plik jest placeholderem dla zarządzania rezerwacjami przez pracownika.</p>
<?php include 'includes/footer.php'; ?>