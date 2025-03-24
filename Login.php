<?php
session_start();
// Check for error messages
$error_message = "";
if(isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Clear the error after displaying
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="Login.css">
    <!-- Add Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>

    <div class="login-container">
        <h2>Login</h2>
        <?php if(!empty($error_message)): ?>
        <div class="error-message" style="color: red; margin-bottom: 15px; text-align: center;">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        <form id="login-form" action="login_process.php" method="POST">
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email">
                <p class="error-message" id="email-error">Enter a valid email (e.g., yourname@gmail.com).</p>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                    <button type="button" id="toggle-password" class="toggle-password" aria-label="Show password">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                <p class="error-message" id="password-error">Password must be at least 6 characters.</p>
            </div>

            <button type="submit" class="login-btn">Log In</button>
        </form>

        <p class="signup-link">Don't have an account? <a href="SignUp.php">Sign up</a></p>
    </div>

    <script src="Login.js"></script>
</body>
</html>