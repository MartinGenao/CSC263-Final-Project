<?php
// Database connection settings
$servername = "localhost"; // Replace with your server address
$username = "root";        // Database username
$password = "a3uytvuk";    // Database password
$dbname = "PetSitter";      // Replace with your actual database name

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
