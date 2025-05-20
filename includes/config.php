<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bania_u_cygana";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}
?>