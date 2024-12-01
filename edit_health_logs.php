<?php
// edit_health_log.php

session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$log_id = $_GET['id'];

$query = "SELECT * FROM health_log WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $log_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$log = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metric = $_POST['metric'];
    $value = $_POST['value'];

    $update_query = "UPDATE health_log SET metric = ?, value = ? WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssii", $metric, $value, $log_id, $user_id);

    if ($stmt->execute()) {
        header("Location: view_health_logs.php");
        exit;
    } else {
        echo "Error updating log.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Health Log</title>
</head>
<body>
    <h2>Edit Health Log</h2>
    <form method="post">
        Metric: <input type="text" name="metric" value="<?= htmlspecialchars($log['metric']) ?>"><br>
        Value: <input type="text" name="value" value="<?= htmlspecialchars($log['value']) ?>"><br>
        <button type="submit">Update</button>
    </form>
</body>
</html>
