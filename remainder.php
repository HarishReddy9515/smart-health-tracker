<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php'; // Include the database connection

// Handle reminder submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['set_reminder'])) {
    $reminder_time = $_POST['reminder_time'];
    $stmt = $conn->prepare("INSERT INTO reminders (user_id, reminder_time) VALUES (?, ?)");
    $stmt->bind_param("is", $_SESSION['user_id'], $reminder_time);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Reminder set successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Reminders</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>
    <h1>Set Reminders</h1>
    <?php if (isset($_SESSION['message'])): ?>
        <p><?php echo $_SESSION['message']; ?></p>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>
    <form method="POST">
        <label for="reminder_time">Reminder Time:</label>
        <input type="time" name="reminder_time" required>
        <button type="submit" name="set_reminder">Set Reminder</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
