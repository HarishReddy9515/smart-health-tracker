<?php
session_start();
include 'db_connect.php'; // Include your database connection

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Fetch health trends from the database (including blood pressure, sleep hours, and weight)
$stmt = $conn->prepare("SELECT log_date, steps, mood, water_intake, blood_pressure, sleep_hours, weight FROM health_logs WHERE user_id = ? ORDER BY log_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($log_date, $steps, $mood, $water_intake, $blood_pressure, $sleep_hours, $weight);

$healthTrends = [];
while ($stmt->fetch()) {
    $healthTrends[] = [
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
    <title>Health Trends</title>
    <link rel="stylesheet" href="style.css">
    <!-- Include Chart.js for Data Visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Page Header -->
    <header>
        <div class="container">
            <h1>Smart Health Tracker</h1>
            <nav>
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
        <section class="welcome">
            <h2>Welcome, <?= htmlspecialchars($username) ?>!</h2>
        </section>

        <!-- Health Trends Section -->
        <section class="health-trends">
            <h3>Your Health Trends</h3>

            <!-- Chart: Steps over Time -->
            <div class="chart-container">
                <canvas id="stepsChart"></canvas>
            </div>

            <!-- Chart: Sleep Hours over Time -->
            <div class="chart-container">
                <canvas id="sleepChart"></canvas>
            </div>

            <!-- Chart: Weight over Time -->
            <div class="chart-container">
                <canvas id="weightChart"></canvas>
            </div>

            <!-- Chart: Blood Pressure over Time -->
            <div class="chart-container">
                <canvas id="bpChart"></canvas>
            </div>

            <!-- Health Data Table -->
            <?php if (count($healthTrends) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Steps</th>
                            <th>Mood</th>
                            <th>Water Intake (L)</th>
                            <th>Blood Pressure</th>
                            <th>Sleep Hours</th>
                            <th>Weight (kg)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($healthTrends as $trend): ?>
                            <tr>
                                <td><?= htmlspecialchars($trend['log_date']) ?></td>
                                <td><?= htmlspecialchars($trend['steps']) ?></td>
                                <td><?= htmlspecialchars($trend['mood']) ?></td>
                                <td><?= htmlspecialchars($trend['water_intake']) ?></td>
                                <td><?= htmlspecialchars($trend['blood_pressure']) ?></td>
                                <td><?= htmlspecialchars($trend['sleep_hours']) ?></td>
                                <td><?= htmlspecialchars($trend['weight']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No health trends found. Please log your health data.</p>
            <?php endif; ?>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <p>&copy; 2024 Smart Health Tracker. All rights reserved.</p>
        </div>
    </footer>

    <!-- Script to generate the charts -->
    <script>
        // Prepare data for charts
        const labels = <?php echo json_encode(array_column($healthTrends, 'log_date')); ?>;
        const stepsData = <?php echo json_encode(array_column($healthTrends, 'steps')); ?>;
        const sleepData = <?php echo json_encode(array_column($healthTrends, 'sleep_hours')); ?>;
        const weightData = <?php echo json_encode(array_column($healthTrends, 'weight')); ?>;
        const bpData = <?php echo json_encode(array_column($healthTrends, 'blood_pressure')); ?>;

        // Create chart for steps over time
        const ctx1 = document.getElementById('stepsChart').getContext('2d');
        const stepsChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Steps',
                    data: stepsData,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { autoSkip: true, maxTicksLimit: 10 },
                    y: { beginAtZero: true }
                }
            }
        });

        // Create chart for sleep hours over time
        const ctx2 = document.getElementById('sleepChart').getContext('2d');
        const sleepChart = new Chart(ctx2, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sleep Hours',
                    data: sleepData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { autoSkip: true, maxTicksLimit: 10 },
                    y: { beginAtZero: true }
                }
            }
        });

        // Create chart for weight over time
        const ctx3 = document.getElementById('weightChart').getContext('2d');
        const weightChart = new Chart(ctx3, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Weight',
                    data: weightData,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { autoSkip: true, maxTicksLimit: 10 },
                    y: { beginAtZero: true }
                }
            }
        });

        // Create chart for blood pressure over time
        const ctx4 = document.getElementById('bpChart').getContext('2d');
        const bpChart = new Chart(ctx4, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Blood Pressure',
                    data: bpData,
                    borderColor: 'rgba(153, 102, 255, 1)',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: { autoSkip: true, maxTicksLimit: 10 },
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
