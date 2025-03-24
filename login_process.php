<?php
// Enable detailed error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once 'db_connect.php';

// Clear any previous error messages
unset($_SESSION['login_error']);

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate input
        $email = trim(strtolower($_POST['email']));
        $password = $_POST['password'];

        // Input validation
        if (empty($email) || empty($password)) {
            throw new Exception("Please fill in all fields.");
        }

        // Prepare SQL statement
        $sql = "SELECT user_id, username, password FROM individual WHERE email = ?";
        
        // Prepare and execute statement
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Preparation failed: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        
        if (!$stmt->execute()) {
            throw new Exception("Execution failed: " . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // No user found with this email
            throw new Exception("Invalid email or password.");
        }

        $user = $result->fetch_assoc();

        // Verify password
        if (!password_verify($password, $user['password'])) {
            throw new Exception("Invalid email or password.");
        }

        // Successful login
        session_regenerate_id(true);
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();

    } else {
        // Not a POST request
        throw new Exception("Invalid request method.");
    }
} catch (Exception $e) {
    // Log the error
    error_log("Login Error: " . $e->getMessage());
    
    // Store error message in session
    $_SESSION['login_error'] = $e->getMessage();
    
    // Redirect back to login page
    header("Location: Login.php");
    exit();
} finally {
    // Ensure database connection is closed
    if (isset($conn)) {
        $conn->close();
    }
}
?>