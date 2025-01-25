<?php
session_start();
require 'config.php';

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

// Fetch the lecturer's name using MySQLi Object-Oriented
$query = $mysqli->prepare("SELECT full_name FROM lecturers WHERE id = ?");
$query->bind_param("i", $lecturer_id);
$query->execute();
$result = $query->get_result();
$lecturer = $result->fetch_assoc();

// Fetch the lecturer's courses using MySQLi Object-Oriented
$courses_query = $mysqli->prepare("SELECT * FROM courses WHERE lecturer_id = ?");
$courses_query->bind_param("i", $lecturer_id);
$courses_query->execute();
$courses_result = $courses_query->get_result();

$current_page = basename($_SERVER['PHP_SELF']); // Get the current page name

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light-mode';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Data</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="theme.js" defer></script> <!-- Ensure the theme.js file is included -->
</head>
<body class="<?php echo htmlspecialchars($theme); ?>">
    <ul class="navbar">
        <li class="welcome-box">Welcome,<br> <?php echo htmlspecialchars($lecturer['full_name']); ?></li>
        <li><a href="home.php" <?php echo ($current_page == 'home.php') ? 'class="active"' : ''; ?>>Home</a></li>
        <li><a href="view_data.php" <?php echo ($current_page == 'view_data.php') ? 'class="active"' : ''; ?>>View Data</a></li>
        <li><a href="update_data.php" <?php echo ($current_page == 'update_data.php') ? 'class="active"' : ''; ?>>Update Data</a></li>
        <li><a href="choose_student.php" <?php echo ($current_page == 'choose_student.php') ? 'class="active"' : ''; ?>>Choose Student</a></li>
        <li><a href="search_by_phone.php" <?php echo ($current_page == 'search_by_phone.php') ? 'class="active"' : ''; ?>>Search by Phone Number</a></li>
        <li><a href="create_courses.php" <?php echo ($current_page == 'create_courses.php') ? 'class="active"' : ''; ?>>Create Courses</a></li>
        <li><a href="drop_courses.php" <?php echo ($current_page == 'drop_courses.php') ? 'class="active"' : ''; ?>>Drop Courses</a></li>
        <li class="right"><a href="logout.php">Logout</a></li>
        <li class="right toggle-box"><button id="toggle-theme" class="dark-mode-toggle">Toggle Dark Mode</button></li>
    </ul>

    <div class="container">
        <div class="header">
            <h1>View Data</h1>
            <a href="home.php" class="back-button">Back</a>
        </div>
        <form method="GET" action="view_data.php">
            <div class="form-group full-width">
                <label for="course_id">Select Course</label>
                <select id="course_id" name="course_id">
                    <?php while ($course = $courses_result->fetch_assoc()) { ?>
                        <option value="<?php echo htmlspecialchars($course['id']); ?>">
                            <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name'] . ' (Group: ' . $course['group_number'] . ')'); ?>
                        </option>
                    <?php } ?>
                </select>
                <input type="submit" value="View Students">
            </div>
        </form>

        <?php
        if (isset($_GET['course_id']) && !empty($_GET['course_id'])) {
            $course_id = $_GET['course_id'];

            // Fetch the students enrolled in the selected course using MySQLi Object-Oriented
            $students_query = $mysqli->prepare("SELECT students.* FROM students JOIN student_course ON students.id = student_course.student_id WHERE student_course.course_id = ?");
            $students_query->bind_param("i", $course_id);
            $students_query->execute();
            $students_result = $students_query->get_result();

            if ($students_result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Matric No</th><th>Full Name</th><th>Phone Number</th><th>Program Code</th></tr>";
                while ($student = $students_result->fetch_assoc()) {
                    echo "<tr><td>" . htmlspecialchars($student['matric_no']) . "</td><td>" . htmlspecialchars($student['full_name']) . "</td><td>" . htmlspecialchars($student['phone_number']) . "</td><td>" . htmlspecialchars($student['program_code']) . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No students found for this course.</p>";
            }
        }
        ?>
    </div>
</body>
</html>
