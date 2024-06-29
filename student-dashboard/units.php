<?php
require_once 'auth.php';
$error_message = '';
$success_message = '';
try {
    require '../includes/connection.php';
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT program FROM registrations WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $student_program = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student_program) {
        if (isset($_POST["semester"])) {
            $program = $student_program['program'];
            $semester = $_POST['semester'];
            $selected_units = $_POST['selected_units'];

            $unit_names = array(
                'SIT 413' => 'Cloud Computing',
                'SIT 415' => 'Computer Project',
                'SIT 416' => 'ERP Systems',
                'SIT 414' => 'Internet of Things',
                'SIT 412' => 'Mobile Application Programming',
                'SIT 411' => 'Multimedia Systems',
                'SIT 413' => 'Cloud Computing',
                'SIT 421' => 'Big Data Analytics and Application',
                'SIT 422' => 'Contemporary Topics in IT',
                'SIT 423' => 'Real Time Systems',
                'SIT 424' => 'Accounting Information Systems',
                'CSC 441' => 'Knowledge Engineering and Society',
                'CSC 453' => 'Social Network Computing',
                'CIT 415' => 'Mobile Computing',
                'CIT 413' => 'Computer Architecture',
                'SIT 420' => 'DSS Systems',
                'SIT 314' => 'Database Systems',
                'SIT 312' => 'Graphics and Virtuality',
                'SIT 311' => 'Artificial Intelligence',
                'SIT 321' => 'Machine Learning',
                'SIT 322' => 'Distributed Systems',
                'SIT 323' => 'Information Systems',
                'SIT 324' => 'Inroduction to Programming',
                'CSC 341' => 'Web Development',
                'CSC 353' => 'Group Project'
            );

            $units_with_names = array();
            foreach ($selected_units as $unit_code) {
                if (isset($unit_names[$unit_code])) {
                    $units_with_names[$unit_code] = $unit_names[$unit_code];
                }
            }

            $unit_grades_array = array_fill_keys(array_keys($units_with_names), null);

            $unit_grades_json = json_encode($unit_grades_array);

            $stmt = $conn->prepare("SELECT id FROM student_units WHERE student_id = :user_id AND semester = :semester");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':semester', $semester);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $error_message = "You have already submitted units for this semester. You cannot submit again.";
            } else {
                $units_json = json_encode($units_with_names);

                $insert_stmt = $conn->prepare("INSERT INTO student_units (student_id, semester, program, units, unit_grades) VALUES (:user_id, :semester, :program, :units, :unit_grades)");
                $insert_stmt->bindParam(':user_id', $user_id);
                $insert_stmt->bindParam(':semester', $semester);
                $insert_stmt->bindParam(':program', $program);
                $insert_stmt->bindParam(':units', $units_json);
                $insert_stmt->bindParam(':unit_grades', $unit_grades_json);

                if ($insert_stmt->execute()) {
                    $success_message = "Units submitted successfully. Waiting for approval.";
                } else {
                    $error_message = "Error submitting units. Please try again.";
                }
            }
        }
    } else {
        $error_message = "Program information not found for the user.";
    }
} catch (PDOException $e) {
    $error_message =  "Database error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Student-dashboard - Units</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <style>
        .table-container {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
        }

        .unit-selection {
            display: none;
        }
    </style>
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
                    <a href="units.php" class="list-group-item list-group-item-action active">Units Registration</a>
                    <a href="unit_status.php" class="list-group-item list-group-item-action">Unit Status</a>
                    <a href="results.php" class="list-group-item list-group-item-action">Results</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="content">
                    <h2 class="mt-2">Units Registration</h2>
                    <?php
                    require '../includes/connection.php';

                    $user_id = $_SESSION['user_id'];
                    try {
                        $stmt = $conn->prepare("SELECT program FROM registrations WHERE id = :user_id");
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();
                        $student_program = $stmt->fetch();

                        if ($student_program['program'] == "IT") {
                    ?>
                            <form action="units.php" method="post">
                                <div class="form-group">
                                    <label for="semester">Select Semester:</label>
                                    <?php if ($error_message !== '') : ?>
                                        <div class="alert alert-danger" id="error_message"><?= $error_message ?></div>
                                    <?php endif; ?>

                                    <?php if ($success_message !== '') : ?>
                                        <div class="alert alert-success" id="success_message"><?= $success_message ?></div>
                                    <?php endif; ?>
                                    <select class="form-control mb-3" id="semester" name="semester">
                                        <option value="">Select Semester</option>
                                        <option value="Sem 1">Semester 1</option>
                                        <option value="Sem 2">Semester 2</option>
                                    </select>
                                </div>

                                <div class="card unit-selection" id="unitSelection1">
                                    <div class="card-body">
                                        <h4 class="card-title">Select Units for Semester 1</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 413" id="sit413">
                                                        <label class="form-check-label" for="sit413">SIT 413 - Cloud Computing</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 415" id="sit415">
                                                        <label class="form-check-label" for="sit415">SIT 415 - Computer Project</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 416" id="sit416">
                                                        <label class="form-check-label" for="sit416">SIT 416 - ERP Systems</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 414" id="sit414">
                                                        <label class="form-check-label" for="sit414">SIT 414 - Internet of Things</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 412" id="sit412">
                                                        <label class="form-check-label" for="sit412">SIT 412 - Mobile Application Programming</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 411" id="sit411">
                                                        <label class="form-check-label" for="sit411">SIT 411 - Multimedia Systems</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <button type="submit" class="btn btn-primary m-2">Submit</button>
                                </div>

                                <div class="card unit-selection" id="unitSelection2">
                                    <div class="card-body">
                                        <h4 class="card-title">Select Units for Semester 2</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 421" id="sit421">
                                                        <label class="form-check-label" for="sit421">SIT 421 - Big Data Analytics and Applications</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 422" id="sit422">
                                                        <label class="form-check-label" for="sit422">SIT 422 - Contemporary Topics in IT</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 423" id="sit423">
                                                        <label class="form-check-label" for="sit423">SIT 423 - Real Time Systems</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 424" id="sit424">
                                                        <label class="form-check-label" for="sit424">SIT 424 - Accounting Information Systems</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="CSC 441" id="csc441">
                                                        <label class="form-check-label" for="csc441">CSC 441 - Knowledge Engineering and Society</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="CSC 453" id="csc453">
                                                        <label class="form-check-label" for="csc453">CSC 453 - Social Network Computing</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary m-2">Submit</button>
                                </div>
                            </form>
                        <?php
                        } elseif ($student_program['program'] == "CS") {
                        ?>
                            <form action="units.php" method="post">
                                <div class="form-group">
                                    <label for="semester">Select Semester:</label>
                                    <?php if ($error_message !== '') : ?>
                                        <div class="alert alert-danger" id="error_message"><?= $error_message ?></div>
                                    <?php endif; ?>

                                    <?php if ($success_message !== '') : ?>
                                        <div class="alert alert-success" id="success_message"><?= $success_message ?></div>
                                    <?php endif; ?>
                                    <select class="form-control mb-3" id="semester" name="semester">
                                        <option value="">Select Semester</option>
                                        <option value="Sem 1">Semester 1</option>
                                        <option value="Sem 2">Semester 2</option>
                                    </select>
                                </div>

                                <div class="card unit-selection" id="unitSelection1">
                                    <div class="card-body">
                                        <h4 class="card-title">Select Units for Semester 1</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="CIT 413" id="cit413">
                                                        <label class="form-check-label" for="cit413">CIT 413 - Mobile Computing</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="CIT 415" id="cit415">
                                                        <label class="form-check-label" for="cit415">CIT 415 - Computer Architecture</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 420" id="sit420">
                                                        <label class="form-check-label" for="sit420">SIT 420 - DSS Systems</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 314" id="sit314">
                                                        <label class="form-check-label" for="sit314">SIT 314 - Database Systems</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 312" id="sit312">
                                                        <label class="form-check-label" for="sit312">SIT 312 - Graphics and Virtuality</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 311" id="sit311">
                                                        <label class="form-check-label" for="sit311">SIT 311 - Artificial Intelligence</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <button type="submit" class="btn btn-primary m-2">Submit</button>
                                </div>

                                <div class="card unit-selection" id="unitSelection2">
                                    <div class="card-body">
                                        <h4 class="card-title">Select Units for Semester 2</h4>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 321" id="sit321">
                                                        <label class="form-check-label" for="sit321">SIT 321 - Machine Learning</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 322" id="sit322">
                                                        <label class="form-check-label" for="sit322">SIT 322 - Distributed Systems</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 323" id="sit323">
                                                        <label class="form-check-label" for="sit323">SIT 323 - Information Systems</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="SIT 324" id="sit324">
                                                        <label class="form-check-label" for="sit324">SIT 324 - Introduction to Programming</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="CSC 341" id="csc341">
                                                        <label class="form-check-label" for="csc341">CSC 341 - Web Development</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card mb-3">
                                                    <div class="card-body">
                                                        <input type="checkbox" class="form-check-input" name="selected_units[]" value="CSC 353" id="csc353">
                                                        <label class="form-check-label" for="csc353">CSC 353 - Group Project</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary m-2">Submit</button>
                                </div>
                            </form>
                    <?php

                        } else {
                            $error_message = "program not found.";
                        }
                    } catch (PDOException $e) {
                        $error_message = "Database error: " . $e->getMessage();
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $("#error_message").fadeOut('slow');
                $("#success_message").fadeOut('slow');
            }, 3000);
        });
    </script>
    <script>
        document.getElementById('semester').addEventListener('change', function() {
            var unitSelection1 = document.getElementById('unitSelection1');
            var unitSelection2 = document.getElementById('unitSelection2');

            unitSelection1.style.display = 'none';
            unitSelection2.style.display = 'none';

            var submitButton1 = unitSelection1.querySelector('button');
            var submitButton2 = unitSelection2.querySelector('button');

            if (this.value === "Sem 1") {
                unitSelection1.style.display = 'block';
                unitSelection2.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                    checkbox.checked = false; // Uncheck all checkboxes in Semester 2
                });
                if (isAllChecked('unitSelection1')) {
                    submitButton1.disabled = false;
                } else {
                    submitButton1.disabled = true;
                }
            } else if (this.value === "Sem 2") {
                unitSelection2.style.display = 'block';
                unitSelection1.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                    checkbox.checked = false; // Uncheck all checkboxes in Semester 1
                });
                if (isAllChecked('unitSelection2')) {
                    submitButton2.disabled = false;
                } else {
                    submitButton2.disabled = true;
                }
            }
        });

        function isAllChecked(containerId) {
            var checkboxes = document.querySelectorAll('#' + containerId + ' input[type="checkbox"]');
            for (var i = 0; i < checkboxes.length; i++) {
                if (!checkboxes[i].checked) {
                    return false;
                }
            }
            return true;
        }

        function updateSubmitButton(containerId) {
            var submitButton = document.querySelector('#' + containerId + ' button');
            if (isAllChecked(containerId)) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }

        // Add event listeners to checkboxes
        var checkboxes1 = document.querySelectorAll('#unitSelection1 input[type="checkbox"]');
        var checkboxes2 = document.querySelectorAll('#unitSelection2 input[type="checkbox"]');

        checkboxes1.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateSubmitButton('unitSelection1');
            });
        });

        checkboxes2.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                updateSubmitButton('unitSelection2');
            });
        });
    </script>

</body>

</html>