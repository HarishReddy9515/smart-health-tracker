<?php
session_start();
include 'db_connect.php'; // Include the database connection

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle form submission for profile and health data update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_email = $_POST['email'];
    $new_password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
    $blood_pressure = $_POST['blood_pressure'];
    $sleep_hours = $_POST['sleep_hours'];
    $weight = $_POST['weight'];

    // Begin transaction to ensure both profile and health data are updated atomically
    $conn->begin_transaction();

    try {
        // Update user profile email and password (if a new password is provided)
        if ($new_password) {
            $stmt = $conn->prepare("UPDATE users SET email = ?, password = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_email, $new_password, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $new_email, $user_id);
        }

        if (!$stmt->execute()) {
            throw new Exception("Error updating user profile.");
        }

        // Update health data in health_logs table
        $stmt2 = $conn->prepare("UPDATE health_logs SET blood_pressure = ?, sleep_hours = ?, weight = ? WHERE user_id = ? ORDER BY log_date DESC LIMIT 1");
        $stmt2->bind_param("ssdi", $blood_pressure, $sleep_hours, $weight, $user_id);

        if (!$stmt2->execute()) {
            throw new Exception("Error updating health data.");
        }

        // Commit transaction if both updates succeed
        $conn->commit();
        $message = "Profile and health data updated successfully!";
    } catch (Exception $e) {
        // Rollback transaction if any error occurs
        $conn->rollback();
        $message = "Error: " . $e->getMessage();
    }

    // Close statement
    $stmt->close();
    $stmt2->close();
}

// Fetch current user details
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();

// Fetch recent health data
$stmt = $conn->prepare("SELECT blood_pressure, sleep_hours, weight FROM health_logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($blood_pressure, $sleep_hours, $weight);
$stmt->fetch();
$stmt->close();
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
                <label>Username: <?= htmlspecialchars($username) ?></label><br>

                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required><br><br>

                <label>Password (Leave empty if not changing):</label>
                <input type="password" name="password"><br><br>

                <h2>Health Data</h2>
                <label>Blood Pressure (e.g., 120/80):</label>
                <input type="text" name="blood_pressure" value="<?= htmlspecialchars($blood_pressure) ?>" required><br><br>

                <label>Sleep Hours:</label>
                <input type="number" name="sleep_hours" value="<?= htmlspecialchars($sleep_hours) ?>" step="0.1" required><br><br>

                <label>Weight (kg):</label>
                <input type="number" name="weight" value="<?= htmlspecialchars($weight) ?>" step="0.1" required><br><br>

                <button type="submit">Update Profile</button>
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
