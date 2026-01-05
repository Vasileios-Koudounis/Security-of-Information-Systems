<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form</title>
</head>
<body>
    <h3>New user registration</h3>

<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['username']) && $_SESSION['username'] !== '') {
    echo "<font color=red>You are already logged in!</font></br>";
    echo "Please <a href='logout.php'>logout</a> first";
    exit;
}

$login_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if(!isset($_POST['new_username'], $_POST['new_password']) || trim($_POST['new_username']) =='' || trim($_POST['new_password']) == '') {
        $login_message = "Missing username or password.";
    }
    else {
        // SECURITY FIX: Include the centralized connection file
        require_once 'config.php';

        $new_username = trim($_POST['new_username']);
        $new_password = trim($_POST['new_password']);

        // SECURITY FIX: Hash the password using a strong algorithm (bcrypt)
        // We never store the plain text password.
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // SECURITY FIX: Use Prepared Statements to prevent SQL Injection
        // We use ? placeholders instead of concatenating variables directly.
        $stmt = $conn->prepare("INSERT INTO login_users (username, password) VALUES (?, ?)");
        
        if ($stmt) {
            $stmt->bind_param("ss", $new_username, $hashed_password);

            try {
                if ($stmt->execute()) {
                    echo "<font color=red>Successful registration!</font>";
                    echo "<p />You can now use the <a href='login.php'>login</a> page";
                    exit;
                } else {
                    $login_message = "Error registering user.";
                }
            } catch (mysqli_sql_exception $e) {
                // Catch duplicate user error without exposing DB details
                $login_message = "Error, probably user already exists!";
            }
            $stmt->close();
        } else {
             $login_message = "Database error.";
        }

        $conn->close();
    }
}
?>

    <p/>
    <form method="POST" action="register.php">
        <input type="text" name="new_username" placeholder="Username"><br />
        <input type="password" name="new_password" placeholder="Password"><br />
        <button type="submit">Register</button>
    </form>
    <br />

    <?php
        if (!empty($login_message)) { 
            // SECURITY FIX: Sanitize output message (good practice)
            echo "<font color=red>" . htmlspecialchars($login_message) . "</font>";
            echo "<p />Go to the <a href='login.php'>login</a> page";
        }
    ?>
</body>
</html>