<?php
require_once 'auth.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Student-dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
                    <a href="index.php" class="list-group-item list-group-item-action active">Dashboard</a>
                    <a href="units.php" class="list-group-item list-group-item-action">Units Registration</a>
                    <a href="unit_status.php" class="list-group-item list-group-item-action">Unit Status</a>
                    <a href="results.php" class="list-group-item list-group-item-action">Results</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="content">
                    <h2 class="mb-2 mt-2">Your Profile</h2>
                    <?php
                    require '../includes/connection.php';
                    $user_id = $_SESSION['user_id'];

                    try {
                        $stmt = $conn->prepare("SELECT admission_number, first_name, last_name, email, registration_date FROM registrations WHERE id = :user_id");
                        $stmt->bindParam(':user_id', $user_id);
                        $stmt->execute();
                        $user = $stmt->fetch();

                        if ($user) {
                            echo '<div class="card">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title">Profile Information</h5>';

                            // Add Bootstrap table classes for styling
                            echo '<table class="table table-bordered table-striped">';

                            echo '<thead>';
                            echo '<tr><th>Field</th><th>Value</th></tr>';
                            echo '</thead>';

                            echo '<tbody>';
                            echo '<tr><th>First Name</th><td>' . $user['first_name'] . '</td></tr>';
                            echo '<tr><th>Last Name</th><td>' . $user['last_name'] . '</td></tr>';
                            echo '<tr><th>Admission Number</th><td>' . $user['admission_number'] . '</td></tr>';
                            echo '<tr><th>Email</th><td>' . $user['email'] . '</td></tr>';
                            $formattedDate = date('d F, Y', strtotime($user['registration_date']));
                            echo '<tr><th>Admission Date</th><td>' . $formattedDate . '</td></tr>';

                            echo '</tbody>';

                            echo '</table>';
                            echo '</div>';
                            echo '</div>';
                        } else {
                            echo '<p>User not found.</p>';
                        }
                    } catch (PDOException $e) {
                        echo '<p>Database error: ' . $e->getMessage() . '</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</body>

</html>