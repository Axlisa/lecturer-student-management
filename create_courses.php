<?php
session_start();
require 'config.php';

if (!isset($_SESSION['lecturer_id'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_code = strtoupper(str_replace(' ', '', $_POST['course_code']));
    $course_name = strtoupper($_POST['course_name']);
    $lecturer_id = $_SESSION['lecturer_id'];
    $generate_group = $_POST['generate_group'];

    if ($generate_group == 'yes') {
        // Fetch the highest group number for the course code and lecturer
        $group_query = "SELECT group_number FROM courses WHERE course_code = ? AND lecturer_id = ? ORDER BY LENGTH(group_number) DESC, group_number DESC LIMIT 1";
        $group_stmt = $pdo->prepare($group_query);
        $group_stmt->execute([$course_code, $lecturer_id]);
        $highest_group = $group_stmt->fetchColumn();

        if ($highest_group) {
            // Extract the number part from the highest group and increment it
            $highest_group_number = intval(substr($highest_group, 1)) + 1;
            $group_number = 'G' . $highest_group_number;
        } else {
            // If no group exists, start with G1
            $group_number = 'G1';
        }

        try {
            $query = "INSERT INTO courses (course_code, course_name, group_number, lecturer_id) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            if ($stmt->execute([$course_code, $course_name, $group_number, $lecturer_id])) {
                $message = "<div class='success-message'>Course created successfully with group number $group_number.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='error-message'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        // Check for duplicate course code and name without group number
        $duplicate_check_query = "SELECT COUNT(*) FROM courses WHERE course_code = ? AND lecturer_id = ? AND group_number IS NULL";
        $duplicate_check_stmt = $pdo->prepare($duplicate_check_query);
        $duplicate_check_stmt->execute([$course_code, $lecturer_id]);
        $duplicate_count = $duplicate_check_stmt->fetchColumn();

        if ($duplicate_count > 0) {
            $message = "<div class='error-message'>Error: Duplicate entry for course code without a group number.</div>";
        } else {
            try {
                $query = "INSERT INTO courses (course_code, course_name, lecturer_id) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($query);
                if ($stmt->execute([$course_code, $course_name, $lecturer_id])) {
                    $message = "<div class='success-message'>Course created successfully.</div>";
                }
            } catch (PDOException $e) {
                $message = "<div class='error-message'>Error: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// Fetch the lecturer's name
$lecturer_id = $_SESSION['lecturer_id'];
$query = "SELECT full_name FROM lecturers WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$lecturer_id]);
$lecturer = $stmt->fetch(PDO::FETCH_ASSOC);

$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light-mode';
$current_page = basename($_SERVER['PHP_SELF']); // Get the current page name
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Courses</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="theme.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="<?php echo htmlspecialchars($theme); ?>">
    <ul class="navbar">
        <li class="welcome-box">Welcome,<br> <?php echo isset($lecturer['full_name']) ? $lecturer['full_name'] : 'Lecturer'; ?></li>
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
        <h1>Create Courses</h1>
        <?php
        if ($message) {
            echo $message;
        }
        ?>
        <form method="POST" action="create_courses.php">
            <div class="form-group full-width">
                <label for="course_code">Course Code</label>
                <input type="text" id="course_code" name="course_code" placeholder="Course Code" required>
            </div>
            <div class="form-group full-width">
                <label for="course_name">Course Name</label>
                <input type="text" id="course_name" name="course_name" placeholder="Course Name" required>
            </div>
            <div class="form-group full-width">
                <label>Generate Group Number</label>
                <input type="radio" id="generate_yes" name="generate_group" value="yes" checked> Yes
                <input type="radio" id="generate_no" name="generate_group" value="no"> No
            </div>
            <input type="submit" value="Create Course"></input>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            // Ensure course code and group number have no spaces and are capitalized
            function capitalizeInput(input) {
                return input.toUpperCase().replace(/ /g, '');
            }

            // Ensure course name is capitalized but can have spaces
            function capitalizeNameInput(input) {
                return input.toUpperCase();
            }

            $('#course_code').on('input', function() {
                var courseCode = capitalizeInput($(this).val());
                $(this).val(courseCode);
                if (courseCode) {
                    $.ajax({
                        type: 'POST',
                        url: 'check_course_code.php',
                        data: { course_code: courseCode },
                        dataType: 'json',
                        success: function(response) {
                            if (response.course_name) {
                                $('#course_name').val(response.course_name);
                            } else {
                                $('#course_name').val('');
                            }
                        }
                    });
                }
            });

            $('#course_name').on('input', function() {
                var courseName = capitalizeNameInput($(this).val());
                $(this).val(courseName);
            });

            $('#generate_yes').on('change', function() {
                $('#group_number_group').hide();
            });

            $('#generate_no').on('change', function() {
                $('#group_number_group').show();
            });

            // Initialize the display based on the default radio selection
            if ($('#generate_yes').is(':checked')) {
                $('#group_number_group').hide();
            } else {
                $('#group_number_group').show();
            }
        });

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
