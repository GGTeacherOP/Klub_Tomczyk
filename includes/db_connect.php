<?php
ini_set('display_errors', 1); // Pokazuj błędy na etapie deweloperskim
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$db_host = 'localhost';
$db_user = 'root'; // Domyślny użytkownik XAMPP
$db_pass = '';     // Domyślne hasło XAMPP (puste)
$db_name = 'nightclub_db';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Krytyczny błąd połączenia z bazą danych: " . $conn->connect_error . ". Sprawdź konfigurację w /includes/db_connect.php");
}
$conn->set_charset("utf8mb4");

// --- Funkcje Pomocnicze ---
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function getUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function isUserApproved($dbConn = null) {
    if (!isLoggedIn()) return false;
    // Jeśli $dbConn nie jest przekazane, użyj globalnego $conn
    global $conn;
    $current_conn = $dbConn ?? $conn;

    $userId = getUserId();
    if (!$userId) return false;

    $stmt = $current_conn->prepare("SELECT is_approved FROM users WHERE user_id = ?");
    if (!$stmt) {
        error_log("Błąd przygotowania zapytania w isUserApproved: " . $current_conn->error);
        return false;
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user ? (bool)$user['is_approved'] : false;
}

function requireLogin($customRedirectPage = null) {
    $base_url = "/nightclub"; // Zmień jeśli projekt jest w innym miejscu
    $redirectPage = $customRedirectPage ? $base_url . "/" . $customRedirectPage : $base_url . "/zaloguj.php";

    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Zapamiętaj, dokąd użytkownik chciał iść
        header("Location: " . $redirectPage);
        exit();
    }
}

function requireRole($requiredRole, $customRedirectPage = null) {
    requireLogin(); // Najpierw upewnij się, że jest zalogowany
    $base_url = "/nightclub";
    $redirectPage = $customRedirectPage ? $base_url . "/" . $customRedirectPage : $base_url . "/index.php";

    if (getUserRole() !== $requiredRole) {
        $_SESSION['message'] = "Nie masz uprawnień do dostępu do tej strony.";
        $_SESSION['message_type'] = "error";
        header("Location: " . $redirectPage);
        exit();
    }
}

// Funkcja do sprawdzania, czy użytkownik (klient) jest zatwierdzony przed dostępem do pewnych funkcji
function requireApprovedUser($dbConn = null) {
    requireLogin(); // Musi być zalogowany
    if (getUserRole() === 'klient' && !isUserApproved($dbConn)) {
        $_SESSION['message'] = "Twoje konto oczekuje na zatwierdzenie przez pracownika. Niektóre funkcje są niedostępne.";
        $_SESSION['message_type'] = "warning";
        header("Location: /nightclub/index.php"); // Przekieruj na stronę główną lub stronę informacyjną
        exit();
    }
}

// Funkcja do bezpiecznego wyświetlania danych
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>