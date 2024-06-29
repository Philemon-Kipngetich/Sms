<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require 'includes/connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
require './PHPMailer/src/Exception.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first-name'];
    $last_name = $_POST['last-name'];
    $email = $_POST['email'];
    $program = $_POST['program'];
    $admission_number = $_POST['admission-number'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $checkAdmissionStmt = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE admission_number = :admission_number");
        $checkAdmissionStmt->bindParam(':admission_number', $admission_number);
        $checkAdmissionStmt->execute();
        $admissionCount = $checkAdmissionStmt->fetchColumn();

        $checkEmailStmt = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE email = :email");
        $checkEmailStmt->bindParam(':email', $email);
        $checkEmailStmt->execute();
        $emailCount = $checkEmailStmt->fetchColumn();

        if ($admissionCount > 0 && $emailCount > 0) {
            $response = ["error" => "Both the admission number and email already exist in the database."];
        } elseif ($admissionCount > 0) {
            $response = ["error" => "The admission number already exists in the database."];
        } elseif ($emailCount > 0) {
            $response = ["error" => "The email address already exists in the database."];
        } else {
            date_default_timezone_set('Africa/Nairobi');
            $currentLocalTime = date('Y-m-d H:i:s');

            $stmt = $conn->prepare("INSERT INTO registrations (first_name, last_name, email, program, admission_number, password, registration_date)
                                    VALUES (:first_name, :last_name, :email, :program, :admission_number, :password, :registration_date)");

            // Bind parameters
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':program', $program);
            $stmt->bindParam(':admission_number', $admission_number);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':registration_date', $currentLocalTime);
            if ($stmt->execute()) {
                sendWelcomeEmail($email, $admission_number, $first_name);
                $response = ["success" => "Registration successful! You can now log in."];
            } else {
                $response = ["error" => "Registration failed. Please try again."];
            }
        }
    } catch (PDOException $e) {
        $response = ["error" => "Database error: " . $e->getMessage()];
    }

    echo json_encode($response);
    exit();
}

function sendWelcomeEmail($recipientEmail, $admissionNumber, $first_name)
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->Port = $_ENV['SMTP_PORT'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = 'tls';

    $mail->setFrom($_ENV['SMTP_USER'], 'Some College');

    $mail->addAddress($recipientEmail);

    // Email subject and body
    $mail->Subject = 'Welcome to College';
    $mail->isHTML(true);
    $mail->Body = '<p>Dear ' . $first_name .',</p>
                   <p>Welcome to the Some College!</p>
                   <p>Your admission number is: ' . $admissionNumber . '</p>
                   <p>Thank you for choosing our college. We look forward to having you as part of our community.</p>';

    try {
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email sending failed for ' . $recipientEmail . ': ' . $e->getMessage());
        return false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - University of Embu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/style.css">
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

    <?php include('header.php'); ?>

    <section class="registration-form py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title text-center">Registration</h2>
                            <p class="card-text text-center">Please fill out the form below to register for a Bachelor's degree program at our college. All fields are required.</p>
                            <div class="alert alert-success alert-dismissible fade show" id="success-message" style="display: none;"></div>
                            <div class="alert alert-danger alert-dismissible fade show" id="error-message" style="display: none;"></div>

                            <form action="application-form.php" method="POST" id="registration-form">

                                <!-- Personal Information -->
                                <div class="mb-3">
                                    <h5>Personal Information</h5>
                                </div>
                                <div class="mb-3">
                                    <label for="first-name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first-name" name="first-name" value="<?php echo isset($_POST['first-name']) ? htmlspecialchars($_POST['first-name']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="last-name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last-name" name="last-name" value="<?php echo isset($_POST['last-name']) ? htmlspecialchars($_POST['last-name']) : ''; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter valid email to receive email notifications." value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>

                                <!-- Program Selection -->
                                <div class="mb-3">
                                    <h5>Program Selection</h5>
                                </div>
                                <div class="mb-3">
                                    <label for="program" class="form-label">Program</label>
                                    <select class="form-select" id="program" name="program" required>
                                        <option value="" disabled selected>Select your program</option>
                                        <option value="IT" <?php echo isset($_POST['program']) && $_POST['program'] === 'IT' ? 'selected' : ''; ?>>Information Technology (IT)</option>
                                        <option value="CS" <?php echo isset($_POST['program']) && $_POST['program'] === 'CS' ? 'selected' : ''; ?>>Computer Science</option>
                                    </select>
                                </div>

                                <!-- Admission Number -->
                                <div class="mb-3">
                                    <label for="admission-number" class="form-label">Admission Number</label>
                                    <input type="text" class="form-control" id="admission-number" name="admission-number" value="<?php echo isset($_POST['admission-number']) ? htmlspecialchars($_POST['admission-number']) : ''; ?>" required readonly>
                                </div>

                                <!-- Password -->
                                <div class="mb-3">
                                    <h5>Password</h5>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Create Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button type="button" id="password-toggle" class="btn btn-outline-secondary">
                                            <i class="fas fa-eye" id="password-icon"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="mb-3 text-center">
                                    <button type="submit" class="btn btn-primary">Register</button>
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
    <script src="js/main.js"></script>
    <script>
        // Function to show the spinner
        function showSpinner() {
            document.getElementById("spinner").style.display = "block";
        }

        // Function to hide the spinner
        function hideSpinner() {
            document.getElementById("spinner").style.display = "none";
        }

        document.addEventListener("DOMContentLoaded", function() {
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

            function showSuccess(message) {
                successMessage.textContent = message;
                successMessage.style.display = "block";
                window.scrollTo(0, 0);
                setTimeout(function() {
                    successMessage.style.display = "none";
                    window.location.href = "login.php";
                }, 3000);
            }

            function showError(message) {
                errorMessage.textContent = message;
                errorMessage.style.display = "block";
                window.scrollTo(0, 0);
                setTimeout(function() {
                    errorMessage.style.display = "none";
                }, 5000);
            }

            const registrationForm = document.getElementById("registration-form");
            registrationForm.addEventListener("submit", function(event) {
                event.preventDefault();

                // Show the spinner when the request starts
                showSpinner();

                const formData = new FormData(registrationForm);

                fetch('application-form.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccess(data.success);
                            registrationForm.reset();
                        } else if (data.error) {
                            showError(data.error);
                        }
                    })
                    .catch(error => {
                        showError('An error occurred. Please try again later.');
                    })
                    .finally(() => {
                        // Hide the spinner when the request completes (whether success or error)
                        hideSpinner();
                    });
            });
        });
    </script>
</body>

</html>