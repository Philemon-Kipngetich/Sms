<?php
session_start();
if (!isset($_SESSION["admin_id"]) && !isset($_SESSION['admin'])) {
    header("Location: ../admin.php");
    exit();
}
$inactiveTimeout = 900;
if (isset($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity'] > $inactiveTimeout)) {
    session_unset();
    session_destroy();
    header("Location: ../admin.php");
    exit();
}
$_SESSION['admin_last_activity'] = time();
