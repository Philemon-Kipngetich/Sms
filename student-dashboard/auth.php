<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}
$inactiveTimeout = 900;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactiveTimeout)) {
    session_unset();
    session_destroy();    
    header("Location: ../login.php");
    exit();
}
$_SESSION['last_activity'] = time();
