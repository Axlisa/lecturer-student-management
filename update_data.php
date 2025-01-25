<?php
session_start();
require 'config.php';

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$lecturer_id = $_SESSION['lecturer_id'];
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light-mode';

// Fetch the lecturer's name using PDO
$query = $pdo->prepare("SELECT full_name FROM lecturers WHERE id = :id");
$query->bindParam(':id', $lecturer_id);
$query->execute();
$lecturer = $query->fetch(PDO::FETCH_ASSOC);

// Fetch the lecturer's courses using PDO
$courses_query = $pdo->prepare("SELECT * FROM courses WHERE lecturer_id = :lecturer_id");
$courses_query->bindParam(':lecturer_id', $lecturer_id);
$courses_query->execute();
$courses_result = $courses_query->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $full_name = strtoupper($_POST['full_name']);
    $phone_number = $_POST['phone_number'];
    $program_code = strtoupper($_POST['program_code']);

    // Update student information using PDO
    $update_query = $pdo->prepare("UPDATE students SET full_name = :full_name, phone_number = :phone_number, program_code = :program_code WHERE id = :student_id");
    $update_query->bindParam(':full_name', $full_name);
    $update_query->bindParam(':phone_number', $phone_number);
    $update_query->bindParam(':program_code', $program_code);
    $update_query->bindParam(':student_id', $student_id);

    if ($update_query->execute()) {
        $success_message = "Student updated successfully.";
    } else {
        $error_message = "Error: " . $update_query->errorInfo()[2];
    }
}

$current_page = basename($_SERVER['PHP_SELF']); // Get the current page name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Data</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="theme.js"></script> <!-- Include the theme.js file -->
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
            <h1>Update Data</h1>
            <a href="home.php" class="back-button">Back</a>
        </div>
        <?php
        if (isset($success_message)) {
            echo "<div class='success-message'>{$success_message}</div>";
        } elseif (isset($error_message)) {
            echo "<div class='error-message'>{$error_message}</div>";
        }
        ?>
        <form method="GET" action="update_data.php">
            <div class="form-group full-width">
                <label for="course_id">Select Course</label>
                <select id="course_id" name="course_id" onchange="this.form.submit()">
                    <option value="">--Select Course--</option>
                    <?php foreach ($courses_result as $course) { ?>
                        <option value="<?php echo htmlspecialchars($course['id']); ?>" <?php echo (isset($_GET['course_id']) && $_GET['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($course['course_code'] . ' - ' . $course['course_name'] . ' (Group: ' . $course['group_number'] . ')'); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </form>

        <?php
        if (isset($_GET['course_id']) && !empty($_GET['course_id'])) {
            $course_id = $_GET['course_id'];

            // Fetch the students enrolled in the selected course using PDO
            $students_query = $pdo->prepare("SELECT students.* FROM students JOIN student_course ON students.id = student_course.student_id WHERE student_course.course_id = :course_id");
            $students_query->bindParam(':course_id', $course_id);
            $students_query->execute();
            $students_result = $students_query->fetchAll(PDO::FETCH_ASSOC);

            echo "<form method='POST' action='update_data.php'>";
            echo "<div class='form-group full-width'>";
            echo "<label for='student_id'>Select Student</label>";
            echo "<select id='student_id' name='student_id' onchange='populateStudentData()'>";
            echo "<option value=''>--Select Student--</option>";
            foreach ($students_result as $student) {
                $student_data = htmlspecialchars(json_encode($student), ENT_QUOTES, 'UTF-8');
                echo "<option value='{$student['id']}' data-student='{$student_data}'>{$student['full_name']}</option>";
            }
            echo "</select>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='full_name'>Full Name</label>";
            echo "<input type='text' id='full_name' name='full_name' placeholder='Full Name' required>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='phone_number'>Phone Number</label>";
            echo "<input type='text' id='phone_number' name='phone_number' placeholder='Phone Number' required>";
            echo "</div>";
            echo "<div class='form-group'>";
            echo "<label for='program_code'>Program Code</label>";
            echo "<input type='text' id='program_code' name='program_code' placeholder='Program Code' required>";
            echo "</div>";
            echo "<div class='form-group full-width'>";
            echo "<input type='submit' value='Update Student'></input>";
            echo "</div>";
            echo "</form>";
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
    </script>
</body>
</html>
