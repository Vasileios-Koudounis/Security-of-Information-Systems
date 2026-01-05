<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
        table { border-collapse: collapse; width: 30%; border: 1px solid black; }
        td, tr { width: 50%; padding: 8px; text-align: left; }
    </style>
</head>

<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
require_once 'config.php';

// Insert new website
if(isset($_POST['new_website'], $_POST['new_username'], $_POST['new_password']) && 
   trim($_POST['new_website']) !='' && trim($_POST['new_username']) != '' && trim($_POST['new_password']) != '') {
    
    $new_website = trim($_POST["new_website"]);
    $new_username = trim($_POST["new_username"]);
    $new_password = trim($_POST["new_password"]);

    // SECURITY FIX: Prepared Statement with Subquery
    // Note: It is safer to use $_SESSION['user_id'] if available, but username works if unique.
    $stmt = $conn->prepare("INSERT INTO websites (login_user_id, web_url, web_username, web_password) VALUES ((SELECT id FROM login_users WHERE username=?), ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $new_website, $new_username, $new_password);
    $stmt->execute();
    $stmt->close();

    unset($_POST['new_website']);
    unset($_POST['new_username']);
    unset($_POST['new_password']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Delete website
if(isset($_POST['delete_website']) && isset($_POST["websiteid"]) && trim($_POST["websiteid"]) != '') {
    $webid = trim($_POST["websiteid"]);

    // SECURITY FIX: Prepared Statement & Access Control
    // We explicitly check that the 'login_user_id' matches the current user to prevent IDOR (deleting others' data)
    $stmt = $conn->prepare("DELETE FROM websites WHERE webid=? AND login_user_id=(SELECT id FROM login_users WHERE username=?)");
    $stmt->bind_param("is", $webid, $username);
    $stmt->execute();
    $stmt->close();

    unset($_POST['websiteid']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Display websites
// SECURITY FIX: Prepared Statement
$stmt = $conn->prepare("SELECT * FROM websites INNER JOIN login_users ON websites.login_user_id=login_users.id WHERE login_users.username=?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// SECURITY FIX: Output Encoding (Sanitization)
echo "<h3>Entries of " . htmlspecialchars($username) . "</h3>";

if ($result && $result->num_rows >= 1) {
    while ($row = $result->fetch_assoc()) {
        // SECURITY FIX: Prevent Stored XSS by encoding output
        // If a malicious script was somehow stored, this converts <script> to &lt;script&gt;
        $safe_url = htmlspecialchars($row["web_url"]);
        $safe_web_user = htmlspecialchars($row["web_username"]);
        $safe_web_pass = htmlspecialchars($row["web_password"]);
        $safe_id = htmlspecialchars($row["webid"]);

        echo "<table border=0>";
        echo    "<tr style='background-color: #f4f4f4;'><td colspan=2>" . $safe_url . "</td></tr>" . 
                "<tr><td>Username: " . $safe_web_user . "</td><td>Password: " . $safe_web_pass . "</td></tr>";

        echo    "<tr><td><form method='POST' style='height: 3px'>" . 
                "<input type='hidden' name='websiteid' value='" . $safe_id . "'>" .
                "<button type='submit' name='delete_website'>Delete</button></form></td></tr>";

        echo    "<tr><td colspan=2 style=height: 20px;></td></tr>";
        echo "</table><p/>";
    }
} else {
    echo "<p><font color=red>No entries found.</font></p>";
}

$stmt->close();
$conn->close();
?>

<body>
    <p/>
    <form method="POST" action="dashboard.php">
        <input type="text" name="new_website" placeholder="website"><br />
        <input type="text" name="new_username" placeholder="Username"><br />
        <input type="password" name="new_password" placeholder="Password"><br />
        <button type="submit">Insert new website</button>
    </form>
    <p/>
    <a href="notes.php">Notes - announcements</a>
    <p/>
    <a href="logout.php">Logout</a>
    <p/>
    <a href="index.html">Home page</a>
</body>
</html>