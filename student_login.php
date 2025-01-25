<?php
session_start();
require 'config.php';

// Fetch available programs
$programs_query = $mysqli->query("SELECT program_code, program_name FROM program_codes");

// Fetch available lecturers
$lecturers_query = $mysqli->query("SELECT id, full_name FROM lecturers");

// Fetch courses based on lecturer
function fetch_courses($lecturer_id) {
    global $mysqli;
    $courses_query = $mysqli->prepare("SELECT id, course_code, course_name, group_number FROM courses WHERE lecturer_id = ?");
    $courses_query->bind_param("i", $lecturer_id);
    $courses_query->execute();
    return $courses_query->get_result();
}

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['step'])) {
        if ($_POST['step'] == 'lecturer') {
            // Save lecturer and student details in session
            $_SESSION['lecturer_id'] = $_POST['lecturer_id'];
            $_SESSION['matric_no'] = strtoupper($_POST['matric_no']);
            $_SESSION['full_name'] = strtoupper($_POST['full_name']);
            $_SESSION['phone_number'] = strtoupper($_POST['phone_number']);
            $_SESSION['program_code'] = $_POST['program_code'];
            
            // Redirect to course selection step
            header("Location: student_login.php?step=course");
            exit;
        }

        if ($_POST['step'] == 'course') {
            // Save selected courses in session
            $_SESSION['course_ids'] = $_POST['course_ids'];
            
            // Redirect to registration step
            header("Location: student_login.php?step=register");
            exit;
        }

        if ($_POST['step'] == 'register') {
            // Retrieve student details from session
            $matric_no = $_SESSION['matric_no'];
            $full_name = $_SESSION['full_name'];
            $phone_number = $_SESSION['phone_number'];
            $program_code = $_SESSION['program_code'];
            $course_ids = $_SESSION['course_ids'];
            $lecturer_id = $_SESSION['lecturer_id'];

            // Check for duplicate entries
            $check_query = $mysqli->prepare("SELECT id FROM students WHERE matric_no = ? OR full_name = ? OR phone_number = ?");
            $check_query->bind_param("sss", $matric_no, $full_name, $phone_number);
            $check_query->execute();
            $check_result = $check_query->get_result();

            if ($check_result->num_rows > 0) {
                $error_message = "Error: Duplicate entry detected for Matric No, Full Name, or Phone Number.";
            } else {
                $query = $mysqli->prepare("INSERT INTO students (matric_no, full_name, phone_number, program_code) VALUES (?, ?, ?, ?)");
                $query->bind_param("ssss", $matric_no, $full_name, $phone_number, $program_code);

                if ($query->execute()) {
                    $student_id = $mysqli->insert_id;

                    foreach ($course_ids as $course_id) {
                        // Insert student-course relationship
                        $enroll_query = $mysqli->prepare("INSERT INTO student_course (student_id, course_id, lecturer_id) VALUES (?, ?, ?)");
                        $enroll_query->bind_param("iii", $student_id, $course_id, $lecturer_id);
                        $enroll_query->execute();
                    }

                    $success_message = "Student registered and enrolled in courses successfully.";
                } else {
                    $error_message = "Error: " . $query->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Module</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <script src="path/to/theme.js"></script>

    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .full-width {
            grid-column: span 2;
        }
        .course-checkboxes label {
            display: block;
        }
        .lecturer-section {
            text-align: center;
            margin: 20px 0;
        }
        .course-selection {
            text-align: center;
            margin-top: 20px;
        }
    </style>
    <script>
        function convertToUpperCase(element) {
            element.value = element.value.toUpperCase();
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Module</h1>
            <a href="login.php" class="back-button">Back</a>
        </div>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (!isset($_GET['step']) || $_GET['step'] == 'lecturer'): ?>
            <!-- Step 1: Enter Student Details and Choose Lecturer -->
            <form method="POST" action="student_login.php">
                <input type="hidden" name="step" value="lecturer">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" required oninput="convertToUpperCase(this)">
                    </div>
                    <div class="form-group">
                        <label for="matric_no">Matric No</label>
                        <input type="text" id="matric_no" name="matric_no" required oninput="convertToUpperCase(this)">
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Phone Number</label>
                        <input type="text" id="phone_number" name="phone_number" required oninput="convertToUpperCase(this)">
                    </div>
                    <div class="form-group">
                        <label for="program_code">Program Code</label>
                        <select id="program_code" name="program_code" required>
                            <?php while ($program = $programs_query->fetch_assoc()) { ?>
                                <option value="<?php echo $program['program_code']; ?>">
                                    <?php echo htmlspecialchars($program['program_code'] . ' – ' . $program['program_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group full-width lecturer-section">
                        <label for="lecturer_id">Select Lecturer</label>
                        <select id="lecturer_id" name="lecturer_id" required>
                            <?php while ($lecturer = $lecturers_query->fetch_assoc()) { ?>
                                <option value="<?php echo $lecturer['id']; ?>">
                                    <?php echo htmlspecialchars($lecturer['full_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <input type="submit" value="Choose Courses">
                    </div>
                </div>
            </form>
        <?php elseif ($_GET['step'] == 'course'): ?>
            <!-- Step 2: Choose Courses -->
            <form method="POST" action="student_login.php">
                <input type="hidden" name="step" value="course">
                <div class="course-selection">
                    <p>Select Courses offered by the selected lecturer:</p>
                    <div class="course-checkboxes">
                        <?php
                        // Fetch courses based on selected lecturer
                        $lecturer_id = $_SESSION['lecturer_id'];
                        $courses = fetch_courses($lecturer_id);
                        while ($course = $courses->fetch_assoc()) { ?>
                            <label>
                                <input type="checkbox" name="course_ids[]" value="<?php echo $course['id']; ?>"> 
                                <?php echo htmlspecialchars($course['course_code'] . " - " . $course['course_name'] . " (Group: " . $course['group_number'] . ")"); ?>
                            </label><br>
                        <?php } ?>
                    </div>
                    <input type="submit" value="Register">
                </div>
            </form>
        <?php elseif ($_GET['step'] == 'register'): ?>
            <!-- Step 3: Register -->
            <form method="POST" action="student_login.php">
                <input type="hidden" name="step" value="register">
                <div class="form-group full-width">
                    <p>You have selected the following courses:</p>
                    <ul>
                        <?php
                        foreach ($_SESSION['course_ids'] as $course_id) {
                            $course_query = $mysqli->prepare("SELECT course_code, course_name, group_number FROM courses WHERE id = ?");
                            $course_query->bind_param("i", $course_id);
                            $course_query->execute();
                            $course_result = $course_query->get_result();
                            $course = $course_result->fetch_assoc();
                            echo "<li>" . htmlspecialchars($course['course_code'] . " - " . $course['course_name'] . " (Group: " . $course['group_number'] . ")") . "</li>";
                        }
                        ?>
                    </ul>
                </div>
                <div class="form-group full-width">
                    <input type="submit" value="Confirm Registration">
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
