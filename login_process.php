<?php
session_start();
require_once 'db_connect.php';

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim(strtolower($_POST['email']));
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = "Please fill in all fields.";
            throw new Exception("Empty email or password.");
        }

        // Prepare statement with error handling
        // Prepare statement, to enhanced security feature, prevent SQL-Injection ' OR 1=1-- -
        // bind_param("s", $email): Binds the email to the SQL query ("s" means string).
        //execute(): Runs the query.
        //get_result(): Gets the query result.
        
        $sql = "SELECT id, username, password FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                // The fetch_assoc() / mysqli_fetch_assoc() function fetches a result row as an associative array. Note: Fieldnames returned from this function are case-sensitive.
                $user = $result->fetch_assoc();

                // Secure password verification
                // password_verify() compares the entered password with the hashed password stored in the database.
                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(true);

                // session_regenerate_id(true): Generates a new session ID to prevent session fixation attacks.
                    $_SESSION['loggedin'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];

                    // Redirect to dashboard
                    header("Location: dashboard.html");
                    exit();
                } else {
                    $_SESSION['login_error'] = "Invalid email or password.";
                    throw new Exception("Incorrect password for user: $email");
                }
            } else {
                $_SESSION['login_error'] = "Invalid email or password.";
                throw new Exception("Login attempt failed for non-existent user: $email");
            }

            $stmt->close();
        } else {
            throw new Exception("Database query preparation failed.");
        }
    }
} catch (Exception $e) {
    error_log($e->getMessage()); // Log the error for debugging
} finally {
    $conn->close();
    header("Location: Login.html");
    exit();
}
?>
