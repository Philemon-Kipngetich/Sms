<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel - Manage Students</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        #spinner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            display: none;
            font-size: 3rem;
        }
    </style>
</head>

<body>
    <div class="spinner" id="spinner">
        <i class="fas fa-spinner fa-spin"></i>
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand ms-2" href="#">Welcome, <?= $_SESSION["admin"] ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="change_password.php" class="btn btn-info m-2">Change Password</a>
                </li>
                <li class="nav-item">
                    <a href="add_admin.php" class="btn btn-success m-2">Add Admin</a>
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
                    <a href="manage_students.php" class="list-group-item list-group-item-action active">Manage Students</a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="content">
                    <h2 class="mt-2">Student Grades</h2>
                    <?php
                    require '../includes/connection.php';
                    try {
                        $stmt = $conn->prepare("SELECT id, first_name, admission_number FROM registrations");
                        $stmt->execute();

                        $mgstmt = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (count($mgstmt) > 0) {
                    ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>First Name</th>
                                            <th>Admission Number</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($mgstmt as $row) {
                                            echo "<tr>";
                                            echo "<td>" . $row["first_name"] . "</td>";
                                            echo "<td>" . $row["admission_number"] . "</td>";
                                            echo "<td><button class='btn btn-outline-primary btn-sm view-units-btn' data-student-id='" . $row["id"] . "' data-semester='Sem 1' data-student-name='" . $row["first_name"] . "'>Grade Semester 1</button></td>";
                                            echo "<td><button class='btn btn-outline-success btn-sm view-units-btn' data-student-id='" . $row["id"] . "' data-semester='Sem 2' data-student-name='" . $row["first_name"] . "'>Grade Semester 2</button></td>";

                                            echo "</tr>";
                                        }

                                        ?>
                                    </tbody>
                                </table>
                            </div>
                    <?php
                        } else {
                            echo "<p>No student(s) found.</p>";
                        }
                    } catch (PDOException $e) {
                        echo "Error: " . $e->getMessage();
                    }
                    ?>

                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="modal fade" id="unitsModal" tabindex="-1" role="dialog" aria-labelledby="unitsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unitsModalLabel">Grade Student</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        function showSpinner() {
            document.getElementById("spinner").style.display = "block";
        }

        // Function to hide the spinner
        function hideSpinner() {
            document.getElementById("spinner").style.display = "none";
        }
        document.querySelectorAll('.view-units-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                var studentId = this.getAttribute('data-student-id');
                var studentName = this.getAttribute('data-student-name');
                var semester = this.getAttribute('data-semester');
                showSpinner();
                document.querySelector('#unitsModal .modal-title').innerHTML = 'Grade ' + studentName + ' for ' + semester;

                var fetchUrl = 'get_student_units.php?student_id=' + studentId + '&semester=' + semester;

                fetch(fetchUrl)
                    .then(response => response.text())
                    .then(data => {
                        document.querySelector('#unitsModal .modal-body').innerHTML = data;
                        hideSpinner();
                        $('#unitsModal').modal('show');


                        $('#unitsModal form').submit(function(event) {
                            event.preventDefault();


                            var formData = $(this).serialize();

                            showSpinner();

                            $.ajax({
                                type: 'POST',
                                url: 'update_grades.php',
                                data: formData,
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        $('#unitsModal .modal-body').html('<p class="text-success">' + response.message + '</p>');

                                        setTimeout(function() {
                                            $('#unitsModal').modal('hide');
                                        }, 5000);
                                    } else {
                                        $('#unitsModal .modal-body').html('<p class="text-danger">' + response.message + '</p>');
                                    }
                                },
                                error: function() {
                                    $('#unitsModal .modal-body').html('<p class="text-danger">An error occurred</p>');
                                },
                                complete: function() {
                                    hideSpinner();
                                }
                            });
                        });
                    })
                    .catch(error => console.error('Error:', error));
                hideSpinner();
            });
        });
    </script>

</body>

</html>