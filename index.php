<?php require_once __DIR__ . '/includes/header.php'; ?>

<section id="welcome">
    <h2>Witaj w NightClub!</h2>
    <p>Najlepsze imprezy, niezapomniane koncerty i wyjątkowa atmosfera. Przeżyj noc pełną wrażeń!</p>
    <?php if (!isLoggedIn()): ?>
        <p id="login-prompt" class="info-text">Zaloguj się, aby kupować bilety lub rezerwować salę.</p>
    <?php elseif (getUserRole() == 'klient' && !isUserApproved()): ?>
        <p class="info-text warning">Twoje konto oczekuje na zatwierdzenie przez pracownika. Niektóre funkcje mogą być ograniczone.</p>
    <?php endif; ?>
</section>

<section id="upcoming-events">
    <h3>Najbliższe Wydarzenia</h3>
    <div class="events-grid">
        <?php
        $today = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("SELECT event_id, name, date, ticket_price, image_url, total_tickets, tickets_sold FROM events WHERE date >= ? ORDER BY date ASC LIMIT 3");
        if (!$stmt) { echo "Błąd przygotowania zapytania (wydarzenia): " . $conn->error; }
        else {
            $stmt->bind_param("s", $today);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while ($event = $result->fetch_assoc()) {
                    $available_tickets = $event['total_tickets'] - $event['tickets_sold'];
                    echo "<div class='event-card'>";
                    echo "<img src='/nightclub/" . sanitize_output($event['image_url']) . "' alt='" . sanitize_output($event['name']) . "'>";
                    echo "<h4>" . sanitize_output($event['name']) . "</h4>";
                    echo "<p>Data: " . date("d.m.Y H:i", strtotime($event['date'])) . "</p>";
                    echo "<p>Cena biletu: " . sanitize_output($event['ticket_price']) . " PLN</p>";
                    echo "<p>Dostępne bilety: " . $available_tickets . "</p>";
                    if (isLoggedIn() && getUserRole() == 'klient' && isUserApproved() && $available_tickets > 0) {
                         echo "<a href='/nightclub/client/kup_bilety.php?event_id=" . $event['event_id'] . "' class='btn'>Kup Bilet</a>";
                    } elseif ($available_tickets <= 0) {
                        echo "<p class='info-text error'>Bilety wyprzedane!</p>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<p>Aktualnie brak nadchodzących wydarzeń.</p>";
            }
            $stmt->close();
        }
        ?>
    </div>
</section>

<section id="customer-reviews">
    <h3>Opinie Klientów</h3>
    <div class="reviews-list">
        <?php
        $stmt_reviews = $conn->prepare("SELECT r.comment, r.rating, u.first_name FROM reviews r JOIN users u ON r.user_id = u.user_id ORDER BY r.created_at DESC LIMIT 5");
        if (!$stmt_reviews) { echo "Błąd przygotowania zapytania (opinie): " . $conn->error; }
        else {
            $stmt_reviews->execute();
            $reviews_result = $stmt_reviews->get_result();
            if ($reviews_result->num_rows > 0) {
                while ($review = $reviews_result->fetch_assoc()) {
                    echo "<div class='review'>";
                    echo "<p><strong>" . sanitize_output($review['first_name']) . "</strong> (Ocena: " . str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) . ")</p>";
                    echo "<p>" . nl2br(sanitize_output($review['comment'])) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>Brak opinii. Bądź pierwszy!</p>";
            }
            $stmt_reviews->close();
        }
        ?>
    </div>
    <?php if (isLoggedIn() && getUserRole() == 'klient' && isUserApproved()): ?>
    <div class="add-review-form">
        <h4>Dodaj swoją opinię</h4>
        <form action="/nightclub/actions/add_review_action.php" method="POST" class="styled-form">
            <div>
                <label for="rating">Ocena (1-5):</label>
                <select name="rating" id="rating" required>
                    <option value="5">5 - Doskonale ★★★★★</option>
                    <option value="4">4 - Bardzo dobrze ★★★★☆</option>
                    <option value="3" selected>3 - Dobrze ★★★☆☆</option>
                    <option value="2">2 - Słabo ★★☆☆☆</option>
                    <option value="1">1 - Bardzo słabo ★☆☆☆☆</option>
                </select>
            </div>
            <div>
                <label for="comment">Komentarz:</label>
                <textarea name="comment" id="comment" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn">Dodaj Opinię</button>
        </form>
    </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>