<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '/../.env');
$dotenv->load();
require_once 'auth.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['student_id'], $_POST['semester'])) {
        $student_id = $_POST['student_id'];
        $semester = $_POST['semester'];

        if ($semester === 'Sem 1' || $semester === 'Sem 2') {
            $gradesKey = "semester_grades";

            if (isset($_POST[$gradesKey]) && is_array($_POST[$gradesKey])) {
                $grades = $_POST[$gradesKey];

                require '../includes/connection.php';

                try {
                    // Update database
                    $stmt = $conn->prepare("UPDATE student_units SET unit_grades = :unit_grades, is_approved = 1 WHERE student_id = :student_id AND semester = :semester");
                    $stmt->bindParam(':student_id', $student_id);
                    $stmt->bindParam(':semester', $semester);
                    $unit_grades_json = json_encode($grades);
                    $stmt->bindParam(':unit_grades', $unit_grades_json);
                    $updateSuccess = $stmt->execute();

                    if ($updateSuccess) {
                        $response = ['success' => true, 'message' => 'Grades updated successfully'];

                        $stmt = $conn->prepare("SELECT email, first_name FROM registrations WHERE id = :student_id");
                        $stmt->bindParam(':student_id', $student_id);
                        $stmt->execute();
                        $studentData = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($studentData) {
                            $studentEmail = $studentData['email'];
                            $studentFirstName = $studentData['first_name'];

                            $mail = new PHPMailer(true);
                            $mail->isSMTP();
                            $mail->Host = $_ENV['SMTP_HOST'];
                            $mail->Port = $_ENV['SMTP_PORT'];
                            $mail->SMTPAuth = true;
                            $mail->Username = $_ENV['SMTP_USER'];
                            $mail->Password = $_ENV['SMTP_PASS'];
                            $mail->SMTPSecure = 'tls';

                            $mail->setFrom($_ENV['SMTP_USER'], 'Some College');
                            $mail->addAddress($studentEmail, $studentFirstName);
                            $mail->isHTML(true);

                            $mail->Subject = 'Grades Update Notification';
                            $mail->Body = '<html><body>';
                            $mail->Body .= '<p>Dear ' . $studentFirstName . ',</p>';
                            $mail->Body .= '<p>Your grades for ' . $semester . ' have been updated.</p>';
                            $mail->Body .= '<p>Please <a href="https://studentms.azurewebsites.net/login.php">click here</a> to log in and check your results.</p>';
                            $mail->Body .= '<p>Best regards,  some college</p>';
                            $mail->Body .= '</body></html>';

                            try {
                                $mail->send();
                            } catch (Exception $e) {
                                $response = ['success' => false, 'message' => 'Grades updated, but email sending failed'];
                            }
                        } else {
                            $response = ['success' => false, 'message' => 'No data found'];
                        }
                    } else {
                        $response = ['success' => false, 'message' => 'Error updating grades'];
                    }
                } catch (PDOException $e) {
                    $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
                }
            } else {
                $response = ['success' => false, 'message' => 'Invalid grades data'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid semester'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Invalid request'];
    }
} else {
    $response = ['success' => false, 'message' => 'Invalid request method'];
}

header('Content-Type: application/json');
echo json_encode($response);
