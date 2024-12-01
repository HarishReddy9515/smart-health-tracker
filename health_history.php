<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

include 'db_connect.php'; // Include the database connection

// Fetch all health data for the user
$stmt = $conn->prepare("SELECT date, steps, calories, sleep_hours FROM health_log WHERE user_id = ? ORDER BY date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($date, $steps, $calories, $sleep_hours);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health History</title>
</head>
<body>
    <h1>Health History</h1>
    <a href="dashboard.php">Back to Dashboard</a> <!-- Link back to the dashboard -->

    <table>
        <tr>
            <th>Date</th>
            <th>Steps</th>
            <th>Calories</th>
            <th>Sleep Hours</th>
        </tr>
        <?php
        // Display all health data
        while ($stmt->fetch()): ?>
            <tr>
                <td><?php echo htmlspecialchars($date); ?></td>
                <td><?php echo htmlspecialchars($steps); ?></td>
                <td><?php echo htmlspecialchars($calories); ?></td>
                <td><?php echo htmlspecialchars($sleep_hours); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <?php
    $stmt->close();
    ?>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Smart Health Tracker. All rights reserved.</p>
    </footer>
</body>
</html>
