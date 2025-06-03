<?php
require_once __DIR__ . '/../includes/header.php';
requireRole('klient');
requireApprovedUser();

if (!isset($_SESSION['ticket_order'])) {
    $_SESSION['message'] = "Brak danych zamówienia do przetworzenia.";
    $_SESSION['message_type'] = "error";
    header("Location: /nightclub/client/kup_bilety.php");
    exit();
}

$order = $_SESSION['ticket_order'];
?>

<h2>Potwierdzenie Zakupu i Symulacja Płatności</h2>

<div class="payment-summary styled-form">
    <h3>Podsumowanie Zamówienia Biletów</h3>
    <p><strong>Wydarzenie:</strong> <?php echo sanitize_output($order['event_name']); ?></p>
    <p><strong>Ilość biletów:</strong> <?php echo $order['quantity']; ?></p>
    <p><strong>Cena za bilet:</strong> <?php echo number_format($order['ticket_price'], 2); ?> PLN</p>
    <p><strong>Łączna kwota do zapłaty:</strong> <?php echo number_format($order['total_price'], 2); ?> PLN</p>

    <hr>
    <h4>Symulacja Płatności</h4>
    <p>To jest jedynie wizualna strona symulacji płatności dla projektu szkolnego. W rzeczywistej aplikacji tutaj znajdowałby się formularz płatności lub integracja z bramką płatniczą.</p>
    
    <form action="/nightclub/actions/process_payment_action.php" method="POST">
        <p>Proszę kliknąć "Zapłać", aby sfinalizować zakup.</p>
        <button type="submit" name="finalize_payment" class="btn btn-large">Zapłać i Kup Bilety</button>
    </form>
    <a href="/nightclub/client/kup_bilety.php?event_id=<?php echo $order['event_id']; ?>" class="btn btn-secondary" style="margin-top: 10px;">Anuluj i wróć</a>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>