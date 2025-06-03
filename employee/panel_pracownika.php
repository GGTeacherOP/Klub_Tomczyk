<?php
require_once __DIR__ . '/../includes/header.php';
requireRole('pracownik');

$current_view = isset($_GET['view']) ? $_GET['view'] : 'dashboard'; // Domyślny widok
?>

<h2>Panel Pracownika</h2>
<div class="panel-container">
    <aside class="panel-sidebar">
        <ul>
            <li><a href="?view=dashboard" class="<?php echo $current_view == 'dashboard' ? 'active' : ''; ?>">Pulpit</a></li>
            <li><a href="?view=users" class="<?php echo $current_view == 'users' ? 'active' : ''; ?>">Zatwierdzanie Kont</a></li>
            <li><a href="?view=reservations" class="<?php echo $current_view == 'reservations' ? 'active' : ''; ?>">Zarządzanie Rezerwacjami</a></li>
            <li><a href="?view=drinks" class="<?php echo $current_view == 'drinks' ? 'active' : ''; ?>">Stan Magazynowy Drinków</a></li>
            <li><a href="?view=messages" class="<?php echo $current_view == 'messages' ? 'active' : ''; ?>">Wiadomości od Klientów</a></li>
        </ul>
    </aside>
    <section class="panel-content">
        <?php if ($current_view == 'dashboard'): ?>
            <h3>Pulpit Pracownika</h3>
            <p>Witaj w panelu pracownika, <?php echo sanitize_output($_SESSION['email']); ?>!</p>
            <p>Wybierz jedną z opcji z menu po lewej stronie, aby rozpocząć zarządzanie.</p>
            <?php
                $stmt_pending_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'klient' AND is_approved = FALSE");
                $pending_users_count = $stmt_pending_users->fetch_assoc()['count'];
                $stmt_pending_reservations = $conn->query("SELECT COUNT(*) as count FROM reservations WHERE status = 'oczekujaca'");
                $pending_reservations_count = $stmt_pending_reservations->fetch_assoc()['count'];
            ?>
            <div class="dashboard-summaries">
                <div class="summary-card">
                    <h4>Konta do Zatwierdzenia</h4>
                    <p class="count"><?php echo $pending_users_count; ?></p>
                    <?php if ($pending_users_count > 0) echo '<a href="?view=users" class="btn btn-sm">Przejdź</a>'; ?>
                </div>
                <div class="summary-card">
                    <h4>Rezerwacje Oczekujące</h4>
                    <p class="count"><?php echo $pending_reservations_count; ?></p>
                     <?php if ($pending_reservations_count > 0) echo '<a href="?view=reservations" class="btn btn-sm">Przejdź</a>'; ?>
                </div>
            </div>

        <?php elseif ($current_view == 'users'): ?>
            <h3>Zatwierdzanie Nowych Kont Klientów</h3>
            <?php
            $stmt_users_to_approve = $conn->prepare("SELECT user_id, email, first_name, last_name, created_at FROM users WHERE role = 'klient' AND is_approved = FALSE ORDER BY created_at ASC");
            $stmt_users_to_approve->execute();
            $users_to_approve = $stmt_users_to_approve->get_result();

            if ($users_to_approve->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Imię i Nazwisko</th>
                            <th>Data Rejestracji</th>
                            <th>Akcja</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($user = $users_to_approve->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo sanitize_output($user['email']); ?></td>
                            <td><?php echo sanitize_output($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo date("d.m.Y H:i", strtotime($user['created_at'])); ?></td>
                            <td>
                                <form action="/nightclub/actions/approve_user_action.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="approve_user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Zatwierdź</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Brak nowych kont klientów oczekujących na zatwierdzenie.</p>
            <?php endif;
            $stmt_users_to_approve->close();
            ?>

        <?php elseif ($current_view == 'reservations'): ?>
            <h3>Zarządzanie Rezerwacjami Klientów</h3>
            <h4>Rezerwacje Oczekujące na Potwierdzenie</h4>
            <?php
            $stmt_pending_res = $conn->prepare(
                "SELECT r.reservation_id, u.email AS user_email, h.name AS hall_name, r.reservation_date, r.reservation_time_start, r.reservation_time_end, r.total_price, r.created_at 
                 FROM reservations r 
                 JOIN users u ON r.user_id = u.user_id
                 JOIN halls h ON r.hall_id = h.hall_id
                 WHERE r.status = 'oczekujaca' ORDER BY r.created_at ASC"
            );
            $stmt_pending_res->execute();
            $pending_reservations = $stmt_pending_res->get_result();

            if ($pending_reservations->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID Rez.</th>
                            <th>Klient (Email)</th>
                            <th>Sala</th>
                            <th>Data Imprezy</th>
                            <th>Godziny</th>
                            <th>Cena (PLN)</th>
                            <th>Złożono</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($res = $pending_reservations->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $res['reservation_id']; ?></td>
                            <td><?php echo sanitize_output($res['user_email']); ?></td>
                            <td><?php echo sanitize_output($res['hall_name']); ?></td>
                            <td><?php echo date("d.m.Y", strtotime($res['reservation_date'])); ?></td>
                            <td><?php echo date("H:i", strtotime($res['reservation_time_start'])) . " - " . date("H:i", strtotime($res['reservation_time_end'])); ?></td>
                            <td><?php echo number_format($res['total_price'], 2); ?></td>
                            <td><?php echo date("d.m.Y H:i", strtotime($res['created_at'])); ?></td>
                            <td>
                                <form action="/nightclub/actions/confirm_reservation_action.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="confirm_reservation_id" value="<?php echo $res['reservation_id']; ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Potwierdź</button>
                                </form>
                                <form action="/nightclub/actions/cancel_reservation_employee_action.php" method="POST" style="display:inline;" class="cancel-form">
                                    <input type="hidden" name="cancel_reservation_id_employee" value="<?php echo $res['reservation_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm cancel-action">Anuluj</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Brak rezerwacji oczekujących na potwierdzenie.</p>
            <?php endif;
            $stmt_pending_res->close();
            ?>
            <hr>
            <h4>Wszystkie Potwierdzone i Nadchodzące Rezerwacje</h4>
            <?php
            $stmt_confirmed_res = $conn->prepare(
                "SELECT r.reservation_id, u.email AS user_email, h.name AS hall_name, r.reservation_date, r.reservation_time_start, r.reservation_time_end, r.total_price, r.status
                 FROM reservations r 
                 JOIN users u ON r.user_id = u.user_id
                 JOIN halls h ON r.hall_id = h.hall_id
                 WHERE r.status = 'potwierdzona' AND r.reservation_date >= CURDATE()
                 ORDER BY r.reservation_date ASC, r.reservation_time_start ASC"
            );
            $stmt_confirmed_res->execute();
            $confirmed_reservations = $stmt_confirmed_res->get_result();
            if ($confirmed_reservations->num_rows > 0): ?>
                 <table>
                    <thead>
                        <tr>
                            <th>ID Rez.</th>
                            <th>Klient (Email)</th>
                            <th>Sala</th>
                            <th>Data Imprezy</th>
                            <th>Godziny</th>
                            <th>Cena (PLN)</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($res = $confirmed_reservations->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $res['reservation_id']; ?></td>
                            <td><?php echo sanitize_output($res['user_email']); ?></td>
                            <td><?php echo sanitize_output($res['hall_name']); ?></td>
                            <td><?php echo date("d.m.Y", strtotime($res['reservation_date'])); ?></td>
                            <td><?php echo date("H:i", strtotime($res['reservation_time_start'])) . " - " . date("H:i", strtotime($res['reservation_time_end'])); ?></td>
                            <td><?php echo number_format($res['total_price'], 2); ?></td>
                             <td><span class="status-<?php echo sanitize_output($res['status']); ?>"><?php echo sanitize_output(ucfirst(str_replace('_', ' ', $res['status']))); ?></span></td>
                            <td>
                                <form action="/nightclub/actions/cancel_reservation_employee_action.php" method="POST" style="display:inline;" class="cancel-form">
                                    <input type="hidden" name="cancel_reservation_id_employee" value="<?php echo $res['reservation_id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm cancel-action">Anuluj</button>
                                </form>
                                </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                 <p>Brak potwierdzonych, nadchodzących rezerwacji.</p>
            <?php endif;
            $stmt_confirmed_res->close();
            ?>


        <?php elseif ($current_view == 'drinks'): ?>
            <h3>Stan Magazynowy Drinków</h3>
            <?php
            $stmt_drinks_stock = $conn->prepare("SELECT drink_id, name, quantity_available, price_per_unit FROM drinks ORDER BY name ASC");
            $stmt_drinks_stock->execute();
            $drinks_stock = $stmt_drinks_stock->get_result();
            ?>
            <h4>Aktualny Stan</h4>
            <?php if ($drinks_stock->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa Drinka</th>
                            <th>Dostępna Ilość</th>
                            <th>Cena Jedn. (PLN)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($drink = $drinks_stock->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $drink['drink_id']; ?></td>
                            <td><?php echo sanitize_output($drink['name']); ?></td>
                            <td><?php echo $drink['quantity_available']; ?></td>
                            <td><?php echo number_format($drink['price_per_unit'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Brak zdefiniowanych drinków w systemie.</p>
            <?php endif;
            // Resetuj wskaźnik wyniku, aby ponownie użyć $drinks_stock w formularzu
            $drinks_stock->data_seek(0);
            ?>
            <hr>
            <h4>Aktualizuj Stan (Dodaj Ilość)</h4>
            <form action="/nightclub/actions/update_drinks_action.php" method="POST" class="styled-form-inline">
                <div>
                    <label for="drink_id_to_update">Wybierz drink:</label>
                    <select name="drink_id_to_update" id="drink_id_to_update" required>
                        <option value="">-- Wybierz --</option>
                        <?php while($drink_option = $drinks_stock->fetch_assoc()): ?>
                            <option value="<?php echo $drink_option['drink_id']; ?>"><?php echo sanitize_output($drink_option['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label for="quantity_to_add">Ilość do dodania:</label>
                    <input type="number" name="quantity_to_add" id="quantity_to_add" min="1" required>
                </div>
                <button type="submit" name="update_drink_submit" class="btn">Zaktualizuj Stan</button>
            </form>
            <?php $stmt_drinks_stock->close(); ?>

        <?php elseif ($current_view == 'messages'): ?>
            <h3>Wiadomości od Klientów</h3>
            <?php
            $stmt_messages = $conn->prepare(
                "SELECT cm.message_id, u.email AS user_email, cm.subject, cm.message, cm.sent_at, cm.is_read 
                 FROM contact_messages cm
                 JOIN users u ON cm.user_id = u.user_id
                 ORDER BY cm.is_read ASC, cm.sent_at DESC"
            );
            $stmt_messages->execute();
            $messages_result = $stmt_messages->get_result();

            if ($messages_result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Od</th>
                            <th>Temat</th>
                            <th>Fragment Wiadomości</th>
                            <th>Otrzymano</th>
                            <th>Status</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while($msg = $messages_result->fetch_assoc()): ?>
                        <tr class="<?php echo !$msg['is_read'] ? 'unread-message' : ''; ?>">
                            <td><?php echo sanitize_output($msg['user_email']); ?></td>
                            <td><?php echo sanitize_output($msg['subject']); ?></td>
                            <td><?php echo sanitize_output(substr($msg['message'], 0, 50)); ?>...</td>
                            <td><?php echo date("d.m.Y H:i", strtotime($msg['sent_at'])); ?></td>
                            <td><?php echo $msg['is_read'] ? 'Przeczytana' : '<strong>Nowa</strong>'; ?></td>
                            <td>
                                <button class="btn btn-sm view-message-btn" 
                                        data-id="<?php echo $msg['message_id']; ?>"
                                        data-email="<?php echo sanitize_output($msg['user_email']); ?>"
                                        data-subject="<?php echo sanitize_output($msg['subject']); ?>"
                                        data-sent="<?php echo date("d.m.Y H:i", strtotime($msg['sent_at'])); ?>"
                                        data-message="<?php echo sanitize_output(nl2br($msg['message'])); ?>"
                                        data-is-read="<?php echo $msg['is_read']; ?>">
                                    Pokaż
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>

                <div id="messageModal" class="modal">
                    <div class="modal-content">
                        <span class="close-modal-btn">&times;</span>
                        <h4 id="modalSubject"></h4>
                        <p><small>Od: <span id="modalEmail"></span>, Data: <span id="modalSentDate"></span></small></p>
                        <hr>
                        <p id="modalMessageBody"></p>
                        <form id="markAsReadForm" action="/nightclub/actions/mark_message_read_action.php" method="POST" style="display:none;">
                            <input type="hidden" name="message_id_to_mark" id="message_id_to_mark_input">
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <p>Brak wiadomości od klientów.</p>
            <?php endif;
            $stmt_messages->close();
            ?>


        <?php else: ?>
            <p>Wybierz opcję z menu po lewej stronie.</p>
        <?php endif; ?>
    </section>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>