<?php require_once __DIR__ . '/includes/header.php'; ?>

<h2>Nasza Oferta</h2>

<section class="hall-offer">
    <h3>Sale Prywatne do Wynajęcia</h3>
    <div class="halls-grid">
    <?php
    $halls_stmt = $conn->prepare("SELECT hall_id, name, description, capacity, base_price FROM halls WHERE name IN ('Sala Mała', 'Sala Duża') ORDER BY capacity ASC");
    if (!$halls_stmt) { echo "Błąd przygotowania zapytania: " . $conn->error; }
    else {
        $halls_stmt->execute();
        $halls_result = $halls_stmt->get_result();
        if ($halls_result->num_rows > 0) {
            while($hall = $halls_result->fetch_assoc()){
                $image_path = "/nightclub/images/placeholder_hall_" . (strtolower(str_replace(' ', '_', $hall['name']))) . ".png";
                echo "<div class='hall-description-card'>";
                echo "<img src='" . $image_path . "' alt='" . sanitize_output($hall['name']) . "'>";
                echo "<h4>" . sanitize_output($hall['name']) . "</h4>";
                echo "<p>" . nl2br(sanitize_output($hall['description'])) . "</p>";
                echo "<p><strong>Pojemność:</strong> do " . sanitize_output($hall['capacity']) . " osób</p>";
                echo "<p><strong>Cena bazowa za wynajem:</strong> " . sanitize_output($hall['base_price']) . " PLN</p>";
                if (isLoggedIn() && getUserRole() == 'klient' && isUserApproved()) {
                     echo "<a href='/nightclub/client/rezerwacja_sali.php?hall_id=" . $hall['hall_id'] . "' class='btn'>Zarezerwuj Tę Salę</a>";
                }
                echo "</div>";
            }
        } else {
            echo "<p>Brak dostępnych sal prywatnych w ofercie.</p>";
        }
        $halls_stmt->close();
    }
    ?>
    </div>
</section>

<section class="concert-hall-offer">
    <h3>Sala Koncertowa</h3>
    <?php
    $concert_hall_stmt = $conn->prepare("SELECT name, description, capacity FROM halls WHERE name = 'Sala Koncertowa'");
    if (!$concert_hall_stmt) { echo "Błąd przygotowania zapytania: " . $conn->error; }
    else {
        $concert_hall_stmt->execute();
        $concert_hall_result = $concert_hall_stmt->get_result();
        $concert_hall = $concert_hall_result->fetch_assoc();
        if ($concert_hall) {
            echo "<div class='hall-description-card concert-hall'>";
            echo "<img src='/nightclub/images/placeholder_hall_concert.png' alt='" . sanitize_output($concert_hall['name']) . "'>";
            echo "<h4>" . sanitize_output($concert_hall['name']) . "</h4>";
            echo "<p>" . nl2br(sanitize_output($concert_hall['description'])) . "</p>";
            echo "<p><strong>Pojemność:</strong> do " . sanitize_output($concert_hall['capacity']) . " osób</p>";
            echo "<p>Tutaj odbywają się nasze najlepsze koncerty i wydarzenia specjalne! Sprawdź <a href='/nightclub/index.php#upcoming-events'>nadchodzące wydarzenia</a>.</p>";
            echo "</div>";
        }
        $concert_hall_stmt->close();
    }
    ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>