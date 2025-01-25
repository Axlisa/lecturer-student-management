<?php
session_start();
require 'config.php';

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

// Fetch the lecturer's name using MySQLi Procedural
$query = "SELECT full_name FROM lecturers WHERE id = ?";
$stmt = mysqli_prepare($mysqli_proc, $query);
mysqli_stmt_bind_param($stmt, "i", $lecturer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$lecturer = mysqli_fetch_assoc($result);
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light-mode';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="theme.js"></script> <!-- Include the theme.js file -->
</head>
<body class="<?php echo htmlspecialchars($theme); ?>">
    <ul class="navbar">
        <li class="welcome-box">Welcome,<br> <?php echo htmlspecialchars($lecturer['full_name']); ?></li>
        <li><a href="home.php">Home</a></li>
        <li><a href="view_data.php">View Data</a></li>
        <li><a href="update_data.php">Update Data</a></li>
        <li><a href="choose_student.php">Choose Student</a></li>
        <li><a href="search_by_phone.php">Search by Phone Number</a></li>
        <li><a href="create_courses.php">Create Courses</a></li>
        <li><a href="drop_courses.php">Drop Courses</a></li>
        <li class="right"><a href="logout.php">Logout</a></li>
        <li class="right toggle-box"><button id="toggle-theme" class="dark-mode-toggle">Toggle Dark Mode</button></li>
    </ul>

    <div class="container">
        <h1>Home</h1>
        <p>Welcome to the Lecturer Dashboard. Please use the navigation menu to access different functionalities.</p>
    </div>
</body>
</html>
