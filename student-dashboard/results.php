<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Student-dashboard - Results</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand ms-2" href="#">Student Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="change_password.php" class="btn btn-info m-2">Change Password</a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-danger m-2">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3">
                <div class="list-group">
                    <a href="index.php" class="list-group-item list-group-item-action">Dashboard</a>
                    <a href="units.php" class="list-group-item list-group-item-action">Unit Registrations</a>
                    <a href="unit_status.php" class="list-group-item list-group-item-action">Unit Status</a>
                    <a href="results.php" class="list-group-item list-group-item-action active">Results</a>
                </div>
            </div>
            <!-- Main Content Area -->
            <div class="col-md-9">
                <div class="content">
                    <h2 class="mt-2">Results</h2>
                    <?php
                    require '../includes/connection.php';

                    $user_id = $_SESSION['user_id'];
                    try {
                        $stmt = $conn->prepare("SELECT program FROM registrations WHERE id = :user_id");
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();
                        $student_program = $stmt->fetch();
                    } catch (PDOException $e) {
                        echo '<p>Database error: ' . $e->getMessage() . '</p>';
                    }
                    ?>
                    <div class="col-md-6">
                        <form method="post" action="" name="resultsForm">
                            <div class="form-group">
                                <label for="resultSemesterSelect">Select Semester:</label>
                                <div class="d-flex">
                                    <select name="resultsSemester" id="resultSemesterSelect" class="form-control">
                                        <option value="Sem 1" <?php if (isset($_POST['resultsSemester']) && $_POST['resultsSemester'] == 'Sem 1') echo ' selected'; ?>>Semester 1</option>
                                        <option value="Sem 2" <?php if (isset($_POST['resultsSemester']) && $_POST['resultsSemester'] == 'Sem 2') echo ' selected'; ?>>Semester 2</option>
                                    </select>
                                    <input type="submit" name="viewResults" value="View Results" class="btn btn-primary ms-2">
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php
                    if (isset($_POST['viewResults'])) {
                        $selected_semester = $_POST['resultsSemester'];

                        try {
                            $stmt = $conn->prepare("SELECT semester, units, unit_grades, is_approved FROM student_units WHERE student_id = :user_id AND semester = :selected_semester");
                            $stmt->bindParam(':user_id', $user_id);
                            $stmt->bindParam(':selected_semester', $selected_semester);
                            $stmt->execute();

                            // Fetch all rows into an array
                            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // Count the number of rows
                            $rowCount = count($rows);

                            if ($rowCount > 0) {
                                $row = $rows[0];
                                $unitsJSON = $row['units'];
                                $unitGradesJSON = $row['unit_grades'];

                                if ($row['is_approved']) {
                                    $unitsArray = json_decode($unitsJSON, true);
                                    $unitGradesArray = json_decode($unitGradesJSON, true);

                                    if ($unitsArray !== null && $unitGradesArray !== null) {
                                        echo '<h4 class="mt-4">Results for ' . htmlspecialchars($selected_semester) . '</h4>';
                                        echo '<div class="table-responsive">';
                                        echo '<table class="table table-bordered">';
                                        echo '<thead><tr><th>Unit Code</th><th>Unit Name</th><th>Grade</th></tr></thead>';
                                        echo '<tbody>';

                                        foreach ($unitsArray as $unitCode => $unitName) {
                                            if (isset($unitGradesArray[$unitCode])) {
                                                $grade = $unitGradesArray[$unitCode];
                                                echo '<tr><td>' . $unitCode . '</td><td>' . $unitName . '</td><td>' . $grade . '</td></tr>';
                                            }
                                        }

                                        echo '</tbody>';
                                        echo '</table>';
                                        echo '</div>';

                                        echo '<form method="post" action="download_results.php">';
                                        echo '<input type="hidden" name="semester" value="' . htmlspecialchars($selected_semester) . '">';
                                        echo '<button type="submit" class="btn btn-success mb-2">Print Results</button>';
                                        echo '</form>';
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
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>