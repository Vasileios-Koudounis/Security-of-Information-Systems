<?php
// db_config.php

// SECURITY FIX: Centralized connection logic.
// We should use a limited database user (e.g., 'app_user') instead of 'root'.
$host = "localhost";
$user = "root";  // Change this to 'app_user' if you created one in HeidiSQL
$pass = "";      // Change this to the password you set for 'app_user'
$db   = "pwd_mgr";

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>