<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and normalize input
    // strip_tags() removes any HTML tags, which can help prevent malicious code injection.
    // htmlspecialchars() converts special characters into HTML entities to further protect against malicious scripts.
    // filter_var(..., FILTER_SANITIZE_EMAIL) cleans the email address by removing illegal characters.
    // The password fields are retrieved without extra sanitation because they will be hashed before being stored.
    $username = htmlspecialchars(strip_tags(trim($_POST['username'])));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];
    
    // Validate input: check for empty fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['signup_error'] = "Please fill in all fields.";
        header("Location: SignUp.html");
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['signup_error'] = "Invalid email format.";
        header("Location: SignUp.html");
        exit();
    }
    
    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['signup_error'] = "Passwords do not match.";
        header("Location: SignUp.html");
        exit();
    }
    
    // Enforce a strong password (e.g., at least 8 characters, including numbers and special characters)
    if (strlen($password) < 8 || !preg_match('/[0-9]/', $password) || !preg_match('/[\W]/', $password)) {
        $_SESSION['signup_error'] = "Password must be at least 8 characters long and include a number and a special character.";
        header("Location: SignUp.html");
        exit();
    }
    
    // Check if email already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['signup_error'] = "Database error. Please try again later.";
        header("Location: SignUp.html");
        exit();
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['signup_error'] = "Email already exists.";
        header("Location: SignUp.html");
        exit();
    }
    
    // Hash password
    // bcrypt algorithm
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        $_SESSION['signup_error'] = "Database error. Please try again later.";
        header("Location: SignUp.html");
        exit();
    }
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if ($stmt->execute()) {
        $_SESSION['signup_success'] = "Registration successful! You can now login.";
        header("Location: Login.html");
        exit();
    } else {
        $_SESSION['signup_error'] = "Something went wrong. Please try again.";
        header("Location: SignUp.html");
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>
