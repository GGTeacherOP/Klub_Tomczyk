<?php
require_once __DIR__ . '/../includes/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role_choice = $_POST['role_choice'];

    $_SESSION['form_data'] = ['email' => $email, 'role_choice' => $role_choice]; // Zapamiętaj dane na wypadek błędu

    if (empty($email) || empty($password) || empty($role_choice)) {
        $_SESSION['message'] = "Wszystkie pola są wymagane.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/zaloguj.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT user_id, email, password, role, is_approved FROM users WHERE email = ? AND role = ?");
    $stmt->bind_param("ss", $email, $role_choice);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($password === $user['password']) { // Porównanie haseł (bez szyfrowania)
            // Sprawdzenie zatwierdzenia tylko dla klienta
            if ($user['role'] === 'klient' && !$user['is_approved']) {
                $_SESSION['message'] = "Twoje konto oczekuje na zatwierdzenie przez pracownika. Nie możesz się jeszcze zalogować.";
                $_SESSION['message_type'] = "warning";
                header("Location: /nightclub/zaloguj.php");
                exit();
            }

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            // $_SESSION['is_approved'] nie jest już potrzebne w sesji, bo mamy funkcję isUserApproved()

            unset($_SESSION['form_data']); // Usuń zapamiętane dane formularza

            $_SESSION['message'] = "Logowanie pomyślne. Witaj " . sanitize_output($user['email']) . "!";
            $_SESSION['message_type'] = "success";

            $redirect_url = $_SESSION['redirect_url'] ?? '/nightclub/index.php';
            unset($_SESSION['redirect_url']);
            header("Location: " . $redirect_url); // Przekieruj na stronę główną lub zapamiętaną
            exit();

        } else {
            $_SESSION['message'] = "Nieprawidłowe hasło.";
            $_SESSION['message_type'] = "error";
            header("Location: /nightclub/zaloguj.php");
        }
    } else {
        $_SESSION['message'] = "Nie znaleziono użytkownika z podanym adresem e-mail i rolą.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/zaloguj.php");
    }
    $stmt->close();
} else {
    header("Location: /nightclub/zaloguj.php");
}
exit();
?>