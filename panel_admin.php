<?php
include 'includes/header.php';
require 'includes/config.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Pobierz dane
$users = $pdo->query("SELECT * FROM users")->fetchAll();
$inventory = $pdo->query("SELECT * FROM inventory")->fetchAll();
?>

<div class="container">
    <h1>Panel administratora</h1>
    
    <div class="admin-section">
        <h2>Użytkownicy</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Rola</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= $user['email'] ?></td>
                <td><?= $user['role'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="admin-section">
        <h2>Stan magazynu</h2>
        <table>
            <tr>
                <th>Sala</th>
                <th>Drinki</th>
                <th>Aktualizacja</th>
            </tr>
            <?php foreach ($inventory as $item): ?>
            <tr>
                <td><?= $item['sala'] ?></td>
                <td><?= $item['drinki'] ?></td>
                <td><?= $item['ostatnia_aktualizacja'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>