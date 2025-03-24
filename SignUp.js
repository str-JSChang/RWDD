document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById("signup-form");
    const usernameInput = document.getElementById("username");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const confirmPasswordInput = document.getElementById("confirm-password");
    
    // Password toggle functionality for both password fields
    function setupPasswordToggle(passwordField, toggleButton) {
        if (toggleButton) {
            toggleButton.addEventListener('click', function() {
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    this.setAttribute('aria-label', 'Hide password');
                } else {
                    passwordField.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                    this.setAttribute('aria-label', 'Show password');
                }
            });
        }
    }

    // Setup toggle for password fields
    setupPasswordToggle(
        passwordInput, 
        document.getElementById("toggle-password")
    );
    setupPasswordToggle(
        confirmPasswordInput, 
        document.getElementById("toggle-confirm-password")
    );

    // Form submission event listener
    signupForm.addEventListener("submit", function(event) {
        let isValid = true;

        // Username validation (must contain alphabets and be at least 6 characters)
        const username = usernameInput.value.trim();
        const usernameRegex = /^(?=.*[a-zA-Z])[a-zA-Z0-9]{6,}$/;
        const usernameErrorElement = document.getElementById("username-error");
        if (!usernameRegex.test(username)) {
            usernameErrorElement.textContent = "Username must contain letters and be at least 6 characters.";
            usernameErrorElement.style.display = "block";
            isValid = false;
        } else {
            usernameErrorElement.style.display = "none";
        }

        // Email validation (must be in proper format)
        const email = emailInput.value.trim();
        const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        const emailErrorElement = document.getElementById("email-error");
        if (!emailRegex.test(email)) {
            emailErrorElement.textContent = "Enter a valid email format (e.g., example@gmail.com).";
            emailErrorElement.style.display = "block";
            isValid = false;
        } else {
            emailErrorElement.style.display = "none";
        }

        // Password validation (minimum 6 characters)
        const password = passwordInput.value.trim();
        const passwordErrorElement = document.getElementById("password-error");
        if (password.length < 6) {
            passwordErrorElement.textContent = "Password must be at least 6 characters.";
            passwordErrorElement.style.display = "block";
            isValid = false;
        } else {
            passwordErrorElement.style.display = "none";
        }

        // Confirm password validation (must match password)
        const confirmPassword = confirmPasswordInput.value.trim();
        const confirmPasswordErrorElement = document.getElementById("confirm-password-error");
        if (confirmPassword !== password || confirmPassword === "") {
            confirmPasswordErrorElement.textContent = "Passwords do not match.";
            confirmPasswordErrorElement.style.display = "block";
            isValid = false;
        } else {
            confirmPasswordErrorElement.style.display = "none";
        }

        // Prevent form submission if validation fails
        if (!isValid) {
            event.preventDefault();
        }
    });
});