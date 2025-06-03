<?php
require_once __DIR__ . '/../includes/header.php';
requireRole('wlasciciel');

$current_view = isset($_GET['view']) ? $_GET['view'] : 'dashboard'; // Domyślny widok
?>

<h2>Panel Właściciela</h2>
<div class="panel-container">
    <aside class="panel-sidebar">
        <ul>
            <li><a href="?view=dashboard" class="<?php echo $current_view == 'dashboard' ? 'active' : ''; ?>">Pulpit</a></li>
            <li><a href="?view=ticket_sales" class="<?php echo $current_view == 'ticket_sales' ? 'active' : ''; ?>">Sprzedaż Biletów</a></li>
            <li><a href="?view=reservations_overview" class="<?php echo $current_view == 'reservations_overview' ? 'active' : ''; ?>">Przegląd Rezerwacji</a></li>
            <li><a href="?view=users_management" class="<?php echo $current_view == 'users_management' ? 'active' : ''; ?>">Zarządzanie Użytkownikami</a></li>
             <li><a href="?view=reports" class="<?php echo $current_view == 'reports' ? 'active' : ''; ?>">Raporty Finansowe</a></li>
        </ul>
    </aside>
    <section class="panel-content">
        <?php if ($current_view == 'dashboard'): ?>
            <h3>Pulpit Właściciela</h3>
            <p>Witaj w panelu właściciela, <?php echo sanitize_output($_SESSION['email']); ?>!</p>
            <p>Tutaj znajdziesz kluczowe informacje i narzędzia do zarządzania klubem.</p>
            <?php
                // Szybkie statystyki
                $today = date("Y-m-d");
                $stmt_upcoming_events_count = $conn->query("SELECT COUNT(*) as count FROM events WHERE date >= CURDATE()");
                $upcoming_events_count = $stmt_upcoming_events_count->fetch_assoc()['count'];

                $stmt_active_reservations_count = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'potwierdzona' AND reservation_date >= CURDATE()");
                $active_reservations_count = $stmt_active_reservations_count->fetch_assoc()['count'];
                
                $stmt_total_users_count = $conn->query("SELECT COUNT(*) as count FROM users");
                $total_users_count = $stmt_total_users_count->fetch_assoc()['count'];
            ?>
             <div class="dashboard-summaries">
                <div class="summary-card">
                    <h4>Nadchodzące Wydarzenia</h4>
                    <p class="count"><?php echo $upcoming_events_count; ?></p>
                    <?php if ($upcoming_events_count > 0) echo '<a href="?view=ticket_sales" class="btn btn-sm">Zobacz</a>'; ?>
                </div>
                <div class="summary-card">
                    <h4>Aktywne Rezerwacje</h4>
                    <p class="count"><?php echo $active_reservations_count; ?></p>
                     <?php if ($active_reservations_count > 0) echo '<a href="?view=reservations_overview" class="btn btn-sm">Zobacz</a>'; ?>
                </div>
                 <div class="summary-card">
                    <h4>Liczba Użytkowników</h4>
                    <p class="count"><?php echo $total_users_count; ?></p>
                    <a href="?view=users_management" class="btn btn-sm">Zarządzaj</a>
                </div>
            </div>


        <?php elseif ($current_view == 'ticket_sales'): ?>
            <h3>Sprzedaż Biletów na Nadchodzące Koncerty</h3>
            <?php
            $stmt_events = $conn->prepare(
                "SELECT event_id, name, date, ticket_price, tickets_sold, total_tickets 
                 FROM events 
                 WHERE date >= CURDATE() 
                 ORDER BY date ASC"
            );
            $stmt_events->execute();
            $events_sales = $stmt_events->get_result();

            if ($events_sales->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nazwa Wydarzenia</th>
                            <th>Data</th>
                            <th>Cena Biletu (PLN)</th>
                            <th>Sprzedane Bilety</th>
                            <th>Pula Biletów</th>
                            <th>Przychód (PLN)</th>
                            <th>Zapełnienie (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $total_revenue_all_events = 0;
                    while($event = $events_sales->fetch_assoc()): 
                        $revenue = $event['tickets_sold'] * $event['ticket_price'];
                        $total_revenue_all_events += $revenue;
                        $fill_percentage = ($event['total_tickets'] > 0) ? ($event['tickets_sold'] / $event['total_tickets']) * 100 : 0;
                    ?>
                        <tr>
                            <td><?php echo sanitize_output($event['name']); ?></td>
                            <td><?php echo date("d.m.Y H:i", strtotime($event['date'])); ?></td>
                            <td><?php echo number_format($event['ticket_price'], 2); ?></td>
                            <td><?php echo $event['tickets_sold']; ?></td>
                            <td><?php echo $event['total_tickets']; ?></td>
                            <td><?php echo number_format($revenue, 2); ?></td>
                            <td><?php echo number_format($fill_percentage, 1); ?>%</td>
                        </tr>
                    <?php endwhile; ?>
                        <tr class="summary-row">
                            <td colspan="5" style="text-align:right;"><strong>SUMA PRZYCHODÓW (nadchodzące):</strong></td>
                            <td colspan="2"><strong><?php echo number_format($total_revenue_all_events, 2); ?> PLN</strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Brak nadchodzących wydarzeń w systemie lub brak sprzedaży biletów.</p>
            <?php endif;
            $stmt_events->close();
            ?>

        <?php elseif ($current_view == 'reservations_overview'): ?>
            <h3>Przegląd Najbliższych Rezerwacji</h3>
            <?php
            $stmt_reservations = $conn->prepare(
                "SELECT r.reservation_id, u.email AS user_email, h.name AS hall_name, 
                        r.reservation_date, r.reservation_time_start, r.reservation_time_end, 
                        r.base_hall_price, r.drinks_price, r.extras_price, r.total_price, r.status 
                 FROM reservations r
                 JOIN users u ON r.user_id = u.user_id
                 JOIN halls h ON r.hall_id = h.hall_id
                 WHERE r.reservation_date >= CURDATE() AND r.status IN ('potwierdzona', 'oczekujaca')
                 ORDER BY r.reservation_date ASC, r.reservation_time_start ASC"
            );
            $stmt_reservations->execute();
            $reservations = $stmt_reservations->get_result();

            if ($reservations->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Rez.</th>
                            <th>Klient</th>
                            <th>Sala</th>
                            <th>Data</th>
                            <th>Godziny</th>
                            <th>Cena Sali (PLN)</th>
                            <th>Cena Drinków (PLN)</th>
                            <th>Cena Dodatków (PLN)</th>
                            <th>Koszt Całk. (PLN)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                    $total_potential_revenue_reservations = 0;
                    while($res = $reservations->fetch_assoc()): 
                        if ($res['status'] == 'potwierdzona') { // Sumuj tylko potwierdzone dla przychodu
                            $total_potential_revenue_reservations += $res['total_price'];
                        }
                    ?>
                        <tr>
                            <td><?php echo $res['reservation_id']; ?></td>
                            <td><?php echo sanitize_output($res['user_email']); ?></td>
                            <td><?php echo sanitize_output($res['hall_name']); ?></td>
                            <td><?php echo date("d.m.Y", strtotime($res['reservation_date'])); ?></td>
                            <td><?php echo date("H:i", strtotime($res['reservation_time_start'])) . " - " . date("H:i", strtotime($res['reservation_time_end'])); ?></td>
                            <td><?php echo number_format($res['base_hall_price'], 2); ?></td>
                            <td><?php echo number_format($res['drinks_price'], 2); ?></td>
                            <td><?php echo number_format($res['extras_price'], 2); ?></td>
                            <td><strong><?php echo number_format($res['total_price'], 2); ?></strong></td>
                            <td><span class="status-<?php echo sanitize_output($res['status']); ?>"><?php echo sanitize_output(ucfirst(str_replace('_', ' ', $res['status']))); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                        <tr class="summary-row">
                            <td colspan="8" style="text-align:right;"><strong>SUMA POTENCJALNYCH PRZYCHODÓW (potwierdzone rezerwacje):</strong></td>
                            <td colspan="2"><strong><?php echo number_format($total_potential_revenue_reservations, 2); ?> PLN</strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Brak nadchodzących rezerwacji (potwierdzonych lub oczekujących).</p>
            <?php endif;
            $stmt_reservations->close();
            ?>

        <?php elseif ($current_view == 'users_management'): ?>
            <h3>Zarządzanie Użytkownikami</h3>
            <h4>Zmiana Roli Klienta na Pracownika</h4>
            <form action="/nightclub/actions/change_role_action.php" method="POST" class="styled-form-inline">
                <div>
                    <label for="user_email_to_change">Adres e-mail klienta:</label>
                    <input type="email" name="user_email_to_change" id="user_email_to_change" required>
                </div>
                <button type="submit" name="change_role_submit" class="btn">Zmień na Pracownika</button>
            </form>
            <hr>
            <h4>Lista Użytkowników (Pracownicy i Klienci)</h4>
            <?php
            $stmt_all_users = $conn->prepare("SELECT user_id, email, first_name, last_name, role, is_approved, created_at FROM users WHERE role IN ('klient', 'pracownik') ORDER BY role, email");
            $stmt_all_users->execute();
            $all_users = $stmt_all_users->get_result();
            if($all_users->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Imię Nazwisko</th>
                            <th>Rola</th>
                            <th>Status Konta</th>
                            <th>Zarejestrowany</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($user = $all_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo sanitize_output($user['email']); ?></td>
                            <td><?php echo sanitize_output($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo sanitize_output(ucfirst($user['role'])); ?></td>
                            <td><?php echo $user['is_approved'] ? '<span class="status-approved">Zatwierdzone</span>' : ($user['role'] == 'klient' ? '<span class="status-pending">Oczekuje</span>' : 'N/A'); ?></td>
                            <td><?php echo date("d.m.Y", strtotime($user['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Brak użytkowników (klientów/pracowników) w systemie.</p>
            <?php endif; $stmt_all_users->close(); ?>


        <?php elseif ($current_view == 'reports'): ?>
            <h3>Raporty Finansowe (Uproszczone)</h3>
            
            <h4>Przychody z Biletów (Wszystkie Zakończone Wydarzenia)</h4>
            <?php
            $stmt_past_events_revenue = $conn->prepare(
                "SELECT SUM(tickets_sold * ticket_price) as total_revenue 
                 FROM events 
                 WHERE date < CURDATE()"
            );
            $stmt_past_events_revenue->execute();
            $past_events_revenue_result = $stmt_past_events_revenue->get_result()->fetch_assoc();
            $past_events_total_revenue = $past_events_revenue_result['total_revenue'] ?? 0;
            $stmt_past_events_revenue->close();
            ?>
            <p>Całkowity przychód z biletów ze wszystkich zakończonych wydarzeń: <strong><?php echo number_format($past_events_total_revenue, 2); ?> PLN</strong></p>

            <h4>Przychody z Rezerwacji (Wszystkie Zakończone/Zapłacone Rezerwacje)</h4>
            <p class="info-text">Ta sekcja wymagałaby implementacji oznaczania rezerwacji jako 'zapłacona' po imprezie. Obecnie pokazujemy sumę wszystkich 'zakończonych' rezerwacji jako przykład.</p>
            <?php
            $stmt_completed_reservations_revenue = $conn->prepare(
                "SELECT SUM(total_price) as total_revenue 
                 FROM reservations 
                 WHERE status = 'zakonczona'" // Załóżmy, że 'zakonczona' oznacza również opłaconą
            );
            $stmt_completed_reservations_revenue->execute();
            $completed_reservations_revenue_result = $stmt_completed_reservations_revenue->get_result()->fetch_assoc();
            $completed_reservations_total_revenue = $completed_reservations_revenue_result['total_revenue'] ?? 0;
            $stmt_completed_reservations_revenue->close();
            ?>
            <p>Całkowity przychód z zakończonych rezerwacji (zakładając opłacenie): <strong><?php echo number_format($completed_reservations_total_revenue, 2); ?> PLN</strong></p>
            <hr>
            <p><strong>ŁĄCZNY PRZYCHÓD (zakończone wydarzenia i rezerwacje): <?php echo number_format($past_events_total_revenue + $completed_reservations_total_revenue, 2); ?> PLN</strong></p>
            <p class="info-text small-text">Uwaga: To są bardzo uproszczone raporty. Pełne raportowanie wymagałoby bardziej szczegółowego śledzenia transakcji, kosztów, itp.</p>


        <?php else: ?>
            <p>Wybierz opcję z menu po lewej stronie.</p>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>