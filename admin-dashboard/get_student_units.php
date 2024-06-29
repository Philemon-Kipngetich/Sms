<?php
require_once 'auth.php';
$possibleGrades = ['A', 'B', 'C', 'D', 'F'];

if (isset($_GET['student_id'], $_GET['semester'])) {
    $student_id = $_GET['student_id'];
    $semester = $_GET['semester'];

    require '../includes/connection.php';

    try {
        $stmt = $conn->prepare("SELECT semester, program, unit_grades FROM student_units WHERE student_id = :student_id AND semester = :semester");
        $stmt->bindParam(':student_id', $student_id);
        $stmt->bindParam(':semester', $semester);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $semesterName = $result['semester'];
            $program = $result['program'];
            $unit_grades_json = $result['unit_grades'];

            $unit_grades_array = json_decode($unit_grades_json, true);

            echo "<h4>$semesterName</h4>";
            echo "<p>Program: $program</p>";
            echo "<form action='update_grades.php' method='post'>";
            echo "<input type='hidden' name='student_id' value='$student_id'>";
            echo "<input type='hidden' name='semester' value='$semester'>";
            echo "<div class='form-group'>";
            echo "<label for='programUnits'>Unit Grades:</label>";
            echo "<ul id='programUnits' class='list-group'>";
            foreach ($unit_grades_array as $unit => $existingGrade) {
                echo "<li class='list-group-item'>";
                echo "$unit: ";
                echo "<select name='semester_grades[$unit]' class='form-select'>";
                echo "<option value=''>--Select--</option>";
                foreach ($possibleGrades as $possibleGrade) {
                    $selected = ($existingGrade === $possibleGrade) ? 'selected' : '';
                    echo "<option value='$possibleGrade' $selected>$possibleGrade</option>";
                }
                echo "</select>";
                echo "</li>";
            }

            echo "</ul>";
            echo "</div>";
            echo "<button type='submit' class='btn btn-primary mt-3'>Submit Grades</button>";
            echo "</form>";
        } else {
            echo "<h4>Data not found for $semester</h4>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
