<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Admin Panel - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
</head>

<body>
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
                    <a href="index.php" class="list-group-item list-group-item-action active">Dashboard</a>
                    <a href="manage_students.php" class="list-group-item list-group-item-action">Manage students</a>
                </div>
            </div>
            <!-- Main Content -->
            <div class="col-md-9">
                <div class="content">
                    <h2 class="mb-2 mt-2">Dashboard</h2>
                    <?php
                    include_once '../includes/connection.php';

                    $stmtIT = $conn->prepare("SELECT COUNT(*) AS total_it_students FROM registrations WHERE program = 'IT'");
                    $stmtIT->execute();
                    $resultIT = $stmtIT->fetch(PDO::FETCH_ASSOC);
                    $totalITStudents = $resultIT['total_it_students'];

                    $stmtCS = $conn->prepare("SELECT COUNT(*) AS total_cs_students FROM registrations WHERE program = 'CS'");
                    $stmtCS->execute();
                    $resultCS = $stmtCS->fetch(PDO::FETCH_ASSOC);
                    $totalCSStudents = $resultCS['total_cs_students'];
                    ?>
                    <!-- Inside your HTML -->
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card bg-primary text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">IT Students</h5>
                                    <p class="card-text"><?= $totalITStudents ?></p>
                                    <a href="#" class="btn btn-info" id="showITForm">View IT Students</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">CS Students</h5>
                                    <p class="card-text"><?= $totalCSStudents ?></p>
                                    <a href="#" class="btn btn-info" id="showCSForm">View CS Students</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="itStudentsForm" style="display: none;">
                        <div class="col-md-12">
                            <h5>Information Technology Students</h5>
                            <?php
                            $stmtIT = $conn->prepare("SELECT id, admission_number, first_name, email FROM registrations WHERE program = 'IT'");
                            $stmtIT->execute();
                            $itStudents = $stmtIT->fetchAll(PDO::FETCH_ASSOC);

                            if (count($itStudents) > 0) {
                            ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered border-primary">
                                        <thead>
                                            <tr>
                                                <th>Admission Number</th>
                                                <th>First Name</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($itStudents as $student) {
                                                echo "<tr>";
                                                echo "<td>{$student['admission_number']}</td>";
                                                echo "<td>{$student['first_name']}</td>";
                                                echo "<td>{$student['email']}</td>";
                                                echo "<td><button class='btn btn-danger delete-student' data-student-id='{$student['id']}'>Delete</button></td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php
                            } else {
                                echo "<p>No students found.</p>";
                            }
                            ?>
                        </div>
                    </div>
                    <div class="row" id="csStudentsForm" style="display: none;">
                        <div class="col-md-12">
                            <h5>Computer Science Students</h5>
                            <?php
                            $stmtCS = $conn->prepare("SELECT id, admission_number, first_name, email FROM registrations WHERE program = 'CS'");
                            $stmtCS->execute();
                            $csStudents = $stmtCS->fetchAll(PDO::FETCH_ASSOC);

                            if (count($csStudents) > 0) {
                            ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered border-primary">
                                        <thead>
                                            <tr>
                                                <th>Admission Number</th>
                                                <th>First Name</th>
                                                <th>Email</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach ($csStudents as $student) {
                                                echo "<tr>";
                                                echo "<td>{$student['admission_number']}</td>";
                                                echo "<td>{$student['first_name']}</td>";
                                                echo "<td>{$student['email']}</td>";
                                                echo "<td><button class='btn btn-danger delete-student' data-student-id='{$student['id']}'>Delete</button></td>";
                                                echo "</tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php
                            } else {
                                echo "<p>No students found.</p>";
                            }
                            ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        document.getElementById('showITForm').addEventListener('click', function() {
            document.getElementById('itStudentsForm').style.display = 'block';
            document.getElementById('csStudentsForm').style.display = 'none';
        });

        document.getElementById('showCSForm').addEventListener('click', function() {
            document.getElementById('csStudentsForm').style.display = 'block';
            document.getElementById('itStudentsForm').style.display = 'none';
        });

        $('.delete-student').on('click', function() {
            const studentId = $(this).data('student-id');
            if (confirm('Are you sure you want to delete this student?')) {
                // Send an AJAX request to delete the student
                $.ajax({
                    url: 'delete_student.php',
                    method: 'POST',
                    data: {
                        studentId: studentId
                    },
                    success: function(response) {
                        if (response === 'success') {
                            location.reload();
                        } else {
                            alert('Error deleting student.');
                        }
                    },
                    error: function() {
                        alert('Error deleting student.');
                    }
                });
            }
        });
    </script>
</body>

</html>