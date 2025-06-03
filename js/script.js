document.addEventListener('DOMContentLoaded', function() {

    // --- Aktywny link w nawigacji ---
    const navLinks = document.querySelectorAll('header nav ul li a');
    // Używamy basename($_SERVER['PHP_SELF']) w PHP do dodawania klasy 'active',
    // więc JS nie jest tu już krytycznie potrzebny, ale można go zostawić jako fallback lub do bardziej złożonej logiki.
    // Dla uproszczenia, polegamy na PHP w header.php

    // --- Walidacja formularza rejestracji (przykład) ---
    const registrationForm = document.querySelector('form[action="/nightclub/actions/register_action.php"]');
    if (registrationForm) {
        const passwordInput = registrationForm.querySelector('#password');
        const confirmPasswordInput = registrationForm.querySelector('#confirm_password');
        registrationForm.addEventListener('submit', function(event) {
            if (passwordInput.value.length < 6) {
                alert('Hasło musi mieć co najmniej 6 znaków.');
                event.preventDefault();
                passwordInput.focus();
                return;
            }
            if (passwordInput.value !== confirmPasswordInput.value) {
                alert('Hasła nie są zgodne.');
                event.preventDefault();
                confirmPasswordInput.focus();
                return;
            }
        });
    }

    // --- Dynamiczne obliczanie ceny rezerwacji sali ---
    const reservationForm = document.getElementById('reservationForm');
    if (reservationForm) {
        const hallSelect = reservationForm.querySelector('#hall_id');
        const drinkInputs = reservationForm.querySelectorAll('.drink-quantity-input');
        const extraCheckboxes = reservationForm.querySelectorAll('.extra-checkbox-input');

        const priceHallDisplay = reservationForm.querySelector('#price_hall_display');
        const priceDrinksDisplay = reservationForm.querySelector('#price_drinks_display');
        const priceExtrasDisplay = reservationForm.querySelector('#price_extras_display');
        const priceTotalDisplay = reservationForm.querySelector('#price_total_display');
        const hallCapacityInfo = reservationForm.querySelector('#hall-capacity-info');

        function updateHallCapacityInfo() {
            if (hallSelect && hallSelect.value && hallCapacityInfo) {
                const selectedOption = hallSelect.options[hallSelect.selectedIndex];
                const capacity = selectedOption.dataset.capacity;
                if (capacity) {
                    hallCapacityInfo.textContent = `Pojemność wybranej sali: ${capacity} osób.`;
                } else {
                    hallCapacityInfo.textContent = '';
                }
            } else if (hallCapacityInfo) {
                 hallCapacityInfo.textContent = '';
            }
        }


        function calculateReservationTotalPrice() {
            let hallPrice = 0;
            let drinksTotalPrice = 0;
            let extrasTotalPrice = 0;

            if (hallSelect && hallSelect.value) {
                const selectedOption = hallSelect.options[hallSelect.selectedIndex];
                hallPrice = parseFloat(selectedOption.dataset.basePrice) || 0;
            }

            drinkInputs.forEach(input => {
                const quantity = parseInt(input.value) || 0;
                const pricePerUnit = parseFloat(input.dataset.price) || 0;
                const maxQuantity = parseInt(input.getAttribute('max')) || 0;
                const drinkId = input.id.split('_')[1]; // Zakłada format id="drink_ID"
                const warningSpan = reservationForm.querySelector(`#warning_${drinkId}`);
                const availableDisplay = reservationForm.querySelector(`#avail_${drinkId}_display`);

                if (quantity > maxQuantity) {
                    if (warningSpan) warningSpan.textContent = `Max: ${maxQuantity}!`;
                    input.value = maxQuantity; // Auto-korekta
                    drinksTotalPrice += maxQuantity * pricePerUnit;
                } else {
                    if (warningSpan) warningSpan.textContent = '';
                    drinksTotalPrice += quantity * pricePerUnit;
                }
                // Aktualizacja wyświetlanej dostępnej ilości (jeśli potrzebne dynamicznie - bardziej złożone)
                // Na razie zakładamy, że max jest statyczny z PHP
            });

            extraCheckboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    extrasTotalPrice += parseFloat(checkbox.dataset.price) || 0;
                }
            });

            const total = hallPrice + drinksTotalPrice + extrasTotalPrice;

            if (priceHallDisplay) priceHallDisplay.textContent = hallPrice.toFixed(2);
            if (priceDrinksDisplay) priceDrinksDisplay.textContent = drinksTotalPrice.toFixed(2);
            if (priceExtrasDisplay) priceExtrasDisplay.textContent = extrasTotalPrice.toFixed(2);
            if (priceTotalDisplay) priceTotalDisplay.textContent = total.toFixed(2);
        }

        if (hallSelect) {
            hallSelect.addEventListener('change', function() {
                calculateReservationTotalPrice();
                updateHallCapacityInfo();
            });
            // Inicjalne wywołanie dla wybranej sali (np. z GET)
            calculateReservationTotalPrice();
            updateHallCapacityInfo();
        }
        drinkInputs.forEach(input => input.addEventListener('input', calculateReservationTotalPrice));
        extraCheckboxes.forEach(checkbox => checkbox.addEventListener('change', calculateReservationTotalPrice));

        // Walidacja daty i godzin przy wysyłaniu
        reservationForm.addEventListener('submit', function(event) {
            const dateInput = reservationForm.querySelector('#reservation_date');
            const timeStartInput = reservationForm.querySelector('#reservation_time_start');
            const timeEndInput = reservationForm.querySelector('#reservation_time_end');

            if (!dateInput.value || !timeStartInput.value || !timeEndInput.value) {
                alert('Proszę wypełnić datę oraz godziny rozpoczęcia i zakończenia.');
                event.preventDefault();
                return;
            }
            
            const selectedDateTimeStartStr = dateInput.value + 'T' + timeStartInput.value;
            const selectedDateTimeEndStr = dateInput.value + 'T' + timeEndInput.value;
            
            const selectedDateTimeStart = new Date(selectedDateTimeStartStr);
            const selectedDateTimeEnd = new Date(selectedDateTimeEndStr);

            const now = new Date();
            now.setHours(0,0,0,0); // Dla porównania samej daty

            if (selectedDateTimeStart < now) { // Porównujemy pełną datę i godzinę, jeśli data jest dzisiejsza
                 if (new Date(dateInput.value) < now) {
                    alert('Data rezerwacji nie może być z przeszłości.');
                    event.preventDefault();
                    dateInput.focus();
                    return;
                 }
            }

            if (selectedDateTimeStart >= selectedDateTimeEnd) {
                alert('Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia.');
                event.preventDefault();
                timeEndInput.focus();
                return;
            }
            
            // Minimalny czas trwania rezerwacji (np. 1 godzina)
            const minDurationMs = 60 * 60 * 1000; 
            if ((selectedDateTimeEnd - selectedDateTimeStart) < minDurationMs) {
                alert('Minimalny czas trwania rezerwacji to 1 godzina.');
                event.preventDefault();
                return;
            }
        });
    }


    // --- Potwierdzenie anulowania (ogólne dla formularzy z klasą .cancel-form) ---
    const cancelForms = document.querySelectorAll('.cancel-form');
    cancelForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!confirm('Czy na pewno chcesz wykonać tę akcję? Zmiany mogą być nieodwracalne.')) {
                event.preventDefault();
            }
        });
    });


    // --- Modal dla wiadomości w panelu pracownika ---
    const messageModal = document.getElementById('messageModal');
    const viewMessageButtons = document.querySelectorAll('.view-message-btn');
    const closeModalButton = document.querySelector('.close-modal-btn');
    const markAsReadForm = document.getElementById('markAsReadForm');
    const messageIdInput = document.getElementById('message_id_to_mark_input');

    viewMessageButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('modalSubject').textContent = this.dataset.subject;
            document.getElementById('modalEmail').textContent = this.dataset.email;
            document.getElementById('modalSentDate').textContent = this.dataset.sent;
            document.getElementById('modalMessageBody').innerHTML = this.dataset.message; // Użyj innerHTML, bo wiadomość może zawierać <br>

            if (messageModal) messageModal.style.display = "block";
            
            // Jeśli wiadomość nie była przeczytana, oznacz ją jako przeczytaną
            if (this.dataset.isRead == "0" && markAsReadForm && messageIdInput) {
                messageIdInput.value = this.dataset.id;
                // Symulacja natychmiastowego wysłania formularza w tle, 
                // lub użyj AJAX jeśli chcesz uniknąć przeładowania
                // Proste rozwiązanie:
                // markAsReadForm.submit(); 
                // Lepsze z AJAX, ale dla uproszczenia projektu szkolnego można pominąć
                // Na razie, po zamknięciu modala strona się odświeży przez `mark_message_read_action.php`
                // Dla UX, przycisk mógłby zmienić stan, a JS mógłby wysłać zapytanie AJAX.
                // Aktualnie, po kliknięciu "Pokaż", akcja oznaczania jako przeczytanej musi być wywołana
                // np. przez kliknięcie "zamknij" lub osobny przycisk w modalu.
                // Prostsze: zakładamy, że pokazanie = chęć przeczytania.
                // Wywołanie akcji oznaczania po stronie serwera po kliknięciu "Pokaż" wymagałoby przekierowania z ID wiadomości.
                // Lub, jak teraz: po prostu `mark_message_read_action.php` przekierowuje z powrotem.
                // Jeśli chcemy, aby działo się to w tle:
                if (this.closest('tr')) this.closest('tr').classList.remove('unread-message'); // Zdejmij podświetlenie w JS
                
                // Automatyczne wysłanie formularza, jeśli jest to nowa wiadomość
                // To spowoduje przeładowanie, co nie jest idealne dla UX, ale działa.
                // Lepszym rozwiązaniem byłby AJAX.
                fetch(markAsReadForm.action, {
                    method: 'POST',
                    body: new FormData(markAsReadForm) // Wysyła message_id_to_mark
                }).then(response => {
                    if (!response.ok) console.error('Błąd oznaczania wiadomości jako przeczytanej');
                    // Można by tu dynamicznie zaktualizować listę, ale prościej jest polegać na odświeżeniu przy następnym ładowaniu widoku
                }).catch(error => console.error('Fetch error:', error));

            }
        });
    });

    if (closeModalButton && messageModal) {
        closeModalButton.addEventListener('click', function() {
            messageModal.style.display = "none";
        });
    }

    if (messageModal) {
        window.addEventListener('click', function(event) {
            if (event.target == messageModal) {
                messageModal.style.display = "none";
            }
        });
    }

});