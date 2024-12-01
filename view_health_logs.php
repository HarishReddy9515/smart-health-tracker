<?php
session_start();
include 'db_connect.php'; // Include the database connection

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];  // Get the logged-in user's ID

// Fetch recent health logs from the database
$stmt = $conn->prepare("SELECT log_date, steps, mood, water_intake, blood_pressure, sleep_hours, weight 
                        FROM health_logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 10");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($log_date, $steps, $mood, $water_intake, $blood_pressure, $sleep_hours, $weight);

$healthLogs = [];
while ($stmt->fetch()) {
    $healthLogs[] = [
        'log_date' => $log_date,
        'steps' => $steps,
        'mood' => $mood,
        'water_intake' => $water_intake,
        'blood_pressure' => $blood_pressure,
        'sleep_hours' => $sleep_hours,
        'weight' => $weight
    ];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Health Logs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Your Health Logs</h1>
        </header>

        <?php if (count($healthLogs) > 0): ?>
            <div class="table-wrapper">
                <table class="health-log-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Steps</th>
                            <th>Mood</th>
                            <th>Water Intake (L)</th>
                            <th>Blood Pressure (mmHg)</th>
                            <th>Hours of Sleep</th>
                            <th>Weight (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($healthLogs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['log_date']) ?></td>
                                <td><?= htmlspecialchars($log['steps']) ?></td>
                                <td><?= htmlspecialchars($log['mood']) ?></td>
                                <td><?= htmlspecialchars($log['water_intake']) ?></td>
                                <td><?= htmlspecialchars($log['blood_pressure']) ?></td>
                                <td><?= htmlspecialchars($log['sleep_hours']) ?></td>
                                <td><?= htmlspecialchars($log['weight']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No health logs found.</p>
        <?php endif; ?>

        <footer>
            <a href="dashboard.php">Back to Dashboard</a>
        </footer>
    </div>
</body>
</html>
