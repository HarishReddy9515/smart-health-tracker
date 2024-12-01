<?php
// delete_health_log.php

session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$log_id = $_GET['id'];

$query = "DELETE FROM health_log WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $log_id, $user_id);

if ($stmt->execute()) {
    header("Location: view_health_logs.php");
    exit;
} else {
    echo "Error deleting log.";
}
?>
