<?php
session_start(); // Start session to access user data

// Connect to the database
$conn = new mysqli("localhost", "root", "Harish09876@", "health_tracker");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if user_id is set
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id']; // Get user_id from session
        $log_date = $_POST['log_date'];
        $steps = $_POST['steps'];
        $mood = $_POST['mood'];
        $water_intake = $_POST['water_intake'];

        $sql = "INSERT INTO health_logs (user_id, log_date, steps, mood, water_intake) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isisi", $user_id, $log_date, $steps, $mood, $water_intake);

        if ($stmt->execute()) {
            echo "Health log saved successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "You need to log in to save health data.";
    }
}

?>

<!-- HTML Form for Health Logging -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Health Data</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Log Your Health Data</h1>
    <form method="POST" action="">
        <label for="log_date">Date:</label>
        <input type="date" id="log_date" name="log_date" required><br>

        <label for="steps">Steps:</label>
        <input type="number" id="steps" name="steps" required><br>

        <label for="mood">Mood:</label>
        <input type="text" id="mood" name="mood" required><br>

        <label for="water_intake">Water Intake (Liters):</label>
        <input type="number" step="0.1" id="water_intake" name="water_intake" required><br>

        <button type="submit">Log Health Data</button>
    </form>
</body>
</html>
