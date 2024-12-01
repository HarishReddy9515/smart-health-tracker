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

// Weather API
$apiKey = "870b11c26229578fcfda329de5fb85fc"; // Your OpenWeather API key
$city = isset($_GET['city']) ? $_GET['city'] : "London"; // Default city is London

// Initialize cURL session
$apiUrl = "http://api.openweathermap.org/data/2.5/weather?q={$city}&units=metric&appid={$apiKey}";
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL request and store response
$response = curl_exec($ch);

// Check if the request was successful
if ($response === FALSE) {
    $error = curl_error($ch);
    echo "cURL Error: $error"; // Debugging output
    $temperature = "N/A";
    $description = "Weather data not available.";
    $weatherMessage = "Unable to retrieve weather data at this time.";
} else {
    $weatherData = json_decode($response, true);
    if ($weatherData['cod'] == 200) {
        $temperature = $weatherData['main']['temp'];
        $description = $weatherData['weather'][0]['description'];
        if ($temperature > 25) {
            $weatherMessage = "It's a hot day; stay hydrated and avoid excessive outdoor activity.";
        } elseif ($temperature >= 15) {
            $weatherMessage = "It's a comfortable day; enjoy being outdoors!";
        } elseif ($temperature >= 5) {
            $weatherMessage = "It's chilly; dress warmly to stay comfortable.";
        } else {
            $weatherMessage = "It's a cold day; stay warm and avoid staying outside too long.";
        }
    } else {
        $temperature = "N/A";
        $description = "Weather data not available.";
        $weatherMessage = "Unable to retrieve weather data.";
    }
}

curl_close($ch);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Smart Health Tracker</title>
    <link rel="stylesheet" href="style.css">
    <!-- Include Chart.js for visualizing health data -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Page Header -->
    <header>
        <div class="container">
            <h1 class="title">Smart Health Tracker</h1>
            <nav class="navbar">
                <a href="add_health_logs.php">Add Health Log</a>
                <a href="view_health_logs.php">View All Logs</a>
                <a href="health_trends.php">Health Trends</a>
                <a href="profile_update.php">Update Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <section class="dashboard-overview">
            <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
            <p>Today's Weather</p>
            <div class="weather">
                <p>City: <?= htmlspecialchars($city) ?></p>
                <p>Temperature: <?= htmlspecialchars($temperature) ?> °C</p>
                <p>Description: <?= htmlspecialchars($description) ?></p>
                <p><?= htmlspecialchars($weatherMessage) ?></p>
                <form action="" method="get">
                    <label for="city">Change City:</label>
                    <input type="text" name="city" id="city" value="<?= htmlspecialchars($city) ?>" required>
                    <button type="submit">Update</button>
                </form>
            </div>
        </section>

        <!-- Health Logs Section -->
        <section class="health-logs">
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

                <!-- Chart.js Visualization for Steps -->
                <canvas id="stepsChart" width="400" height="200"></canvas>
                <script>
                    const stepsData = <?php echo json_encode(array_map(function($log) { return $log['steps']; }, $healthLogs)); ?>;
                    const logDates = <?php echo json_encode(array_map(function($log) { return $log['log_date']; }, $healthLogs)); ?>;
                    
                    const ctx = document.getElementById('stepsChart').getContext('2d');
                    const stepsChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: logDates,
                            datasets: [{
                                label: 'Steps',
                                data: stepsData,
                                borderColor: 'rgba(75, 192, 192, 1)',
                                fill: false
                            }]
                        }
                    });
                </script>
            <?php else: ?>
                <p>No health logs found.</p>
            <?php endif; ?>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Smart Health Tracker. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
