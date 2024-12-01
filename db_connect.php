<?php
$servername = "localhost";  // Usually "localhost" if using XAMPP
$username = "root";         // Default username for XAMPP is "root"
$password = "Harish09876@";             // Default password for XAMPP is empty
$dbname = "health_tracker"; // Change this to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

