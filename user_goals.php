<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit;
}

include 'db_connect.php'; // Include the database connection

// Handle form submission for setting goals
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $steps_goal = $_POST['steps_goal'];
    $calories_goal = $_POST['calories_goal'];
    $sleep_hours_goal = $_POST['sleep_hours_goal'];

    // Check if goals already exist
    $stmt = $conn->prepare("SELECT id FROM user_goals WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        // Update existing goals
        $stmt->close();
        $stmt = $conn->prepare("UPDATE user_goals SET steps_goal = ?, calories_goal = ?, sleep_hours_goal = ? WHERE user_id = ?");
        $stmt->bind_param("iiii", $steps_goal, $calories_goal, $sleep_hours_goal, $_SESSION['user_id']);
    } else {
        // Insert new goals
        $stmt->close();
        $stmt = $conn->prepare("INSERT INTO user_goals (user_id, steps_goal, calories_goal, sleep_hours_goal) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $_SESSION['user_id'], $steps_goal, $calories_goal, $sleep_hours_goal);
    }
    
    if ($stmt->execute()) {
        echo "<p>Goals set successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Fetch current user goals
$stmt = $conn->prepare("SELECT steps_goal, calories_goal, sleep_hours_goal FROM user_goals WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($steps_goal, $calories_goal, $sleep_hours_goal);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Goals</title>
</head>
<body>
    <h1>Set Your Health Goals</h1>
    <form method="POST">
        <label for="steps_goal">Steps Goal:</label>
        <input type="number" name="steps_goal" value="<?php echo htmlspecialchars($steps_goal); ?>" required><br>
        <label for="calories_goal">Calories Goal:</label>
        <input type="number" name="calories_goal" value="<?php echo htmlspecialchars($calories_goal); ?>" required><br>
        <label for="sleep_hours_goal">Sleep Hours Goal:</label>
        <input type="number" step="0.1" name="sleep_hours_goal" value="<?php echo htmlspecialchars($sleep_hours_goal); ?>" required><br>
        <button type="submit">Set Goals</button>
    </form>

    <footer>
        <p>&copy; 2024 Smart Health Tracker. All rights reserved.</p>
    </footer>
</body>
</html>
