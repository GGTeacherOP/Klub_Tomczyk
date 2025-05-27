$(document).ready(function() {
    // Opinion slider
    var currentIndex = 0;
    var opinions = $('.opinion');
    var totalOpinions = opinions.length;

    function showNextOpinion() {
        opinions.eq(currentIndex).fadeOut(500, function() {
            currentIndex = (currentIndex + 1) % totalOpinions;
            opinions.eq(currentIndex).fadeIn(500);
        });
    }

    if (totalOpinions > 0) {
        opinions.first().show();
        setInterval(showNextOpinion, 5000);
    }

    // Modal for contact form
    $('#open_contact_form').click(function() {
        $('#contact_modal').fadeIn(300);
    });
    $('#close_modal').click(function() {
        $('#contact_modal').fadeOut(300);
    });

    // Login form validation
    $('#login_form').on('submit', function(event) {
        var email = $('input[name="email"]').val().trim();
        var password = $('input[name="password"]').val().trim();
        if (email === '' || password === '') {
            event.preventDefault();
            $('.error-message').text('Proszę wypełnić wszystkie pola.');
        }
    });

    // Registration form validation
    $('#register_form').on('submit', function(event) {
        var email = $('input[name="email"]').val().trim();
        var password = $('input[name="password"]').val().trim();
        var confirm_password = $('input[name="confirm_password"]').val().trim();
        if (email === '' || password === '' || confirm_password === '') {
            event.preventDefault();
            alert('Proszę wypełnić wszystkie pola.');
            return;
        }
        if (password !== confirm_password) {
            event.preventDefault();
            alert('Hasła nie są zgodne.');
        }
    });

    // Confirmation for reservation cancellation
    $('form[action="cancel_reservation_client.php"], form[action="cancel_reservation_employee.php"]').on('submit', function(event) {
        if (!confirm('Czy na pewno chcesz anulować tę rezerwację?')) {
            event.preventDefault();
        }
    });

    // AJAX spinner for inventory form
    $('#inventory_form').on('submit', function() {
        $('#drinks_container').append('<div class="spinner"></div>');
    });

    // Dynamic drinks loading in employee panel
    $('#sala').on('change', function() {
        var sala = $(this).val();
        if (sala) {
            $.ajax({
                url: 'get_drinks.php',
                method: 'POST',
                data: { sala: sala },
                beforeSend: function() {
                    $('#drinks_container').html('<div class="spinner"></div>').show();
                },
                success: function(data) {
                    $('#drinks_container').html(data).slideDown(300);
                    $('#submit_inventory').show();
                },
                error: function() {
                    $('#drinks_container').html('<p>Błąd ładowania drinków.</p>');
                }
            });
        } else {
            $('#drinks_container').slideUp(300, function() {
                $(this).empty();
            });
            $('#submit_inventory').hide();
        }
    });
});