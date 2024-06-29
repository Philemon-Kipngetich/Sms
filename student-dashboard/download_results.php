<?php
require_once 'auth.php';
require '../includes/connection.php';
require_once __DIR__."/fpdf/fpdf.php";
// Mapping of program abbreviations to full names
$programAbbreviations = array(
    'IT' => 'Bsc. Information Technology',
    'CS' => 'Bsc. Computer Science'
);

if (isset($_POST['semester'])) {
    $selected_semester = $_POST['semester'];
    $user_id = $_SESSION['user_id'];

    try {
        // Fetch student information from the registration table
        $stmt = $conn->prepare("SELECT first_name, last_name, program, admission_number FROM registrations WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $studentInfo = $stmt->fetch();

        // Combine first_name and last_name into a full name
        $fullName = $studentInfo['first_name'] . ' ' . $studentInfo['last_name'];

        // Check if the abbreviation exists in the mapping, and if so, replace it with the full name
        if (isset($studentInfo['program']) && isset($programAbbreviations[$studentInfo['program']])) {
            $fullProgramName = $programAbbreviations[$studentInfo['program']];
        } else {
            // If the abbreviation is not found in the mapping, use the original abbreviation
            $fullProgramName = $studentInfo['program'];
        }

        $stmt = $conn->prepare("SELECT semester, units, unit_grades, is_approved FROM student_units WHERE student_id = :user_id AND semester = :selected_semester");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':selected_semester', $selected_semester);
        $stmt->execute();

        $result = $stmt->fetchAll();
        $rowCount = count($result);

        if ($rowCount > 0) {
            $row = $result[0];
            $unitsJSON = $row['units'];
            $unitGradesJSON = $row['unit_grades'];

            if ($row['is_approved']) {
                $unitsArray = json_decode($unitsJSON, true);
                $unitGradesArray = json_decode($unitGradesJSON, true);

                if ($unitsArray !== null && $unitGradesArray !== null) {
                    // Create a PDF object using FPDF
                    $pdf = new FPDF();
                    $pdf->AddPage();

                    // Add school logo and name (Modify the paths and coordinates accordingly)
                    $pdf->Image('../images/uoemlogo.png', 90, 10, 30);
                    $pdf->Cell(190, 30, '', 0, 1, 'C');
                    $pdf->SetFont('Arial', 'B', 16);
                    $pdf->Cell(190, 10, 'University of Embu', 0, 1, 'C');

                    // Add student name, admission number, and program enrolled
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(190, 10, '', 0, 1, 'C'); // Add a line break
                    $pdf->Cell(190, 10, 'Student Name: ' . $fullName, 0, 1, 'L');
                    $pdf->Cell(190, 10, 'Admission Number: ' . $studentInfo['admission_number'], 0, 1, 'L');
                    $pdf->Cell(190, 10, 'Program Enrolled: ' . $fullProgramName, 0, 1, 'L');
                    $pdf->Cell(190, 10, '', 0, 1, 'C'); // Add a line break

                    $pdf->Cell(190, 10, $selected_semester . ' Results', 0, 1, 'L');

                    // Create a table header
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(30, 10, 'Unit Code', 1);
                    $pdf->Cell(90, 10, 'Unit Name', 1);
                    $pdf->Cell(30, 10, 'Grade', 1);
                    $pdf->Ln();

                    $pdf->SetFont('Arial', '', 12);
                    foreach ($unitsArray as $unitCode => $unitName) {
                        if (isset($unitGradesArray[$unitCode])) {
                            $grade = $unitGradesArray[$unitCode];
                            $pdf->Cell(30, 10, $unitCode, 1);
                            $pdf->Cell(90, 10, $unitName, 1);
                            $pdf->Cell(30, 10, $grade, 1);
                            $pdf->Ln();
                        }
                    }

                    // Set the PDF name and headers for download
                    $pdfName = 'results_' . $selected_semester . '.pdf';
                    $pdf->Output($pdfName, 'D');
                } else {
                    echo '<p>Error decoding JSON data for units or unit grades.</p>';
                }
            } else {
                echo '<p>' . htmlspecialchars($selected_semester) . ' is not approved. Results cannot be displayed.</p>';
            }
        } else {
            echo '<p>No unit records found for ' . htmlspecialchars($selected_semester) . '.</p>';
        }
    } catch (PDOException $e) {
        echo '<p>Database error: ' . $e->getMessage() . '</p>';
    }
}
