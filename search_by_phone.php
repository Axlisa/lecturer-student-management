<?php
session_start();
require 'config.php';

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];

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
    <title>Search by Phone Number</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="theme.js" defer></script> <!-- Include the theme.js file -->
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
            <h1>Search by Phone Number</h1>
            <a href="home.php" class="back-button">Back</a>
        </div>
        <form method="GET" action="search_by_phone.php">
            <div class="form-group full-width">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="Phone Number" required>
                <input type="submit" value="Search"></input>
            </div>
        </form>

        <?php
        if (isset($_GET['phone_number']) && !empty($_GET['phone_number'])) {
            $phone_number = $_GET['phone_number'];

            $students_query = $mysqli->prepare("SELECT students.* FROM students JOIN student_course ON students.id = student_course.student_id JOIN courses ON student_course.course_id = courses.id WHERE courses.lecturer_id = ? AND students.phone_number LIKE ?");
            $phone_number_like = $phone_number . "%"; // Match the start of the phone number
            $students_query->bind_param("is", $lecturer_id, $phone_number_like);
            $students_query->execute();
            $students_result = $students_query->get_result();

            if ($students_result->num_rows > 0) {
                echo "<table>";
                echo "<tr><th>Matric No</th><th>Full Name</th><th>Phone Number</th><th>Program Code</th></tr>";
                while ($student = $students_result->fetch_assoc()) {
                    echo "<tr><td>{$student['matric_no']}</td><td>{$student['full_name']}</td><td>{$student['phone_number']}</td><td>{$student['program_code']}</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No students found with this phone number.</p>";
            }
        }
        ?>
    </div>

    <script>
        function populateStudentData() {
            var studentSelect = document.getElementById('student_id');
            var selectedOption = studentSelect.options[studentSelect.selectedIndex];
            var studentData = JSON.parse(selectedOption.getAttribute('data-student'));

            document.getElementById('full_name').value = studentData.full_name;
            document.getElementById('phone_number').value = studentData.phone_number;
            document.getElementById('program_code').value = studentData.program_code;
        }

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
