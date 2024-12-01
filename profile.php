<?php
session_start();
include 'db_connect.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Fetch user details from the database
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

// Fetch recent health data (blood pressure, sleep hours, weight)
$stmt = $conn->prepare("SELECT blood_pressure, sleep_hours, weight FROM health_logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($blood_pressure, $sleep_hours, $weight);
$stmt->fetch();
$stmt->close();

// Handle form submission for updating profile and health data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate user input
    $newUsername = htmlspecialchars(trim($_POST['username']));
    $newEmail = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } else {
        // Update user details (username and email)
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newUsername, $newEmail, $user_id);

        if ($stmt->execute()) {
            // Sanitize and update health data (blood pressure, sleep hours, weight)
            $blood_pressure = htmlspecialchars(trim($_POST['blood_pressure']));
            $sleep_hours = floatval($_POST['sleep_hours']);
            $weight = floatval($_POST['weight']);

            $stmt2 = $conn->prepare("UPDATE health_logs 
                                     SET blood_pressure = ?, sleep_hours = ?, weight = ? 
                                     WHERE user_id = ? ORDER BY log_date DESC LIMIT 1");
            $stmt2->bind_param("ssdi", $blood_pressure, $sleep_hours, $weight, $user_id);
            
            if ($stmt2->execute()) {
                $message = "Profile and health data updated successfully!";
            } else {
                $message = "Error updating health data.";
            }
            $stmt2->close();
        } else {
            $message = "Error updating profile.";
        }
        $stmt->close();

        // Redirect to dashboard after successful update
        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Update Your Profile</h1>
        </div>
    </header>

    <div class="container">
        <section class="profile-update">
            <!-- Display success or error message -->
            <?php if ($message): ?>
                <p class="message"><?= htmlspecialchars($message) ?></p>
            <?php endif; ?>

            <form method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required><br><br>

                <h2>Health Data</h2>
                <label for="blood_pressure">Blood Pressure (e.g., 120/80):</label>
                <input type="text" id="blood_pressure" name="blood_pressure" value="<?= htmlspecialchars($blood_pressure) ?>" required><br>

                <label for="sleep_hours">Sleep Hours:</label>
                <input type="number" id="sleep_hours" name="sleep_hours" value="<?= htmlspecialchars($sleep_hours) ?>" step="0.1" required><br>

                <label for="weight">Weight (kg):</label>
                <input type="number" id="weight" name="weight" value="<?= htmlspecialchars($weight) ?>" step="0.1" required><br><br>

                <input type="submit" value="Update Profile">
            </form>

            <p><a href="dashboard.php">Back to Dashboard</a></p>
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
