<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
</head>

<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_SESSION['username']) && $_SESSION['username'] !== '') {
    header("Location: dashboard.php");
    exit;
}

$login_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if(!isset($_POST['username'], $_POST['password']) || trim($_POST['username']) =='' || trim($_POST['password']) == '') {
        $login_message = "Missing username or password.";
    }
    else {
        require_once 'config.php';

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // SECURITY FIX: Use Prepared Statements (Prevents ' OR 1=1 SQLi)
        $stmt = $conn->prepare("SELECT id, username, password FROM login_users WHERE username = ?");
        
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                
                // SECURITY FIX: Verify the hash instead of comparing plain text strings
                if (password_verify($password, $row['password'])) {
                    
                    // SECURITY FIX: Regenerate Session ID to prevent Session Fixation attacks
                    session_regenerate_id(true);

                    $_SESSION['username'] = $row['username'];
                    $_SESSION['loggedin'] = true;
                    // Store ID to use it in other queries safely
                    $_SESSION['user_id'] = $row['id']; 

                    header("Location: dashboard.php");
                    exit;
                } else {
                    $login_message = "Invalid username or password";
                }
            } else {
                $login_message = "Invalid username or password";
            }
            $stmt->close();
        } else {
            $login_message = "Database error";
        }
        $conn->close();
    }
}
?>

<body>
    <h3>Password Manager</h3>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required><br />
        <input type="password" name="password" placeholder="Password"><br />
        <button type="submit">Login</button>
    </form>
    <br />
    <?php if (!empty($login_message)) { echo "<font color=red>" . htmlspecialchars($login_message) . "</font>"; } ?>
    <p/>
    <a href="register.php">Register new user</a>
    <p/>
    <a href="index.html">Home page</a>
</body>
</html>