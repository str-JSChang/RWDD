<?php
session_start();

// Check for error message
if(isset($_SESSION['signup_error'])) {
    $error_message = $_SESSION['signup_error'];
    // Clear the error message from the session
    unset($_SESSION['signup_error']);
} else {
    $error_message = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="SignUp.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>

    <div class="signup-container">
        <h2>Create an Account</h2>

        <?php if(!empty($error_message)): ?>
        <div class="error-alert">
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>
        
        <form id="signup-form" action="signup_process.php" method="POST">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username">
                <p class="error-message" id="username-error">Username must contain alphabets and be at least 6 characters long.</p>
            </div>
            
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email">
                <p class="error-message" id="email-error">Enter a valid email format (e.g., example@gmail.com).</p>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password">
                    <button type="button" id="toggle-password" class="toggle-password" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <p class="error-message" id="password-error">Password must be at least 6 characters long.</p>
            </div>

            <div class="input-group">
                <label for="confirm-password">Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Confirm your password">
                    <button type="button" id="toggle-confirm-password" class="toggle-password" aria-label="Show password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <p class="error-message" id="confirm-password-error">Passwords do not match.</p>
            </div>

            <button type="submit" class="signup-btn">Sign Up</button>
        </form>

        <p class="login-link">Already have an account? <a href="Login.php">Log in</a></p>
    </div>

    <script src="SignUp.js"></script>

</body>
</html>