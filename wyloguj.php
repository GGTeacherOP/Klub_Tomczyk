<?php
require_once __DIR__ . '/includes/db_connect.php'; // Dla session_start()
$_SESSION = array(); // Wyczyść wszystkie zmienne sesji
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("Location: /nightclub/index.php");
exit();
?>