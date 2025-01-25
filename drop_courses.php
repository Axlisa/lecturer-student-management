<?php
session_start();
require 'config.php';

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];
$courses_query = $mysqli->prepare("SELECT * FROM courses WHERE lecturer_id = ?");
$courses_query->bind_param("i", $lecturer_id);
$courses_query->execute();
$courses_result = $courses_query->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];

    // Deleting student_course relationships first
    $delete_student_course_query = $mysqli->prepare("DELETE FROM student_course WHERE course_id = ?");
    $delete_student_course_query->bind_param("i", $course_id);
    $delete_student_course_query->execute();

    // Deleting the course itself
    $delete_course_query = $mysqli->prepare("DELETE FROM courses WHERE id = ?");
    $delete_course_query->bind_param("i", $course_id);

    if ($delete_course_query->execute()) {
        $message = "<p class='success-message'>Course dropped successfully.</p>";
    } else {
        $message = "<p class='error-message'>Error: " . $delete_course_query->error . "</p>";
    }
}

// Fetch the lecturer's name
$query = $mysqli->prepare("SELECT full_name FROM lecturers WHERE id = ?");
$query->bind_param("i", $lecturer_id);
$query->execute();
$result = $query->get_result();
$lecturer = $result->fetch_assoc();

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light-mode';
$current_page = basename($_SERVER['PHP_SELF']); // Get the current page name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Drop Courses</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body class="<?php echo htmlspecialchars($theme); ?>">
    <ul class="navbar">
        <li class="welcome-box">Welcome,<br> <?php echo isset($lecturer['full_name']) ? htmlspecialchars($lecturer['full_name']) : 'Lecturer'; ?></li>
        <li><a href="home.php" <?php echo ($current_page == 'home.php') ? 'class="active"' : ''; ?>>Home</a></li>
        <li><a href="view_data.php" <?php echo ($current_page == 'view_data.php') ? 'class="active"' : ''; ?>>View Data</a></li>
        <li><a href="update_data.php" <?php echo ($current_page == 'update_data.php') ? 'class="active"' : ''; ?>>Update Data</a></li>
        <li><a href="choose_student.php" <?php echo ($current_page == 'choose_student.php') ? 'class="active"' : ''; ?>>Choose Student</a></li>
        <li><a href="search_by_phone.php" <?php echo ($current_page == 'search_by_phone.php') ? 'class="active"' : ''; ?>>Search by Phone Number</a></li>
        <li><a href="create_courses.php" <?php echo ($current_page == 'create_courses.php') ? 'class="active"' : ''; ?>>Create Courses</a></li>
        <li><a href="drop_courses.php" <?php echo ($current_page == 'drop_courses.php') ? 'class="active"' : ''; ?>>Drop Courses</a></li>
        <li class="right"><a href="logout.php">Logout</a></li>
        <li class="right toggle-box"><button class="dark-mode-toggle" id="toggle-theme">Toggle Dark Mode</button></li>
    </ul>

    <div class="container">
        <h1 class="drop-courses">Drop Courses</h1>
        <?php
        if (isset($message)) {
            echo $message;
        }
        ?>
        <form class="drop-courses" method="POST" action="drop_courses.php">
            <div class="form-group full-width">
                <label for="course_id">Select Course</label>
                <select id="course_id" name="course_id" required>
                    <?php while ($course = $courses_result->fetch_assoc()) { ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['course_code'] . " - " . $course['course_name'] . " (Group: " . $course['group_number'] . ")"); ?></option>
                    <?php } ?>
                </select>
                <input type="submit" value="Drop Course"></input>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleMode() {
            const currentTheme = document.body.classList.contains('dark-mode') ? 'dark-mode' : 'light-mode';
            const newTheme = currentTheme === 'light-mode' ? 'dark-mode' : 'light-mode';

            document.body.classList.remove(currentTheme);
            document.body.classList.add(newTheme);

            document.cookie = `theme=${newTheme}; path=/`;
        }

        document.getElementById('toggle-theme').addEventListener('click', toggleMode);

        document.addEventListener('DOMContentLoaded', function() {
            const theme = document.cookie.split('; ').find(row => row.startsWith('theme=')).split('=')[1];
            if (theme === 'dark-mode') {
                document.body.classList.add('dark-mode');
            }
        });
    </script>
</body>
</html>
