<?php
require 'includes/connection.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST["user_id"];
    $token = $_POST["token"];

    $token_hash = hash("sha256", $token);

    $stmt = $conn->prepare("SELECT reset_token_expires_at FROM registrations WHERE reset_token_hash = :token_hash AND id = :user_id");
    $stmt->bindParam(':token_hash', $token_hash);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($user === null) {
        echo json_encode(["error" => "Token not found."]);
        exit();
    }

    if (strtotime($user["reset_token_expires_at"]) <= time()) {
        echo json_encode(["error" => "Token has expired."]);
        exit();
    }

    if (strlen($_POST["new_password"]) < 8) {
        echo json_encode(["error" => "Password must be at least 8 characters"]);
        exit();
    }

    if (!preg_match("/[a-z]/i", $_POST["new_password"])) {
        echo json_encode(["error" => "Password must contain at least one letter"]);
        exit();
    }

    if (!preg_match("/[0-9]/", $_POST["new_password"])) {
        echo json_encode(["error" => "Password must contain at least one number"]);
        exit();
    }

    if ($_POST["new_password"] !== $_POST["confirm_password"]) {
        echo json_encode(["error" => "Passwords must match."]);
        exit();
    }

    $hashed_password = password_hash($_POST["new_password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE registrations SET password = :new_password, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = :user_id");
    $stmt->bindParam(':new_password', $hashed_password);
    $stmt->bindParam(':user_id', $user_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => "Password reset successfully. You will be redirected to login page shortly."]);
        exit();
    }
    else{
        echo json_encode(["error" => "Password reset failed."]);
        exit();
    }
} else {
    echo "Invalid method request";
}
