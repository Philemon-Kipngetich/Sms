<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Student-dashboard - Status</title>
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
                    <a href="unit_status.php" class="list-group-item list-group-item-action active">Unit Status</a>
                    <a href="results.php" class="list-group-item list-group-item-action">Results</a>
                </div>
            </div>
            <!-- Main Content Area -->
            <div class="col-md-9">
                <div class="content">
                    <h2 class="mt-2">Unit Status</h2>
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
                        <form method="post" action="" name="statusForm">
                            <div class="form-group">
                                <label for="semesterSelect">Select Semester for Unit Status:</label>
                                <div class="d-flex">
                                    <select name="statusSemester" id="semesterSelect" class="form-control">
                                        <option value="Sem 1" <?php if (isset($_POST['statusSemester']) && $_POST['statusSemester'] == 'Sem 1') echo ' selected'; ?>>Semester 1</option>
                                        <option value="Sem 2" <?php if (isset($_POST['statusSemester']) && $_POST['statusSemester'] == 'Sem 2') echo ' selected'; ?>>Semester 2</option>
                                    </select>
                                    <input type="submit" name="viewStatus" value="View Status" class="btn btn-primary ms-2">
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php
                    if (isset($_POST['viewStatus'])) {
                        $selected_semester = $_POST['statusSemester'];
                        try {
                            $stmt = $conn->prepare("SELECT semester, units, is_approved FROM student_units WHERE student_id = :user_id AND semester = :selected_semester");
                            $stmt->bindParam(':user_id', $user_id);
                            $stmt->bindParam(':selected_semester', $selected_semester);
                            $stmt->execute();

                            // Fetch all rows into an array
                            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            // Count the number of rows
                            $rowCount = count($rows);

                            if ($rowCount > 0) {
                                // Rows were found, proceed with fetching and displaying data
                                echo '<h4 class="mt-3">Units and Approval Status for ' . htmlspecialchars($selected_semester) . '</h4>';
                                echo '<div class="table-responsive">';
                                echo '<table class="table table-bordered">';
                                echo '<thead><tr><th>Unit Code</th><th>Unit Name</th><th>Status</th></tr></thead>';
                                echo '<tbody>';

                                foreach ($rows as $row) {
                                    $unitsArray = json_decode($row['units'], true);
                                    if ($unitsArray !== null) {
                                        foreach ($unitsArray as $unitCode => $unitName) {
                                            $status = $row['is_approved'] ? 'Approved' : 'Not Approved';

                                            echo '<tr><td>' . $unitCode . '</td><td>' . $unitName . '</td><td>' . $status . '</td></tr>';
                                        }
                                    } else {
                                        echo '<p>Error decoding JSON data for units.</p>';
                                    }
                                }

                                echo '</tbody>';
                                echo '</table>';
                                echo '</div>';
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