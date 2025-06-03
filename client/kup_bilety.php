<?php
require_once __DIR__ . '/../includes/header.php';
requireRole('klient');
requireApprovedUser();

$event_id_filter = isset($_GET['event_id']) ? intval($_GET['event_id']) : null;
?>

<h2>Kup Bilety na Wydarzenia</h2>

<?php
$today = date("Y-m-d H:i:s");
$sql_events = "SELECT event_id, name, description, date, ticket_price, total_tickets, tickets_sold, image_url FROM events WHERE date >= ? AND (total_tickets - tickets_sold) > 0 ";
if ($event_id_filter) {
    $sql_events .= " AND event_id = ? ";
}
$sql_events .= " ORDER BY date ASC";

$stmt = $conn->prepare($sql_events);

if ($event_id_filter) {
    $stmt->bind_param("si", $today, $event_id_filter);
} else {
    $stmt->bind_param("s", $today);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0): ?>
    <div class="events-grid tickets-purchase-grid">
    <?php while($event = $result->fetch_assoc()):
        $available_tickets = $event['total_tickets'] - $event['tickets_sold'];
    ?>
        <div class="event-card">
            <img src="/nightclub/<?php echo sanitize_output($event['image_url']); ?>" alt="<?php echo sanitize_output($event['name']); ?>">
            <h4><?php echo sanitize_output($event['name']); ?></h4>
            <p><strong>Data:</strong> <?php echo date("d.m.Y H:i", strtotime($event['date'])); ?></p>
            <p><strong>Cena za bilet:</strong> <?php echo number_format($event['ticket_price'], 2); ?> PLN</p>
            <p><strong>Dostępne bilety:</strong> <?php echo $available_tickets; ?></p>
            <p><?php echo nl2br(sanitize_output(substr($event['description'], 0, 150))); ?>...</p>

            <form action="/nightclub/actions/buy_tickets_action.php" method="POST" class="styled-form-inline">
                <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                <div>
                    <label for="quantity_<?php echo $event['event_id']; ?>">Ilość:</label>
                    <input type="number" id="quantity_<?php echo $event['event_id']; ?>" name="quantity" value="1" min="1" max="<?php echo $available_tickets; ?>" required>
                </div>
                <button type="submit" name="buy_tickets_submit" class="btn">Kup Teraz</button>
            </form>
        </div>
    <?php endwhile; ?>
    </div>
<?php else: ?>
    <p>Obecnie brak dostępnych biletów na nadchodzące wydarzenia<?php echo $event_id_filter ? ' o podanym ID' : ''; ?>.</p>
<?php endif;
$stmt->close();
?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>