<?php
session_start();
require '../database.php';

if (!isset($_SESSION['customerID']) || empty($_POST['newPhone']) || empty($_POST['password'])) {
    header('Location: account.php');
    exit();
}

$customerID = $_SESSION['customerID'];
$newPhone = $_POST['newPhone'];
$password = $_POST['password'];

// Validate phone number format
if (!preg_match('/^[0-9]{11}$/', $newPhone)) {
    $_SESSION['error'] = "Contact number must be 11 digits";
    header('Location: account.php');
    exit();
}

// Verify password
$sql = "SELECT password FROM customer_tbl WHERE customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Incorrect password";
    header('Location: account.php');
    exit();
}

// Update phone number
$sql = "UPDATE customer_tbl SET contactNum = ? WHERE customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $newPhone, $customerID);

if ($stmt->execute()) {
    $_SESSION['success'] = "Contact number updated successfully!";
} else {
    $_SESSION['error'] = "Failed to update Contact number. Please try again.";
}

header('Location: account.php');
exit(); 