<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require 'includes/connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admission_number = $_POST['admission-number'];

    $stmt = $conn->prepare("SELECT id, email FROM registrations WHERE admission_number = :admission_number");
    $stmt->bindParam(':admission_number', $admission_number);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = $user['id'];
        $user_email = $user['email'];

        $token = bin2hex(random_bytes(16));
        $token_hash = hash("sha256", $token);
        date_default_timezone_set('Africa/Nairobi');
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

        $stmt = $conn->prepare("UPDATE registrations SET reset_token_hash = :token_hash, reset_token_expires_at = :expiry WHERE email = :user_email AND id = :user_id");
        $stmt->bindParam(':token_hash', $token_hash);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':user_email', $user_email);
        $stmt->bindParam(':user_id', $user_id);

        if ($stmt->execute()) {
            sendResetLink($user_email, $token);
            $success_message = "Password reset link sent to your email";
        } else {
            $error_message = "Error sending email link";
        }
    } else {
        $error_message = "Admission number not found in the database.";
    }
}
function sendResetLink($email, $token)
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

    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset';
    $mail->Body = '<html><body>';
    $mail->Body .= '<p>Click <a href="https://studentms.azurewebsites.net/reset-password.php?token=' . $token . '> here </a> to reset your password.</p>';
    $mail->Body .= '</body></html>';
    try {

        $mail->send();
    } catch (Exception $e) {

        $success_message = "Message could not be sent. Mailer error: {$mail->ErrorInfo}";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - University of Embu</title>
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
                            <h2 class="card-title text-center">Forgot Password?</h2>
                            <p class="card-text text-center">The reset link will be sent to your email.</p>
                            <?php if ($error_message !== '') : ?>
                                <div class="alert alert-danger" id="error_message"><?= $error_message ?></div>
                            <?php endif; ?>

                            <?php if ($success_message !== '') : ?>
                                <div class="alert alert-success" id="success_message"><?= $success_message ?></div>
                            <?php endif; ?>
                            <form method="POST" id="login-form">
                                <div class="mb-3">
                                    <label for="admission-number" class="form-label">Admission Number</label>
                                    <input type="text" class="form-control" id="admission-number" name="admission-number" required>
                                </div>
                                <div class="mb-3 text-center">
                                    <button type="submit" class="btn btn-primary">Send</button>
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
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