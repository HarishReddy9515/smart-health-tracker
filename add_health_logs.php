<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $log_date = $_POST['log_date'];
    $steps = $_POST['steps'];
    $mood = $_POST['mood'];
    $water_intake = $_POST['water_intake'];
    $blood_pressure = $_POST['blood_pressure'];
    $sleep_hours = $_POST['sleep_hours'];
    $weight = $_POST['weight'];

    $stmt = $conn->prepare("INSERT INTO health_logs (user_id, log_date, steps, mood, water_intake, blood_pressure, sleep_hours, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssdsdi", $user_id, $log_date, $steps, $mood, $water_intake, $blood_pressure, $sleep_hours, $weight);

    if ($stmt->execute()) {
        echo "<p class='success-msg'>Health log added successfully!</p>";
    } else {
        echo "<p class='error-msg'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Fetch previous health logs
$stmt = $conn->prepare("SELECT log_date, steps, mood, water_intake, blood_pressure, sleep_hours, weight FROM health_logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Health Logs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Add Health Logs</h1>
        <form method="POST" class="health-log-form">
            <div class="form-group">
                <label for="log_date">Date:</label>
                <input type="date" name="log_date" required>
            </div>
            
            <div class="form-group">
                <label for="steps">Steps:</label>
                <input type="number" name="steps" placeholder="Enter your steps" required>
            </div>

            <div class="form-group">
                <label for="mood">Mood:</label>
                <input type="text" name="mood" placeholder="Good/Bad" required>
            </div>

            <div class="form-group">
                <label for="water_intake">Water Intake (L):</label>
                <input type="number" step="0.1" name="water_intake" placeholder="Liters" required>
            </div>

            <div class="form-group">
                <label for="blood_pressure">Blood Pressure (mmHg):</label>
                <input type="text" name="blood_pressure" placeholder="e.g., 120/80" required>
            </div>

            <div class="form-group">
                <label for="sleep_hours">Hours of Sleep:</label>
                <input type="number" step="0.1" name="sleep_hours" placeholder="e.g., 7.5" required>
            </div>

            <div class="form-group">
                <label for="weight">Weight (kg):</label>
                <input type="number" step="0.1" name="weight" placeholder="Enter your weight" required>
            </div>

            <button type="submit" class="btn">Add Log</button>
        </form>

        <h2>Your Recent Health Logs</h2>
        <div class="table-wrapper">
            <table class="health-log-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Steps</th>
                        <th>Mood</th>
                        <th>Water Intake (L)</th>
                        <th>Blood Pressure</th>
                        <th>Hours of Sleep</th>
                        <th>Weight (kg)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['log_date']); ?></td>
                        <td><?= htmlspecialchars($row['steps']); ?></td>
                        <td><?= htmlspecialchars($row['mood']); ?></td>
                        <td><?= htmlspecialchars($row['water_intake']); ?></td>
                        <td><?= htmlspecialchars($row['blood_pressure']); ?></td>
                        <td><?= htmlspecialchars($row['sleep_hours']); ?></td>
                        <td><?= htmlspecialchars($row['weight']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <footer>
            <a href="dashboard.php" class="back-link">Back to Dashboard</a>
        </footer>
    </div>
</body>
</html>
