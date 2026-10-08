<?php
// db.php - Database connection setup using PHP's native MySQLi extension

// 1. Define database server hostname (usually 'localhost' for local servers like XAMPP/WAMP)
$host = "localhost";

// 2. Database administrative username ('root' is default for local development)
$user = "root";

// 3. Database password (blank by default on local XAMPP/WAMP environments)
$password = "";

// 4. Target database schema name created in schema.sql
$database = "pGateway_schema";

// 5. Instantiate a new MySQLi connection object using parameters above
$conn = new mysqli($host, $user, $password, $database);

// 6. Check if the connection failed during initialization
if ($conn->connect_error) {
    // Terminate script execution immediately and display the connection error message
    die("Database Connection Failed: " . $conn->connect_error);
}

// If no errors occurred, $conn remains available to any file that includes 'db.php'
?>