<?php include 'includes/header.php'; ?>
    <h2>Witamy w Bania u Cygana</h2>
    <p>Oferujemy wynajem sal na imprezy urodzinowe z możliwością wyboru dodatków takich jak DJ, fotobudka, ochrona i wiele innych.</p>
    <?php if (isLoggedIn()): ?>
        <p>Witaj, <?php echo htmlspecialchars($_SESSION['email']); ?>! Możesz teraz dokonać rezerwacji lub przejść do swojego panelu.</p>
    <?php else: ?>
        <p>Zaloguj się, aby dokonać rezerwacji.</p>
    <?php endif; ?>
    <h2>Najbliższe wydarzenia</h2>
    <div class="events">
        <?php
        $stmt = $conn->prepare("SELECT sala, data FROM bookings WHERE status = 'confirmed' AND data BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY data");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<p><strong>" . htmlspecialchars($row['sala']) . "</strong>: " . htmlspecialchars($row['data']) . "</p>";
            }
        } else {
            echo "<p>Brak nadchodzących wydarzeń w ciągu najbliższych 7 dni.</p>";
        }
        $stmt->close();
        ?>
    </div>
    <h2>Opinie</h2>
    <div class="opinions-slider">
        <?php
        $stmt = $conn->prepare("SELECT o.opinion, u.email, o.created_at FROM opinions o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='opinion' style='display:none;'><p><strong>" . htmlspecialchars($row['email']) . "</strong> (" . htmlspecialchars($row['created_at']) . "): " . htmlspecialchars($row['opinion']) . "</p></div>";
            }
        } else {
            echo "<div class='opinion' style='display:none;'><p>Brak opinii do wyświetlenia.</p></div>";
        }
        $stmt->close();
        ?>
    </div>
<?php include 'includes/footer.php'; ?>