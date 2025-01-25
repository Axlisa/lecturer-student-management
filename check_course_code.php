<?php
require 'config.php';

if (isset($_POST['course_code'])) {
    $course_code = strtoupper(str_replace(' ', '', $_POST['course_code']));

    $query = "SELECT course_name FROM courses WHERE course_code = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$course_code]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($course) {
        echo json_encode(['course_name' => $course['course_name']]);
    } else {
        echo json_encode(['course_name' => '']);
    }
}
?>
