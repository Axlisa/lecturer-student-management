<?php
session_start();
require 'config.php';

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = strtoupper($_POST['username']);
    $password = $_POST['password'];

    $query = $mysqli->prepare("SELECT * FROM lecturers WHERE username = ?");
    $query->bind_param("s", $username);
    $query->execute();
    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $lecturer = $result->fetch_assoc();
        if (password_verify($password, $lecturer['password'])) {
            $_SESSION['lecturer_id'] = $lecturer['id'];
            header("Location: home.php");
            exit;
        } else {
            $error_message = "Invalid password.";
        }
    } else {
        $error_message = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lecturer Login</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

    <div class="container">
        <h1>Lecturer Login</h1>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <input type="text" name="username" id="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="submit" value="Login">
            <a href="register.php" class="button">Register</a><br>
            <a href="student_login.php" class="button">Student Module</a>
        </form>
    </div>

    <script>
        document.getElementById('username').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>
