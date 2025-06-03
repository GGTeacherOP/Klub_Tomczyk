<?php
session_start();

$db_host = 'localhost';
$db_user = 'root'; // Zmień na swoje dane
$db_pass = '';     // Zmień na swoje dane
$db_name = 'nightclub_db';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Błąd połączenia z bazą danych: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Funkcje pomocnicze
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function checkUserApproval($userId, $dbConn) {
    $stmt = $dbConn->prepare("SELECT is_approved FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user ? $user['is_approved'] : false;
}

// Przekierowanie, jeśli użytkownik nie jest zalogowany
function requireLogin($redirectPage = 'zaloguj.php') {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: /nightclub/$redirectPage"); // Dostosuj ścieżkę
        exit();
    }
    // Sprawdzenie, czy konto klienta jest zatwierdzone, jeśli próbuje uzyskać dostęp do czegoś innego niż wylogowanie
    if (getUserRole() === 'klient' && basename($_SERVER['PHP_SELF']) !== 'wyloguj.php') {
        global $conn; // Potrzebujemy dostępu do $conn
        if (!checkUserApproval($_SESSION['user_id'], $conn)) {
            $_SESSION['message'] = "Twoje konto oczekuje na zatwierdzenie przez pracownika. Niektóre funkcje mogą być niedostępne.";
            $_SESSION['message_type'] = "warning";
            // Można by tu przekierować na stronę informacyjną lub pozwolić na ograniczony dostęp
        }
    }
}

// Przekierowanie, jeśli użytkownik nie ma odpowiedniej roli
function requireRole($role, $redirectPage = 'index.php') {
    requireLogin();
    if (getUserRole() !== $role) {
        $_SESSION['message'] = "Brak uprawnień do tej sekcji.";
        $_SESSION['message_type'] = "error";
        header("Location: /nightclub/$redirectPage"); // Dostosuj ścieżkę
        exit();
    }
}
?>