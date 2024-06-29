<?php
session_start();
if (isset($_SESSION["user_id"])) {
    header("Location: student-dashboard/index.php");
    exit();
}
require 'includes/connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admission_number = $_POST['admission-number'];
    $password = $_POST['password'];

    try {
        $stmt_check_admission = $conn->prepare("SELECT id FROM registrations WHERE admission_number = :admission_number");
        $stmt_check_admission->bindParam(':admission_number', $admission_number);
        $stmt_check_admission->execute();
        $admission_exists = $stmt_check_admission->fetchColumn();

        if ($admission_exists) {
            $stmt = $conn->prepare("SELECT id, admission_number, last_name, password FROM registrations WHERE admission_number = :admission_number");
            $stmt->bindParam(':admission_number', $admission_number);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['last_activity'] = time();
                echo json_encode(["success" => "Login successful."]);
                exit();
            } else {
                echo json_encode(["error" => "Invalid admission number or password."]);
                exit();
            }
        } else {
            echo json_encode(["error" => "Admission number does not exist."]);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => "Database error: " . $e->getMessage()]);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include('header.php'); ?>


    <section class="login-form py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title text-center">Student Login</h2>
                            <p class="card-text text-center">Enter your admission number and password to log in.</p>
                            <div class="alert alert-success alert-dismissible fade show" id="success-message" style="display: none;"></div>
                            <div class="alert alert-danger alert-dismissible fade show" id="error-message" style="display: none;"></div>
                            <form method="POST" id="login-form">
                                <div class="mb-3">
                                    <label for="admission-number" class="form-label">Admission Number</label>
                                    <input type="text" class="form-control" id="admission-number" name="admission-number" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button type="button" id="password-toggle" class="btn btn-outline-secondary">
                                            <i class="fas fa-eye" id="password-icon"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="mb-3 text-center">
                                    <button type="submit" class="btn btn-primary">Login</button>
                                </div>
                                <div class="mb-3 text-center">
                                    <a href="forgot_password.php">Forgot Password?</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include('footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loginForm = document.getElementById("login-form");
            const successMessage = document.getElementById("success-message");
            const errorMessage = document.getElementById("error-message");

            const passwordInput = document.getElementById("password");
            const passwordToggle = document.getElementById("password-toggle");
            const passwordIcon = document.getElementById("password-icon");

            passwordToggle.addEventListener("click", function() {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    passwordIcon.classList.remove("fa-eye");
                    passwordIcon.classList.add("fa-eye-slash");
                } else {
                    passwordInput.type = "password";
                    passwordIcon.classList.remove("fa-eye-slash");
                    passwordIcon.classList.add("fa-eye");
                }
            });

            loginForm.addEventListener("submit", function(event) {
                event.preventDefault();

                successMessage.style.display = "none";
                errorMessage.style.display = "none";

                const formData = new FormData(loginForm);

                fetch('login.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            successMessage.textContent = data.success;
                            successMessage.style.display = "block";

                            setTimeout(function() {
                                successMessage.style.display = "none";
                                window.location.href = "student-dashboard/index.php";
                            }, 3000);
                        } else if (data.error) {
                            errorMessage.textContent = data.error;
                            errorMessage.style.display = "block";

                            setTimeout(function() {
                                errorMessage.style.display = "none";
                            }, 3000);
                        }
                    })
                    .catch(error => {
                        console.error('An error occurred:', error);
                    });
            });
        });
    </script>
</body>

</html>