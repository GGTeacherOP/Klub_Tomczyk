<?php
session_start();
include 'config.php';

function login($email, $password) {
    global $conn;
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $conn->query($sql);
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($user['status'] == 'confirmed') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['status'] = $user['status'];
            return true;
        }
    }
    return false;
}

function logout() {
    session_destroy();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}
?>