<?php
$servername = "localhost";
$username = "root"; // Replace with your database username
$password = ""; // Replace with your database password
$dbname = "jof_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set maximum allowed packet size
$conn->query('SET GLOBAL max_allowed_packet=67108864'); // 64MB
$conn->query('SET GLOBAL wait_timeout=28800'); // 8 hours
$conn->query('SET GLOBAL interactive_timeout=28800'); // 8 hours

// Set the charset to handle special characters
$conn->set_charset("utf8mb4");
?>
