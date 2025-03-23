<?php
session_start();

// check
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: Login.html");
    exit();
}
?>