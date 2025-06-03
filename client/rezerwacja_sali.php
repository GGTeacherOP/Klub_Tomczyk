<?php
require_once __DIR__ . '/../includes/header.php';
requireRole('klient');
requireApprovedUser();

$selected_hall_id = isset($_GET['hall_id']) ? intval($_GET['hall_id']) : null;

// Pobranie dostępnych sal prywatnych
$halls_stmt = $conn->prepare("SELECT hall_id, name, base_price, capacity FROM halls WHERE name IN ('Sala Mała', 'Sala Duża') ORDER BY capacity ASC");
$halls_stmt->execute();
$halls_result = $halls_stmt->get_result();
$private_halls = [];
while($row = $halls_result->fetch_assoc()) {
    $private_halls[] = $row;
}
$halls_stmt->close();

// Pobranie dostępnych drinków
$drinks_stmt = $conn->prepare("SELECT drink_id, name, price_per_unit, quantity_available FROM drinks ORDER BY name ASC");
$drinks_stmt->execute();
$drinks_result = $drinks_stmt->get_result();
$available_drinks = [];
while($row = $drinks_result->fetch_assoc()) {
    $available_drinks[] = $row;
}
$drinks_stmt->close();

// Pobranie dostępnych dodatków
$extras_stmt = $conn->prepare("SELECT extra_id, name, price, description FROM extras ORDER BY name ASC");
$extras_stmt->execute();
$extras_result = $extras_stmt->get_result();
$available_extras = [];
while($row = $extras_result->fetch_assoc()) {
    $available_extras[] = $row;
}
$extras_stmt->close();
?>

<h2>Rezerwacja Sali Prywatnej</h2>
<p>Wybierz salę, termin oraz opcjonalne drinki i dodatki. Pamiętaj, że rezerwacja oczekuje na potwierdzenie przez pracownika.</p>

<form action="/nightclub/actions/create_reservation_action.php" method="POST" id="reservationForm" class="styled-form">
    <fieldset>
        <legend>1. Wybierz Salę</legend>
        <div>
            <label for="hall_id">Sala:</label>
            <select name="hall_id" id="hall_id" required>
                <option value="">-- Wybierz salę --</option>
                <?php foreach ($private_halls as $hall): ?>
                    <option value="<?php echo $hall['hall_id']; ?>" 
                            data-base-price="<?php echo $hall['base_price']; ?>" 
                            data-capacity="<?php echo $hall['capacity']; ?>"
                            <?php echo ($selected_hall_id == $hall['hall_id']) ? 'selected' : ''; ?>>
                        <?php echo sanitize_output($hall['name']) . " (Cena: " . $hall['base_price'] . " PLN, Poj.: " . $hall['capacity'] . " os.)"; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p id="hall-capacity-info" class="info-text"></p>
        </div>
    </fieldset>

    <fieldset>
        <legend>2. Termin Rezerwacji</legend>
        <div>
            <label for="reservation_date">Data:</label>
            <input type="date" id="reservation_date" name="reservation_date" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
        </div>
        <div class="time-inputs">
            <div>
                <label for="reservation_time_start">Godzina rozpoczęcia:</label>
                <input type="time" id="reservation_time_start" name="reservation_time_start" required step="1800"> </div>
            <div>
                <label for="reservation_time_end">Godzina zakończenia:</label>
                <input type="time" id="reservation_time_end" name="reservation_time_end" required step="1800">
            </div>
        </div>
    </fieldset>

    <fieldset>
        <legend>3. Zamów Drinki (Opcjonalnie)</legend>
        <div id="drinks-list">
            <?php if (!empty($available_drinks)): ?>
                <?php foreach ($available_drinks as $drink): ?>
                <div class="drink-item">
                    <label for="drink_<?php echo $drink['drink_id']; ?>">
                        <?php echo sanitize_output($drink['name']); ?> (<?php echo number_format($drink['price_per_unit'], 2); ?> PLN/szt)
                        <br><small>Dostępne: <span id="avail_<?php echo $drink['drink_id']; ?>_display"><?php echo $drink['quantity_available']; ?></span></small>
                    </label>
                    <input type="number" name="drinks[<?php echo $drink['drink_id']; ?>]" id="drink_<?php echo $drink['drink_id']; ?>"
                           min="0" max="<?php echo $drink['quantity_available']; ?>" value="0"
                           data-price="<?php echo $drink['price_per_unit']; ?>" class="drink-quantity-input">
                    <span class="drink-availability-warning" id="warning_<?php echo $drink['drink_id']; ?>"></span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Brak dostępnych drinków w ofercie.</p>
            <?php endif; ?>
        </div>
    </fieldset>

    <fieldset>
        <legend>4. Wybierz Dodatki (Opcjonalnie)</legend>
        <div id="extras-list">
             <?php if (!empty($available_extras)): ?>
                <?php foreach ($available_extras as $extra): ?>
                <div class="extra-item">
                    <input type="checkbox" name="extras[]" id="extra_<?php echo $extra['extra_id']; ?>"
                           value="<?php echo $extra['extra_id']; ?>" data-price="<?php echo $extra['price']; ?>" class="extra-checkbox-input">
                    <label for="extra_<?php echo $extra['extra_id']; ?>">
                        <?php echo sanitize_output($extra['name']); ?> (+ <?php echo number_format($extra['price'], 2); ?> PLN)
                        <?php if(!empty($extra['description'])): ?>
                            <br><small><?php echo sanitize_output($extra['description']); ?></small>
                        <?php endif; ?>
                    </label>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Brak dostępnych dodatków w ofercie.</p>
            <?php endif; ?>
        </div>
    </fieldset>

    <div id="summary" class="reservation-summary">
        <h3>Podsumowanie Wstępne</h3>
        <p>Cena za salę: <span id="price_hall_display">0.00</span> PLN</p>
        <p>Cena za drinki: <span id="price_drinks_display">0.00</span> PLN</p>
        <p>Cena za dodatki: <span id="price_extras_display">0.00</span> PLN</p>
        <p><strong>Całkowita wstępna cena: <span id="price_total_display">0.00</span> PLN</strong></p>
        <p class="info-text">Płatność na miejscu po imprezie, po potwierdzeniu rezerwacji przez pracownika.</p>
    </div>

    <button type="submit" class="btn btn-large">Złóż Wstępną Rezerwację</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>