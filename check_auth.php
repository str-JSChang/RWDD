<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: Login.php");
    exit();
}
?>