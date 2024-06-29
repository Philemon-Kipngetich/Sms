<?php
require_once 'auth.php';
require '../includes/connection.php';
$error_message = '';
$success_message = '';

try {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT password FROM registrations WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        if (password_verify($currentPassword, $user['password'])) {
            if ($newPassword === $confirmPassword) {
                $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE registrations SET password = :new_password WHERE id = :user_id");
                $stmt->bindParam(':new_password', $newPasswordHash);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();

                $success_message = "Password changed successfully.";
            } else {
                $error_message = "New passwords do not match.";
            }
        } else {
            $error_message = "Incorrect current password.";
        }
    }
} catch (PDOException $e) {
    $error_message = "Database Connection Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Change Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand ms-2" href="#">Student Portal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon me-2"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="index.php" class="btn btn-info m-2">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-danger m-2">Logout</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container mt-3">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Change Password</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($error_message !== '') : ?>
                            <div class="alert alert-danger" id="error_message"><?= $error_message ?></div>
                        <?php endif; ?>

                        <?php if ($success_message !== '') : ?>
                            <div class="alert alert-success" id="success_message"><?= $success_message ?></div>
                        <?php endif; ?>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password:</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password:</label>
                                <div class="input-group">
                                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                                    <button type="button" class="btn btn-outline-secondary" id="toggle_new_password">Show</button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password:</label>
                                <div class="input-group">
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                    <button type="button" class="btn btn-outline-secondary" id="toggle_confirm_password">Show</button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Change Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("../footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        // Function to toggle password visibility
        function togglePasswordVisibility(inputId, toggleButtonId) {
            const inputElement = document.getElementById(inputId);
            const toggleButton = document.getElementById(toggleButtonId);

            if (inputElement.type === "password") {
                inputElement.type = "text";
                toggleButton.textContent = "Hide";
            } else {
                inputElement.type = "password";
                toggleButton.textContent = "Show";
            }
        }

        // Add click event listeners to toggle buttons
        document.getElementById("toggle_new_password").addEventListener("click", function() {
            togglePasswordVisibility("new_password", "toggle_new_password");
        });

        document.getElementById("toggle_confirm_password").addEventListener("click", function() {
            togglePasswordVisibility("confirm_password", "toggle_confirm_password");
        });
    </script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $("#error_message").fadeOut('slow');
                $("#success_message").fadeOut('slow');
            }, 3000);
        });
    </script>
</body>

</html>