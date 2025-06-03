<?php
require_once __DIR__ . '/../includes/header.php';
requireRole('klient');
// requireApprovedUser(); // Choć panel jest dostępny, niektóre akcje mogą tego wymagać

$user_id = getUserId();
$current_view = isset($_GET['view']) ? $_GET['view'] : 'info'; // Domyślny widok
?>

<h2>Panel Klienta</h2>
<div class="panel-container">
    <aside class="panel-sidebar">
        <ul>
            <li><a href="?view=info" class="<?php echo $current_view == 'info' ? 'active' : ''; ?>">Moje Dane</a></li>
            <li><a href="?view=reservations" class="<?php echo $current_view == 'reservations' ? 'active' : ''; ?>">Moje Rezerwacje</a></li>
            <li><a href="?view=tickets" class="<?php echo $current_view == 'tickets' ? 'active' : ''; ?>">Moje Bilety</a></li>
        </ul>
    </aside>
    <section class="panel-content">
        <?php if ($current_view == 'info'): ?>
            <h3>Informacje o Koncie</h3>
            <?php
            $stmt_user = $conn->prepare("SELECT first_name, last_name, email, created_at, is_approved FROM users WHERE user_id = ?");
            $stmt_user->bind_param("i", $user_id);
            $stmt_user->execute();
            $user_info = $stmt_user->get_result()->fetch_assoc();
            $stmt_user->close();
            ?>
            <?php if ($user_info): ?>
                <p><strong>Imię:</strong> <?php echo sanitize_output($user_info['first_name']); ?></p>
                <p><strong>Nazwisko:</strong> <?php echo sanitize_output($user_info['last_name']); ?></p>
                <p><strong>Email:</strong> <?php echo sanitize_output($user_info['email']); ?></p>
                <p><strong>Data rejestracji:</strong> <?php echo date("d.m.Y H:i", strtotime($user_info['created_at'])); ?></p>
                <p><strong>Status konta:</strong> <?php echo $user_info['is_approved'] ? '<span class="status-approved">Zatwierdzone</span>' : '<span class="status-pending">Oczekuje na zatwierdzenie</span>'; ?></p>
                <?php if (!$user_info['is_approved']): ?>
                    <p class="info-text warning">Niektóre funkcje, jak rezerwacja sali czy zakup biletów, będą dostępne po zatwierdzeniu konta przez pracownika.</p>
                <?php endif; ?>
            <?php else: ?>
                <p class="error">Nie udało się załadować informacji o użytkowniku.</p>
            <?php endif; ?>

        <?php elseif ($current_view == 'reservations'): ?>
            <h3>Moje Rezerwacje</h3>
            <?php
            if (!isUserApproved($conn)) {
                echo "<p class='info-text warning'>Twoje konto musi zostać zatwierdzone, aby zarządzać rezerwacjami. Możesz je przeglądać po zatwierdzeniu.</p>";
            } else {
                $stmt_reservations = $conn->prepare(
                    "SELECT r.reservation_id, h.name AS hall_name, r.reservation_date, r.reservation_time_start, r.reservation_time_end, r.total_price, r.status 
                     FROM reservations r 
                     JOIN halls h ON r.hall_id = h.hall_id 
                     WHERE r.user_id = ? ORDER BY r.reservation_date DESC, r.reservation_time_start DESC"
                );
                $stmt_reservations->bind_param("i", $user_id);
                $stmt_reservations->execute();
                $reservations = $stmt_reservations->get_result();

                if ($reservations->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Sala</th>
                                <th>Data</th>
                                <th>Godziny</th>
                                <th>Cena (PLN)</th>
                                <th>Status</th>
                                <th>Akcje</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($res = $reservations->fetch_assoc()):
                            $can_cancel = false;
                            if (($res['status'] == 'oczekujaca' || $res['status'] == 'potwierdzona')) {
                                $reservation_datetime_str = $res['reservation_date'] . ' ' . $res['reservation_time_start'];
                                $cancel_deadline = strtotime($reservation_datetime_str) - (24 * 3600); // 24h przed
                                if (time() < $cancel_deadline) {
                                    $can_cancel = true;
                                }
                            }
                        ?>
                            <tr>
                                <td><?php echo $res['reservation_id']; ?></td>
                                <td><?php echo sanitize_output($res['hall_name']); ?></td>
                                <td><?php echo date("d.m.Y", strtotime($res['reservation_date'])); ?></td>
                                <td><?php echo date("H:i", strtotime($res['reservation_time_start'])) . " - " . date("H:i", strtotime($res['reservation_time_end'])); ?></td>
                                <td><?php echo number_format($res['total_price'], 2); ?></td>
                                <td><span class="status-<?php echo sanitize_output($res['status']); ?>"><?php echo sanitize_output(ucfirst(str_replace('_', ' ', $res['status']))); ?></span></td>
                                <td>
                                    <?php if ($can_cancel): ?>
                                    <form action="/nightclub/actions/cancel_reservation_action.php" method="POST" style="display:inline;" class="cancel-form">
                                        <input type="hidden" name="cancel_reservation_id" value="<?php echo $res['reservation_id']; ?>">
                                        <button type="submit" class="btn btn-danger btn-sm cancel-action">Anuluj</button>
                                    </form>
                                    <?php elseif ($res['status'] == 'oczekujaca' || $res['status'] == 'potwierdzona'): ?>
                                        Minął termin anulowania
                                    <?php else: echo "-"; endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nie masz jeszcze żadnych rezerwacji.</p>
                <?php endif;
                $stmt_reservations->close();
            } // koniec else isUserApproved
            ?>

        <?php elseif ($current_view == 'tickets'): ?>
            <h3>Moje Zakupione Bilety</h3>
            <?php
             if (!isUserApproved($conn)) {
                echo "<p class='info-text warning'>Twoje konto musi zostać zatwierdzone, aby zarządzać biletami. Możesz je przeglądać po zatwierdzeniu.</p>";
            } else {
                $stmt_tickets = $conn->prepare(
                    "SELECT t.ticket_id, e.name AS event_name, e.date AS event_date, t.quantity, t.total_price, t.purchase_date 
                     FROM tickets t
                     JOIN events e ON t.event_id = e.event_id
                     WHERE t.user_id = ? ORDER BY t.purchase_date DESC"
                );
                $stmt_tickets->bind_param("i", $user_id);
                $stmt_tickets->execute();
                $tickets = $stmt_tickets->get_result();

                if ($tickets->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID Biletu</th>
                                <th>Wydarzenie</th>
                                <th>Data Wydarzenia</th>
                                <th>Ilość</th>
                                <th>Cena Całk. (PLN)</th>
                                <th>Data Zakupu</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while($ticket = $tickets->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $ticket['ticket_id']; ?></td>
                                <td><?php echo sanitize_output($ticket['event_name']); ?></td>
                                <td><?php echo date("d.m.Y H:i", strtotime($ticket['event_date'])); ?></td>
                                <td><?php echo $ticket['quantity']; ?></td>
                                <td><?php echo number_format($ticket['total_price'], 2); ?></td>
                                <td><?php echo date("d.m.Y H:i", strtotime($ticket['purchase_date'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>Nie zakupiłeś jeszcze żadnych biletów.</p>
                <?php endif;
                $stmt_tickets->close();
            } // koniec else isUserApproved
            ?>
        <?php else: ?>
            <p>Wybierz opcję z menu po lewej stronie.</p>
        <?php endif; ?>
    </section>
</div>


<?php require_once __DIR__ . '/../includes/footer.php'; ?>