<?php
require 'includes/connection.php';
$token = $_GET["token"];
$token_hash = hash("sha256", $token);

$stmt = $conn->prepare("SELECT id, reset_token_expires_at FROM registrations WHERE reset_token_hash = :token_hash");
$stmt->bindParam(':token_hash', $token_hash);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the token is not found or has expired
if (
    !$user || !isset($user["reset_token_expires_at"]) || strtotime($user["reset_token_expires_at"]) <= time()
) {
    $tokenValid = false;
} else {
    $tokenValid = true;
    $user_id = $user["id"];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - University of Embu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>
    <?php include('header.php'); ?>

    <section class="login-form py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <?php if ($tokenValid) : ?>
                                <h2 class="card-title text-center">Reset Password</h2>
                                <p class="card-text text-center">Create New Password</p>
                                <form method="POST" id="login-form" action="process-reset-password.php">
                                    <div class="alert alert-success alert-dismissible fade show" id="success-message" style="display: none;"></div>
                                    <div class="alert alert-danger alert-dismissible fade show" id="error-message" style="display: none;"></div>
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id) ?>">
                                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password:</label>
                                        <div class="input-group">
                                            <input type="password" name="new_password" class="form-control" id="new_password" required>
                                            <button type="button" class="btn btn-outline-secondary" id="toggleNewPassword">Show</button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm New Password:</label>
                                        <div class="input-group">
                                            <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                                            <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">Show</button>
                                        </div>
                                    </div>

                                    <div class="mb-3 text-center">
                                        <button type="submit" class="btn btn-primary">Update</button>
                                    </div>
                                </form>
                            <?php else : ?>
                                <div class="alert alert-danger">
                                    <?php if (!$user) : ?>
                                        Token not found
                                    <?php elseif (!isset($user["reset_token_expires_at"])) : ?>
                                        Invalid token data. Please request another password reset.
                                    <?php else : ?>
                                        The password reset token has expired. Password reset tokens are valid for a limited time. Please request another password reset.
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
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

            loginForm.addEventListener("submit", function(event) {
                event.preventDefault();

                successMessage.style.display = "none";
                errorMessage.style.display = "none";

                const formData = new FormData(loginForm);

                fetch('process-reset-password.php', {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            successMessage.textContent = data.success;
                            successMessage.style.display = "block";

                            setTimeout(function() {
                                window.location.href = "login.php";
                            }, 5000);
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

        function togglePasswordVisibility(inputId, toggleButtonId) {
            const passwordInput = document.getElementById(inputId);
            const toggleButton = document.getElementById(toggleButtonId);

            toggleButton.addEventListener("click", function() {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    toggleButton.textContent = "Hide";
                } else {
                    passwordInput.type = "password";
                    toggleButton.textContent = "Show";
                }
            });
        }

        togglePasswordVisibility("new_password", "toggleNewPassword");
        togglePasswordVisibility("confirm_password", "toggleConfirmPassword");
    </script>
</body>

</html>