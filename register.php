<?php
require 'config.php';

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = strtoupper($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $full_name = strtoupper($_POST['full_name']);

    // Check for duplicate entries
    $check_query = $mysqli->prepare("SELECT id FROM lecturers WHERE username = ?");
    $check_query->bind_param("s", $username);
    $check_query->execute();
    $check_result = $check_query->get_result();

    if ($check_result->num_rows > 0) {
        $error_message = "Error: Duplicate entry detected for Username.";
    } else {
        $query = $mysqli->prepare("INSERT INTO lecturers (username, password, full_name) VALUES (?, ?, ?)");
        $query->bind_param("sss", $username, $password, $full_name);

        if ($query->execute()) {
            $success_message = "Registration successful.";
        } else {
            $error_message = "Error: " . $query->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lecturer Registration</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Lecturer Registration</h1>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <input type="text" name="username" placeholder="Username" id="username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="text" name="full_name" placeholder="Full Name" id="full_name" required>
            <input type="submit" value="Register">
            <a href="login.php" class="button">Login</a>
        </form>
    </div>

    <script>
        document.getElementById('username').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        document.getElementById('full_name').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>
