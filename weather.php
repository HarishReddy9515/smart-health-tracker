<?php
session_start();
include 'db_connect.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details from database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Weather API integration
$api_key = "870b11c26229578fcfda329de5fb85fc"; // Your API key
$city = "london"; // Default city

if (isset($_POST['city'])) {
    $city = htmlspecialchars($_POST['city']); // Get city from user input
}

$weather_url = "http://api.openweathermap.org/data/2.5/weather?q=$city&units=metric&appid=$api_key";

// Fetch weather data
$weather_data = file_get_contents($weather_url);
if ($weather_data === FALSE) {
    $error_message = "Unable to retrieve weather data at this time.";
    $temperature = "N/A";
    $description = "Weather data not available";
} else {
    $weather_array = json_decode($weather_data, true);
    if ($weather_array['cod'] == 200) {
        $temperature = $weather_array['main']['temp'];
        $description = $weather_array['weather'][0]['description'];
        // Add suggestions based on temperature
        if ($temperature < 10) {
            $advice = "It's chilly; dress warmly to stay comfortable.";
        } elseif ($temperature >= 10 && $temperature < 20) {
            $advice = "The weather is mild; a light jacket should be fine.";
        } else {
            $advice = "It's warm outside; stay cool and hydrated.";
        }
    } else {
        $error_message = "Unable to retrieve weather data at this time.";
        $temperature = "N/A";
        $description = "Weather data not available";
    }
}

// Fetch recent health logs from database
$stmt = $conn->prepare("SELECT log_date, steps, mood, water_intake FROM health_logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($log_date, $steps, $mood, $water_intake);

$healthLogs = [];
while ($stmt->fetch()) {
    $healthLogs[] = [
        'log_date' => $log_date,
        'steps' => $steps,
        'mood' => $mood,
        'water_intake' => $water_intake
    ];
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($username) ?>!</h1>

    <h3>Today's Weather</h3>

    <!-- Weather Form to input city -->
    <form method="POST">
        <label for="city">Enter City:</label>
        <input type="text" name="city" id="city" value="<?= htmlspecialchars($city) ?>" required>
        <button type="submit">Get Weather</button>
    </form>

    <p>City: <?= htmlspecialchars($city) ?></p>
    <p>Temperature: <?= htmlspecialchars($temperature) ?> °C</p>
    <p>Description: <?= htmlspecialchars($description) ?></p>
    <p><?= isset($advice) ? $advice : "Weather data not available." ?></p>

    <h3>Your Recent Health Logs</h3>
    <?php if (count($healthLogs) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Steps</th>
                    <th>Mood</th>
                    <th>Water Intake (L)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($healthLogs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['log_date']) ?></td>
                        <td><?= htmlspecialchars($log['steps']) ?></td>
                        <td><?= htmlspecialchars($log['mood']) ?></td>
                        <td><?= htmlspecialchars($log['water_intake']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No health logs found.</p>
    <?php endif; ?>

    <p><a href="view_health_logs.php">View All Logs</a></p>
    <p><a href="health_trends.php">Health Trends</a></p>
</body>
</html>
