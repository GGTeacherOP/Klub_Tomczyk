<?php
require_once 'config.php';

function login($email, $password) {
    global $conn;
    $stmt = $conn->prepare("SELECT id, email, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    if ($user && $password === $user['password'] && $user['status'] === 'confirmed') {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['status'] = $user['status'];
        return true;
    }
    return false;
}

function logout() {
    session_unset();
    session_destroy();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && $_SESSION['status'] === 'confirmed';
}
?>