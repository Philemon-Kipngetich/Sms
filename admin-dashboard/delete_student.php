<?php
require_once 'auth.php';
require_once '../includes/connection.php';

if (isset($_POST['studentId'])) {
    $studentId = $_POST['studentId'];

    try {
        $conn->beginTransaction();

        $stmtDeleteUnits = $conn->prepare("DELETE FROM student_units WHERE student_id = :studentId");
        $stmtDeleteUnits->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $stmtDeleteUnits->execute();

        $stmtDeleteStudent = $conn->prepare("DELETE FROM registrations WHERE id = :studentId");
        $stmtDeleteStudent->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $stmtDeleteStudent->execute();

        $conn->commit();

        if ($stmtDeleteStudent->rowCount() > 0) {
            echo 'success';
        } else {
            echo 'error';
        }
    } catch (PDOException $e) {
        $conn->rollback();
        echo 'error';
    } finally {
        $conn = null;
    }
} else {
    echo 'error';
}
